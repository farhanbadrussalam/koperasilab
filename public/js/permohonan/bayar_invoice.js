$(function() {
    console.log(dataKeuangan);
    descInvoice(dataKeuangan);
})

function descInvoice(keuangan){
    let dataPengajuan = keuangan.permohonan;

    let hargaLayanan = dataPengajuan.harga_layanan;
    let qty = dataPengajuan.jumlah_kontrol+dataPengajuan.jumlah_pengguna;
    let jumLayanan = dataPengajuan.total_harga;
    let periode = JSON.parse(dataPengajuan.periode_pemakaian);
    let jumPpn = 0;
    let jumDiskon = 0;
    let descInvoice = `
        <tr>
            <th class="text-start">${dataPengajuan.layanan_jasa.nama_layanan}</th>
            <td>${formatRupiah(hargaLayanan)}</td>
            <td>${qty}</td>
            <td>${periode.length}</td>
            <td>${formatRupiah(jumLayanan)}</td>
        </tr>
    `;

    if(keuangan.ppn){
        let valPpn = keuangan.ppn;
        jumPpn = jumLayanan * (valPpn/100);
        descInvoice += `
            <tr>
                <th class="text-start">PPN ${valPpn}%</th>
                <td></td>
                <td></td>
                <td></td>
                <td>${formatRupiah(jumPpn)}</td>
            </tr>
        `;
    }
    
    for (const [i,diskon] of keuangan.diskon.entries()) {
        countDiskon = jumLayanan * (diskon.diskon/100);
        jumDiskon += countDiskon;
        descInvoice += `
            <tr>
                <th class="text-start">${diskon.name}&nbsp${diskon.diskon}%</th>
                <td></td>
                <th colspan="2"></th>
                <td>- ${formatRupiah(countDiskon)}</td>
            </tr>
        `;
    }

    // total harga
    let jumTotal = jumLayanan + jumPpn - jumDiskon;
    descInvoice += `
        <tr>
            <td></td>
            <td></td>
            <th colspan="2">Total Jumlah</th>
            <td>${formatRupiah(jumTotal)}</td>
        </tr>
    `;
    $('#deskripsiInvoice').html(descInvoice);
    
}