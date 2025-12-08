<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>419 Page Expired</title>
</head>
<body style="font-family: sans-serif; text-align: center; padding-top: 50px;">
    <div class="container">
        <h2>Halaman Kedaluwarsa</h2>
        <p style="font-size: 20px;">Sesi Anda telah kedaluwarsa karena tidak ada aktivitas. Silakan coba kembali.</p>
        <p>
            <a href="{{ url()->previous() }}" onclick="event.preventDefault(); window.history.back();" style="color: #007bff; text-decoration: none;">Kembali ke Halaman Sebelumnya</a>
        </p>
    </div>
</body>
</html>
