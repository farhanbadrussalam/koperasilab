<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SURAT TUGAS</title>
    @include('report.template.style')
</head>

<body>

    @include('report.template.header')

    <main>
        @yield('content')
    </main>

    {{-- @include('report.template.footer') --}}
</body>

</html>
