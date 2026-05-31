<div class="modal fade" id="createTldModal" tabindex="-1" aria-labelledby="createTldModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-primary-subtle text-primary rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-device-ssd fs-5"></i>
                    </div>
                    <h5 class="modal-title fw-bold text-dark" id="createTldModalLabel">Tambah TLD</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-create" method="post" data-parsley-validate>
                @csrf
                <div class="modal-body row g-3 px-4 pt-3 pb-2">
                    <div class="col-12">
                        <label for="inputNoSeri" class="form-label fw-semibold text-secondary">Nomer Seri <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="nomer_seri" 
                               id="inputNoSeri" 
                               class="form-control rounded-3" 
                               placeholder="e.g. TLD-109283"
                               autocomplete="off" 
                               required
                               data-parsley-minlength="3"
                               data-parsley-trigger="input"
                               data-parsley-required-message="Nomor seri wajib diisi.">
                    </div>
                    <div class="col-md-6">
                        <label for="inputJenisTld" class="form-label fw-semibold text-secondary">Jenis TLD <span class="text-danger">*</span></label>
                        <select name="jenis" id="inputJenisTld" class="form-select rounded-3" required data-parsley-required-message="Jenis TLD wajib dipilih.">
                            <option value="">Pilih...</option>
                            <option value="kontrol">Kontrol</option>
                            <option value="pengguna">Pengguna</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="inputMerk" class="form-label fw-semibold text-secondary">Merk TLD <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="merk" 
                               id="inputMerk" 
                               class="form-control rounded-3" 
                               placeholder="e.g. Harshaw"
                               autocomplete="off" 
                               required
                               data-parsley-required-message="Merk TLD wajib diisi.">
                    </div>
                </div>
                <div class="modal-footer border-top-0 px-4 pb-4 pt-2 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill" id="btn-create">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let currentTldId = null;
    let currentMode = 'create';

    /**
     * Fungsi utama untuk membuka modal dalam mode create atau edit
     */
    function openModalTld(mode, id = null) {
        currentMode = mode;
        currentTldId = id;
        const modal = $('#createTldModal');
        const modalTitle = $('#createTldModalLabel');

        if (mode === 'create') {
            modalTitle.text('Tambah TLD');
            modal.modal('show');
        } else if (mode === 'edit') {
            modalTitle.text('Edit TLD');
            spinner('show', modal.find('.modal-content'));
            ajaxGet(`management/tld/${id}`, null, (result) => {
                spinner('hide', modal.find('.modal-content'));
                if (result.meta.code == 200) {
                    $('#inputNoSeri').val(result.data.no_seri_tld);
                    $('#inputJenisTld').val(result.data.jenis);
                    $('#inputMerk').val(result.data.merk);
                    modal.modal('show');
                }
            });
        }
    }

    $(function () {
        const formParsley = $('#form-create').parsley();
        let typingTimer;                // Variabel untuk menyimpan timer

        // Reset form dan hapus timer saat modal ditutup
        $('#createTldModal').on('hidden.bs.modal', function() {
            clearTimeout(typingTimer);
            const form = $('#form-create');
            form[0].reset();
            form.parsley().reset();
            form.find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
            currentTldId = null;
            currentMode = 'create';
        });

        const doneTypingInterval = 500;  // Waktu tunggu dalam milidetik (1000 ms = 1 detik)
        const myInput = document.getElementById('inputNoSeri');

        // Event ketika user mengetik (bisa pakai 'input' atau 'keyup')
        myInput.addEventListener('input', function () {
            // 1. Hapus timer sebelumnya setiap kali user mengetik
            clearTimeout(typingTimer);

            // 2. Buat timer baru
            typingTimer = setTimeout(function () {
                // Baris ini akan dieksekusi 1 detik setelah user BERHENTI mengetik
                searchTld(myInput.value);
            }, doneTypingInterval);
        });

        $('#form-create').on("submit", (evt) => {
            evt.preventDefault();

            if (!formParsley.validate()) {
                return;
            }

            const formData = new FormData(evt.target);
            let url = `management/tld`;

            if (currentMode === 'edit') {
                url = `management/tld/${currentTldId}`;
                formData.append('_method', 'PUT'); // Method spoofing untuk Laravel update
            }

            spinner('show', $('#btn-create'));
            ajaxPost(url, formData, result => {
                if (result.meta.code == 200) {
                    Swal.fire({
                        icon: 'success',
                        text: result.data.msg,
                        timer: 1200,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        $('#createTldModal').modal('hide');
                        if (typeof datatable_tld !== 'undefined' && datatable_tld) {
                            datatable_tld.ajax.reload();
                        }
                        spinner('hide', $('#btn-create'));
                    })
                }
            }, error => {
                spinner('hide', $('#btn-create'));
            })
        });
    });

    function searchTld(no_seri){
        const fieldInstance = $('#inputNoSeri').parsley();

        if (no_seri === '') {
            fieldInstance.reset();
            $(fieldInstance.element).removeClass('is-valid is-invalid');
            return;
        }

        let url = `management/searchTld?q=${no_seri}`;
        if (currentMode === 'edit' && currentTldId) {
            url += `&ignore_id=${currentTldId}`;
        }

        ajaxGet(url, false, result => {
            fieldInstance.removeError('unique-tld');
            if(result.meta.code == 200){
                if(result.data.length > 0){
                    fieldInstance.addError('unique-tld', {message: 'Nomer seri sudah terdaftar.'});
                    $('#inputNoSeri').removeClass('is-valid').addClass('is-invalid');
                } else {
                    $('#inputNoSeri').removeClass('is-invalid').addClass('is-valid');
                }
            }
        });
    }
</script>
