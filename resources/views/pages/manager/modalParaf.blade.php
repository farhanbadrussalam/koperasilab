<div class="modal fade" id="modal-lhu">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Document LHU</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="content-pertanyaan" class="row"></div>
                <div class="d-flex justify-content-between m-2 mt-4 flex-wrap text-center">
                    <input type="hidden" id="idLhu">
                    <div class="row w-50">
                        <div class="wrapper">
                            <img src="#" width="200" height="114" class="rounded border p-0" alt="ttd penyelia lab" id="ttd_penyelialab">
                            <p class="mt-2">Pelaksana LAB</p>
                        </div>
                    </div>
                    <div class="row w-50">
                        <div class="wrapper">
                            <img src="#" width="200" height="114" class="rounded border p-0" alt="ttd pelaksana lab" id="ttd_pelaksanalab">
                            <p class="mt-2">Penyelia LAB</p>
                        </div>
                    </div>
                    <div class="row w-100">
                        <div class="wrapper" id="content-ttd">
                            <button class="btn btn-danger btn-sm position-absolute ms-1 mt-1" id="signature-clear-lhu"><i class="bi bi-trash"></i></button>
                            <canvas id="signature-lhu" class="signature-pad border border-success-subtle rounded border-2" width=200 height=110></canvas>
                            <p>Manager</p>
                        </div>
                        <div class="wrapper" id="content-show">
                            <img src="#" width="200" height="114" class="rounded border p-0" alt="ttd manager" id="ttd_manager">
                            <p class="mt-2">Manager</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" id="actionModalLhu">
                <button class="btn btn-outline-primary" role="button" id="sendTtdLhu">Save</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-kip">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Document KIP</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="invoice p-2 rounded">
                    <div class="row">
                        <div class="col-12">
                            <div class="fw-bolder">Nama Layanan</div>
                            <div><span id="txtNamaLayananModal"></span></div>
                        </div>
                        <br>
                        <div class="col-6">
                            <div class="fw-bolder">Customer</div>
                            <div><span id="txtNamaPelangganModal"></span></div>
                        </div>
                        <div class="col-6">
                            <div class="fw-bolder">Email</div>
                            <div><span id="txtAlamatModal">-</span></div>
                        </div>
                    </div>
                    <hr>
                    <h3>Rincian: </h3>
                    <table class="table table-borderless w-100" id="rincian-table-kip">
                    </table>
                </div>
                <div class="d-flex justify-content-center m-2">
                    <input type="hidden" id="idKip">
                    <div class="row">
                        <div class="wrapper" id="content-ttd-invoice">
                            <button class="btn btn-danger btn-sm position-absolute ms-1 mt-1" id="signature-clear-invoice"><i class="bi bi-trash"></i></button>
                            <canvas id="signature-kip" class="signature-pad border border-success-subtle rounded border-2" width=200 height=110></canvas>
                            <p class="text-center">Manager</p>
                        </div>
                        <div class="wrapper" id="content-show-invoice">
                            <img src="#" width="200" height="114" class="rounded border p-0" alt="ttd manager invoice" id="ttd_manager_invoice">
                            <p class="text-center mt-2">Manager</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" id="actionModalKip">
                <button class="btn btn-outline-primary" role="button" id="sendTtdKIP">Save</button>
            </div>
        </div>
    </div>
</div>
