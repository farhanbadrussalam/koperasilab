function openPenyimpananModal(obj) {
    $('#content-penyimpanan-tld').html('');
    $('#penyimpananModal').modal('show');
    spinner('show', $('#content-penyimpanan-tld'), {
        width: '50px',
        height: '50px'
    });
    const index = $(obj).parent().data("index");
    ajaxGet(`api/v1/penyelia/getById/${dataPenyelia[index].penyelia_hash}`, false, result => {
        let htmlRincianTld = '';

        for (const detail of result.data.permohonan?.kontrak?.rincian_list_tld) {
            if(result.data.periodenow.count_tld == detail.count_tld){
                let html = ``;
                let inPenyimpanan = detail.status == 5 ? true : false;
                for (const TLD of detail.tld) {
                    html += `
                        <div class="card card-default mb-1">
                            <div class="card-body d-flex justify-content-between py-2">
                                <span>${TLD.no_seri_tld}</span>
                                <div class="">
                                    <small class="text-${inPenyimpanan ? 'secondary' : 'success'}">${inPenyimpanan ? 'Penyimpanan' : 'Aktif'}</small>
                                </div>
                            </div>
                        </div>
                    `;
                }

                if(!detail.pengguna){
                    htmlRincianTld = html + htmlRincianTld;
                } else {
                    htmlRincianTld += html;
                }
            }
        }

        if(htmlRincianTld == ''){
            htmlRincianTld = `
                <div class="card card-default mb-1">
                    <div class="card-body text-center py-2">
                        <span>Tidak ada TLD</span>
                    </div>
                </div>
            `;
        }

        spinner('hide', $('#content-penyimpanan-tld'));
        $('#content-penyimpanan-tld').html(htmlRincianTld);
    });
}
