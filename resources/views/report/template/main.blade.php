
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    {{-- CKEditor --}}
    {{-- @include('report.template.style') --}}
    {{-- @include('report.template.style-main') --}}
    @yield('style')
</head>

<body>

    @yield('header')

    <main class="main">
        @yield('content')
    </main>

    @yield('footer')

    {{-- @include('report.template.footer') --}}
</body>

</html>
