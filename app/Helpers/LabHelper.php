<?php

use Illuminate\Support\Facades\Session;
use App\Events\NotifikasiEvent;
use App\Models\notifikasi;
use App\Models\User;
use App\Models\Penyelia;
use App\Models\Permohonan;
use App\Models\Permohonan_dokumen;
use App\Models\Pengiriman_detail;
use App\Models\Kontrak_periode;
use App\Models\Master_ttd;
use Illuminate\Support\Facades\Crypt;

use App\Helpers\TableWidthFixer;
use App\Models\Keuangan;
use App\Models\Kontrak;
use App\Models\Kontrak_detail;
use App\Models\Master_pengguna;
use App\Models\Invoice;
use Carbon\Carbon;

use App\Services\Notifier;

if (!function_exists('formatCurrency')) {
    function formatCurrency(int $amount)
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}

if (!function_exists('generateToken')) {
    function generateToken()
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }
        $_token = Session::get('token');
        if ($_token == NULL) {
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
    function notifikasi(array $data, string $message)
    {
        if (!isset($data['to_user']) || !isset($data['type'])) {
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

if (!function_exists('unmask')) {
    function unmask(mixed $data)
    {
        $regMask = ['.', ',', '-', '_'];
        $unmaskedAmount = str_replace($regMask, '', $data);

        return $unmaskedAmount;
    }
}

if (!function_exists('statusFormat')) {
    function statusFormat(string $feature, int $status)
    {
        $htmlStatus = '';
        $status = (int)$status;
        if ($feature == 'jadwal') {
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
        } else if ($feature == 'permohonan' || $feature == 'frontdesk') {
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
        } else if ($feature == 'petugas') {
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
    function formatBytes(int $size, int $precision = 2): string
    {
        $base = log($size, 1024);
        $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');

        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[(int) floor($base)];
    }
}

if (!function_exists('iconDocument')) {
    function iconDocument(string $type): string
    {
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
    function encryptor(mixed $value)
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
    function decryptor(mixed $value)
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
    function stringSplit(string $str, string $prefix)
    {
        if (substr($str, 0, strlen($prefix)) === $prefix) {
            $str = substr($str, strlen($prefix));
        }
        return $str;
    }
}

#ex: Thursday, 31 Aug 2023 12:42 WIB
if (!function_exists('convert_date')) {
    function convert_date(mixed $tanggal, $type = false, $language = 'id')
    {
        $format = '';
        $month3 = false;
        $month_eng = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $month_id = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $month3_id = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
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
            case 7:
                # Sep 2025
                $format = 'M Y';
                $month3 = true;
                break;
            case 8:
                # Monday
                $format = 'l';
                break;
            case 9:
                $format = 'd M';
                break;
            case 10:
                $format = 'Y';
                break;
            case 11:
                # Sep
                $format = 'M';
                $month3 = true;
                break;
            case 12:
                # September
                $format = 'M';
                break;
            case 13:
                # 12/Januari/2026
                $format = 'd/M/Y';
                break;
        }

        $new_tanggal = date($format, strtotime($tanggal));
        if ($language == 'id') {
            // Mengganti nama hari dalam bahasa Inggris dengan bahasa Indonesia
            $new_tanggal = str_replace(
                ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                ['Senin', 'Selasa', 'Rabu', 'Kamis', "Jum'at", 'Sabtu', 'Minggu'],
                $new_tanggal
            );

            // Mengganti nama bulan dalam bahasa Inggris dengan bahasa Indonesia
            $new_tanggal = str_replace(
                $month_eng,
                $month3 ? $month3_id : $month_id,
                $new_tanggal
            );
        }

        // Mengembalikan tanggal dengan format yang diinginkan
        return $new_tanggal;
    }
}

if (!function_exists('getAvatar')) {
    function getAvatar(string $id_user)
    {
        $uidHash = $id_user ? decryptor($id_user) : null;

        $urlDev = asset("assets/img/default-avatar.jpg");
        if ($uidHash) {
            $user = User::findOrFail($uidHash);

            if ($user->profile) {
                $urlDev = asset("storage/images/avatar/" . $user->profile->avatar);
            }
        }
    }
}

if (!function_exists('renderUserAvatar')) {
    /**
     * Render user avatar and optionally the user's name.
     *
     * @param mixed $user The user object
     * @param bool $showName Whether to show the name alongside the avatar
     * @param string $size Custom size for the avatar (e.g., '35px' or '40px')
     * @param string $additionalClasses Additional CSS classes for the avatar container/image
     * @return string HTML output
     */
    function renderUserAvatar($user, bool $showName = true, string $size = '35px', string $additionalClasses = '')
    {
        if (!$user) {
            $avatarUrl = asset('assets/img/default-avatar.jpg');
            $name = '-';
            $initial = '?';
        } else {
            // jika $user ini adalah id user
            if (is_numeric($user)) {
                $user = User::find($user);
            }
            $name = $user->name;
            $initial = strtoupper(substr($name, 0, 1));

            // Fetch avatar from relation or fallback
            if ($user->profile && $user->profile->media) {
                // If it is stored in Master_media table
                $avatarUrl = asset('storage/' . $user->profile->media->file_path . '/' . $user->profile->media->file_hash);
            } elseif ($user->profile && $user->profile->avatar && !is_numeric($user->profile->avatar)) {
                // Fallback if the legacy text filename is stored directly
                $avatarUrl = asset('storage/images/avatar/' . $user->profile->avatar);
            } else {
                $avatarUrl = null;
            }
        }

        // Clean values to prevent XSS
        $escName = e($name);
        $escInitial = e($initial);
        $escSize = e($size);
        $escClasses = e($additionalClasses);

        if ($avatarUrl) {
            $avatarHtml = '<img src="' . e($avatarUrl) . '" alt="' . $escName . '" width="' . $escSize . '" height="' . $escSize . '" class="rounded-circle ' . $escClasses . '" style="width: ' . $escSize . '; height: ' . $escSize . '; object-fit: cover;" onerror="this.outerHTML=\'<div class=\\\'rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center ' . $escClasses . '\\\' style=\\\'width: ' . $escSize . '; height: ' . $escSize . '; font-weight: 600; font-size: calc(' . $escSize . ' * 0.4);\\\'>' . $escInitial . '</div>\'">';
        } else {
            // High-premium soft-gradient or pastel background with initials
            $bgColors = ['#55c57a', '#3f51b5', '#2979ff', '#aa00ff', '#ff6d00', '#00bfa5', '#ec407a'];
            $colorIdx = $user ? (ord(substr($name, 0, 1)) % count($bgColors)) : 0;
            $bgColor = $bgColors[$colorIdx];

            $avatarHtml = '<div class="rounded-circle text-white fw-bold d-inline-flex align-items-center justify-content-center shadow-sm ' . $escClasses . '" style="width: ' . $escSize . '; height: ' . $escSize . '; background-color: ' . $bgColor . '; font-size: calc(' . $escSize . ' * 0.45); line-height: 1;">' . $escInitial . '</div>';
        }

        if ($showName) {
            return '<div class="d-inline-flex align-items-center gap-2">' . $avatarHtml . '<span class="fw-semibold text-dark text-truncate">' . $escName . '</span></div>';
        }

        return $avatarHtml;
    }
}


if (!function_exists('strPad')) {
    function strPad(int $angka, $jumlah = 3)
    {

        // Menggunakan str_pad untuk menambahkan nol di depan angka
        $angkaFormatted = str_pad($angka, $jumlah, '0', STR_PAD_LEFT);

        return $angkaFormatted;
    }
}

if (!function_exists('getRomawiBulan')) {
    function getRomawiBulan(int $bulan)
    {
        $romawi = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        return $romawi[$bulan - 1]; // Bulan ke-1 (Januari) dimulai dari index 0
    }
}

if (!function_exists('angkaKeHuruf')) {
    function angkaKeHuruf(int $angka)
    {
        $angka = (int)$angka;

        $bilangan = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan'];
        $ribu     = ['', 'ribu', 'juta', 'miliar', 'triliun'];

        if ($angka === 0) return 'nol';
        if ($angka < 10)  return $bilangan[$angka];

        if ($angka === 10) return 'sepuluh';
        if ($angka === 11) return 'sebelas';
        if ($angka < 20)   return $bilangan[$angka - 10] . ' belas';

        if ($angka < 100) {
            $puluh = intdiv($angka, 10);
            $sisa  = $angka % 10;
            return trim($bilangan[$puluh] . ' puluh ' . ($sisa ? angkaKeHuruf($sisa) : ''));
        }

        // ✅ perbaikan di sini
        if ($angka === 100) return 'seratus';
        if ($angka < 200)  return trim('seratus ' . angkaKeHuruf($angka - 100));

        if ($angka < 1000) {
            $ratus = intdiv($angka, 100);
            $sisa  = $angka % 100;
            return trim($bilangan[$ratus] . ' ratus ' . ($sisa ? angkaKeHuruf($sisa) : ''));
        }

        // 1000 ke atas
        $result = '';
        $idx = 0;
        while ($angka > 0) {
            $chunk = $angka % 1000;
            if ($chunk) {
                if ($idx === 1 && $chunk === 1) {
                    $result = 'seribu ' . $result; // bukan "satu ribu"
                } else {
                    $result = trim(angkaKeHuruf($chunk) . ' ' . $ribu[$idx]) . ' ' . $result;
                }
            }
            $angka = intdiv($angka, 1000);
            $idx++;
        }
        return trim(preg_replace('/\s+/', ' ', $result));
    }
}

if (!function_exists('generateNoDokumen')) {
    function generateNoDokumen(String $jenis, ?int $id = null): string
    {
        $appName = 'JKRL';
        // Mengambil bulan sekarang dan mengubah ke dalam format Romawi
        $bulanSekarang = date('n'); // n = format angka bulan tanpa nol
        $romawiBulan = getRomawiBulan($bulanSekarang);

        // Tahun saat ini
        $tahunSekarang = date('Y');

        if ($jenis === 'invoice') {
            $lastDoc = Keuangan::orderBy('id_keuangan', 'desc')->first();
        } else {
            // Incremental number
            $lastDoc = Permohonan_dokumen::where('jenis', $jenis)
                ->orderBy('id_dokumen', 'desc')
                ->first();
        }

        $lastNumber = 0;
        if ($lastDoc) {
            $nomorDokumen = ($jenis === 'invoice') ? $lastDoc->no_invoice : $lastDoc->nomer;
            // Mencari grup angka pertama dalam string nomor dokumen (misal: 0001)
            preg_match('/\d+/', $nomorDokumen, $matches);
            $lastNumber = isset($matches[0]) ? (int)$matches[0] : 0;
        }
        $increment = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        $noKontrak = '';

        switch ($jenis) {
            case 'KontrakPengujian':
                // Format nomor
                $permohonan = Permohonan::with("jenis_layanan")->where('id_permohonan', $id)->first();
                $alias = $permohonan->jenis_layanan->alias;

                $noKontrak = "{$increment}/{$alias}/{$appName}/{$romawiBulan}/{$tahunSekarang}";
                break;
            case 'SuratPengujian':
                // Format nomor
                $permohonan = Permohonan::with(["layanan_jasa", "layanan_jasa.satuankerja"])->where('id_permohonan', $id)->first();
                $alias = $permohonan->layanan_jasa->satuankerja->alias;

                $noKontrak = "{$increment}/SPP/NL-{$alias}/{$romawiBulan}/{$tahunSekarang}";
                break;
            case 'kontrak':
                // Format nomor
                $permohonan = Permohonan::with("jenis_layanan")->where('id_permohonan', $id)->first();
                $alias = strtoupper(substr($permohonan->jenis_layanan->name, 0, 1));

                $noKontrak = "{$alias}-{$increment}/{$appName}/{$romawiBulan}/{$tahunSekarang}";
                break;
            case 'tandaterima':
                // Format nomor
                $noKontrak = "{$increment}/{$romawiBulan}/{$tahunSekarang}";
                break;
            case 'surattugas':
                // mengambil satuan kerja
                $satuankerja = Penyelia::with(['permohonan', 'permohonan.layanan_jasa', 'permohonan.layanan_jasa.satuankerja'])
                    ->where('penyelia.id_penyelia', $id)
                    ->first();
                $alias = $satuankerja->permohonan->layanan_jasa->satuankerja->alias;

                $noKontrak = "{$increment}/NL-{$alias}/{$romawiBulan}/{$tahunSekarang}";
                break;
            case 'invoice':
                $permohonan = Permohonan::with("jenis_layanan")->where('id_permohonan', $id)->first();
                $alias = $permohonan->jenis_layanan->alias;

                $noKontrak = "{$increment}/INV-{$alias}/{$romawiBulan}/{$tahunSekarang}";
                break;
            case 'surpeng':
                // Format nomor kontrak
                $noKontrak = "{$increment}/{$appName}-B/{$romawiBulan}/{$tahunSekarang}";
                break;
            case 'kwitansi':
                // Format nomor kwitansi
                $permohonan = Permohonan::with("jenis_layanan")->where('id_permohonan', $id)->first();
                $alias = $permohonan->jenis_layanan->alias;

                $noKontrak = "{$increment}/KW-{$alias}/{$appName}/{$romawiBulan}/{$tahunSekarang}";
                break;
            case 'adendum':
                // Format nomor
                $permohonan = Permohonan::with("jenis_layanan")->where('id_permohonan', $id)->first();
                $alias = 'A';

                $noKontrak = "{$alias}-{$increment}/{$appName}/{$romawiBulan}/{$tahunSekarang}";
                break;

            default:
                $noKontrak = "{$increment}/{$romawiBulan}/{$tahunSekarang}";
                break;
        }


        return $noKontrak;
    }
}

if (!function_exists('messageSanity')) {
    function messageSanity(array $validationErrors)
    {
        $errorMessage = [];
        foreach ($validationErrors as $fieldName => $errors) {
            foreach ($errors as $error) {
                $fieldLabel = ucwords(str_replace('_', ' ', $fieldName));
                $error = explode(':', $error);
                $errorMessage[$fieldName . '.' . $error[0]] = match ($error[0]) {
                    'required' => "Harap isi {$fieldLabel}",
                    'unique' => "{$fieldLabel} sudah terdaftar",
                    'max' => "{$fieldLabel} maksimal {$error[1]} karakter",
                    'min' => "{$fieldLabel} minimal {$error[1]} karakter",
                    'email' => "Harap isi {$fieldLabel} dengan format email yang benar",
                    'string' => "Harap isi {$fieldLabel} dengan format string yang benar",
                    'captcha' => 'Captcha tidak valid',
                    'confirmed' => 'Password tidak sama dengan Konfirmasi Password',
                    default => "Format {$fieldLabel} tidak valid ({$error[0]})",
                };
                if ($fieldName === 'g-recaptcha-response' && $error === 'required') {
                    $errorMessage[$fieldName . '.' . $error] = 'Harap verifikasi Captcha';
                }
            }
        }
        return $errorMessage;
    }
}

if (!function_exists('jenislayanan')) {
    function jenislayanan(mixed $parent, mixed $child)
    {
        return trim(preg_replace('/\s+/', '', $parent->name . ' ' . $child->name));
    }
}

if (!function_exists('contenMetodePembayaran')) {
    function contenMetodePembayaran(mixed $content, $variabels = [])
    {
        // Log::info($metodePembayaran);
        if (is_array($variabels)) {
            foreach ($variabels as $key => $value) {
                foreach ($value as $key2 => $value2) {
                    $content = html_entity_decode($content);
                    $content = str_replace('@' . $key2, $value2, $content);
                }
            }
        }
        return $content;
    }
}

if (!function_exists('normalizeMentionKey')) {
    function normalizeMentionKey(string $raw): string
    {
        // Hilangkan entity, spasi, marker @ / # / {{ }}
        $k = html_entity_decode(trim($raw), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // buang {{VAR}} atau {{ VAR }}
        // $k = preg_replace('/^\s*\{\{\s*|\s*\}\}\s*$/', '', $k);
        // buang marker di depan (@, #, $, % ... sesuaikan jika perlu)
        $k = preg_replace('/^[@#\$%]+/', '', $k);
        return strtoupper(trim($k)); // konsistenkan ke UPPERCASE
    }
}

if (!function_exists('importHtmlFragment')) {
    function importHtmlFragment(DOMDocument $dom, string $htmlFragment): DOMDocumentFragment
    {
        $tmp = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $tmp->loadHTML(
            '<div id="__wrap__">' .
                $htmlFragment .
                '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        $wrap = $tmp->getElementById('__wrap__');
        $frag = $dom->createDocumentFragment();
        foreach (iterator_to_array($wrap->childNodes) as $child) {
            $frag->appendChild($dom->importNode($child, true));
        }
        return $frag;
    }
}

/**
 * Render mention CKEditor menjadi nilai.
 * Opsi:
 *  - html_keys: daftar KEY yang boleh berisi HTML (akan disisipkan sebagai fragment)
 *  - allowed_tags: whitelist tag jika ingin pakai strip_tags sederhana
 *  - sanitizer: callable($html, $key): string  -> gunakan kalau pakai HTML Purifier
 *  - default: nilai default jika key tidak ada
 */
if (!function_exists('renderMentionsToValuesFlexible')) {
    function renderMentionsToValuesFlexible(string $html = "", array $map = [], array $options = []): string
    {
        $htmlKeys    = array_map('strtoupper', $options['html_keys'] ?? []);
        $allowedTags = $options['allowed_tags'] ?? '<p><br><strong><b><em><i><u><span><div><ul><ol><li><table><thead><tbody><tr><td><th><h1><h2><h3><h4><h5><h6>';
        $sanitizer   = $options['sanitizer'] ?? null; // contoh: fn($h,$k)=>Purifier::clean($h)
        $default     = $options['default']   ?? '';

        $wrapperStart = '<div id="__root__">';
        $wrapperEnd = '</div>';
        $dom = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadHTML($wrapperStart . $html . $wrapperEnd, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        // Cari node mention lewat data-mention ATAU class=mention
        foreach ($xpath->query('//*[@data-mention] | //*[(contains(concat(" ", normalize-space(@class), " "), " mention "))]') as $node) {
            /** @var DOMElement $node */
            $rawAttr = $node->getAttribute('data-mention');
            $rawAttr = html_entity_decode($rawAttr, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            $key = null;

            // 1) Coba parse JSON {"id":"..."}
            $data = json_decode($rawAttr, true);
            if (is_array($data) && isset($data['id'])) {
                $key = normalizeMentionKey((string)$data['id']);
            }

            // 2) Jika bukan JSON tapi ada string (mis. "@PERUSAHAAN")
            if ($key === null && $rawAttr !== '') {
                $key = normalizeMentionKey($rawAttr);
            }

            // 3) Fallback: ambil dari teks dalam span (mis. "@PERUSAHAAN")
            if ($key === null || $key === '') {
                $key = normalizeMentionKey($node->textContent ?? '');
            }

            // Ambil value
            // $value = array_key_exists($key, $map) ? (string)$map[$key] : $default;
            $has = array_key_exists($key, $map);
            $valueRaw = $has ? ((string)$map[$key] == '' ? $default : (string)$map[$key]) : $default;

            if (in_array($key, $htmlKeys, true)) {
                $value = $valueRaw;

                // Sanitasi (disarankan pakai HTML Purifier di produksi)
                if (is_callable($sanitizer)) {
                    $value = (string) $sanitizer($value, $key);
                } else {
                    $value = strip_tags($value, $allowedTags);
                }

                // Ganti node mention dengan fragment HTML
                $frag = importHtmlFragment($dom, $value);
                $node->parentNode->replaceChild($frag, $node);
            } else {
                // Ganti node mention dengan text node
                $text = $dom->createTextNode($valueRaw);
                $node->parentNode->replaceChild($text, $node);
            }
        }

        // Kembalikan innerHTML
        $root = $dom->getElementById('__root__');
        $out = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $out .= $dom->saveHTML($child);
        }

        $containerPx = a4ContentWidthPx($options['orientation'], 40, 40, 96); // samakan dgn @page margin & DPI
        $out = convertTableWidthsToPx($out, $containerPx);

        $out = TableWidthFixer::colgroupToFirstRowCellPx($out, 800);
        return $out;
    }
}

if (!function_exists('a4ContentWidthPx')) {
    // A4 @ 96 DPI ≈ 794 px lebar. Sesuaikan margin yang kamu pakai di @page.
    function a4ContentWidthPx(string $orientation = 'portrait', int $marginLeftPx = 40, int $marginRightPx = 40, int $dpi = 96): int
    {
        [$wIn, $hIn] = $orientation === 'portrait' ? [8.268, 11.693] : [11.693, 8.268];
        $wPx = (int) round($wIn * $dpi);
        return max(0, $wPx - ($marginLeftPx + $marginRightPx));
    }
}

if (!function_exists('convertTableWidthsToPx')) {

    function convertTableWidthsToPx(string $html, int $containerPx): string
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        $xp = new DOMXPath($dom);

        foreach ($xp->query('//table') as $table) {
            if ($table instanceof DOMElement) {
                // Table width (% → px) di style
                $style = $table->getAttribute('style');
                if (preg_match('/width\s*:\s*([\d.]+)%/i', $style, $m)) {
                    $px = (int) round($containerPx * (float) $m[1] / 100);
                    $style = preg_replace('/width\s*:\s*[\d.]+%/i', "width: {$px}px", $style);
                    $table->setAttribute('style', $style);
                }
            }

            // colgroup/col
            foreach ($xp->query('./colgroup/col', $table) as $col) {
                if ($col instanceof DOMElement) {
                    $cs = $col->getAttribute('style');
                    if (preg_match('/width\s*:\s*([\d.]+)%/i', $cs, $m2)) {
                        $px = (int) round($containerPx * (float) $m2[1] / 100);
                        $cs = preg_replace('/width\s*:\s*[\d.]+%/i', "width: {$px}px", $cs);
                        $col->setAttribute('style', $cs);
                    }
                }
            }

            // th/td
            foreach ($xp->query('.//th|.//td', $table) as $cell) {
                if ($cell instanceof DOMElement) {
                    $cs = $cell->getAttribute('style');
                    if (preg_match('/width\s*:\s*([\d.]+)%/i', $cs, $m3)) {
                        $px = (int) round($containerPx * (float) $m3[1] / 100);
                        $cs = preg_replace('/width\s*:\s*[\d.]+%/i', "width: {$px}px", $cs);
                        $cell->setAttribute('style', $cs);
                    } elseif ($cell->hasAttribute('width') && preg_match('/^\s*([\d.]+)%\s*$/', $cell->getAttribute('width'), $m4)) {
                        $px = (int) round($containerPx * (float) $m4[1] / 100);
                        $cell->setAttribute('width', (string) $px); // atribut width px
                    }
                }
            }
        }
        return $dom->saveHTML();
    }
}

if (!function_exists('calculateInvoice')) {
    function calculateInvoice(mixed $total_harga, $diskon = [], $ppn = false, $pph = false)
    {
        $subJumlah = 0;

        foreach ($diskon as $item) {
            $item->jumDiskon = $total_harga * ($item->diskon / 100);
            $subJumlah += $item->jumDiskon;
        }

        $jumAfterDiskon = $total_harga - $subJumlah;

        $jumPph = $pph ? $total_harga * ($pph / 100) : 0;
        $jumAfterPph = $jumAfterDiskon - $jumPph;
        $jumPpn = $ppn ? $total_harga * ($ppn / 100) : 0;
        $subTotal = $jumAfterPph + $jumPpn;

        return [
            'diskon' => $diskon,
            'jumAfterDiskon' => $jumAfterDiskon,
            'jumPpn' => $jumPpn,
            'jumPph' => $jumPph,
            'subTotal' => $subTotal,
        ];
    }
}

if (!function_exists('isReminderPeriod')) {
    function isReminderPeriod(mixed $period, mixed $offset, $hNow = false)
    {
        $period = Carbon::create($period);
        $hMinus = $period->copy()->sub("month", $offset);

        // hari ini
        $hNow = $hNow ? Carbon::create($hNow) : Carbon::now();

        return $hNow->between($hMinus, $period->subDay());
    }
}

if (!function_exists('isFinishKontrak')) {
    function isFinishKontrak(int $id_kontrak)
    {
        // ambil kontrak
        $kontrak = Kontrak::with(
            'invoice',
            'periode'
        )->find($id_kontrak);

        // cek apakah invoice sudah di bayar atau belum
        $statusInvoice = false;
        if ($kontrak->invoice) {
            $invoice = $kontrak->invoice;
            $statusInvoice = $invoice->status == 5 ? true : false; // 5 = sudah di bayar
        }

        // cek apakah periode udah selesai atau belum
        $statusPeriode = false;
        $arrDocument = array('tld', 'lhu');
        foreach ($kontrak->periode as $periode) {
            if ($periode->status == 2) {
                $statusPeriode = true;
                break;
            }
        }
    }
}

if (!function_exists('getPeriodeAwal')) {
    function getPeriodeAwal(Kontrak $kontrak)
    {
        $JL = jenislayanan($kontrak->jenis_layanan_parent, $kontrak->jenis_layanan);

        $periodeAwal = array();
        if ($kontrak->is_zerocek == 1) {
            if ($kontrak->is_have_tld == 0) {
                $periodeAwal = array(0);
            } else if ($kontrak->is_have_tld == 1 && $JL != 'ZeroCekTanpaKontrak') {
                $periodeAwal = array(1, 2);
            }
        } else if ($kontrak->is_zerocek == 0) {
            if ($kontrak->is_have_tld == 1 && $JL != 'EvaluasiTanpaKontrak') {
                $periodeAwal = array(1, 2);
            }
        }

        return $periodeAwal;
    }
}

if (!function_exists('cekPeriodeComplete')) {
    function cekPeriodeComplete(int $id_kontrak, int $periode)
    {
        $period = Kontrak_periode::with('permohonan', 'permohonan.invoice')->where('id_kontrak', $id_kontrak)
            ->where('periode', $periode)
            ->first();

        $kontrak = Kontrak::with([
            'jenis_layanan',
            'jenis_layanan_parent',
            'invoice',
            'pengiriman',
            'pengiriman.detail'
        ])->find($id_kontrak);

        $JL = jenislayanan($kontrak->jenis_layanan_parent, $kontrak->jenis_layanan);
        $periodeAwal = getPeriodeAwal($kontrak);
        $lastPeriode = $kontrak->periode_all['jml_periode'] == $periode;

        $aktifDokumen = array('invoice', 'tld', 'lhu');
        if ($periode == 0) {
            $aktifDokumen = array_diff($aktifDokumen, array('tld'));
        }
        foreach ($aktifDokumen as $dokumen) {
            if ($dokumen === 'invoice' && $period->permohonan?->invoice == null) continue;
            if ($dokumen === 'tld') {
                if ($JL == 'KontrakSewa' && $lastPeriode) continue;
                if (in_array($period->periode, $periodeAwal)) continue;
            }

            if ($dokumen === 'lhu') {
                if ($period->status == 2) continue; // status 2 = Pengembalian
            }

            $getPengiriman = false;
            foreach ($kontrak->pengiriman as $pengiriman) {
                $cekDokumen = Pengiriman_detail::where('id_pengiriman', $pengiriman->id_pengiriman)
                    ->where('jenis', $dokumen)
                    ->where('periode', $periode)
                    ->first();

                if ($cekDokumen) {
                    $getPengiriman = $pengiriman;
                    break;
                }
            }

            // if($dokumen === 'invoice') {
            //     if ($kontrak->invoice->status != 5) {
            //         return false;
            //     }
            // }

            if ($getPengiriman) {
                if ($getPengiriman->status != 2) {
                    return false;
                }
            } else {
                return false;
            }
        }

        return true;
    }
}

if (!function_exists('notifUnreadCount')) {
    function notifUnreadCount($event = null)
    {
        $query = Auth::user()->unreadNotifications();

        if ($event) {
            // Jika event berupa array → gunakan whereIn
            if (is_array($event)) {
                $query->whereIn('data->event', $event);
            } else {
                // Jika event berupa string → gunakan where biasa
                $query->where('data->event', $event);
            }
        }

        $count = $query->count();

        if ($count > 0) {
            $count = $count > 99 ? '99+' : $count;
        } else {
            $count = false;
        }

        return $count;
    }
}

if (!function_exists('notifRead')) {
    function notifRead($event = null, $id = null)
    {
        Notifier::read($event, $id);
    }
}

if (!function_exists('uploadSignatur')) {
    function uploadSignatur(mixed $signed, mixed $user)
    {
        // 1. Bersihkan header base64 (data:image/png;base64,...)
        // Agar yang tersisa hanya raw string enkripsinya
        $imageParts = explode(";base64,", $signed);
        $imageTypeAux = explode("image/", $imageParts[0]);
        $imageBase64 = base64_decode($imageParts[1]);

        Master_ttd::where('user_id', $user->id)->get()->each->delete();

        $params = [
            'user_id' => $user->id,
            'image_blob' => $imageParts[1],
            'status' => 1,
            'image_type' => $imageTypeAux[1],
        ];

        // 2. Simpan ke Tabel master_ttd
        // Pakai updateOrCreate agar jika sudah ada, dia update. Jika belum, dia create.
        $result = Master_ttd::create($params);
        if ($result) {
            return $result->id;
        }

        return null;
    }
}

if (!function_exists('range_date')) {
    function range_date(mixed $start, mixed $end, int $type)
    {
        $typeDateStart = false;
        $typeDateEnd = false;
        if ($type == 1) {
            $typeDateStart = 7;
            $typeDateEnd = 7;
        } else if ($type == 2) {
            $typeDateStart = 6;
            $typeDateEnd = 6;
        }

        // jika tahun antara start_date dan end_date sama
        if (substr($start, 0, 4) == substr($end, 0, 4)) {
            if ($type == 1) {
                $typeDateStart = 11;
            } else if ($type == 2) {
                $typeDateStart = 12;
            }
        }

        return [
            'start' => convert_date($start, $typeDateStart),
            'end' => convert_date($end, $typeDateEnd)
        ];
    }
}

if (!function_exists('setKontrakAdendum')) {
    function setKontrakAdendum(int $id_kontrak, int $periode)
    {
        $kontrak = Kontrak::find($id_kontrak);

        if ($periode >= $kontrak->periode_active->periode) {
            $result = Kontrak_detail::where('id_kontrak', $id_kontrak)
                ->where('status', 2)
                ->where('periode', '<=', $kontrak->periode_active->periode)
                ->get();

            foreach ($result as $key => $value) {
                if ($value->pengguna_lama) {
                    $change = Kontrak_detail::where('id_kontrak', $id_kontrak)
                        ->where('status', 1)
                        ->where('id_pengguna_divisi', $value->pengguna_lama)
                        ->first();

                    // update sync status tld
                    $value->status_tld_1 = $change->status_tld_1;
                    $value->status_tld_2 = $change->status_tld_2;

                    // update master_pengguna yang diganti
                    Master_pengguna::where('id_pengguna', $change->id_pengguna_divisi)->update(['status' => 1]);

                    $change->update(['status' => 99]);
                }
                $value->status = 1;
                $value->save();
            }
        }
    }
}

if (!function_exists('asset_versioned')) {
    /**
     * Generate an asset path for the application with a manual version query string.
     *
     * @param string $path
     * @param string|null $secure
     * @return string
     */
    function asset_versioned($path, $secure = null)
    {
        $version = config('app.version', '1.0.0');
        return asset($path, $secure) . '?v=' . $version;
    }
}

