<div class="modal fade" id="editPetugasModal" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header border-0">
          <h4 class="modal-title">Edit Petugas Layanan</h4>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form id="form-edit" method="post">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <input type="hidden" name="petugasId" id="inputPetugasIdEdit" class="form-control" readonly>
                <div class="mb-3 row">
                    <label for="inputSatuanKerjaEdit" class="col-sm-3 col-form-label">Satuan kerja</label>
                    <div class="col-sm-8">
                        <input type="text" name="satuankerja" id="inputSatuanKerjaEdit" class="form-control" readonly>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="inputSatuanLabEdit" class="col-sm-3 col-form-label">Satuan LAB</label>
                    <div class="col-sm-8">
                        <select name="satuan_lab" id="inputSatuanLabEdit" class="form-select" required>
                            <option value="">-- Select LAB --</option>
                            @foreach ($lab as $val)
                                <option value="{{ encryptor($val->id) }}">{{ $val->name_lab }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="inputPegawaiEdit" class="col-sm-3 col-form-label">Pegawai JKRL</label>
                    <div class="col-sm-8">
                        <input type="text" name="pegawai" id="inputPegawaiEdit" class="form-control" readonly>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="inputOtorisasi" class="col-sm-3 col-form-label">Otorisasi</label>
                    <div class="col-sm-8">
                        <select name="otorisasi[]" id="inputOtorisasiEdit" class="form-select" multiple="multiple" required>
                            <option></option>
                            @foreach ($otorisasi as $val)
                                <option value="{{ $val->name }}">{{ stringSplit($val->name, 'Otorisasi-') }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3 row text-danger text-center" id="divMessage" style="display: none;">
                    <div class="col-12" id="txtMessage"></div>
                </div>
            </div>
            <div class="modal-footer justify-content-between border-0">
              <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
