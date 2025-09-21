<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property-read mixed $permohonan_log_hash
 * @method static \Illuminate\Database\Eloquent\Builder|Detail_permohonan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Detail_permohonan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Detail_permohonan query()
 */
	class Detail_permohonan extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_doc
 * @property string|null $name
 * @property string|null $jenis
 * @property array|null $pertanyaan
 * @property int|null $status 1 = active, 99 = remove
 * @property int|null $version
 * @property int|null $id_doc_version
 * @property string $content
 * @property int|null $id_header
 * @property int|null $id_footer
 * @property string|null $alias
 * @property array|null $variables
 * @property string|null $view
 * @property string|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Documents|null $footer
 * @property-read mixed $data_pertanyaan
 * @property-read mixed $doc_hash
 * @property-read mixed $doc_version_hash
 * @property-read mixed $footer_hash
 * @property-read mixed $header_hash
 * @property-read Documents|null $header
 * @method static \Illuminate\Database\Eloquent\Builder|Documents newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Documents newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Documents query()
 * @method static \Illuminate\Database\Eloquent\Builder|Documents whereAlias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Documents whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Documents whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Documents whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Documents whereIdDoc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Documents whereIdDocVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Documents whereIdFooter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Documents whereIdHeader($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Documents whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Documents whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Documents wherePertanyaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Documents whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Documents whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Documents whereVariables($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Documents whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Documents whereView($value)
 */
	class Documents extends \Eloquent {}
}

namespace App\Models{
/**
 * @property-read mixed $jadwalpetugas_hash
 * @property-read mixed $otorisasi
 * @property-read \App\Models\User|null $petugas
 * @method static \Illuminate\Database\Eloquent\Builder|Jadwal_petugas newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Jadwal_petugas newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Jadwal_petugas query()
 */
	class Jadwal_petugas extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_jenis_pembayaran
 * @property int|null $id_satuankerja
 * @property string|null $name
 * @property string|null $content
 * @property int|null $status 1 = aktif, 0 = tidak aktif
 * @property array|null $variables
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $jenis_pembayaran_hash
 * @method static \Illuminate\Database\Eloquent\Builder|Jenis_pembayaran newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Jenis_pembayaran newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Jenis_pembayaran query()
 * @method static \Illuminate\Database\Eloquent\Builder|Jenis_pembayaran whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jenis_pembayaran whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jenis_pembayaran whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jenis_pembayaran whereIdJenisPembayaran($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jenis_pembayaran whereIdSatuankerja($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jenis_pembayaran whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jenis_pembayaran whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jenis_pembayaran whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jenis_pembayaran whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jenis_pembayaran whereVariables($value)
 */
	class Jenis_pembayaran extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_keuangan
 * @property int|null $id_permohonan
 * @property string|null $id_pengiriman
 * @property int|null $id_jenis_pembayaran
 * @property array|null $variabel_jenis_pembayaran
 * @property string|null $no_invoice
 * @property int|null $status
 * @property int|null $ppn
 * @property int|null $pph
 * @property array|null $document_faktur
 * @property array|null $bukti_bayar
 * @property array|null $bukti_bayar_pph
 * @property string|null $ttd
 * @property int|null $ttd_by
 * @property int|null $plt
 * @property int|null $total_harga
 * @property string|null $paid_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Keuangan_diskon> $diskon
 * @property-read int|null $diskon_count
 * @property-read mixed $keuangan_hash
 * @property-read mixed $media
 * @property-read mixed $media_bukti_bayar
 * @property-read mixed $media_bukti_bayar_pph
 * @property-read mixed $permohonan_hash
 * @property-read \App\Models\Jenis_pembayaran|null $metode_pembayaran
 * @property-read \App\Models\Pengiriman|null $pengiriman
 * @property-read \App\Models\Permohonan|null $permohonan
 * @property-read \App\Models\User|null $usersig
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan query()
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan whereBuktiBayar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan whereBuktiBayarPph($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan whereDocumentFaktur($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan whereIdJenisPembayaran($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan whereIdKeuangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan whereIdPengiriman($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan whereIdPermohonan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan whereNoInvoice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan wherePlt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan wherePph($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan wherePpn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan whereTotalHarga($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan whereTtd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan whereTtdBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan whereVariabelJenisPembayaran($value)
 */
	class Keuangan extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_diskon
 * @property int|null $id_keuangan
 * @property string|null $name
 * @property int|null $diskon
 * @property-read mixed $diskon_hash
 * @property-read mixed $keuangan_hash
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan_diskon newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan_diskon newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan_diskon query()
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan_diskon whereDiskon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan_diskon whereIdDiskon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan_diskon whereIdKeuangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan_diskon whereName($value)
 */
	class Keuangan_diskon extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_kontrak
 * @property int|null $id_layanan
 * @property int|null $id_keuangan
 * @property int|null $jenis_layanan_1
 * @property int|null $jenis_layanan_2
 * @property string|null $tipe_kontrak
 * @property string|null $no_kontrak
 * @property int|null $jenis_tld
 * @property string|null $periode_pemakaian
 * @property array|null $periode_next
 * @property int|null $jumlah_pengguna
 * @property int|null $jumlah_kontrol
 * @property int|null $harga_layanan
 * @property string|null $ttd
 * @property int|null $ttd_by
 * @property int|null $total_harga
 * @property int $status
 * @property string|null $note
 * @property int|null $file_lhu
 * @property int|null $id_pelanggan
 * @property int|null $is_have_tld
 * @property int|null $is_zerocek
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permohonan_dokumen> $dokumen
 * @property-read int|null $dokumen_count
 * @property-read mixed $data_radiasi
 * @property-read mixed $document_kontrak
 * @property-read mixed $kontrak_hash
 * @property-read mixed $periode_all
 * @property-read \App\Models\Keuangan|null $invoice
 * @property-read \App\Models\Master_jenistld|null $jenisTld
 * @property-read \App\Models\Master_jenisLayanan|null $jenis_layanan
 * @property-read \App\Models\Master_jenisLayanan|null $jenis_layanan_parent
 * @property-read \App\Models\Master_layanan_jasa|null $layanan_jasa
 * @property-read \App\Models\User|null $pelanggan
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Kontrak_tld> $pengguna
 * @property-read int|null $pengguna_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pengiriman> $pengiriman
 * @property-read int|null $pengiriman_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Kontrak_periode> $periode
 * @property-read int|null $periode_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Kontrak_tld> $rincian_list_tld
 * @property-read int|null $rincian_list_tld_count
 * @property-read \App\Models\User|null $signature
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Master_tld> $tld_aktif
 * @property-read int|null $tld_aktif_count
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak query()
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereFileLhu($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereHargaLayanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereIdKeuangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereIdKontrak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereIdLayanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereIdPelanggan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereIsHaveTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereIsZerocek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereJenisLayanan1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereJenisLayanan2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereJenisTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereJumlahKontrol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereJumlahPengguna($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereNoKontrak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak wherePeriodeNext($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak wherePeriodePemakaian($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereTipeKontrak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereTotalHarga($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereTtd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereTtdBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereUpdatedAt($value)
 */
	class Kontrak extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_map_pengguna
 * @property int $id_kontrak
 * @property int $id_pengguna
 * @property int|null $id_tld
 * @property int $status
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $pengguna_map_hash
 * @property-read \App\Models\Kontrak_tld|null $kontrak_tld
 * @property-read \App\Models\Master_pengguna|null $pengguna
 * @property-read \App\Models\Master_tld|null $tld_pengguna
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_pengguna newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_pengguna newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_pengguna query()
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_pengguna whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_pengguna whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_pengguna whereIdKontrak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_pengguna whereIdMapPengguna($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_pengguna whereIdPengguna($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_pengguna whereIdTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_pengguna whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_pengguna whereUpdatedAt($value)
 */
	class Kontrak_pengguna extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_periode
 * @property int|null $id_kontrak
 * @property int|null $periode
 * @property string|null $start_date
 * @property string|null $end_date
 * @property int|null $id_permohonan Untuk permohonan evaluasi
 * @property string|null $nomer_surpeng
 * @property int|null $status
 * @property int|null $count_tld
 * @property int|null $created_by
 * @property string|null $created_surpeng_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $periode_hash
 * @property-read mixed $permohonan_hash
 * @property-read mixed $tld_in_periode
 * @property-read \App\Models\Kontrak|null $kontrak
 * @property-read \App\Models\Penyelia|null $penyelia
 * @property-read \App\Models\Permohonan|null $permohonan
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_periode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_periode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_periode query()
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_periode whereCountTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_periode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_periode whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_periode whereCreatedSurpengAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_periode whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_periode whereIdKontrak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_periode whereIdPeriode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_periode whereIdPermohonan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_periode whereNomerSurpeng($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_periode wherePeriode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_periode whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_periode whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_periode whereUpdatedAt($value)
 */
	class Kontrak_periode extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_kontrak_tld
 * @property int|null $id_kontrak
 * @property array|null $id_tld
 * @property int|null $count
 * @property int|null $id_pengguna
 * @property int|null $id_divisi
 * @property int|null $count_tld
 * @property int|null $status
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Master_divisi|null $divisi
 * @property-read mixed $kontrak_hash
 * @property-read mixed $kontrak_tld_hash
 * @property-read \App\Models\Master_tld|null $tld
 * @property-read \App\Models\Kontrak|null $kontrak
 * @property-read \App\Models\Master_pengguna|null $pengguna
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_tld newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_tld newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_tld query()
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_tld whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_tld whereCountTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_tld whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_tld whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_tld whereIdDivisi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_tld whereIdKontrak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_tld whereIdKontrakTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_tld whereIdPengguna($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_tld whereIdTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_tld whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_tld whereUpdatedAt($value)
 */
	class Kontrak_tld extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $id_keuangan
 * @property int|null $status
 * @property string|null $note
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $log_keuangan_hash
 * @method static \Illuminate\Database\Eloquent\Builder|Log_keuangan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Log_keuangan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Log_keuangan query()
 * @method static \Illuminate\Database\Eloquent\Builder|Log_keuangan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_keuangan whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_keuangan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_keuangan whereIdKeuangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_keuangan whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_keuangan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_keuangan whereUpdatedAt($value)
 */
	class Log_keuangan extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $id_pengiriman
 * @property int|null $status
 * @property string|null $note
 * @property int|null $media
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $log_pengiriman_hash
 * @method static \Illuminate\Database\Eloquent\Builder|Log_pengiriman newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Log_pengiriman newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Log_pengiriman query()
 * @method static \Illuminate\Database\Eloquent\Builder|Log_pengiriman whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_pengiriman whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_pengiriman whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_pengiriman whereIdPengiriman($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_pengiriman whereMedia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_pengiriman whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_pengiriman whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_pengiriman whereUpdatedAt($value)
 */
	class Log_pengiriman extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $id_penyelia
 * @property int|null $id_map
 * @property int|null $status
 * @property string|null $message
 * @property string|null $note
 * @property string|null $document
 * @property int|null $flag
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $log_penyelia_hash
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Log_penyelia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Log_penyelia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Log_penyelia query()
 * @method static \Illuminate\Database\Eloquent\Builder|Log_penyelia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_penyelia whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_penyelia whereDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_penyelia whereFlag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_penyelia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_penyelia whereIdMap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_penyelia whereIdPenyelia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_penyelia whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_penyelia whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_penyelia whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_penyelia whereUpdatedAt($value)
 */
	class Log_penyelia extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $id_permohonan
 * @property int $status
 * @property int|null $flag
 * @property string|null $note
 * @property int|null $file
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $log_permohonan_hash
 * @method static \Illuminate\Database\Eloquent\Builder|Log_permohonan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Log_permohonan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Log_permohonan query()
 * @method static \Illuminate\Database\Eloquent\Builder|Log_permohonan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_permohonan whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_permohonan whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_permohonan whereFlag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_permohonan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_permohonan whereIdPermohonan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_permohonan whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_permohonan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_permohonan whereUpdatedAt($value)
 */
	class Log_permohonan extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_log_tld
 * @property int|null $id_tld
 * @property int|null $status
 * @property string|null $message
 * @property string|null $note
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $log_tld_hash
 * @method static \Illuminate\Database\Eloquent\Builder|Log_tld newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Log_tld newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Log_tld query()
 * @method static \Illuminate\Database\Eloquent\Builder|Log_tld whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_tld whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_tld whereIdLogTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_tld whereIdTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_tld whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_tld whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_tld whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_tld whereUpdatedAt($value)
 */
	class Log_tld extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_alamat
 * @property int|null $id_perusahaan
 * @property string|null $jenis
 * @property string|null $alamat
 * @property string|null $kode_pos
 * @property int|null $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $alamat_hash
 * @method static \Illuminate\Database\Eloquent\Builder|Master_alamat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_alamat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_alamat query()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_alamat whereAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_alamat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_alamat whereIdAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_alamat whereIdPerusahaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_alamat whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_alamat whereKodePos($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_alamat whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_alamat whereUpdatedAt($value)
 */
	class Master_alamat extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_divisi
 * @property string|null $kode_lencana
 * @property int|null $id_perusahaan
 * @property string|null $name
 * @property int|null $status
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $divisi_hash
 * @property-read \App\Models\Perusahaan|null $perusahaan
 * @method static \Illuminate\Database\Eloquent\Builder|Master_divisi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_divisi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_divisi query()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_divisi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_divisi whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_divisi whereIdDivisi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_divisi whereIdPerusahaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_divisi whereKodeLencana($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_divisi whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_divisi whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_divisi whereUpdatedAt($value)
 */
	class Master_divisi extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_ekspedisi
 * @property string|null $name
 * @property string|null $deskripsi
 * @property int|null $status
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $ekspedisi_hash
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ekspedisi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ekspedisi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ekspedisi query()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ekspedisi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ekspedisi whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ekspedisi whereDeskripsi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ekspedisi whereIdEkspedisi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ekspedisi whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ekspedisi whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ekspedisi whereUpdatedAt($value)
 */
	class Master_ekspedisi extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_jenisLayanan
 * @property string|null $name
 * @property int|null $parent
 * @property array|null $jobs
 * @property array|null $jobs_paralel
 * @property int|null $jobs_paralel_point
 * @property int|null $status
 * @property string|null $alias
 * @property int|null $created_by
 * @property string|null $created_date
 * @property string|null $updated_date
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Master_jenisLayanan> $child
 * @property-read int|null $child_count
 * @property-read mixed $jenis_layanan_hash
 * @property-read mixed $parent_hash
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenisLayanan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenisLayanan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenisLayanan query()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenisLayanan whereAlias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenisLayanan whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenisLayanan whereCreatedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenisLayanan whereIdJenisLayanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenisLayanan whereJobs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenisLayanan whereJobsParalel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenisLayanan whereJobsParalelPoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenisLayanan whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenisLayanan whereParent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenisLayanan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenisLayanan whereUpdatedDate($value)
 */
	class Master_jenisLayanan extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_jenisTld
 * @property string|null $name
 * @property string|null $order_jobs
 * @property int|null $status
 * @property-read mixed $jenis_tld_hash
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenistld newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenistld newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenistld query()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenistld whereIdJenisTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenistld whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenistld whereOrderJobs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenistld whereStatus($value)
 */
	class Master_jenistld extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_jobs
 * @property int|null $id_layanan
 * @property string|null $name
 * @property int|null $order
 * @property int|null $status
 * @property int|null $upload_doc
 * @property-read mixed $jobs_hash
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jobs newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jobs newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jobs query()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jobs whereIdJobs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jobs whereIdLayanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jobs whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jobs whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jobs whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jobs whereUploadDoc($value)
 */
	class Master_jobs extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_layanan
 * @property string|null $nama_layanan
 * @property int $status
 * @property array|null $jobs
 * @property int|null $satuankerja_id
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $layanan_hash
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Master_jobs> $jobs_pelaksana
 * @property-read int|null $jobs_pelaksana_count
 * @property-read \App\Models\Satuan_kerja|null $satuankerja
 * @method static \Illuminate\Database\Eloquent\Builder|Master_layanan_jasa newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_layanan_jasa newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_layanan_jasa query()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_layanan_jasa whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_layanan_jasa whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_layanan_jasa whereIdLayanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_layanan_jasa whereJobs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_layanan_jasa whereNamaLayanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_layanan_jasa whereSatuankerjaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_layanan_jasa whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_layanan_jasa whereUpdatedAt($value)
 */
	class Master_layanan_jasa extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $file_hash
 * @property string $file_ori
 * @property int $file_size
 * @property string $file_type
 * @property string|null $file_path
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $media_hash
 * @property-read \App\Models\Keuangan|null $keuangan
 * @method static \Illuminate\Database\Eloquent\Builder|Master_media newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_media newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_media query()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_media whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_media whereFileHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_media whereFileOri($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_media whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_media whereFileSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_media whereFileType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_media whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_media whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_media whereUpdatedAt($value)
 */
	class Master_media extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_pengguna
 * @property array|null $id_radiasi
 * @property int|null $id_perusahaan
 * @property string|null $kode_lencana
 * @property string|null $nik
 * @property string|null $name
 * @property int|null $id_divisi
 * @property string|null $jenis_kelamin
 * @property string|null $tempat_lahir
 * @property string|null $tanggal_lahir
 * @property int|null $ktp
 * @property string|null $keterangan
 * @property int|null $status
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Master_divisi|null $divisi
 * @property-read mixed $pengguna_hash
 * @property-read mixed $radiasi
 * @property-read \App\Models\Master_media|null $media_ktp
 * @property-read \App\Models\Perusahaan|null $perusahaan
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna query()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna whereIdDivisi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna whereIdPengguna($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna whereIdPerusahaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna whereIdRadiasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna whereJenisKelamin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna whereKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna whereKodeLencana($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna whereKtp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna whereNik($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna whereTanggalLahir($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna whereTempatLahir($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna whereUpdatedAt($value)
 */
	class Master_pengguna extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_pertanyaan
 * @property int|null $id_layananjasa
 * @property string|null $pertanyaan
 * @property int|null $type
 * @property int|null $mandatory
 * @property-read mixed $pertanyaan_hash
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pertanyaan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pertanyaan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pertanyaan query()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pertanyaan whereIdLayananjasa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pertanyaan whereIdPertanyaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pertanyaan whereMandatory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pertanyaan wherePertanyaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pertanyaan whereType($value)
 */
	class Master_pertanyaan extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_price
 * @property int|null $id_jenisTld
 * @property string|null $id_jenisLayanan
 * @property string|null $keterangan
 * @property int|null $qty
 * @property int|null $price
 * @property int|null $created_by
 * @property string|null $created_date
 * @property string|null $updated_date
 * @property-read mixed $jenis_tld_hash
 * @property-read mixed $price_hash
 * @property-read \App\Models\Master_jenistld|null $jenisTld
 * @method static \Illuminate\Database\Eloquent\Builder|Master_price newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_price newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_price query()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_price whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_price whereCreatedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_price whereIdJenisLayanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_price whereIdJenisTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_price whereIdPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_price whereKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_price wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_price whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_price whereUpdatedDate($value)
 */
	class Master_price extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_radiasi
 * @property string|null $nama_radiasi
 * @property int|null $status
 * @property-read mixed $radiasi_hash
 * @method static \Illuminate\Database\Eloquent\Builder|Master_radiasi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_radiasi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_radiasi query()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_radiasi whereIdRadiasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_radiasi whereNamaRadiasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_radiasi whereStatus($value)
 */
	class Master_radiasi extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_tld
 * @property string|null $no_seri_tld
 * @property string|null $merk
 * @property string|null $jenis
 * @property string|null $tanggal_pengadaan
 * @property int|null $kepemilikan
 * @property string|null $digunakan
 * @property int|null $status
 * @property-read mixed $tld_hash
 * @property-read \App\Models\Perusahaan|null $pemilik
 * @method static \Illuminate\Database\Eloquent\Builder|Master_tld newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_tld newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_tld query()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_tld whereDigunakan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_tld whereIdTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_tld whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_tld whereKepemilikan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_tld whereMerk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_tld whereNoSeriTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_tld whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_tld whereTanggalPengadaan($value)
 */
	class Master_tld extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id_pengiriman
 * @property string|null $no_resi
 * @property string|null $jenis_pengiriman
 * @property int|null $id_ekspedisi
 * @property int|null $id_permohonan
 * @property int|null $id_kontrak
 * @property \App\Models\User|null $tujuan
 * @property \App\Models\Master_alamat|null $alamat
 * @property string|null $detail_alamat
 * @property int|null $status
 * @property int|null $periode
 * @property array|null $bukti_pengiriman
 * @property array|null $bukti_penerima
 * @property int|null $created_by
 * @property string|null $send_at
 * @property string|null $recived_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pengiriman_detail> $detail
 * @property-read int|null $detail_count
 * @property-read \App\Models\Master_ekspedisi|null $ekspedisi
 * @property-read mixed $permohonan_hash
 * @property-read \App\Models\Kontrak|null $kontrak
 * @property-read \App\Models\Permohonan|null $permohonan
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman query()
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereBuktiPenerima($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereBuktiPengiriman($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereDetailAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereIdEkspedisi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereIdKontrak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereIdPengiriman($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereIdPermohonan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereJenisPengiriman($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereNoResi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman wherePeriode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereRecivedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereSendAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereTujuan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereUpdatedAt($value)
 */
	class Pengiriman extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_pengiriman_detail
 * @property string|null $id_pengiriman
 * @property string|null $jenis
 * @property int|null $periode
 * @property array|null $list_tld
 * @property string|null $nomer_surpeng
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $pengiriman_detail_hash
 * @property-read mixed $pengiriman_hash
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman_detail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman_detail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman_detail query()
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman_detail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman_detail whereIdPengiriman($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman_detail whereIdPengirimanDetail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman_detail whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman_detail whereListTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman_detail whereNomerSurpeng($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman_detail wherePeriode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman_detail whereUpdatedAt($value)
 */
	class Pengiriman_detail extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_penyelia
 * @property int|null $id_permohonan
 * @property string|null $id_pengiriman
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 * @property int|null $periode
 * @property int|null $status
 * @property string|null $ttd
 * @property int|null $ttd_by
 * @property array|null $document
 * @property string|null $list_tld
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $createBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permohonan_dokumen> $dokumen
 * @property-read int|null $dokumen_count
 * @property-read mixed $media
 * @property-read mixed $penyelia_hash
 * @property-read mixed $permohonan_hash
 * @property-read mixed $status_hash
 * @property-read mixed $template_surat
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Log_penyelia> $log
 * @property-read int|null $log_count
 * @property-read \App\Models\Pengiriman|null $pengiriman
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Penyelia_map> $penyelia_map
 * @property-read int|null $penyelia_map_count
 * @property-read \App\Models\Kontrak_periode|null $periodenow
 * @property-read \App\Models\Permohonan|null $permohonan
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Penyelia_petugas> $petugas
 * @property-read int|null $petugas_count
 * @property-read \App\Models\User|null $usersig
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia query()
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia whereDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia whereIdPengiriman($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia whereIdPenyelia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia whereIdPermohonan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia whereListTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia wherePeriode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia whereTtd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia whereTtdBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia whereUpdatedAt($value)
 */
	class Penyelia extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_map
 * @property int|null $id_penyelia
 * @property int|null $id_jobs
 * @property int|null $order
 * @property int|null $status 1 = selesai
 * @property int|null $point_jobs
 * @property int|null $done_by
 * @property string|null $done_at
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $doneBy
 * @property-read mixed $jobs_hash
 * @property-read mixed $map_hash
 * @property-read \App\Models\Master_jobs|null $jobs
 * @property-read \App\Models\Master_jobs|null $jobs_paralel
 * @property-read \App\Models\Penyelia|null $penyelia
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Penyelia_petugas> $petugas
 * @property-read int|null $petugas_count
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_map newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_map newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_map query()
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_map whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_map whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_map whereDoneAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_map whereDoneBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_map whereIdJobs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_map whereIdMap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_map whereIdPenyelia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_map whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_map wherePointJobs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_map whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_map whereUpdatedAt($value)
 */
	class Penyelia_map extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_petugas
 * @property int|null $id_user
 * @property int|null $id_map
 * @property int|null $id_penyelia
 * @property int|null $status
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $map_hash
 * @property-read mixed $penyelia_hash
 * @property-read mixed $petugas_hash
 * @property-read mixed $user_hash
 * @property-read \App\Models\Penyelia_map|null $jobs
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_petugas newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_petugas newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_petugas query()
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_petugas whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_petugas whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_petugas whereIdMap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_petugas whereIdPenyelia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_petugas whereIdPetugas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_petugas whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_petugas whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_petugas whereUpdatedAt($value)
 */
	class Penyelia_petugas extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_permohonan
 * @property int|null $id_layanan
 * @property int|null $id_kontrak
 * @property string|null $id_pengiriman
 * @property int|null $id_alamat
 * @property int|null $jenis_layanan_1
 * @property int|null $jenis_layanan_2
 * @property string|null $tipe_kontrak
 * @property int|null $jenis_tld
 * @property array|null $periode_pemakaian
 * @property array|null $periode_next
 * @property int|null $periode Di ambil dari kontrak_periode
 * @property int|null $jumlah_pengguna
 * @property int|null $jumlah_kontrol
 * @property int|null $harga_layanan
 * @property string|null $pic
 * @property string|null $no_hp
 * @property string|null $ttd
 * @property int|null $ttd_by
 * @property int|null $total_harga
 * @property int $status
 * @property string|null $note
 * @property \App\Models\Master_media|null $file_lhu
 * @property int|null $flag_read
 * @property int|null $is_have_tld
 * @property int|null $is_zerocek
 * @property int|null $created_by
 * @property string|null $verify_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permohonan_dokumen> $dokumen
 * @property-read int|null $dokumen_count
 * @property-read mixed $kontrak_hash
 * @property-read mixed $permohonan_hash
 * @property-read \App\Models\Keuangan|null $invoice
 * @property-read \App\Models\Master_jenistld|null $jenisTld
 * @property-read \App\Models\Master_jenisLayanan|null $jenis_layanan
 * @property-read \App\Models\Master_jenisLayanan|null $jenis_layanan_parent
 * @property-read \App\Models\Kontrak|null $kontrak
 * @property-read \App\Models\Master_layanan_jasa|null $layanan_jasa
 * @property-read \App\Models\Penyelia|null $lhu
 * @property-read \App\Models\User|null $pelanggan
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permohonan_pengguna> $pengguna
 * @property-read int|null $pengguna_count
 * @property-read \App\Models\Pengiriman|null $pengiriman
 * @property-read \App\Models\Kontrak_periode|null $periodenow
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permohonan_tld> $rincian_list_tld
 * @property-read int|null $rincian_list_tld_count
 * @property-read \App\Models\User|null $signature
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permohonan_tandaterima> $tandaterima
 * @property-read int|null $tandaterima_count
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan query()
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereFileLhu($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereFlagRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereHargaLayanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereIdAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereIdKontrak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereIdLayanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereIdPengiriman($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereIdPermohonan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereIsHaveTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereIsZerocek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereJenisLayanan1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereJenisLayanan2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereJenisTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereJumlahKontrol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereJumlahPengguna($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereNoHp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan wherePeriode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan wherePeriodeNext($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan wherePeriodePemakaian($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan wherePic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereTipeKontrak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereTotalHarga($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereTtd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereTtdBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereVerifyAt($value)
 */
	class Permohonan extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_dokumen
 * @property int|null $id_permohonan
 * @property int|null $id_kontrak
 * @property int|null $id_doc_template
 * @property int|null $periode
 * @property string|null $nomer
 * @property string|null $nama
 * @property int|null $status
 * @property string|null $jenis
 * @property string|null $ttd
 * @property int|null $ttd_by
 * @property string|null $catatan
 * @property array|null $variables
 * @property array|null $content_value
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Documents|null $doc_template
 * @property-read mixed $dokumen_hash
 * @property-read mixed $permohonan_hash
 * @property-read \App\Models\User|null $usersig
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen query()
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen whereCatatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen whereContentValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen whereIdDocTemplate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen whereIdDokumen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen whereIdKontrak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen whereIdPermohonan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen whereNomer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen wherePeriode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen whereTtd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen whereTtdBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen whereVariables($value)
 */
	class Permohonan_dokumen extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_map_pengguna
 * @property int $id_permohonan
 * @property int $id_pengguna
 * @property int|null $id_tld
 * @property int|null $status
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $pengguna_map_hash
 * @property-read \App\Models\Master_pengguna|null $pengguna
 * @property-read \App\Models\Permohonan_tld|null $permohonan_tld
 * @property-read \App\Models\Master_tld|null $tld_pengguna
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_pengguna newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_pengguna newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_pengguna query()
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_pengguna whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_pengguna whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_pengguna whereIdMapPengguna($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_pengguna whereIdPengguna($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_pengguna whereIdPermohonan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_pengguna whereIdTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_pengguna whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_pengguna whereUpdatedAt($value)
 */
	class Permohonan_pengguna extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_permohonan
 * @property int $id_pertanyaan
 * @property string|null $jawaban
 * @property string|null $note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $permohonan_hash
 * @property-read mixed $pertanyaan_hash
 * @property-read \App\Models\Master_pertanyaan $pertanyaan
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tandaterima newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tandaterima newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tandaterima query()
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tandaterima whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tandaterima whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tandaterima whereIdPermohonan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tandaterima whereIdPertanyaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tandaterima whereJawaban($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tandaterima whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tandaterima whereUpdatedAt($value)
 */
	class Permohonan_tandaterima extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_permohonan_tld
 * @property int|null $id_permohonan
 * @property array|null $id_tld
 * @property int|null $id_kontrak_tld
 * @property string|null $tld_tmp
 * @property int|null $count
 * @property int|null $id_pengguna
 * @property int|null $id_divisi
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Master_divisi|null $divisi
 * @property-read mixed $kontrak_tld_hash
 * @property-read mixed $permohonan_hash
 * @property-read mixed $permohonan_tld_hash
 * @property-read mixed $tld
 * @property-read \App\Models\Master_pengguna|null $pengguna
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tld newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tld newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tld query()
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tld whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tld whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tld whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tld whereIdDivisi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tld whereIdKontrakTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tld whereIdPengguna($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tld whereIdPermohonan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tld whereIdPermohonanTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tld whereIdTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tld whereTldTmp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tld whereUpdatedAt($value)
 */
	class Permohonan_tld extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_perusahaan
 * @property string $nama_perusahaan
 * @property string|null $npwp_perusahaan
 * @property string|null $kode_perusahaan
 * @property string|null $email
 * @property int|null $surat_kuasa
 * @property int|null $status
 * @property string|null $confirm_at
 * @property int|null $confirm_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Master_alamat> $alamat
 * @property-read int|null $alamat_count
 * @property-read mixed $perusahaan_hash
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Perusahaan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Perusahaan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Perusahaan query()
 * @method static \Illuminate\Database\Eloquent\Builder|Perusahaan whereConfirmAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Perusahaan whereConfirmBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Perusahaan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Perusahaan whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Perusahaan whereIdPerusahaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Perusahaan whereKodePerusahaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Perusahaan whereNamaPerusahaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Perusahaan whereNpwpPerusahaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Perusahaan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Perusahaan whereSuratKuasa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Perusahaan whereUpdatedAt($value)
 */
	class Perusahaan extends \Eloquent {}
}

namespace App\Models{
/**
 * @property-read mixed $petugas_hash
 * @property-read \App\Models\tbl_lab|null $lab
 * @property-read \App\Models\User|null $petugas
 * @property-read \App\Models\Satuan_kerja|null $satuankerja
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Petugas_layanan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Petugas_layanan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Petugas_layanan query()
 */
	class Petugas_layanan extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int|null $avatar
 * @property int|null $nik
 * @property string|null $alamat
 * @property string|null $no_hp
 * @property string|null $jenis_kelamin
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Master_media|null $media
 * @method static \Illuminate\Database\Eloquent\Builder|Profile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Profile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Profile query()
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereJenisKelamin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereNik($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereNoHp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereUserId($value)
 */
	class Profile extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $alias
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $satuan_hash
 * @method static \Illuminate\Database\Eloquent\Builder|Satuan_kerja newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Satuan_kerja newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Satuan_kerja query()
 * @method static \Illuminate\Database\Eloquent\Builder|Satuan_kerja whereAlias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Satuan_kerja whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Satuan_kerja whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Satuan_kerja whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Satuan_kerja whereUpdatedAt($value)
 */
	class Satuan_kerja extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $name
 * @property array|null $jobs
 * @property array|null $jobs_paralel
 * @property int|null $status
 * @property-read mixed $list_jobs
 * @property-read mixed $list_jobs_paralel
 * @property-read mixed $setting_layanan_hash
 * @method static \Illuminate\Database\Eloquent\Builder|Setting_layanan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting_layanan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting_layanan query()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting_layanan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting_layanan whereJobs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting_layanan whereJobsParalel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting_layanan whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting_layanan whereStatus($value)
 */
	class Setting_layanan extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @method bool hasRole(string|array $roles)
 * @method bool hasAnyRole(string|array $roles)
 * @method bool hasAllRoles(array $roles)
 * @method \Spatie\Permission\Models\Role[] getRoleNames()
 * @property int $id
 * @property array|null $satuankerja_id
 * @property int|null $id_perusahaan
 * @property string $name
 * @property int|null $status
 * @property string|null $jabatan
 * @property array|null $jobs
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $google_id
 * @property mixed|null $password
 * @property string|null $ttd
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property string|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $satuankerja
 * @property-read mixed $user_hash
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \App\Models\Perusahaan|null $perusahaan
 * @property-read \App\Models\Profile|null $profile
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User role($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGoogleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIdPerusahaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereJabatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereJobs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSatuankerjaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTtd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTwoFactorConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTwoFactorRecoveryCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 */
	class User extends \Eloquent implements \Illuminate\Contracts\Auth\MustVerifyEmail {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $recipient
 * @property int $sender
 * @property string $message
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $status
 * @method static \Illuminate\Database\Eloquent\Builder|notifikasi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|notifikasi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|notifikasi query()
 * @method static \Illuminate\Database\Eloquent\Builder|notifikasi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|notifikasi whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|notifikasi whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|notifikasi whereRecipient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|notifikasi whereSender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|notifikasi whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|notifikasi whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|notifikasi whereUpdatedAt($value)
 */
	class notifikasi extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name_lab
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $lab_hash
 * @method static \Illuminate\Database\Eloquent\Builder|tbl_lab newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|tbl_lab newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|tbl_lab query()
 * @method static \Illuminate\Database\Eloquent\Builder|tbl_lab whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|tbl_lab whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|tbl_lab whereNameLab($value)
 * @method static \Illuminate\Database\Eloquent\Builder|tbl_lab whereUpdatedAt($value)
 */
	class tbl_lab extends \Eloquent {}
}

