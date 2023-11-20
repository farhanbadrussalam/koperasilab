<div class="modal fade" id="create-lhu">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Buat LHU</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
            </div>
            <div class="modal-body">
                {{-- Upload Surat --}}
                <div class="mb-2">
                    <label for="uploadLhu" class="form-label"><span>Lampirkan LHU</span><span
                            class="fw-bold fs-14 text-danger">*</span></label>
                    <div class="card mb-0" style="height: 100px;">
                        <input type="file" name="uploadLhu" id="uploadLhu" class="form-control dropify">
                        <input type="hidden" name="idLhu" id="idLhu">
                    </div>
                    <span class="mb-3 text-muted" style="font-size: 12px;">Allowed file types: pdf,doc,docx.
                        Recommend size under 5MB.</span>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-success" onclick="sendLhu()">Kirim document <i class="bi bi-send"></i></button>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
    setDropify('init', '#uploadLhu', {
        allowedFileExtentions: ['pdf', 'doc', 'docx'],
        maxFileSize: '5M'
    });

    function sendLhu(){
        let idLhu = $('#idLhu').val();
        let lampiran = $('#uploadLhu')[0].files[0];

        if(lampiran){
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('file', lampiran);
            formData.append('idLhu', idLhu);

            $.ajax({
                method: 'POST',
                url: "{{ url('api/lhu/sendDokumen') }}",
                processData: false,
                contentType: false,
                headers: {
                    'Authorization': `Bearer {{ $token }}`
                },
                data: formData
            }).done(function (result) {
                console.log(result);
                if(result.meta.code == 200){
                    $('#create-lhu').modal('hide');
                    dt_tugas?.ajax.reload();
                }
            })
        }else{
            Swal.fire({
                icon: "error",
                text: "Lampiran harap diisi"
            });
        }
    }
</script>
@endpush
