@props([
    'actionButton' => null,
])
<!-- Modal Note Modern -->
<div class="modal fade" id="modalNote" tabindex="-1" aria-labelledby="modalNoteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1.25rem;">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center" id="modalNoteLabel">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-journal-text text-primary fs-5"></i>
                    </div>
                    <span id="modalNoteTitle">Detail Catatan</span>
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="note-content-wrapper bg-light p-3 rounded-4 mb-3">
                    <p id="modalNoteBody" class="mb-0 text-secondary" style="font-size: 0.95rem; line-height: 1.6;">
                        Memuat catatan...
                    </p>
                </div>

                <!-- Meta data (Opsional: misal pengirim/waktu) -->
                <div id="modalNoteMeta" class="d-flex align-items-center text-muted d-none" style="font-size: 0.85rem;">
                    <i class="bi bi-clock me-1"></i>
                    <span id="modalNoteTime">--:--</span>
                    <span class="mx-2">•</span>
                    <i class="bi bi-person me-1"></i>
                    <span id="modalNoteAuthor">Admin</span>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-light fw-semibold px-4 py-2" data-bs-dismiss="modal" style="border-radius: 0.75rem;">Tutup</button>
                @if(isset($actionButton))
                    {{ $actionButton }}
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    class NoteModal {
        constructor() {
            this.modalId = '#modalNote';
            this.selectors = {
                title: '#modalNoteTitle',
                body: '#modalNoteBody',
                meta: '#modalNoteMeta',
                time: '#modalNoteTime',
                author: '#modalNoteAuthor'
            };
        }

        /**
         * Menampilkan loading state dan membuka modal
         */
        showLoading() {
            $(this.selectors.title).text('Memuat...');
            $(this.selectors.body).html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary spinner-border-sm" role="status"></div>
                    <span class="ms-2 text-muted" style="font-size: 0.9rem;">Mengambil data...</span>
                </div>
            `);
            $(this.selectors.meta).addClass('d-none');
            $(this.modalId).modal('show');
        }

        /**
         * Mengambil data dari server via AJAX
         * @param {string} url - Endpoint API
         */
        fetch(url) {
            this.showLoading();

            $.ajax({
                url: url,
                method: 'GET',
                dataType: 'json',
                success: (response) => {
                    // Asumsi struktur response: { success: true, data: { title, note, created_at, author } }
                    const data = response.data || response;
                    this.render(data);
                },
                error: (xhr) => {
                    this.renderError(xhr.statusText);
                }
            });
        }

        /**
         * Mengisi konten modal dengan data
         */
        render(data) {
            $(this.selectors.title).text(data.title || 'Detail Catatan');
            $(this.selectors.body).html(data.note || data.catatan || 'Tidak ada konten catatan.');

            if (data.created_at || data.author) {
                $(this.selectors.time).text(data.created_at || '--:--');
                $(this.selectors.author).text(data.author || 'Admin');
                $(this.selectors.meta).removeClass('d-none');
            } else {
                $(this.selectors.meta).addClass('d-none');
            }
        }

        /**
         * Menampilkan pesan error jika AJAX gagal
         */
        renderError(message) {
            $(this.selectors.title).text('Kesalahan');
            $(this.selectors.body).html(`
                <div class="text-center py-3">
                    <i class="bi bi-exclamation-triangle text-danger fs-2 d-block mb-2"></i>
                    <span class="text-secondary">Gagal memuat data: ${message}</span>
                </div>
            `);
            $(this.selectors.meta).addClass('d-none');
        }

        hide() {
            $(this.modalId).modal('hide');
        }
    }

    // Inisialisasi secara global agar bisa dipanggil dari mana saja
    window.NoteComponent = new NoteModal();
</script>
