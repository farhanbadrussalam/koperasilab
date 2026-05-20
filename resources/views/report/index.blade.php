@php
    $css = file_get_contents(public_path('css/pdf.css'));
    $cssCutome = "";
    switch($template_css) {
        case 'tandaterima':
            $cssCutome = file_get_contents(public_path('css/tandaterima.css'));
            break;
    }
@endphp
@extends('report.template.main')

@section('style')
    <style>
        {!! $css !!}
        {!! $cssCutome !!}
    </style>
@endsection

@section('header')
    @include('report.template.header_')
@endsection

@section('content')
    {!! $body !!}
@endsection

@section('footer')
    @include('report.template.footer_')
@endsection
