@extends('layouts.app')

@section('content')
<div class="hold-transition login-page">
    <div class="login-box">
        <div class="card shadow">
            <div class="card-header text-center">
                <span class="h1"><b>Koperasi</b>LAB</span>
            </div>
            <div class="card-body">
                @if (session('status') == 'verification-link-sent')
                    <div class="alert alert-success" role="alert">
                        {{ __('A fresh verification link has been sent to your email address.') }}
                    </div>
                @endif

                {{ __('Before proceeding, please check your email for a verification link.') }}
                {{ __('If you did not receive the email') }}
                <br><br>
                <div class="d-flex flex-column">
                    <form class="d-inline" method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary p-0 m-0 w-100">{{ __('click here to request another') }}</button>
                    </form>
                    <form class="d-inline" method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-link p-0 m-0 w-100 text-danger">{{ __('Log Out') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
