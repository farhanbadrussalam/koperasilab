@extends('layouts.app')

@section('content')
<div class="hold-transition login-page">
    <div class="login-box">
        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-header bg-transparent text-center border-0 pt-4 pb-2">
                <span class="h2 mb-0 text-primary"><b>Koperasi</b><span class="text-dark">LAB</span></span>
            </div>
            <div class="card-body px-4 pb-4 pt-2">
                <div class="text-center mb-4">
                    <h5 class="fw-bold text-dark mb-2">Verifikasi Email</h5>
                    <p class="text-muted mb-0" style="font-size: 0.95rem;">
                        {{ __('Before proceeding, please check your email for a verification link.') }}
                        <br>
                        {{ __('If you did not receive the email') }},
                    </p>
                </div>

                @if (session('status') == 'verification-link-sent')
                    <div class="alert alert-success border-0 shadow-sm rounded-3 text-center p-3 mb-4" role="alert">
                        <small>{{ __('A fresh verification link has been sent to your email address.') }}</small>
                    </div>
                @endif

                <div class="d-grid gap-2 mt-4">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold rounded-3 mb-2">{{ __('click here to request another') }}</button>
                    </form>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-light w-100 py-2 text-danger fw-semibold rounded-3 border">{{ __('Log Out') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
