<div class="modal fade" id="create-lhu">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Buat LHU</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row">
                <form action="#" method="post">
                    @csrf
                    <input type="hidden" name="" id="idJadwal">
                    <div id="content-pertanyaan"></div>
                    <div class="wrapper text-center" id="content-ttd">
                        <button type="button" class="btn btn-danger btn-sm position-absolute ms-1 mt-1" id="signature-clear-createlhu"><i class="bi bi-trash"></i></button>
                        <canvas id="signature-canvas-createlhu" class="signature-pad border border-success-subtle rounded border-1" width=200 height=150></canvas>
                        <p class="text-center mb-0">{{ $title }}</p>
                        <span>(<span id="nameSignature">{{ Auth::user()->name }}</span>)</span>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-success" id="send-lhu">Kirim document <i class="bi bi-send"></i></button>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
    const pertanyaan_lhu = @json($pertanyaan);
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
                    'Authorization': `Bearer {{ generateToken() }}`
                },
                data: formData
            }).done(function (result) {
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
@vite(['resources/js/component/create_lhu.js'])
@endpush
