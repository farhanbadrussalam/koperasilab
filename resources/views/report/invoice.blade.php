@php
    $css = file_get_contents(public_path('css/pdf.css'));
@endphp
@extends('report.template.main')

@section('style')
    <style>
        {!! $css !!}
    </style>
@endsection

@section('header')
    @include('report.template.header_')
@endsection

@section('content')
    {!! $body !!}
    @section('footer')
        @include('report.template.footer_')
    @endsection
@endsection
