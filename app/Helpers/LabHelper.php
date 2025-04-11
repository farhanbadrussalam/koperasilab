<?php

use Illuminate\Support\Facades\Session;
use App\Events\NotifikasiEvent;
use App\Models\notifikasi;
use App\Models\User;
use App\Models\Penyelia;
use App\Models\Permohonan_dokumen;
use App\Models\Pengiriman_detail;
use Illuminate\Support\Facades\Crypt;

if (!function_exists('formatCurrency')) {
    function formatCurrency($amount)
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}

if (!function_exists('generateToken')) {
    function generateToken() {
        $user = Auth::user();
        $_token = Session::get('token');
        if($_token == NULL){
            $cToken = $user->createToken('api-token');
            $_token = $cToken->plainTextToken;

            Session::put('token', $_token);
            Session::put('token_id', $cToken->accessToken->id);
            session()->save();
        }

        return Session::get('token');
    }
}

if (!function_exists('notifikasi')) {
    function notifikasi($data, $message) {
        if(!isset($data['to_user']) || !isset($data['type'])){
           return response()->json([
            'message' => "Object 'to_user' dan 'type' tidak boleh kosong"
           ], 400);
        }
        $recipient = $data['to_user'];
        $sender = Auth::user()->id;
        $type = $data['type'];

        $saveNotif = array(
            'recipient' => $recipient,
            'sender' => $sender,
            'message' => $message,
            'type' => $type,
            'status' => 1
        );
        $result = notifikasi::create($saveNotif);

        broadcast(new NotifikasiEvent($result, $message))->toOthers();

        return response()->json([
            'message' => "Notifikasi Terkirim"
        ], 200);
    }
}

if(!function_exists('unmask')){
    function unmask($data){
        $regMask = ['.', ',','-'];
        $unmaskedAmount = str_replace($regMask, '', $data);

        return $unmaskedAmount;
    }
}

if(!function_exists('statusFormat')){
    function statusFormat($feature, $status) {
        $htmlStatus = '';
        $status = (int)$status;
        if($feature == 'jadwal'){
            switch ($status) {
                case 0:
                    $htmlStatus = '
                    <div class="d-flex align-items-center">
                        <div><div class="me-1 dot bg-secondary"></div></div>
                        <span class="subbody-medium text-submain text-truncate">Belum ditugaskan</span>
                    </div>
                    ';
                    break;
                case 1:
                    $htmlStatus = '
                    <div class="d-flex align-items-center">
                        <div><div class="me-1 dot bg-info"></div></div>
                        <span class="subbody-medium text-submain text-truncate">Diajukan</span>
                    </div>
                    ';
                    break;
                case 2:
                    $htmlStatus = '
                    <div class="d-flex align-items-center">
                        <div><div class="me-1 dot bg-success"></div></div>
                        <span class="subbody-medium text-submain text-truncate">Bersedia</span>
                    </div>
                    ';
                    break;
                case 3:
                    $htmlStatus = '
                    <div class="d-flex align-items-center">
                        <div><div class="me-1 dot bg-danger"></div></div>
                        <span class="subbody-medium text-submain text-truncate">Tidak bersedia</span>
                    </div>
                    ';
                    break;
                default:
                    $htmlStatus = '
                    <div class="d-flex align-items-center">
                        <div><div class="me-1 dot bg-danger"></div></div>
                        <span class="subbody-medium text-submain text-truncate">Dibatalkan</span>
                    </div>
                    ';
                    break;
            }
        }else if($feature == 'permohonan' || $feature == 'frontdesk'){
            switch ($status) {
                case 1:
                    $htmlStatus = '
                    <div class="d-flex align-items-center">
                        <div><div class="me-1 dot bg-secondary"></div></div>
                        <span class="subbody-medium text-submain text-truncate">Pengajuan</span>
                    </div>
                    ';
                    break;
                case 2:
                    $htmlStatus = '
                    <div class="d-flex align-items-center">
                        <div><div class="me-1 dot bg-info"></div></div>
                        <span class="subbody-medium text-submain text-truncate">Terverifikasi</span>
                    </div>
                    ';
                    break;
                case 3:
                    $htmlStatus = '
                    <div class="d-flex align-items-center">
                        <div><div class="me-1 dot bg-success"></div></div>
                        <span class="subbody-medium text-submain text-truncate">Selesai</span>
                    </div>
                    ';
                    break;
                case 9:
                    $htmlStatus = '
                    <div class="d-flex align-items-center">
                        <div><div class="me-1 dot bg-danger"></div></div>
                        <span class="subbody-medium text-submain text-truncate">Ditolak</span>
                    </div>
                    ';
                    break;
            }
        }else if($feature == 'petugas'){
            switch ($status) {
                case 1:
                    $htmlStatus = '
                    <div class="d-flex align-items-center">
                        <div><div class="me-1 dot bg-danger"></div></div>
                        <span class="subbody-medium text-submain text-truncate">Not verif</span>
                    </div>
                    ';
                    break;

                case 2:
                    $htmlStatus = '
                    <div class="d-flex align-items-center">
                        <div><div class="me-1 dot bg-success"></div></div>
                        <span class="subbody-medium text-submain text-truncate">Verifikasi</span>
                    </div>
                    ';
                    break;
            }
        }

        return $htmlStatus;
    }

}

if (!function_exists('formatBytes')) {
	function formatBytes($size, $precision = 2)
	{
		$base = log($size, 1024);
		$suffixes = array('B', 'KB', 'MB', 'GB', 'TB');

		return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
	}
}

if (!function_exists('iconDocument')){
    function iconDocument($type) {
        $icon = '';
        switch ($type) {
            case 'application/pdf':
                $icon = 'pdf-icon.svg';
                break;
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                $icon = 'word-icon.svg';
                break;
            default:
                $icon = 'other-icon.svg';
                break;
        }
        return $icon;
    }
}

if (!function_exists('generate')) {
    function generate($value = false)
    {
        return uniqid($value);
    }
  }

if (!function_exists('encryptor')) {
  function encryptor($value)
  {
    $secret   = env('ENCRYPTION_KEY', 'robot.txt');
    $base64   = base64_encode(hash('sha256', $secret, true));
    $sub      = substr($base64, 0, 32); //secret key must be 32 char
    $iv       = substr($sub, 0, 16);
    $result   = openssl_encrypt($value, "AES-256-CBC", $sub, 0, $iv);
    $dictionary = array('=', '/', '+');
    $change   = array('', '_', '-');
    $result   = str_replace($dictionary, $change, $result);
    return $result;
  }
}
if (!function_exists('decryptor')) {
    function decryptor($value)
    {
        $dictionary = array('=', '/', '+');
        $change     = array('.', '_', '-');
        $value      = str_replace($change, $dictionary, $value);
        $secret     = env('ENCRYPTION_KEY', 'robot.txt');
        $base64     = base64_encode(hash('sha256', $secret, true));
        $sub        = substr($base64, 0, 32); //secret key must be 32 char
        $iv         = substr($sub, 0, 16);
        $result     = openssl_decrypt($value, "AES-256-CBC", $sub, 0, $iv);
        return $result;
    }
}
if (!function_exists('stringSplit')) {
    function stringSplit($str, $prefix)
    {
        if (substr($str, 0, strlen($prefix)) === $prefix) {
            $str = substr($str, strlen($prefix));
        }
        return $str;
    }
}

#ex: Thursday, 31 Aug 2023 12:42 WIB
if (!function_exists('convert_date')) {
	function convert_date($tanggal, $type = false)
    {
        $format = '';
        switch ($type) {
            case 1:
                # 11 September 2023 12:00
                $format = 'd M Y H:i';
                break;
            case 2:
                # 11 September 2023
                $format = 'd M Y';
                break;
            case 3:
                # Sabtu, 14 Desember 2024 00:00
                $format = 'l, d M Y H:i';
                break;
            case 4:
                # Monday, 11 September 2023
                $format = 'l, d M Y';
                break;
            case 5:
                # 2024-03-24
                $format = 'Y-m-d';
                break;
            case 6:
                # September 2023
                $format = 'M Y';
                break;
        }

        // Mengganti nama hari dalam bahasa Inggris dengan bahasa Indonesia
        $new_tanggal = date($format, strtotime($tanggal));
        $new_tanggal = str_replace(
            ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            ['Senin', 'Selasa', 'Rabu', 'Kamis', "Jum'at", 'Sabtu', 'Minggu'],
            $new_tanggal
        );
        
        // Mengganti nama bulan dalam bahasa Inggris dengan bahasa Indonesia
        $new_tanggal = str_replace(
            ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
            $new_tanggal
        );

        // Mengembalikan tanggal dengan format yang diinginkan
        return $new_tanggal;
    }

}

if (!function_exists('getAvatar')) {
    function getAvatar($id_user){
        $uidHash = $id_user ? decryptor($id_user) : null;

        $urlDev = asset("assets/img/default-avatar.jpg");
        if($uidHash){
            $user = User::findOrFail($uidHash);

            if($user->profile){
                $urlDev = asset("storage/images/avatar/".$user->profile->avatar);
            }
        }

        return $urlDev;
    }
}

if (!function_exists('strPad')) {
    function strPad($angka, $jumlah = 3){

        // Menggunakan str_pad untuk menambahkan nol di depan angka
        $angkaFormatted = str_pad($angka, $jumlah, '0', STR_PAD_LEFT);

        return $angkaFormatted;
    }
}

if (!function_exists('getRomawiBulan')) {
    function getRomawiBulan($bulan){
        $romawi = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        return $romawi[$bulan - 1]; // Bulan ke-1 (Januari) dimulai dari index 0
    }
}

if (!function_exists('angkaKeHuruf')) {
    function angkaKeHuruf($angka){
        $bilangan = array(
            '',
            'satu',
            'dua',
            'tiga',
            'empat',
            'lima',
            'enam',
            'tujuh',
            'delapan',
            'sembilan'
        );

        $ribu = array('', 'ribu', 'juta', 'miliar', 'triliun');

        if ($angka < 10) {
            return $bilangan[$angka];
        } elseif ($angka < 20) {
            return 'sepuluh ' . angkaKeHuruf($angka - 10);
        } elseif ($angka < 100) {
            return $bilangan[floor($angka / 10)] . ' puluh ' . angkaKeHuruf($angka % 10);
        } elseif ($angka < 1000) {
            return $bilangan[floor($angka / 100)] . ' ratus ' . angkaKeHuruf($angka % 100);
        } else {
            $result = '';
            $idxRibuan = 0;
            while ($angka > 0) {
                if ($angka % 1000 > 0) {
                    $result = angkaKeHuruf($angka % 1000) . ' ' . $ribu[$idxRibuan] . ' ' . $result;
                }
                $angka = floor($angka / 1000);
                $idxRibuan++;
            }
            return $result;
        }
    }
}

if (!function_exists('generateNoDokumen')) {
    function generateNoDokumen($jenis, $id = false)
    {
        // Mengambil bulan sekarang dan mengubah ke dalam format Romawi
        $bulanSekarang = date('n'); // n = format angka bulan tanpa nol
        $romawiBulan = getRomawiBulan($bulanSekarang);

        // Tahun saat ini
        $tahunSekarang = date('Y');
        $lastContractNumber = 1;

        // Incremental number
        if($jenis != 'surpeng'){
            $lastContractNumber = Permohonan_dokumen::where('jenis', $jenis)
                                    ->whereMonth('created_at', $bulanSekarang)
                                    ->whereYear('created_at', $tahunSekarang)
                                    ->count(); // Ubah dengan pengambilan nomor terakhir dari database
        }else{
            $lastContractNumber = Pengiriman_detail::where('nomer_surpeng', '!=', null)
                                    ->whereMonth('created_at', $bulanSekarang)
                                    ->whereYear('created_at', $tahunSekarang)
                                    ->count();
        }
        $increment = str_pad($lastContractNumber + 1, 4, '0', STR_PAD_LEFT);

        switch ($jenis) {
            case 'tandaterima':
                // Format nomor kontrak
                $noKontrak = "{$increment}/{$romawiBulan}/{$tahunSekarang}";
                break;
            case 'surattugas':
                // mengambil satuan kerja
                $satuankerja = Penyelia::select('satuankerja.alias')
                ->join('users', 'users.id', '=', 'penyelia.created_by')
                ->join('satuankerja', 'satuankerja.id', '=', 'users.satuankerja_id')
                ->where('penyelia.id_penyelia', $id)
                ->first();

                $noKontrak = "{$increment}/NL-{$satuankerja->alias}/{$romawiBulan}/{$tahunSekarang}";
                break;
            case 'surpeng':
                // Format nomor kontrak
                $noKontrak = "{$increment}/JKRL-B/{$romawiBulan}/{$tahunSekarang}";
                break;
            
        }
        

        return $noKontrak;
    }
}
?>
