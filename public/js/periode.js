class Periode {
    constructor(list = [], options = {}) {
        this.masterData = list;
        this.listPeriode = [];
        this.canShow = options.preview || false;
        this.maxPeriode = options.max || false;
        this.dataonly = options.dataonly || false;
        this.eventSimpan = new CustomEvent('periode.simpan', {});
        this.eventHide = new CustomEvent('periode.hide.modal', {});

        // add element modal to body
        if(this.canShow){
            $('body').append(this.modalShow);
        }else if(this.dataonly){

        }else{
            $('body').append(this.modalCreate);
        }

        $('#modal-pilih-periode').on('hide.bs.modal', () => {
            this.listPeriode = Array.from(this.masterData);
        });

        $('#modal-show-periode').on('hide.bs.modal', () => {
            document.dispatchEvent(this.eventHide);
        });

        $('#btn-simpan-periode').on('click', () => {
            this.simpanPeriode();
        });
    }

    modalCreate(){
        return `
            <div class="modal fade" id="modal-pilih-periode" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="pilih_periode" aria-hidden="false">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="pilih_periode">Pilih periode</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="g-2 row" id="form-pilih-periode">
                            
                            </div>
                            <div class="my-3" id="modal-periode-action">
                                
                            </div>
                        </div>
                        <div class="modal-footer" id="btn-action">
                            <button type="button" class="btn btn-primary" id="btn-simpan-periode">Simpan</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    modalShow() {
        return `
            <div class="modal fade" id="modal-show-periode" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="">Periode</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body row justify-content-center">
                            <div class="" id="list-modal-periode">
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    addData(arr) {
        this.masterData = arr;
    }

    getData() {
        return this.masterData;
    }

    getPeriodeNow(){
        const now = new Date(); // Tanggal sekarang
        let index = false; // Default jika tidak ditemukan

        this.masterData.forEach((item, i) => {
            const startDate = new Date(item.start_date);
            const endDate = new Date(item.end_date);

            if (now >= startDate && now < endDate) {
                index = i; // Simpan indeks jika tanggal berada dalam rentang
            }
        });

        return index !== false ? index + 1 : false;
    }

    show(){
        this.listPeriode = Array.from(this.masterData);
        if(this.canShow){
            this.previewPeriode();
            $('#modal-show-periode').modal('show');
        }else if(this.dataonly){

        }else{
            this.listPeriode.length == 0 ? this.addPeriode() : this.loadPeriode();
            $('#modal-pilih-periode').modal('show');
        }
    }

    previewPeriode() {
        let html = '';
        for (const [index, data] of this.listPeriode.entries()) {
            html += `
                <div>
                    <label class="col-form-label">Periode ${index+1}</label>
                    <div class="input-group">
                        <input type="text" aria-label="Date Start" class="form-control bg-secondary-subtle" name="date_start[]" id="periode_start_${index}" value="${dateFormat(data.start_date, 4)}" data-periode="${index}" placeholder="Pilih Bulan" readonly>
                        <input type="text" aria-label="Date End" class="form-control bg-secondary-subtle" name="date_end[]" id="periode_end_${index}" value="${dateFormat(data.end_date, 4)}" data-periode="${index}"  readonly>
                    </div>
                </div>
            `;
        }
    
        $('#list-modal-periode').html(html);
    }

    addPeriode() {
        let lastPeriode = this.listPeriode[this.listPeriode.length-1];
        if(lastPeriode?.start_date == '' || lastPeriode?.end_date == ''){
            return Swal.fire({
                icon: "warning",
                text: `Silahkan pilih periode ${this.listPeriode.length}`,
            });
        }
        
        this.listPeriode.push({
            start_date: '',
            end_date: ''
        });

        this.loadPeriode();
    }

    loadPeriode(){
        document.getElementById('form-pilih-periode').innerHTML = '';
        this.listPeriode.forEach((data, index) => {
            let isLast = this.listPeriode.length-1 == index ? true : false;

            const div1 = document.createElement('div');
            const div2 = document.createElement('div');
            div2.className = 'input-group';

            const label1 = document.createElement('label');
            label1.className = 'col-form-label';
            label1.textContent = `Periode ${index + 1}`;

            const btnRemove = document.createElement('button');
            btnRemove.className = 'btn btn-outline-danger';
            btnRemove.innerHTML = '<i class="bi bi-dash-lg"></i>';
            btnRemove.onclick = () => {
                this.removePeriode(index);
            }

            const inputStart = document.createElement('input');
            inputStart.className = `form-control ${isLast ? 'date-periode' : 'bg-secondary-subtle'}`;
            inputStart.value = `${isLast ? data.start_date : dateFormat(data.start_date, 4)}`;
            inputStart.name = 'date_start[]';
            inputStart.id = `periode_start_${index}`;
            inputStart.dataset.periode = index;
            inputStart.placeholder = `Pilih Bulan`;
            !isLast ? inputStart.setAttribute('readonly', true) : '';

            const inputEnd = document.createElement('input');
            inputEnd.className = `form-control ${isLast && data.end_date != '' ? 'end-periode' : 'bg-secondary-subtle'}`;
            inputEnd.value = `${isLast ? data.end_date : dateFormat(data.end_date, 4)}`;
            inputEnd.name = 'date_end[]';
            inputEnd.id = `periode_end_${index}`;
            inputEnd.dataset.periode = index;
            inputEnd.placeholder = `Pilih Bulan`;
            !isLast ? inputEnd.setAttribute('readonly', true) : '';

            div2.append(btnRemove);
            div2.append(inputStart);
            div2.append(inputEnd);

            div1.append(label1);
            div1.append(div2);
            
            document.getElementById('form-pilih-periode').append(div1);
        });

        let btnSimpan = document.createElement('button');
        btnSimpan.className = 'btn btn-outline-success col-12';
        btnSimpan.innerHTML = '<i class="bi bi-plus-lg"></i> Tambah periode';
        btnSimpan.onclick = () => {
            this.addPeriode();
        };
        
        $('#modal-periode-action').html('');
        if(this.maxPeriode){
            if(this.listPeriode.length < this.maxPeriode){
                $('#modal-periode-action').append(btnSimpan);
            }
        }else{
            $('#modal-periode-action').append(btnSimpan);
        }

        
        this.setPeriode('all');
    }

    setPeriode(type = 1){
        let lastDate = false;
        if(type == 1 || type == 'all'){
            lastDate = this.listPeriode[this.listPeriode.length-2];
            $('.date-periode').flatpickr({
                altInput: true,
                locale: "id",
                minDate: lastDate ? lastDate.end_date : 'today',
                dateFormat: "Y-m-d",
                altFormat: "j F Y",
                disable: [
                    function(date) {
                        // Hanya mengizinkan tanggal antara 1 dan 10
                        return date.getDate() > 10;
                    }
                ],
                onChange: (selectedDates, dateStr, instance) => {
                    let id_input_start = $(instance.input).data("periode");
        
                    if(dateStr){
                        let nextDate = new Date(dateStr);
                        nextDate.setMonth(nextDate.getMonth() + 2);
            
                        let end_date = nextDate.toISOString().split('T')[0];
                        
                        $(`#periode_end_${id_input_start}`).val(end_date);
                        $(`#periode_end_${id_input_start}`).addClass('end-periode').removeClass('bg-secondary-subtle');
                        $(`#periode_end_${id_input_start}`).attr('readonly', false);
            
                        this.listPeriode[id_input_start].start_date = dateStr;
                        this.listPeriode[id_input_start].end_date = end_date;
                    }else{
                        $(`#periode_end_${id_input_start}`).val('');
                        $(`#periode_end_${id_input_start}`).addClass('bg-secondary-subtle').removeClass('end-periode');
                        $(`#periode_end_${id_input_start}`).attr('readonly', true);
                        this.listPeriode[id_input_start].start_date = '';
                        this.listPeriode[id_input_start].end_date = '';
                    }

                    this.setPeriode(2);
                }
            });
        }
        
        if(type == 2 || type == 'all'){
            lastDate = this.listPeriode[this.listPeriode.length-1];
            $('.end-periode').flatpickr({
                altInput: true,
                locale: "id",
                minDate: lastDate ? lastDate.start_date : 'today',
                maxDate: lastDate ? lastDate.end_date : false,
                dateFormat: "Y-m-d",
                altFormat: "j F Y",
                disable: [
                    function(date) {
                        // Hanya mengizinkan tanggal antara 1 dan 10
                        return date.getDate() > 10;
                    }
                ],
                onChange: (selectedDates, dateStr, instance) => {
                    let id_input_start = $(instance.input).data("periode");
                    if(dateStr){
                        this.listPeriode[id_input_start].end_date = dateStr;
                    }else{
                        this.listPeriode[id_input_start].end_date = '';
                    }
                }
            })
        }
    }

    simpanPeriode(){
        let lastPeriode = this.listPeriode[this.listPeriode.length-1];
    
        if(this.maxPeriode){
            if(this.maxPeriode != this.listPeriode.length){
                return Swal.fire({
                    icon: "warning",
                    text: `Periode kurang, silahkan tambah periode ${this.listPeriode.length}/${this.maxPeriode}`,
                });
            }
        }

        if(lastPeriode?.start_date != ''){
            Swal.fire({
                text: 'Apa anda yakin ingin menyimpan data ?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Iya',
                cancelButtonText: 'Tidak',
                customClass: {
                    confirmButton: 'btn btn-success mx-1',
                    cancelButton: 'btn btn-danger mx-1'
                },
                buttonsStyling: false,
                reverseButtons: true
            }).then(result => {
                if(result.isConfirmed){
                    this.masterData = Array.from(this.listPeriode);
                    this.listPeriode = [];
                    document.dispatchEvent(this.eventSimpan);
                    $('#modal-pilih-periode').modal('hide');
                }
            })
        }else{
            return Swal.fire({
                icon: "warning",
                text: `Silahkan pilih periode ${this.listPeriode.length}`,
            });
        }
    }

    removePeriode(index){
        this.listPeriode.splice(index, 1);
        this.loadPeriode();
        this.setPeriode(2);
    }

    on(eventName, callback = () => {}) {
        return document.addEventListener(eventName, callback);
    }

    destroy(){
        if(this.canShow){
            $('#modal-show-periode').remove();
        }else if(this.dataonly){

        }else{
            $('#modal-pilih-periode').remove();
        }
    }
}