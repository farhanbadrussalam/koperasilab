@extends('layouts.main')

@section('content')
    <style>
        .container {
            max-width: 600px;
            margin: 0 auto;
        }
        h2 {
            margin-bottom: 0;
            color: #dc3545;
        }
        p {
            font-size: 20px;
        }
    </style>
        <div class="d-flex flex-column align-items-center justify-content-center" style="height: 75vh;">
            <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 4rem;"></i>
            <h2>Akses Dilarang</h2>
            <p>Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.</p>
        </div>
@endsection
