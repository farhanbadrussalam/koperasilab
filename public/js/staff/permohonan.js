let detail = false;
let filterComp = false;
$(function () {
    loadData();
    detail = new Detail({
        jenis: 'permohonan',
        tab: {
            tld: true,
            pengguna: true,
            dokumen: true
        }
    });

    filterComp = new FilterComponent('list-filter', {
        filter : {
            status : true,
            jenis_tld : true,
            jenis_layanan : true,
            no_kontrak : true,
            perusahaan: true,
            periode: true
        }
    })

    // SETUP FILTER
    filterComp.on('filter.change', () => loadData());
});

$('#list-pagination').on('click', 'a', function (e) {
    e.preventDefault();
    const pageno = e.target.dataset.page;

    loadData(pageno);
});

function loadData(page = 1) {
    let filterValue = filterComp && filterComp.getAllValue();
    let params = {
        limit: 5,
        page: page,
        filter: {}
    };

    filterValue.jenis_tld && (params.filter.jenis_tld = filterValue.jenis_tld);
    filterValue.status && (params.filter.status = filterValue.status);
    filterValue.jenis_layanan && (params.filter.jenis_layanan_1 = filterValue.jenis_layanan);
    filterValue.jenis_layanan_child && (params.filter.jenis_layanan_2 = filterValue.jenis_layanan_child);
    filterValue.no_kontrak && (params.filter.id_kontrak = filterValue.no_kontrak);
    filterValue.perusahaan && (params.filter.id_perusahaan = filterValue.perusahaan);
    filterValue.periode && (params.filter.periode = filterValue.periode);

    if(Object.keys(params.filter).length > 0) {
        $('#countFilter').html(Object.keys(params.filter).length);
        $('#countFilter').removeClass('d-none');
    } else {
        $('#countFilter').addClass('d-none');
    }

    $('#list-placeholder').show();
    $('#list-container').hide();
    ajaxGet(`api/v1/permohonan/listPengajuan`, params, result => {
        let html = '';

        if(result.data.length == 0){
            html = htmlNoData();
        } else {
            for (const [i, pengajuan] of result.data.entries()) {
                let badgeClass = 'bg-primary-subtle';
                if(pengajuan.tipe_kontrak == 'kontrak lama') {
                    badgeClass = 'bg-success-subtle';
                }

                let btnVerifikasi = '';
                if(pengajuan.status == 1){
                    btnVerifikasi = `
                        <li>
                            <a class="dropdown-item small cursor-pointer" title="Verifikasi" href="${base_url}/staff/permohonan/verifikasi/${pengajuan.permohonan_hash}">
                                <i class="bi bi-check2-circle me-2"></i> Verifikasi
                            </a>
                        </li>
                    `;
                }

                // periode
                let htmlPeriode = !pengajuan.periode ? 'Zero cek' : `Periode ${pengajuan.periode}`;

                let htmlStatusPenyelia = '';
                if(pengajuan.lhu){
                    htmlStatusPenyelia = "Progress Penyelia : ";
                    htmlStatusPenyelia += statusFormat('penyelia', pengajuan.lhu.status);
                    aktifJobs = pengajuan.lhu.penyelia_map.filter(d => d.status == 1);
                    aktifJobs.map(d => {
                        htmlStatusPenyelia += statusFormat('penyelia', d.jobs.status);
                    });
                }

                if(pengajuan.periode && pengajuan.is_have_tld && pengajuan.is_zerocek) {
                    htmlPeriode += ' + Zero cek';
                }

                const params = {
                    tipeKontrak: pengajuan.tipe_kontrak,
                    jenisLayananParent: pengajuan.jenis_layanan_parent.name,
                    jenisLayanan: pengajuan.jenis_layanan.name,
                    format: 'permohonan',
                    status: pengajuan.status,
                    jenisTld: pengajuan.jenis_tld?.name ?? '-',
                    namaLayanan: pengajuan.layanan_jasa?.nama_layanan,
                    periode: pengajuan.periode,
                    created_at: pengajuan.created_at,
                    kontrak: pengajuan.kontrak?.no_kontrak,
                    id: pengajuan.permohonan_hash,
                    is_have_tld: pengajuan.is_have_tld,
                    is_zerocek: pengajuan.is_zerocek,
                    note: pengajuan.note,
                    pelanggan: pengajuan.pelanggan.name,
                    statusPenyelia: htmlStatusPenyelia,
                };

                const btnAction = `
                    <li>
                        <a class="dropdown-item small cursor-pointer" title="Show detail" onclick="showDetail(this)">
                            <i class="bi bi-eye me-2"></i> Detail
                        </a>
                    </li>
                    ${btnVerifikasi}
                `;

                html += cardComponent(params, {btnMenuAction: btnAction});
            }
        }

        $('#list-container').html(html);

        $('#list-pagination').html(createPaginationHTML(result.pagination));

        $('#list-placeholder').hide();
        $('#list-container').show();
    })
}

function showDetail(obj){
    const idPermohonan = $(obj).parent().parent().data("id");
    detail.show(`api/v1/permohonan/getPengajuanById/${idPermohonan}`);
}

function reload(){
    loadData();
}

function clearFilter(){
    filterComp.clear();
    loadData();
}
