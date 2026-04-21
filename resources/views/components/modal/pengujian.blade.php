<div class="modal fade" id="verify_modal_surat_pengujian" tabindex="-1" aria-labelledby="modal_title" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content border-0 shadow-lg" style="border-radius: 1.25rem;">
        <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
          <h5 class="modal-title fw-bold text-dark d-flex align-items-center">
            <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-file-earmark-check text-primary fs-5"></i>
            </div>
            <span id="modal_title">Verifikasi Surat Pengujian</span>
          </h5>
          <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="bg-light p-3 rounded-4 mb-3 h-100">
                        <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-info-circle me-2"></i>Informasi Detail</h6>
                        <div class="mb-3">
                            <label class="small text-muted mb-1">Pemilik</label>
                            <div class="fw-semibold text-dark" id="inputPemilik">-</div>
                            <input type="hidden" name="txt_id_penyelia" id="txt_id_penyelia">
                        </div>
                        <div class="mb-3">
                            <label class="small text-muted mb-1">Alamat</label>
                            <div class="text-secondary small" id="inputAlamat">-</div>
                        </div>
                        <div class="mb-3">
                            <label class="small text-muted mb-1">Jenis Pengujian</label>
                            <div class="badge bg-white text-primary border border-primary-subtle rounded-pill px-3 py-2" id="inputJenisPengujian">-</div>
                        </div>
                        <div class="mb-0">
                            <label class="small text-muted mb-1">Nama Sample/Alat</label>
                            <div class="bg-white rounded-3 p-2 border border-light-subtle overflow-auto" id="list-sample" style="max-height: 150px;">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-1 h-100 d-flex flex-column scroll-light overflow-auto" style="max-height: 350px;">
                        <div id="content-pertanyaan" class="mb-3 pe-2"></div>
                        <div class="mt-auto d-flex justify-content-center pt-3">
                            <div class="wrapper" id="content-ttd"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer border-top-0 pt-0 pb-4 px-4 gap-2">
          <button type="button" class="btn btn-light fw-semibold px-4 py-2 me-auto" data-bs-dismiss="modal" style="border-radius: 0.75rem;">Batal</button>
          <button type="button" class="btn btn-danger fw-semibold px-4 py-2" id="btnDecline" style="border-radius: 0.75rem;">Tolak</button>
          <button type="button" class="btn btn-primary fw-semibold px-4 py-2" id="btnApprove" style="border-radius: 0.75rem;">Setuju</button>
          <button type="button" class="btn btn-primary fw-semibold px-4 py-2" id="btnSimpan" style="border-radius: 0.75rem;">Ajukan Pengujian</button>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<script>
    class PengujianModal {
        constructor() {
            this.modalId = '#verify_modal_surat_pengujian';
            this.selectors = {
                title: `${this.modalId} .modal-title`,
                pemilik: '#inputPemilik',
                idPenyelia: '#txt_id_penyelia',
                alamat: '#inputAlamat',
                jenisPengujian: '#inputJenisPengujian',
                listSample: '#list-sample',
                pertanyaan: '#content-pertanyaan',
                ttd: '#content-ttd',
                btnApprove: '#btnApprove',
                btnDecline: '#btnDecline',
                btnSimpan: '#btnSimpan'
            };

            this.onApproveCallback = null;
            this.onDeclineCallback = null;
            this.onSaveCallback = null;
            this.initEvents();
        }

        initEvents() {
            $(this.selectors.btnApprove).on('click', () => {
                if (this.onApproveCallback) this.onApproveCallback();
            });

            $(this.selectors.btnDecline).on('click', () => {
                if (this.onDeclineCallback) this.onDeclineCallback();
            });

            $(this.selectors.btnSimpan).on('click', () => {
                if (this.onSaveCallback) this.onSaveCallback();
            });
            // on modal hidden, reset content
            $(this.modalId).on('hidden.bs.modal', () => {
                $(this.selectors.pemilik).empty();
                $(this.selectors.alamat).empty();
                $(this.selectors.jenisPengujian).empty();
                $(this.selectors.listSample).empty();
                $(this.selectors.pertanyaan).empty();
                $(this.selectors.ttd).empty();
            });
        }

        /**
         * Mengatur visibilitas tombol berdasarkan mode
         * @param {('create'|'verify')} mode
         */
        setMode(mode) {
            if (mode === 'create') {
                $(this.selectors.btnApprove).addClass('d-none');
                $(this.selectors.btnDecline).addClass('d-none');
                $(this.selectors.btnSimpan).removeClass('d-none');
                $(this.selectors.title).text('Konfirmasi Pengujian');
            } else {
                $(this.selectors.btnApprove).removeClass('d-none');
                $(this.selectors.btnDecline).removeClass('d-none');
                $(this.selectors.btnSimpan).addClass('d-none');
                $(this.selectors.title).text('Verifikasi Surat Pengujian');
            }
        }

        /**
         * Menampilkan loading state
         */
        showLoading() {
            const loadingHtml = `
                <div class="text-center py-2">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <span class="ms-1 small text-muted">Memuat...</span>
                </div>
            `;
            $(this.selectors.pemilik).html(loadingHtml);
            $(this.selectors.alamat).html(loadingHtml);
            $(this.selectors.jenisPengujian).html(loadingHtml);
            $(this.selectors.listSample).html(loadingHtml);
            $(this.selectors.pertanyaan).empty();

            $(this.modalId).modal('show');
        }

        /**
         * Mengambil data untuk verifikasi
         */
        fetch(url, modeOrCallback = 'verify', callbacks = {}) {
            let mode = 'verify';
            let actualCallbacks = callbacks;

            if (typeof modeOrCallback === 'object') {
                actualCallbacks = modeOrCallback;
            } else {
                mode = modeOrCallback;
            }

            this.setMode(mode);
            this.showLoading();

            this.onApproveCallback = actualCallbacks.onApprove || null;
            this.onDeclineCallback = actualCallbacks.onDecline || null;
            this.onSaveCallback = actualCallbacks.onSave || null;

            $.ajax({
                url: url,
                method: 'GET',
                dataType: 'json',
                success: (response) => {
                    const data = response.data || response;
                    this.render(data);
                },
                error: (xhr) => {
                    this.renderError(xhr.statusText);
                }
            });
        }

        /**
         * Menampilkan modal dengan data yang sudah ada (tanpa AJAX)
         */
        open(data, mode = 'create', callbacks = {}) {
            this.setMode(mode);
            this.onApproveCallback = callbacks.onApprove || null;
            this.onDeclineCallback = callbacks.onDecline || null;
            this.onSaveCallback = callbacks.onSave || null;

            $(this.selectors.pertanyaan).empty();

            this.render(data);
            $(this.modalId).modal('show');
        }

        /**
         * Mengisi konten modal
         */
        render(data) {
            $(this.selectors.pemilik).text(data.pemilik || '-');
            $(this.selectors.idPenyelia).val(data.id_penyelia || '');
            $(this.selectors.alamat).text(data.alamat || '-');
            $(this.selectors.jenisPengujian).text(data.jenis_pengujian || '-');

            // Render List Sample
            let sampleHtml = '';
            if (data.samples && data.samples.length > 0) {
                sampleHtml = '<ul class="mb-0 ps-3">';
                data.samples.forEach(item => {
                    sampleHtml += `<li>${item.nama || item}</li>`;
                });
                sampleHtml += '</ul>';
            } else {
                sampleHtml = '<span class="text-muted">Tidak ada data sample</span>';
            }
            $(this.selectors.listSample).html(sampleHtml);

            // Render Pertanyaan (jika ada)
            if (data.pertanyaan) {
                $(this.selectors.pertanyaan).html(data.pertanyaan);
            }
        }

        renderError(message) {
            const errorHtml = `<span class="text-danger small"><i class="bi bi-exclamation-circle me-1"></i>Gagal memuat: ${message}</span>`;
            $(this.selectors.pemilik).html(errorHtml);
            $(this.selectors.alamat).html(errorHtml);
        }

        hide() {
            $(this.modalId).modal('hide');
        }

        /**
         * Helper untuk set callback aksi secara manual
         */
        setActions(onApprove, onDecline) {
            this.onApproveCallback = onApprove;
            this.onDeclineCallback = onDecline;
        }

        setLoadingButton(type, isLoading = true) {
            const btnMap = {
                approve: this.selectors.btnApprove,
                decline: this.selectors.btnDecline,
                save: this.selectors.btnSimpan
            };
            const btn = $(btnMap[type]);

            if (isLoading) {
                btn.attr('disabled', true).prepend('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>');
            } else {
                btn.removeAttr('disabled').find('.spinner-border').remove();
            }
        }
    }

    window.PengujianComponent = new PengujianModal();
</script>
