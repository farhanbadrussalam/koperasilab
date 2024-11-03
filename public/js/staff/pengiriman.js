let nowTab = 1;
$(function () {
    switchLoadTab(1);
});

function switchLoadTab(menu){
    nowTab = menu;
    switch (menu) {
        case 1:
            menu = 'list';
            break;
    
        case 2:
            menu = 'selesai';
            break;
    }

    loadData(1, menu);
}

function loadData(page = 1, menu) {
    let params = {
        limit: 10,
        page: page,
        menu: menu
    };

    $(`#list-placeholder-${menu}`).show();
    $(`#list-container-${menu}`).hide();
    ajaxGet(`api/v1/pengiriman/list`, params, result => {
        console.log(result);
        let html = '';
        if(result.data.length == 0){
            html = `
                <div class="d-flex flex-column align-items-center py-3">
                    <img src="${base_url}/images/no_data2_color.svg" style="width:220px" alt="">
                    <span class="fw-bold mt-3 text-muted">No Data Available</span>
                </div>
            `;
        }

        $(`#list-container-${menu}`).html(html);

        $(`#list-pagination-${menu}`).html(createPaginationHTML(result.pagination));

        $(`#list-placeholder-${menu}`).hide();
        $(`#list-container-${menu}`).show();
    }, error => {
        const result = error.responseJSON;
        if(result.meta?.code && result.meta.code == 500){
            Swal.fire({
                icon: "error",
                text: 'Server error',
            });
            console.error(result.data.msg);
        }else{
            Swal.fire({
                icon: "error",
                text: 'Server error',
            });
            console.error(result.message);
        }
    });
}