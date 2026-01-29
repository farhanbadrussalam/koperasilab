<div class="modal fade" id="modal-add-tld-pengguna" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0 d-block">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="modal-title fw-bold">Tambahkan Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="position-relative mb-3">
                    <span class="position-absolute top-50 start-0 translate-middle-y ms-3 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="customSearch" class="form-control form-control-lg ps-5 bg-light border-0"
                        placeholder="Cari nama atau ID karyawan...">
                </div>
            </div>
            <div class="modal-body p-0 custom-scrollbar vh-100" style="max-height: 400px; overflow-y: auto;">

                <div class="p-2">
                    <table id="table-user" class="table table-borderless w-100 align-middle">
                        <thead class="d-none"> <tr>
                                <th>User Data</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="text-center py-5 d-none" id="emptyState">
                    <i class="bi bi-person-x text-muted opacity-25" style="font-size: 3rem;"></i>
                    <p class="text-muted small mt-2">Pengguna tidak ditemukan</p>
                </div>
            </div>
            <div class="modal-footer border-top-0 justify-content-between bg-white py-3 rounded-bottom-4">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-toggle="modal" data-bs-target="#modal-add-pengguna">
                    <i class="bi bi-person-plus"></i> Tambah Pengguna
                </button>
                <div id="customPagination"></div>
            </div>
        </div>
    </div>
</div>

@include('pages.management.pengguna.create')

<script>
    let datatable_pengguna = false;

    $(function() {
        datatable_pengguna = $('#table-user').DataTable({
            dom: 'rt<"p-0"p>',
            processing: true,
            serverSide: true,
            ajax: {
                url: `${base_url}/management/getDataPengguna`,
                data: {
                    type: 'selected',
                    filter : {
                        name: $('#customSearch').val()
                    }
                }
            },
            bLengthChange: false,
            bFilter: true,bInfo: false,ordering: false,
            columns: [
                { data: 'html',orderable: false },
            ],
            pageLength: 5,
            // D. Event setelah draw (untuk mindahin pagination & handle empty state)
            drawCallback: function(settings) {
                // 1. Pindahkan Pagination ke Footer Modal
                var pagination = $(this).closest('.dataTables_wrapper').find('.dataTables_paginate');
                $('#customPagination').html(pagination);

                // 2. Cek Empty State
                if (settings.aoData.length === 0) {
                    $('#emptyState').removeClass('d-none'); // Munculkan gambar kosong
                    $(this).hide(); // Sembunyikan tabel
                } else {
                    $('#emptyState').addClass('d-none');
                    $(this).show();
                }

                showPopupReload();
            }
        })

        $('#btn-add-pengguna').on('click', () => {
            datatable_pengguna.ajax.reload();
            $('#modal-add-tld-pengguna').modal('show');
        });

        $('#customSearch').on('keyup', reload);
    })
    function btnPilih(obj){
        document.dispatchEvent(new CustomEvent("pengguna.pilih", {
            detail: obj
        }));
    }

    function reload(){
        datatable_pengguna.settings()[0].ajax.data.filter.name = $('#customSearch').val();
        datatable_pengguna.ajax.reload();
    }
</script>
