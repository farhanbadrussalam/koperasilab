<div class="modal fade" id="createPetugasModal" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header border-0">
          <h4 class="modal-title">Create Petugas</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('petugasLayanan.store') }}" method="post">
            @csrf
            <div class="modal-body">
                <div class="mb-3 row @if($idSatuan) d-none @endif">
                    <label for="inputSatuanKerja" class="col-sm-3 col-form-label">Satuan kerja</label>
                    <div class="col-sm-8">
                        <select name="satuankerja" id="inputSatuanKerja" class="form-select" required>
                            <option value="">-- Select satuan kerja --</option>
                            @foreach ($satuanKerja as $val)
                                <option value="{{ encryptor($val->id) }}" @if($idSatuan == $val->id) selected @endif>{{ $val->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="inputSatuanLab" class="col-sm-3 col-form-label">Satuan LAB</label>
                    <div class="col-sm-8">
                        <select name="satuan_lab" id="inputSatuanLab" class="form-select" required>
                            <option value="">-- Select LAB --</option>
                            @foreach ($lab as $val)
                                <option value="{{ encryptor($val->id) }}">{{ $val->name_lab }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="inputPegawai" class="col-sm-3 col-form-label">Pegawai JKRL</label>
                    <div class="col-sm-8">
                        <select name="pegawai" id="inputPegawai" class="form-select" required>
                            <option value="">-- Select Pegawai --</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="inputOtorisasi" class="col-sm-3 col-form-label">Otorisasi</label>
                    <div class="col-sm-8">
                        <select name="otorisasi[]" id="inputOtorisasi" class="form-select" multiple="multiple" required>
                            <option></option>
                            @foreach ($otorisasi as $val)
                                <option value="{{ encryptor($val->name) }}">{{ stringSplit($val->name, 'Otorisasi-') }}</option>
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
