<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 Not Found</title>

    <style>
        body { font-family: sans-serif; text-align: center; padding-top: 50px; }
        .container { max-width: 600px; margin: 0 auto; }
        h2 { margin-bottom: 0; color: #dc3545; }
        p { font-size: 20px; }
        a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Halaman Tidak Ditemukan</h2>
        <p>Maaf, halaman yang Anda cari tidak dapat kami temukan.</p>
        <p>
            <a href="{{ url()->previous() }}" onclick="event.preventDefault(); window.history.back();" style="color: #007bff; text-decoration: none;">Kembali ke Halaman Sebelumnya</a>
        </p>
    </div>
</body>
</html>
