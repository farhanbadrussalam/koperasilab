$(document).ready(function () {
    load_document(5, "header");
    load_document(5, 'body');
    load_document(5, "footer");

    $('#pagination-body').on('click', 'a', function (e) {
        e.preventDefault();
        const pageno = e.target.dataset.page;

        load_document(5, 'body', pageno);
    });

    $('#pagination-header').on('click', 'a', function (e) {
        e.preventDefault();
        const pageno = e.target.dataset.page;

        load_document(5, "header", pageno);
    });

    $('#pagination-footer').on('click', 'a', function (e) {
        e.preventDefault();
        const pageno = e.target.dataset.page;

        load_document(5, "footer", pageno);
    });
});

function load_document(limit = 10, jenis, page = 1) {
    // loop placeholder
    let placeholder = '';
    for (let i = 0; i < limit; i++) {
        placeholder += `
            <tr class="placeholder-glow">
                <td><span class="placeholder col-12"></span></td>
                <td><span class="placeholder col-8"></span></td>
                <td><span class="placeholder col-12"></span></td>
            </tr>
        `;
    }

    if (jenis == "header") {
        $('#list-header').html(placeholder);
    } else if (jenis == "body") {
        $('#list-body').html(placeholder);
    } else {
        $('#list-footer').html(placeholder);
    }

    ajaxGet(`management/document/load`, { page: page, limit: limit, jenis: jenis }, result => {
        let html = createTabel(result.data, jenis);
        if (jenis == "header") {
            $('#list-header').html(html);
            $('#pagination-header').html(createPaginationHTML(result.pagination));
        } else if (jenis == "body") {
            $('#list-body').html(html);
            $('#pagination-body').html(createPaginationHTML(result.pagination));
        } else {
            $('#list-footer').html(html);
            $('#pagination-footer').html(createPaginationHTML(result.pagination));
        }
    }, err => {
        console.error(err);
    });
}

function createTabel(data, jenis) {
    let html = '';
    for (const [index, value] of data.entries()) {
        let urlEdit = `${base_url}/management/document/${value.doc_hash}/edit?type=${jenis}`;
        let btnDelete = jenis != "body" ? `<button class="btn btn-sm btn-danger" onclick="deleteTabel('${value.doc_hash}')"><i class="bi bi-trash"></i></button>` : '';
        html += `
            <tr>
                <td>${index + 1}</td>
                <td>${value.name}</td>
                <td class="text-end">
                    <a href="${urlEdit}" class="btn btn-sm btn-warning"><i class="bi bi-pencil-square"></i></a>
                    ${btnDelete}
                </td>
            </tr>
        `;
    }
    if (data.length == 0) {
        html = `
            <tr>
                <td colspan="3" class="text-center">Tidak ada ${jenis}</td>
            </tr>
        `;
    }
    return html;
}

function deleteTabel(hash) {
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
