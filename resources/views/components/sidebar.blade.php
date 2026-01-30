@props(['activeMenu' => ''])

<aside class="sidebar shadow-lg" style="display: flex; flex-direction: column; height: 100vh; overflow: hidden;">
    <!-- Logo -->
    <div class="p-6 border-b flex-shrink-0" style="border-color: #E5E7EB;">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center font-bold text-white" style="background-color: #2563EB;">
                HM
            </div>
            <div>
                <h1 class="text-lg font-bold" style="color: #111827;">HM Fulfillment</h1>
                <p class="text-xs" style="color: #6B7280;">Management System</p>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="p-4 flex-1 overflow-y-auto" style="overflow-y: auto;">
        <ul class="space-y-1">
            <!-- Main -->
            <li>
                <a href="{{ route('dashboard') }}" class="menu-item {{ $activeMenu === 'dashboard' ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Dashboard
                </a>
            </li>
            
            @if(auth()->user()->hasRole('customer') && auth()->user()->hasAnyPermission(['orders.view', 'orders.create']))
            <li>
                <a href="{{ route('customer.orders.index') }}" class="menu-item {{ $activeMenu === 'orders' ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Orders
                </a>
            </li>
            @elseif(auth()->user()->hasAnyPermission(['orders.view', 'orders.create']))
            <li>
                <a href="{{ route('admin.orders.index') }}" class="menu-item {{ $activeMenu === 'orders' ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Orders
                </a>
            </li>
            @endif

            {{-- Customer: Design Tasks --}}
            @if(auth()->user()->hasRole('customer') && !auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin())
            <li>
                <a href="{{ route('customer.design-tasks.index') }}" class="menu-item {{ $activeMenu === 'design-tasks' ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Design Tasks
                </a>
            </li>
            @endif

            {{-- Designer: Design Tasks --}}
            @if(auth()->user()->hasRole('designer') && !auth()->user()->isSuperAdmin())
            <li>
                <a href="{{ route('admin.design-tasks.index') }}" class="menu-item {{ $activeMenu === 'design-tasks' ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Design Tasks
                </a>
            </li>
            @endif

            {{-- Admin: Design Tasks --}}
            @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
            <li>
                <a href="{{ route('admin.design-tasks.index') }}" class="menu-item {{ $activeMenu === 'design-tasks' ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Design Tasks
                </a>
            </li>
            @endif

            {{-- Customer: My Wallet --}}
            @if(auth()->user()->hasRole('customer') && !auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin())
            <li class="pt-4">
                <p class="px-4 py-2 text-xs font-semibold uppercase" style="color: #9CA3AF;">Finance Management</p>
            </li>
            <li>
                <a href="{{ route('customer.wallet.index') }}" class="menu-item {{ $activeMenu === 'wallet' ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    My Wallet
                </a>
            </li>
            @endif

            @if(auth()->user()->isAdmin())
            @canPermission('products.view')
            <li>
                <a href="{{ route('admin.products.index') }}" class="menu-item {{ $activeMenu === 'products' ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    Products
                </a>
            </li>
            @endcanPermission
            @endif

            <!-- Finance Management - Only for Admin/Super Admin -->
            @if(auth()->user()->isAdmin())
            @canAnyPermission(['top-up.view', 'credit.view', 'wallet.view'])
            <li class="pt-4">
                <p class="px-4 py-2 text-xs font-semibold uppercase" style="color: #9CA3AF;">Finance Management</p>
            </li>

            @canAnyPermission(['top-up.view', 'top-up.create'])
            <li>
                <a href="{{ auth()->user()->hasPermission('top-up.view') ? route('admin.top-up-requests.index') : route('customer.top-up-requests.create') }}" class="menu-item {{ ($activeMenu === 'top-up-requests' || $activeMenu === 'customer-top-up-requests') ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Top-up
                </a>
            </li>
            @endcanAnyPermission

            @canPermission('credit.view')
            <li>
                <a href="{{ route('admin.credits.index') }}" class="menu-item {{ $activeMenu === 'credits' ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Credit 
                </a>
            </li>
            @endcanPermission

            @canPermission('wallet.view')
            <li>
                <a href="{{ route('admin.wallets.index') }}" class="menu-item {{ $activeMenu === 'wallets' ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Wallet 
                </a>
            </li>
            @endcanPermission

            @if(auth()->user()->isSuperAdmin())
            <li>
                <a href="{{ route('admin.currencies.index') }}" class="menu-item {{ $activeMenu === 'currencies' ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                    </svg>
                    Currency & Exchange Rates
                </a>
            </li>
            <li>
                <a href="{{ route('admin.design-prices.users.index') }}" class="menu-item {{ $activeMenu === 'design-prices' ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium">
                    <span class="material-symbols-outlined text-xl">attach_money</span>
                    Design Prices
                </a>
            </li>
            @endif
            @endcanAnyPermission
            @endif

            <!-- Product Management - Only for Admin/Super Admin -->
            @if(auth()->user()->isAdmin())
            @canAnyPermission(['markets.view', 'workshops.view'])
            <li class="pt-4">
                <p class="px-4 py-2 text-xs font-semibold uppercase" style="color: #9CA3AF;">Product Management</p>
            </li>
            
            @canPermission('markets.view')
            <li>
                <a href="{{ route('admin.markets.index') }}" class="menu-item {{ $activeMenu === 'markets' ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Markets
                </a>
            </li>
            @endcanPermission

            @canPermission('workshops.view')
            <li>
                <a href="{{ route('admin.workshops.index') }}" class="menu-item {{ $activeMenu === 'workshops' ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    Workshops
                </a>
            </li>
            @endcanPermission
            @endcanAnyPermission
            @endif

            <!-- Pricing Management - Only for Admin/Super Admin -->
            @if(auth()->user()->isAdmin())
            @canAnyPermission(['pricing-tiers.view', 'pricing-tiers.users'])
            <li class="pt-4">
                <p class="px-4 py-2 text-xs font-semibold uppercase" style="color: #9CA3AF;">Tier Management</p>
            </li>
            
            @canPermission('pricing-tiers.view')
            <li>
                <a href="{{ route('admin.pricing-tiers.index') }}" class="menu-item {{ $activeMenu === 'pricing-tiers' ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Tier Rules
                </a>
            </li>
            @endcanPermission

            @canPermission('pricing-tiers.users')
            <li>
                <a href="{{ route('admin.user-pricing-tiers.index') }}" class="menu-item {{ $activeMenu === 'user-pricing-tiers' ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    User Tier 
                </a>
            </li>
            @endcanPermission
            @endcanAnyPermission
            @endif

            <!-- Operations - Only for Admin/Super Admin -->
            @if(auth()->user()->isAdmin())
            @canPermission('products.view')
            <li class="pt-4">
                <p class="px-4 py-2 text-xs font-semibold uppercase" style="color: #9CA3AF;">Operations</p>
            </li>
            <li>
                <a href="{{ route('admin.import.index') }}" class="menu-item {{ $activeMenu === 'import' ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    Import
                </a>
            </li>
            @endcanPermission
            @canPermission('inventory.view')
            <li>
                <a href="#" class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    Inventory
                </a>
            </li>
            @endcanPermission
            @canPermission('customers.view')
            <li>
                <a href="#" class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Customers
                </a>
            </li>
            @endcanPermission
            @endif

            <!-- Administration - Only for Admin/Super Admin -->
            @if(auth()->user()->isAdmin())
            @canAnyPermission(['users.view', 'permissions.view'])
            <li class="pt-4">
                <p class="px-4 py-2 text-xs font-semibold uppercase" style="color: #9CA3AF;">Administration</p>
            </li>
            
            @canPermission('users.view')
            <li>
                <a href="{{ route('admin.users.index') }}" class="menu-item {{ $activeMenu === 'users' ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    Users
                </a>
            </li>
            @endcanPermission

            @canPermission('users.view')
            <li>
                <a href="{{ route('admin.teams.index') }}" class="menu-item {{ $activeMenu === 'teams' ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Teams
                </a>
            </li>
            @endcanPermission

            @if(auth()->user()->isAdmin())
            <li>
                <a href="#" class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Reports
                </a>
            </li>
            <li>
                <a href="#" class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Settings
                </a>
            </li>
            @endif

            @if(auth()->user()->isSuperAdmin())
            <li>
                <a href="{{ route('admin.permissions.index') }}" class="menu-item {{ $activeMenu === 'permissions' ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    Permissions
                </a>
            </li>
            @endif
            @endcanAnyPermission
            @endif
        </ul>
    </nav>

    <!-- User Info & Logout -->
    <div class="p-4 border-t flex-shrink-0" style="border-color: #E5E7EB; background-color: #F3F4F6;">
        <div class="mb-3">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-semibold text-white text-sm" style="background-color: #2563EB;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold" style="color: #111827;">{{ auth()->user()->name }}</p>
                    @if(auth()->user()->role)
                        <p class="text-xs" style="color: #6B7280;">{{ auth()->user()->role->name }}</p>
                    @endif
                </div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button 
                type="submit"
                class="w-full flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all"
                style="color: #374151; border: 1px solid #D1D5DB; background-color: transparent;"
                onmouseover="this.style.backgroundColor='rgba(0, 0, 0, 0.05)'; this.style.color='#111827';"
                onmouseout="this.style.backgroundColor='transparent'; this.style.color='#374151';"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                Logout
            </button>
        </form>
    </div>
</aside>

