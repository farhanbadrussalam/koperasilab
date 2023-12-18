<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verifikasi Petugas Layanan</title>
    <style>
        .btn {
            border-radius: 5px;
            background-color: #0d6efd;
            font-size: 30px;
            color: white;
            cursor: pointer;
            border: 0;
            text-decoration: none;
            padding: 10px;
        }

        body{
            padding: 0;
            margin: 0;
            font-size: 30px;
        }
    </style>
</head>
<body>
    <p style="text-align: center">
        Anda ditambahkan menjadi petugas <b>{{ stringSplit($data['otorisasi']->name, 'Otorisasi-') }}</b><br>
        Silahkan lakukan verifikasi
    </p>
    <p style="text-align: center">
        <a class="btn" href="{{ url('petugasLayanan/v/'.$data['id']) }}">Verifikasi</a>
    </p>

</body>
</html>
