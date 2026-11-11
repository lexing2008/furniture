<!DOCTYPE html>
<html lang="{{ $language }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    @hasSection('description')
        <meta name="description" content="@yield('description')">
    @endif
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    @vite(['resources/css/site.css'])
    @stack('head')
</head>
<body class="flex flex-col min-h-screen">

    @include('layouts.partials._header')

    <main class="grow">
        <div class="mx-auto w-full max-w-[1550px] py-3 px-4">
            @yield('content')
        </div>
    </main>

    @include('layouts.partials._footer')
    @include('layouts.partials._gallery-modal')
    @include('layouts.partials._company-info-modal')

    @vite(['resources/js/web.js'])
</body>
</html>
