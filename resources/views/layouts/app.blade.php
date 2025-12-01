<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'Laravel'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            transition: transform 0.3s ease;
            background-color: #F3F4F6;
            overflow: hidden;
            z-index: 1000;
        }
        .main-content {
            margin-left: 260px;
            transition: margin-left 0.3s ease;
            background-color: #F9FAFB;
            min-height: 100vh;
        }
        .menu-item {
            transition: all 0.2s ease;
            color: #374151;
        }
        .menu-item:hover {
            background-color: rgba(0, 0, 0, 0.05);
            color: #111827;
        }
        .menu-item.active {
            background-color: rgba(37, 99, 235, 0.1);
            color: #111827;
            border-left: 3px solid #2563EB;
        }
        .menu-item.active:hover {
            background-color: rgba(37, 99, 235, 0.15);
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen" style="background-color: #F9FAFB;">
    <x-sidebar :activeMenu="isset($activeMenu) ? $activeMenu : ''" />

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Header -->
        <header class="bg-white shadow-sm sticky top-0 z-10" style="border-bottom: 1px solid #E5E7EB;">
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold" style="color: #111827;">@yield('header-title', 'Dashboard')</h2>
                        <p class="text-sm" style="color: #6B7280;">@yield('header-subtitle', '')</p>
                    </div>
                    <div class="flex items-center gap-3">
                        @yield('header-actions')
                        <button class="md:hidden p-2 rounded-lg hover:bg-gray-100" onclick="toggleSidebar()">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #111827;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <main class="p-6">
            @if(session('success'))
                <div class="mb-4 p-4 rounded-lg" style="background-color: #D1FAE5; border: 1px solid #10B981;">
                    <p class="text-sm font-medium" style="color: #065F46;">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 rounded-lg" style="background-color: #FEE2E2; border: 1px solid #EF4444;">
                    <p class="text-sm font-medium" style="color: #991B1B;">{{ session('error') }}</p>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('open');
        }
    </script>
    @stack('scripts')
</body>
</html>

