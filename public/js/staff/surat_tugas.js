let signaturePad = false;
let arrJobs = [];
let periodeJs = false;
let objSelected = {};
$(function () {
    // Mengambil periode
    let arrPeriode = dataPenyelia.permohonan.kontrak?.periode ?? dataPenyelia.permohonan.periode_pemakaian.map((d, i) => ({...d, periode: i + 1}));
    let findPeriode = arrPeriode.find(d => d.periode == dataPenyelia.periode);

    if(findPeriode.periode == 0){
        $('#periodePermohonan').html(`Zero cek`);
    }else{
        $('#periodePermohonan').html(`${dateFormat(findPeriode.start_date, 5)} - ${dateFormat(findPeriode.end_date, 5)}`);
    }

    $('#searchPetugas').on('input', () => {
        let val = $('#searchPetugas').val();
        searchPetugasList(val);
    });

    if(!['verif', 'show'].includes(typeSurat)){
        $('#date_start').flatpickr({
            altInput: true,
            locale: "id",
            minDate: 'today',
            dateFormat: "Y-m-d",
            altFormat: "j F Y",
            onChange: (selectedDates, dateStr, instance) => {
                $('#date_end').val('');
                $('#date_end').removeClass('bg-secondary-subtle');
                $('#date_end').attr('readonly', false);

                $('#date_end').flatpickr({
                    altInput: true,
                    locale: "id",
                    minDate: dateStr,
                    dateFormat: "Y-m-d",
                    altFormat: "j F Y",
                })
            }
        });

        if(dataPenyelia.end_date){
            $('#date_end').flatpickr({
                altInput: true,
                locale: "id",
                minDate: dataPenyelia.start_date,
                dateFormat: "Y-m-d",
                altFormat: "j F Y",
            });
        }
    }else{
        const conten_2 = document.getElementById("content-ttd-1");
        if(conten_2){
            signaturePad = signature(conten_2, {
                text: 'Manager',
                defaultSig: dataPenyelia.ttd ?? false,
                name: dataPenyelia?.usersig?.name ?? false
            });
        }
    }

    arrJobs = dataPenyelia.petugas.map(petugas => ({
        idJobs: petugas?.jobs?.jobs_hash,
        idPetugas: petugas.user_hash,
        name: petugas?.user?.name,
        email: petugas?.user?.email
    }));

    loadPetugas();

    listJobs.forEach((d, i) => {
        d.order = i + 1;
    });

    listJobsParalel.forEach((d, i) => {
        d.order = i + 1;
    });

    if(!['verif', 'show'].includes(typeSurat)){
        $('#sortJobs').sortable({
            connectWith: '#sortJobs',
            handle: '.moveon',
            revert: 'true',
            update: (event, ui) => {
                let order = 1;
                for (const element of event.target.children) {
                    listJobs.map(d => d.jobs_hash == element.dataset.idjobs ? d.order = order : false);
                    order++;
                }
            }
        });
    }
})

function tambahPetugas(idJobs, index, name, isParalel = false){
    objSelected = {
        idJobs: idJobs,
        index: index,
        isParalel: isParalel
    }
    $('#modal-list-petugas').html('');
    $('#searchPetugas').val('');
    searchPetugasList();
    $('#modal-name-jobs').text(name);
    $('#modalAddPetugas').modal('show');
}

function searchPetugasList(search = '') {
    if(objSelected){
        const idJobs = objSelected.idJobs;
        const index = objSelected.index;
        const isParalel = objSelected.isParalel;

        $('#modal-list-petugas').html('');

        spinner('show', $('#modal-list-petugas'), {
            width: '40px',
            height: '40px'
        });

        ajaxGet(`api/v1/petugas/list`, {idJobs : idJobs, search : search, idPenyelia: idPenyelia}, result => {
            const data = result.data;
            let html = '';
            for (const petugas of data) {
                html += `
                    <div class="border-bottom py-1 d-flex justify-content-between px-2 hover-1 rounded align-items-center">
                        <div class="text-start">
                            <div class="fw-medium">${petugas.name}</div>
                            <div class="text-secondary">${petugas.email}</div>
                        </div>
                        <div class="text-success cursoron" data-isParalel="${isParalel}" data-idjobs="${idJobs}" data-name="${petugas.name}" data-email="${petugas.email}" data-index="${index}" onclick="pilihPetugas(this,'${petugas.user_hash}')"><i class="bi bi-person-check"></i> Pilih</div>
                    </div>
                `;
            }

            if(data.length == 0){
                html = `
                    <div class="border-bottom py-1 d-flex justify-content-center px-2">
                        <div>
                            <span class="fw-medium">Tidak ada petugas</span>
                        </div>
                    </div>
                `;
            }

            $('#modal-list-petugas').html(html);
        })
    }
}

function pilihPetugas(obj,idPetugas){
    const idJobs = $(obj).data('idjobs');
    const name = $(obj).data('name');
    const email = $(obj).data('email');
    const isParalel = $(obj).data('isparalel');

    // Tambah data ke arrJobs
    let tmp = {
        idJobs: idJobs,
        idPetugas: idPetugas,
        name: name,
        email: email
    };

    if(isParalel){
        tmp.isParalel = isParalel;
    }

    // Pastikan arrJobs[index] adalah array
    // arrJobs[idJobs] = arrJobs[idJobs] || [];


    // Cek apakah idPetugas sudah ada
    const isDuplicate = arrJobs.some(job => job.idPetugas === idPetugas && job.idJobs === idJobs);

    if (!isDuplicate) {
        arrJobs.push(tmp); // Tambahkan hanya jika tidak duplikat
    }
    // return;
    loadPetugas();

    $('#modalAddPetugas').modal('hide');
}

function removePetugas(index) {
    arrJobs.splice(index, 1);
    loadPetugas();
}

function loadPetugas() {
    // Mengambil semua elemen dengan id yang dimulai dengan #list-petugas-
    $('[id^="list-petugas-"]').each(function () {
        const idElement = $(this).attr('id'); // Mendapatkan ID elemen
        const idJobs = idElement.replace('list-petugas-', ''); // Mendapatkan angka ID
        const arrPetugas = arrJobs.filter(job => job.idJobs == idJobs); // Mencari data sesuai ID

        if (arrPetugas.length > 0) {
            // Jika ada data untuk elemen ini
            let html = '';
            for (const petugas of arrPetugas) {
                let btnRemove = !['verif', 'show'].includes(typeSurat)
                    ? `<div class="text-danger cursoron" onclick="removePetugas(${arrJobs.indexOf(petugas)})"><i class="bi bi-person-fill-dash"></i> Hapus</div>`
                    : '';
                html += `
                    <div class="border-bottom py-1 d-flex justify-content-between px-1">
                        <div>
                            <span class="fw-medium">${petugas.name}</span>
                            <span class="text-secondary"> - ${petugas.email}</span>
                        </div>
                        ${btnRemove}
                    </div>
                `;
            }
            $(this).html(html);
        } else {
            // Jika tidak ada data untuk elemen ini
            $(this).html(`
                <p class="w-100 text-center fs-4 m-auto">
                    <i class="bi bi-person-fill-slash"></i> Belum ada petugas
                </p>
            `);
        }
    });
}

function validateAllPetugasFilled() {
    let isAllFilled = true;

    $('[id^="list-petugas-"]').each(function () {
        const idElement = $(this).attr('id');
        const idJobs = idElement.replace('list-petugas-', '');
        const petugas = arrJobs.find(job => job.idJobs == idJobs);

        if (!petugas) {
            isAllFilled = false; // Jika ditemukan elemen yang berisi default, ubah nilai menjadi false
            return false; // Hentikan iterasi lebih awal
        }
    });

    return isAllFilled;
}

function saveSuratTugas(obj){
    let dateStart = $('#date_start').val();
    let dateEnd = $('#date_end').val();

    if(!['verif', 'show'].includes(typeSurat)){
        if(dateStart == '' || dateEnd == '' || arrJobs.length == 0 || !validateAllPetugasFilled()) {
            return Swal.fire({
                icon: "warning",
                text: 'Tolong lengkapi',
            });
        }
    }else{
        if(signaturePad.isEmpty()){
            return Swal.fire({
                icon: "warning",
                text: "Harap berikan tanda tangan terlebih dahulu.",
            });
        }
    }

    Swal.fire({
        icon: 'warning',
        title: `${typeSurat} surat tugas?`,
        showCancelButton: true,
        confirmButtonText: 'Iya',
        cancelButtonText: 'Tidak',
        customClass: {
            confirmButton: 'btn btn-outline-success mx-1',
            cancelButton: 'btn btn-outline-danger mx-1'
        },
        buttonsStyling: false,
        reverseButtons: true
    }).then(result => {
        if(result.isConfirmed){
            const params = new FormData();
            params.append('idPenyelia', idPenyelia);
            if(!['verif', 'show'].includes(typeSurat)){
                params.append('status', 2); // Permintaan ttd manager
                params.append('startDate', dateStart);
                params.append('endDate', dateEnd);
                params.append('petugas', JSON.stringify(arrJobs));
                params.append('jobsMap', JSON.stringify(listJobs));
                params.append('jobsMapParalel', JSON.stringify(listJobsParalel));
                params.append('jenisLog', typeSurat == 'tambah' ? 'created' : 'updated');
            }else{
                params.append('status', 10); // Start Proses LHU
                params.append('ttd', signaturePad.toDataURL());
                params.append('ttd_by', userActive.user_hash);
            }

            spinner('show', $(obj));
            ajaxPost(`api/v1/penyelia/actionSuratTugas`, params, result => {
                if(result.meta.code) {
                    Swal.fire({
                        icon: 'success',
                        text: `Surat tugas berhasil di ${typeSurat}` ,
                        timer: 1200,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = `${base_url}${!['verif', 'show'].includes(typeSurat) ? "/staff/penyelia" : "/manager/surat_tugas"}`;
                        // spinner('hide', $(obj));
                    });
                }

            }, error => {
                spinner('hide', $(obj));
            });
        }
    });

}
