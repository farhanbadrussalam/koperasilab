<div class="modal fade" id="create-surat">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Buat tugas</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-2 fw-bolder">Nama LAB</div>
                    <div class="col-8">: <span id="txtNamaLAB">LAB. Lingkungan</span></div>
                </div>
                <div class="row">
                    <div class="col-2 fw-bolder">Tugas</div>
                    <div class="col-8">: <span id="txtTugas">Uji kebocoran sumber radioaktif</span></div>
                </div>
                <div class="row">
                    <div class="col-2 fw-bolder">Customer</div>
                    <div class="col-8">: <span id="txtCustomer">Kadeem Bradley</span></div>
                </div>
                <div class="row">
                    <div class="col-2 fw-bolder">Jumlah</div>
                    <div class="col-8">: <span id="txtJumlah">20</span></div>
                </div>
                <div class="row">
                    <div class="col-2 fw-bolder">Tanggal</div>
                    <div class="col-8">: <span id="txtTanggal">31 January 2023 - 1 February 2023</span></div>
                </div>
                <h4 class="mt-2 border-bottom pb-2 text-end">List petugas</h4>
                <div class="row mt-3">
                    <div class="col-md-5">
                        <input type="text" name="" id="namaPetugas" class="form-control"
                            placeholder="Pilih petugas">
                    </div>
                    <div class="col-md-5">
                        <input type="text" name="" id="tugas" class="form-control" placeholder="Tugas">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-primary mb-3">Tambah</button>
                    </div>
                </div>
                {{-- <div class="my-2 w-100">
                    <div class="text-center h3">Belum ada petugas</div>
                </div> --}}
                <div class="d-flex flex-wrap">
                    <div class="col-md-12 mb-1">
                        <div class="d-flex align-items-center px-3 shadow-sm cursoron document border">
                            <div>
                                <img class="my-3 img-fluid img-circle" src="#" alt="" onerror="this.src=`{{ asset('assets/img/default-avatar.jpg') }}`" style="width: 3em;">
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <div class="d-flex flex-column">
                                    <div class="text-main">Nama petugas</div>
                                    <span class="text-submain text-secondary">Tugas yang diberikan</span>
                                </div>
                            </div>
                            <div class="p-1">
                                <button class="btn btn-sm btn-outline-danger" title="Hapus"><i
                                        class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 mb-1">
                        <div class="d-flex align-items-center px-3 shadow-sm cursoron document border">
                            <div>
                                <img class="my-3 img-fluid img-circle" src="#" alt="" onerror="this.src=`{{ asset('assets/img/default-avatar.jpg') }}`" style="width: 3em;">
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <div class="d-flex flex-column">
                                    <div class="text-main">Nama petugas</div>
                                    <span class="text-submain text-secondary">Tugas yang diberikan</span>
                                </div>
                            </div>
                            <div class="p-1">
                                <button class="btn btn-sm btn-outline-danger" title="Hapus"><i
                                        class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-success">Kirim tugas <i class="bi bi-send"></i></button>
            </div>
        </div>
    </div>
</div>
