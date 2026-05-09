<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email Anda</title>
    <style>
        body {
            font-family: 'Inter', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            margin-top: 40px;
            margin-bottom: 40px;
        }
        .header {
            background-color: #2563eb; /* Modern Blue */
            padding: 35px 20px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .content {
            padding: 45px 40px;
            color: #4b5563;
            line-height: 1.6;
        }
        .content h2 {
            color: #1f2937;
            font-size: 22px;
            margin-top: 0;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .content p {
            margin-bottom: 25px;
            font-size: 16px;
        }
        .button-container {
            text-align: center;
            margin: 40px 0;
        }
        .btn {
            background-color: #2563eb;
            color: #ffffff;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            display: inline-block;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2), 0 2px 4px -1px rgba(37, 99, 235, 0.1);
        }
        .btn:hover {
            background-color: #1d4ed8;
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3), 0 4px 6px -2px rgba(37, 99, 235, 0.15);
            transform: translateY(-1px);
        }
        .divider {
            border-top: 1px solid #e5e7eb;
            margin: 30px 0;
        }
        .fallback-text {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 8px !important;
        }
        .fallback-link {
            font-size: 14px;
            word-break: break-all;
            color: #2563eb;
            text-decoration: underline;
        }
        .footer {
            background-color: #f9fafb;
            padding: 25px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            border-top: 1px solid #f3f4f6;
        }
        .footer p {
            margin: 5px 0;
        }
        /* Responsive */
        @media only screen and (max-width: 600px) {
            .container {
                margin-top: 0;
                margin-bottom: 0;
                border-radius: 0;
            }
            .content {
                padding: 30px 25px;
            }
        }
    </style>
</head>
<body>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#f4f7fa">
        <tr>
            <td align="center">
                <div class="container">
                    <div class="header">
                        <h1>NuklindoLab</h1>
                    </div>
                    <div class="content">
                        <h2>Halo Sobat NuklindoLab! 👋</h2>
                        <p>
                            Terima kasih telah melakukan registrasi. Kami sangat senang menyambut Anda!
                            Untuk mulai menggunakan sistem kami, silakan verifikasi alamat email Anda dengan mengeklik tombol di bawah ini:
                        </p>
                        
                        <div class="button-container">
                            <!-- Inlined some styles for better email client support -->
                            <a href="{{ $url }}" class="btn" style="color: #ffffff; background-color: #2563eb; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: bold; display: inline-block;">Verifikasi Email Anda</a>
                        </div>
                        
                        <div class="divider"></div>

                        <p class="fallback-text">
                            Jika tombol di atas tidak berfungsi, salin dan tempel URL berikut ke browser Anda:
                        </p>
                        <p style="margin-top: 0;">
                            <a href="{{ $url }}" class="fallback-link">{{ $url }}</a>
                        </p>

                        <p style="margin-top: 30px; font-size: 14px; color: #9ca3af;">
                            Jika Anda tidak merasa mendaftar di sistem kami, silakan abaikan dan hapus email ini. Tautan ini akan kedaluwarsa dalam beberapa waktu.
                        </p>
                    </div>
                    <div class="footer">
                        <p>&copy; {{ date('Y') }} NuklindoLab. Hak Cipta Dilindungi.</p>
                        <p>KoperasiLab - Sistem Informasi Manajemen</p>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>