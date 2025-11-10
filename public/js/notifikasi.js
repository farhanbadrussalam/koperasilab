class WidgetNotifikasi {
    constructor(idElement, data) {
        this._init_(idElement, data);
        this.__toSocket();
        this._bindEventListeners();
        this.loadNotifikasiList();
    }

    _init_(idElement, data) {
        this.channel = data.channel;
        this.id = data.id;
        this.idElement = idElement;
        this.loadHtml();
    }

    __toSocket() {
        this.dataChannel = window.Echo?.private(this.channel).notification((notification) => {
            this.showNotif(notification);
        });
    }

    _bindEventListeners() {
        $('#markAllAsRead').on('click', this.markAllAsRead.bind(this));

        $("#list-notif-card").on('click', this.selectNotif.bind(this));
        $("#all-notif").on('click', this.loadNotifikasiList.bind(this));
        $("#unread-notif").on('click', this.loadNotifikasiList.bind(this));
    }

    showNotif(info) {
        toastr.info(info.pesan, 'Notifikasi');
        this.loadNotifikasiList();
    }

    loadHtml(){
        const container = document.getElementById(this.idElement);
        container.innerHTML = `
            <a id="navbarNotif" class="nav-link position-relative" data-bs-toggle="dropdown" role="button" href="#" data-bs-auto-close="outside">
                <i class="bi bi-bell-fill fs-4"></i>
                <span class="position-absolute translate-middle badge rounded-pill bg-danger" style="font-size: 10px; top: 35%; right: 0px;" id="count_lonceng"></span>
            </a>
            <div id="container-notifikasi"
                class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up position-absolute border-0 rounded-3 shadow py-0"
                style="width: 350px">
                <div class="d-flex justify-content-between align-items-center p-2">
                    <div class="fw-bold">Notifikasi</div>
                    <span class="text-muted fs-8 notif-option" id="markAllAsRead">Tandai semua</span>
                </div>
                <div class="d-flex justify-content-between align-items-center p-1 pt-0">
                    <div class="d-flex gap-1">
                        <div class="notif-option active" name="select-notif" id="all-notif" data-type="all">Semua</div>
                        <div class="notif-option" name="select-notif" id="unread-notif" data-type="unread">Belum dibaca</div>
                    </div>
                    <div class="d-flex gap-1">
                        <div class="notif-option"><i class="bi bi-gear"></i></div>
                    </div>
                </div>
                <div class="dropdown-divider my-0"></div>
                <div id="spinner-notif" class="text-center"></div>
                <ul id="body-notif" class="list-group list-group-flush overflow-auto mb-2" style="max-height: 400px;"></ul>
            </div>
        `;
    }

    loadNotifikasiList(d = null) {
        let type = "";
        if(d) {
            type = d.target.dataset.type;

            $('[name="select-notif"]').removeClass('active');
            $(d.target).addClass('active');
        }
        spinner('show', $('#spinner-notif'), {
            width: '50px',
            height: '50px',
            margin: '10px',
            place: 'after'
        });
        $('#spinner-notif').show();
        $('#body-notif').html('');
        ajaxGet(`getNotif`, {type : type}, result => {
            if(result.meta.code == 200){
                if(result.data.unreadCount > 0){
                    $('#count_lonceng').addClass('d-block').removeClass('d-none');
                    $('#count_lonceng').html(result.data.unreadCount);
                } else {
                    $('#count_lonceng').addClass('d-none').removeClass('d-block');
                }

                let html = '';
                for (const notif of result.data.list) {
                    let url = base_url + notif.data.url;
                    switch (notif.data.event) {
                        case 'Permohonan':
                            let perusahaan = notif.data.perusahaan_id.split('|');
                            html += `
                                <li class="cursor-pointer list-group-item fs-8 list-group-item-action ${!notif.read_at ? 'list-group-item-active' : ''}" data-id="${notif.id}">
                                    <a href="${url}" class="d-flex align-items-center justify-content-between">
                                        <div class="col-10">
                                            <div class="fw-bold text-dark">${perusahaan[1]}</div>
                                            <div class="text-muted">${notif.data.pesan}</div>
                                            <div class="text-muted fs-9">${diffToday(notif.created_at)}</div>
                                        </div>
                                        <div>
                                            ${!notif.read_at ? `<div class="rounded-circle bg-info " style="width: 10px; height: 10px;">&nbsp;</div>` : ''}
                                        </div>
                                    </a>
                                </li>
                            `
                            break;
                        default:
                            html += `
                                <li class="cursor-pointer list-group-item fs-8 list-group-item-action ${!notif.read_at ? 'list-group-item-active' : ''}" data-id="${notif.id}">
                                    <a href="${url}" class="d-flex align-items-center justify-content-between">
                                        <div class="col-10">
                                            <div class="text-muted">${notif.data.pesan}</div>
                                            <div class="text-muted fs-9">${diffToday(notif.created_at)}</div>
                                        </div>
                                        <div>
                                            ${!notif.read_at ? `<div class="rounded-circle bg-info " style="width: 10px; height: 10px;">&nbsp;</div>` : ''}
                                        </div>
                                    </a>
                                </li>
                            `;
                            break;
                    }
                }

                if(result.data.list.length == 0){
                    html = `<div class="text-center py-3">No data notifications</div>`;
                }
                spinner('hide', $('#spinner-notif'));
                $('#body-notif').html(html);
            }
        }, error => {
            spinner('hide', $('#spinner-notif'));
            $('#body-notif').html('<div class="text-center py-3">No data notifications</div>');
        })
    }

    markAllAsRead(){
        ajaxGet(`markAllAsRead`, false, result => {
            if(result.meta.code == 200){
                this.loadNotifikasiList();
            }
        });
    }

    selectNotif(e){
        console.log(e);
    }
}
