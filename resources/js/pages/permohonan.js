import {
    base_url, formatRupiah, createPaginationHTML,
    maskReload, formatSelect2Staff, ajaxPost, unmask, ajaxDelete,
    ajaxGet, role, permission, permissionInRole
} from "../global";
import { modalConfirm, printMedia } from "./component/modal";

document.addEventListener('DOMContentLoaded', function () {
    // Initialisasi
    let idPermohonan = false;
    let datatable_permohonan = false;
    const dt_pengajuan = $('#pengajuan-table').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        ordering: false,
        lengthChange: false,
        infoCallback: function( settings, start, end, max, total, pre ) {
            var api = this.api();
            var pageInfo = api.page.info();

            return 'Page '+ (pageInfo.page+1) +' of '+ pageInfo.pages;
        },
        ajax: {
            url: `${base_url}/getDataPermohonan`,
            data: function(d) {
                d.status = 1
            }
        },
        columns: [
            { data: 'content', name: 'content', orderable: false, searchable: false}
        ]
    });

    const dt_disetujui = $('#disetujui-table').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        ordering: false,
        lengthChange: false,
        infoCallback: function( settings, start, end, max, total, pre ) {
            var api = this.api();
            var pageInfo = api.page.info();

            return 'Page '+ (pageInfo.page+1) +' of '+ pageInfo.pages;
        },
        ajax: {
            url: `${base_url}/getDataPermohonan`,
            data: function(d) {
                d.status = 2
            }
        },
        columns: [
            { data: 'content', name: 'content', orderable: false, searchable: false}
        ]
    });

    const dt_pembayaran = $('#pembayaran-table').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        ordering: false,
        lengthChange: false,
        infoCallback: function( settings, start, end, max, total, pre ) {
            var api = this.api();
            var pageInfo = api.page.info();

            return 'Page '+ (pageInfo.page+1) +' of '+ pageInfo.pages;
        },
        ajax: {
            url: `${base_url}/getDataPermohonan`,
            data: function(d) {
                d.status = 3
            }
        },
        columns: [
            { data: 'content', name: 'content', orderable: false, searchable: false}
        ]
    });

    const dt_return = $('#dikembalikan-table').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        ordering: false,
        lengthChange: false,
        infoCallback: function( settings, start, end, max, total, pre ) {
            var api = this.api();
            var pageInfo = api.page.info();

            return 'Page '+ (pageInfo.page+1) +' of '+ pageInfo.pages;
        },
        ajax: {
            url: `${base_url}/getDataPermohonan`,
            data: function(d) {
                d.status = 9
            }
        },
        columns: [
            { data: 'content', name: 'content', orderable: false, searchable: false}
        ]
    });
    // METHOD
    function reloadTable(index) {
        switch (index) {
            case 1:
                dt_pengajuan?.ajax.reload();
                break;
            case 2:
                dt_disetujui?.ajax.reload();
                break;
            case 3:
                dt_pembayaran?.ajax.reload();
                break;
            case 4:
                dt_return?.ajax.reload();
                break;
        }
    }

    // EVENT
    $("#pengajuan-tab").on('click', () => {reloadTable(1)})
    $("#disetujui-tab").on('click', () => {reloadTable(2)})
    $("#pembayaran-tab").on('click', () => {reloadTable(3)})
    $("#dikembalikan-tab").on('click', () => {reloadTable(4)})

    $("button").click((obj) => {
        console.log(obj)
    })
})
