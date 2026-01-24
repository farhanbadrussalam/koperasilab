$(function () {
    loadPeriode();
    loadKontrakTld();
});


function loadPeriode(){
    let listPeriode = document.getElementById("list-periode");
    let periode = dataKontrak.periode;

    periode.forEach((data, index) => {
        if(data.periode == 0) return;

        listPeriode.innerHTML += `
            <div class="d-flex justify-content-between align-items-center p-2 mb-2 bg-light rounded-3 border mt-1">
                <div>
                    <span class="badge bg-secondary mb-1">Periode ${data.periode}</span>
                    <div class="small fw-bold text-dark">${dateFormat(data.start_date, 4)} - ${dateFormat(data.end_date, 4)}</div>
                </div>
                <i class="bi bi-lock-fill text-secondary fs-5" title="Tidak dapat dihapus"></i>
            </div>
        `;
    })
}

function loadKontrakTld(){
    let params = {
        id_kontrak : dataKontrak.kontrak_hash
    }

    ajaxGet(`api/v1/kontrak/getKontrakTld`, params, result => {
        let htmlPengguna = '';
        let htmlKontrol = '';

        // pisahkan pengguna dan kontrol
        let pengguna = result.data.filter(tld => tld.pengguna);
        let kontrol = result.data.filter(tld => !tld.pengguna);

        // load tld pengguna
        for (const [i, value] of pengguna.entries()) {
            // Radiasi
            let radiasi = value.pengguna.radiasi?.map(d => d.nama_radiasi);

            let fileKtp = value.pengguna.media_ktp ? `${base_url}/storage/${value.pengguna.media_ktp.file_path}/${value.pengguna.media_ktp.file_hash}` : '';

            let data = {
                index: i,
                idHash: value.kontrak_tld_hash,
                name: value.pengguna.name,
                divisi: value.pengguna.divisi?.name || '',
                isCheckedEvaluasi: false,
                radiasi: radiasi,
                fileKtp: fileKtp,
                no_seri_tld: value.tld[0].no_seri_tld,
                htmlDisabled: true
            };

            htmlPengguna += cardPenggunaComponent(data);
        }

        $('#tld-pengguna').html(htmlPengguna);

        // load tld kontrol
        console.log(kontrol);
        for (const [i, value] of kontrol.entries()) {
            let tldHash = '';
            let no_seri_tld = '';

            for (let idx = 0; idx < value.count; idx++) {
                if(value.tld) {
                    tldHash = value.tld[idx].tld_hash;
                    no_seri_tld = value.tld[idx].no_seri_tld;
                } else {
                    tldHash = '';
                    no_seri_tld = '';
                }

                let kodeLencana = value.count > 1 ? `C${idx+1}` : 'C';

                let data = {
                    name: `Kontrol ${value.divisi?.name ?? ''} ${kodeLencana}`,
                    kode: kodeLencana,
                    index: idx,
                    tldHash: value.kontrak_tld_hash,
                    no_seri_tld: no_seri_tld,
                    htmlDisabled: true
                }

                htmlKontrol += cardKontrolComponent(data);
            }
        }

        $('#tld-kontrol').html(htmlKontrol);

        showPopupReload();
    });
}
