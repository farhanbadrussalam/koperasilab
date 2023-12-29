<div class="modal fade" id="confirmModal">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Detail</h4>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div class="row">
                <div  class="col-4 fw-bolder">Nama Layanan</div>
                <div class="col-8">: <span id="txtNamaLayanan">Uji Kebocoran Sumber Radioaktif</span></div>
            </div>
            <div class="row">
                <div  class="col-4 fw-bolder">Jenis Layanan</div>
                <div class="col-8">: <span id="txtJenisLayanan">1-5 Sample</span></div>
            </div>
            <div class="row">
                <div  class="col-4 fw-bolder">Harga</div>
                <div class="col-8">: <span id="txtHarga">Rp 1.700.000</span></div>
            </div>
            <div class="row">
                <div  class="col-4 fw-bolder">Start</div>
                <div class="col-8">: <span id="txtStart">2023-07-26 08:00:00</span></div>
            </div>
            <div class="row">
                <div  class="col-4 fw-bolder">End</div>
                <div class="col-8">: <span id="txtEnd">2023-07-26 17:00:00</span></div>
            </div>
            <div class="row">
                <div  class="col-4 fw-bolder">Status</div>
                <div class="col-8 d-flex"><span>:</span>&nbsp;<span id="txtStatus">Diajukan</span></div>
            </div>
            <div class="row">
                <div  class="col-4 fw-bolder">Surat Penugasan</div>
                <div class="col-8">: <a href="#" target="_blank" id="downloadSuratTugas" class="btn btn-outline-primary btn-sm">Lihat Surat tugas</a></div>
            </div>
            <div class="row text-center mt-3" id="divConfirmBtn">
                <input type="hidden" id="idJadwalPetugas">
                <div class="col-6">
                    <button class="btn btn-danger" onclick="btnConfirm(this)" data-status="{{ encryptor(9) }}">Tolak</button>
                </div>
                <div class="col-6">
                    <button class="btn btn-primary" onclick="btnConfirm(this)" data-status="{{ encryptor(2) }}">Setuju</button>
                </div>
            </div>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
@push('scripts')
    <script>
        function modalConfirm(id){
            $.ajax({
                url: "{{ url('api/penugasan/show') }}/"+id,
                method: 'GET',
                dataType: 'json',
                processData: true,
                headers: {
                    'Authorization': `Bearer {{ $token }}`,
                    'Content-Type': 'application/json'
                }
            }).done(result => {
                let data = result.data;

                $('#txtNamaLayanan').html(data.permohonan.layananjasa.name);
                $('#txtJenisLayanan').html(data.permohonan.jenis_layanan);
                $('#txtHarga').html(formatRupiah(data.permohonan.tarif));
                $('#txtStart').html(convertDate(data.jadwal.date_mulai));
                $('#txtEnd').html(convertDate(data.jadwal.date_selesai));

                $('#idJadwalPetugas').val(data.jadwalpetugas_hash);
                $('#downloadSuratTugas').attr('href', `{{ url('/laporan/suratTugas') }}/${data.permohonan.permohonan_hash}`);
                if(data.status == 1){
                    $('#divConfirmBtn').show();
                }else{
                    $('#divConfirmBtn').hide();
                }
                $('#confirmModal').modal('show');
            })
        }

        function btnConfirm(obj){
            const status = $(obj).data('status');

            let formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('idJadwalPetugas', $('#idJadwalPetugas').val());
            formData.append('status', status);

            $.ajax({
                url: "{{ route('penugasan.update') }}",
                method: "POST",
                dataType: 'json',
                processData: false,
                contentType: false,
                data: formData,
                headers: {
                    'Authorization': `Bearer {{ $token }}`
                }
            }).done(result => {
                const data = result.data;
                if(data.info.status == 2){
                    Swal.fire({
                        icon: 'success',
                        title: data.message
                    });
                    dt_jadwal?.ajax.reload();
                }else{
                    Swal.fire({
                        icon: 'error',
                        title: data.message
                    });
                    dt_jadwal?.ajax.reload();
                }
                $('#confirmModal').modal('hide');
            }).fail(err => {
                console.log(err);
            })
        }
    </script>
@endpush
