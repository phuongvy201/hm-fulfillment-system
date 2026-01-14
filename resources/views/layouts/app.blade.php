<!DOCTYPE html>
<html class="@yield('html-class', 'dark')" lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name', 'HMFULFILL'))</title>
    <meta name="description" content="@yield('description', 'Modern SaaS fulfillment platform for POD sellers.')">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
   
    <style>
        body { font-family: 'Inter', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    </style>
    @stack('styles')
</head>
<body class="bg-background-light dark:bg-background-dark text-[#181511] dark:text-white transition-colors duration-300 @yield('body-class')">
@hasSection('header')
    @yield('header')
@else
    @include('layouts.partials.header')
            @endif

<main class="max-w-[1280px] mx-auto">
            @yield('content')
        </main>

@hasSection('footer')
    @yield('footer')
@else
    @include('layouts.partials.footer')
@endif
    @stack('scripts')
</body>
</html>

