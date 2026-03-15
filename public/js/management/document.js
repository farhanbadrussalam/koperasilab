$(document).ready(function() {
    load_document("header");
    load_document('body');
    load_document("footer");
});

function load_document(jenis, page = 1){
    ajaxGet(`management/document/load`, {page: page, limit: 10, jenis: jenis}, result => {
        let html = createTabel(result.data, jenis);
        if(jenis == "header"){
            $('#list-header').html(html);
        } else if(jenis == "body"){
            $('#list-body').html(html);
        } else {
            $('#list-footer').html(html);
        }
    }, err => {
        console.error(err);
    });
}

function createTabel(data, jenis){
    let html = '';
    for (const [index,value] of data.entries()) {
        let urlEdit = `${base_url}/management/document/${value.doc_hash}/edit?type=${jenis}`;
        let btnDelete = jenis != "body" ? `<button class="btn btn-sm btn-danger" onclick="deleteTabel('${value.doc_hash}')"><i class="bi bi-trash"></i></button>` : '';
        html += `
            <tr>
                <td>${index+1}</td>
                <td>${value.name}</td>
                <td class="text-end">
                    <a href="${urlEdit}" class="btn btn-sm btn-warning"><i class="bi bi-pencil-square"></i></a>
                    ${btnDelete}
                </td>
            </tr>
        `;
    }
    if(data.length == 0){
        html = `
            <tr>
                <td colspan="3" class="text-center">Tidak ada ${jenis}</td>
            </tr>
        `;
    }
    return html;
}

function deleteTabel(hash){
    ajaxDelete(`management/document/${hash}`, result => {
        Swal.fire({
            icon: 'success',
            text: result.data.msg,
            timer: 1200,
            timerProgressBar: true,
            showConfirmButton: false
        }).then(() => {
            load_document("header");
            load_document("footer");
        })
    }, err => {
        console.error(err);
    });
}
