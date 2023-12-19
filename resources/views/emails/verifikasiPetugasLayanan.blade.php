<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verifikasi Petugas Layanan</title>
    <style>
        .btn-mail {
            border-radius: 5px;
            background-color: #0d6efd;
            font-size: 30px;
            color: white;
            cursor: pointer;
            border: 0;
            text-decoration: none;
            padding: 10px;
        }

    </style>
</head>
<body style="font-size: 30px;">
    <p style="text-align: center">
        Anda ditambahkan menjadi petugas <b>{{ stringSplit($data['otorisasi'], 'Otorisasi-') }}</b><br>
        Silahkan lakukan verifikasi
    </p>
    <p style="text-align: center">
        <a class="btn-mail" href="{{ url('petugasLayanan/v/'.$data['id']) }}">Verifikasi</a>
    </p>

</body>
</html>
