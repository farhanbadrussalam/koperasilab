
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    {{-- CKEditor --}}
    @yield('style')
</head>

<body>

    @yield('header')
    @yield('footer')

    <main class="main">
        @yield('content')
    </main>


    {{-- @include('report.template.footer') --}}
</body>

</html>
