<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Ulang Kata Sandi - NuklindoLab</title>
    <style>
        body {
            font-family: 'Nunito', Arial, sans-serif;
            background-color: #f4f6f9;
            color: #333333;
            margin: 0;
            padding: 0;
        }
        .email-wrapper {
            width: 100%;
            background-color: #f4f6f9;
            padding: 40px 0;
            text-align: center;
        }
        .email-content {
            max-width: 570px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            text-align: left;
        }
        .email-header {
            background-color: #0d6efd;
            padding: 30px;
            text-align: center;
        }
        .email-header h2 {
            color: #ffffff;
            margin: 0;
            font-weight: 700;
            letter-spacing: 0.5px;
            font-size: 22px;
        }
        .email-body {
            padding: 40px 30px;
            line-height: 1.6;
        }
        .email-body p {
            margin-bottom: 20px;
            font-size: 15px;
            color: #555555;
        }
        .btn-container {
            text-align: center;
            margin: 35px 0;
        }
        .btn-reset {
            background-color: #0d6efd;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 30px;
            font-size: 15px;
            font-weight: 600;
            border-radius: 8px;
            display: inline-block;
            box-shadow: 0 4px 6px rgba(13, 110, 253, 0.2);
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
            color: #777777;
            border-top: 1px solid #eeeeee;
        }
        .email-footer p {
            margin: 5px 0;
        }
        .security-note {
            font-size: 13px;
            color: #888888;
            border-top: 1px solid #eeeeee;
            padding-top: 20px;
            margin-top: 25px;
        }
        .link-fallback {
            word-break: break-all;
            font-size: 12px;
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-content">
            <div class="email-header">
                <h2>NuklindoLab Koperasi JKRL</h2>
            </div>
            <div class="email-body">
                <p>Halo, <strong>{{ $name }}</strong></p>
                <p>Anda menerima email ini karena kami menerima permintaan atur ulang kata sandi untuk akun Anda di sistem Koperasi JKRL NuklindoLab.</p>
                
                <div class="btn-container">
                    <a href="{{ $url }}" class="btn-reset" target="_blank">Atur Ulang Kata Sandi</a>
                </div>
                
                <p>Tautan pemulihan kata sandi ini hanya berlaku selama <strong>60 menit</strong> sejak email ini dikirimkan.</p>
                <p>Jika Anda tidak merasa melakukan permintaan ini, silakan abaikan email ini dengan aman. Kata sandi Anda tidak akan berubah.</p>
                
                <div class="security-note">
                    <p>Jika Anda mengalami kendala saat mengeklik tombol "Atur Ulang Kata Sandi", silakan salin dan tempel URL berikut ke dalam peramban web Anda:</p>
                    <p class="link-fallback"><a href="{{ $url }}">{{ $url }}</a></p>
                </div>
            </div>
            <div class="email-footer">
                <p>&copy; {{ date('Y') }} NuklindoLab Koperasi JKRL. All rights reserved.</p>
                <p>Layanan Keanggotaan & Keuangan Koperasi</p>
            </div>
        </div>
    </div>
</body>
</html>
