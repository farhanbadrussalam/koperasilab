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
        
        // Infinite scroll pagination state variables
        this.currentPage = 1;
        this.isLoading = false;
        this.hasMore = true;
        this.currentType = 'all';

        this.loadHtml();
    }

    __toSocket() {
        this.dataChannel = window.Echo?.private(this.channel).notification((notification) => {
            this.showNotif(notification);
        });
    }

    _bindEventListeners() {
        $('#markAllAsRead').on('click', this.markAllAsRead.bind(this));

        $("#body-notif").on('click', '.list-group-item', this.selectNotif.bind(this));
        $("#all-notif").on('click', this.loadNotifikasiList.bind(this));
        $("#unread-notif").on('click', this.loadNotifikasiList.bind(this));

        $("#setting-notif").on('click', this.showSetting.bind(this));
        $('#realtimeSwitch').on('change', this.realtimeSwitch.bind(this));

        // Bind scroll listener on the scrollable body of notifications
        $('#body-notif').on('scroll', this.handleScroll.bind(this));

        $('#container-notifikasi').on('click', function(e) {
            e.stopPropagation();
        });
    }

    handleScroll(e) {
        const container = $(e.target);
        // If we scrolled to the bottom (within 20px of the bottom scroll height)
        if (container.scrollTop() + container.innerHeight() >= container[0].scrollHeight - 20) {
            if (this.hasMore && !this.isLoading) {
                this.loadNotifikasiList();
            }
        }
    }

    showNotif(info) {
        toastr.info(info.pesan, 'Notifikasi');
        
        let path = info.url || '';
        if (path && !path.startsWith('/')) {
            path = '/' + path;
        }
        let url = base_url + path;
        let html = '';
        let createdAt = new Date().toISOString();
        let notifId = info.id || '';

        if (info.event === 'Permohonan') {
            let perusahaan = info.perusahaan_id?.split('|') || [];
            let text = perusahaan.length > 1 ? `<div class="fw-bold text-dark">${perusahaan[1]}</div>` : '';
            html = `
                <li class="cursor-pointer list-group-item fs-8 list-group-item-action list-group-item-active" data-id="${notifId}">
                    <a href="${url}" class="d-flex align-items-center justify-content-between">
                        <div class="col-10">
                            ${text}
                            <div class="text-muted">${info.pesan}</div>
                            <div class="text-muted fs-9">${diffToday(createdAt)}</div>
                        </div>
                        <div>
                            <div class="rounded-circle bg-info " style="width: 10px; height: 10px;">&nbsp;</div>
                        </div>
                    </a>
                </li>
            `;
        } else {
            html = `
                <li class="cursor-pointer list-group-item fs-8 list-group-item-action list-group-item-active" data-id="${notifId}">
                    <a href="${url}" class="d-flex align-items-center justify-content-between">
                        <div class="col-10">
                            <div class="text-muted">${info.pesan}</div>
                            <div class="text-muted fs-9">${diffToday(createdAt)}</div>
                        </div>
                        <div>
                            <div class="rounded-circle bg-info " style="width: 10px; height: 10px;">&nbsp;</div>
                        </div>
                    </a>
                </li>
            `;
        }

        let bodyNotif = $('#body-notif');
        if (bodyNotif.find('.list-group-item-action').length === 0) {
            bodyNotif.html(html);
        } else {
            bodyNotif.prepend(html);
        }

        let countBadge = $('#count_lonceng');
        let currentCount = parseInt(countBadge.text()) || 0;
        currentCount++;
        countBadge.addClass('d-block').removeClass('d-none').text(currentCount > 99 ? '99+' : currentCount);

        // Update sidebar badges based on real-time event
        if (info.event) {
            $('.sidebar-notif-badge').each(function() {
                let events = $(this).data('events');
                if (events && Array.isArray(events) && events.includes(info.event)) {
                    let badgeCount = parseInt($(this).text()) || 0;
                    $(this).text(badgeCount + 1).removeClass('d-none');
                }
            });
        }
    }

    loadHtml(){
        const container = document.getElementById(this.idElement);
        container.innerHTML = `
            <a href="#" class="text-secondary position-relative d-flex align-items-center justify-content-center p-2 rounded-circle hover-bg-light transition-all"
                id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="width: 40px; height: 40px;">
                <i class="bi bi-bell-fill fs-5"></i>
                <span class="position-absolute translate-middle badge rounded-pill bg-danger" style="font-size: 10px;top: 20%;right: -5px;" id="count_lonceng"></span>
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
                        <div class="notif-option" id="setting-notif" name="select-notif">
                            <i class="bi bi-gear"></i>
                        </div>
                    </div>
                </div>
                <div class="dropdown-divider my-0"></div>
                <div id="spinner-notif" class="text-center"></div>
                <ul id="body-notif" class="list-group list-group-flush overflow-auto mb-2" style="max-height: 400px;"></ul>
                <div id="body-setting" class="p-3 d-none" style="min-width: 260px;">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="realtimeSwitch"
                            ${userActive.realtime_notifications == 1 ? 'checked' : ''}>
                        <label class="form-check-label" for="realtimeSwitch">Aktifkan notifikasi real-time</label>
                    </div>
                    <small class="text-muted d-block mt-2 fs-8">Jika nonaktif, notifikasi akan tersimpan di database dan tampil saat refresh / buka halaman.</small>
                </div>
            </div>
        `;
    }

    loadNotifikasiList(d = null) {
        if(d) {
            this.currentType = d.target.dataset.type || 'all';
            
            // Reset pagination state when changing filters
            this.currentPage = 1;
            this.hasMore = true;
            this.isLoading = false;

            $('[name="select-notif"]').removeClass('active');
            $(d.target).addClass('active');
            $('#body-setting').addClass('d-none');
            $('#body-notif').removeClass('d-none');
        }

        if (this.isLoading || !this.hasMore) {
            return;
        }

        this.isLoading = true;

        if (this.currentPage === 1) {
            $('#body-notif').html('');
            spinner('show', $('#spinner-notif'), {
                width: '50px',
                height: '50px',
                margin: '10px',
                place: 'after'
            });
            $('#spinner-notif').show();
        } else {
            // Append small inline spinner at the bottom of the list for page > 1 loading feedback
            $('#body-notif').append(`
                <li id="mini-spinner" class="list-group-item text-center py-2 border-0 bg-transparent">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                </li>
            `);
            // Auto scroll slightly to ensure loading indicator is visible
            let bodyNotif = $('#body-notif');
            bodyNotif.scrollTop(bodyNotif[0].scrollHeight);
        }

        ajaxGet(`getNotif`, { type: this.currentType, page: this.currentPage, limit: 10 }, result => {
            this.isLoading = false;
            $('#mini-spinner').remove();

            if(result.meta.code == 200){
                if(result.data.unreadCount > 0){
                    $('#count_lonceng').addClass('d-block').removeClass('d-none');
                    $('#count_lonceng').html(result.data.unreadCount);
                } else {
                    $('#count_lonceng').addClass('d-none').removeClass('d-block');
                }

                let html = '';
                for (const notif of result.data.list) {
                    let path = notif.data.url || '';
                    if (path && !path.startsWith('/')) {
                        path = '/' + path;
                    }
                    let url = base_url + path;
                    switch (notif.data.event) {
                        case 'Permohonan':
                            let perusahaan = notif.data.perusahaan_id?.split('|') || [];

                            let text = perusahaan.length > 1 ? `<div class="fw-bold text-dark">${perusahaan[1]}</div>` : '';
                            html += `
                                <li class="cursor-pointer list-group-item fs-8 list-group-item-action ${!notif.read_at ? 'list-group-item-active' : ''}" data-id="${notif.id}">
                                    <a href="${url}" class="d-flex align-items-center justify-content-between">
                                        <div class="col-10">
                                            ${text}
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

                if(this.currentPage === 1 && result.data.list.length == 0){
                    html = `<div class="text-center py-3">No data notifications</div>`;
                }

                if (this.currentPage === 1) {
                    spinner('hide', $('#spinner-notif'));
                    $('#body-notif').html(html);
                } else {
                    $('#body-notif').append(html);
                }

                // Check if we have more notifications
                this.hasMore = result.data.hasMore;
                if (result.data.list.length > 0) {
                    this.currentPage++;
                }
            }
        }, error => {
            this.isLoading = false;
            $('#mini-spinner').remove();
            if (this.currentPage === 1) {
                spinner('hide', $('#spinner-notif'));
                $('#body-notif').html('<div class="text-center py-3">No data notifications</div>');
            }
        });
    }

    markAllAsRead(){
        ajaxGet(`markAllAsRead`, false, result => {
            if(result.meta.code == 200){
                // Reset pagination and reload
                this.currentPage = 1;
                this.hasMore = true;
                this.isLoading = false;
                this.loadNotifikasiList();
            }
        });
    }

    selectNotif(e){
        e.preventDefault();
        let listItem = $(e.currentTarget);
        let notifId = listItem.data('id');
        let href = listItem.find('a').attr('href');

        if (!notifId) {
            if (href) window.location.href = href;
            return;
        }

        if (!listItem.hasClass('list-group-item-active')) {
            if (href) window.location.href = href;
            return;
        }

        ajaxGet(`readNotif`, { id: notifId }, result => {
            if (href) window.location.href = href;
        }, error => {
            if (href) window.location.href = href;
        });
    }

    showSetting(){
        $('#body-setting').removeClass('d-none');
        $('#body-notif').addClass('d-none');
        $('[name="select-notif"]').removeClass('active');
        $('#setting-notif').addClass('active');
    }

    realtimeSwitch(e) {
        let checked = $(e.target).is(":checked");
        let formData = new FormData();
        formData.append('realtime', checked ? 1 : 0);
        ajaxPost(`settings/notifications/realtime`, formData, result => {
            if(result.meta.code == 200){
                Swal.fire({
                    icon: 'success',
                    text: result.data.message,
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            }
        });
    }
}
