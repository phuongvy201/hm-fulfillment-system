<footer class="px-4 py-16 border-t border-[#e6e1db] dark:border-white/10 mt-12" id="contact">
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-12 max-w-[1280px] mx-auto">
        <div class="col-span-2 lg:col-span-1 flex flex-col gap-6">
            <div class="flex items-center gap-2 text-primary">
                <div class="size-6">
                    <svg fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                        <path d="M42.1739 20.1739L27.8261 5.82609C29.1366 7.13663 28.3989 10.1876 26.2002 13.7654C24.8538 15.9564 22.9595 18.3449 20.6522 20.6522C18.3449 22.9595 15.9564 24.8538 13.7654 26.2002C10.1876 28.3989 7.13663 29.1366 5.82609 27.8261L20.1739 42.1739C21.4845 43.4845 24.5355 42.7467 28.1133 40.548C30.3042 39.2016 32.6927 37.3073 35 35C37.3073 32.6927 39.2016 30.3042 40.548 28.1133C42.7467 24.5355 43.4845 21.4845 42.1739 20.1739Z" fill="currentColor"></path>
                    </svg>
                </div>
                <h2 class="text-xl font-black">{{ config('app.name', 'HMFULFILL') }}</h2>
            </div>
            <p class="text-sm opacity-60">Empowering global sellers with automated print-on-demand fulfillment solutions.</p>
            <div class="flex gap-4">
                <a class="w-8 h-8 rounded-full bg-[#181511]/5 dark:bg-white/5 flex items-center justify-center hover:bg-primary/20 transition-colors" href="#">
                    <span class="material-symbols-outlined text-[18px]">share</span>
                </a>
                <a class="w-8 h-8 rounded-full bg-[#181511]/5 dark:bg-white/5 flex items-center justify-center hover:bg-primary/20 transition-colors" href="#">
                    <span class="material-symbols-outlined text-[18px]">chat</span>
                </a>
                <a class="w-8 h-8 rounded-full bg-[#181511]/5 dark:bg-white/5 flex items-center justify-center hover:bg-primary/20 transition-colors" href="mailto:support@hmfulfill.com">
                    <span class="material-symbols-outlined text-[18px]">alternate_email</span>
                </a>
            </div>
        </div>
        <div class="flex flex-col gap-4">
            <h4 class="font-bold">Product</h4>
            <a class="text-sm opacity-60 hover:text-primary transition-colors" href="#how-it-works">How it works</a>
            <a class="text-sm opacity-60 hover:text-primary transition-colors" href="{{ route('products.index') }}">Catalog</a>
            <a class="text-sm opacity-60 hover:text-primary transition-colors" href="#pricing">Pricing</a>
            <a class="text-sm opacity-60 hover:text-primary transition-colors" href="#resources">Mockup Generator</a>
        </div>
        <div class="flex flex-col gap-4">
            <h4 class="font-bold">Integrations</h4>
            <a class="text-sm opacity-60 hover:text-primary transition-colors" href="#integrations">Shopify</a>
            <a class="text-sm opacity-60 hover:text-primary transition-colors" href="#integrations">Etsy</a>
            <a class="text-sm opacity-60 hover:text-primary transition-colors" href="#integrations">WooCommerce</a>
            <a class="text-sm opacity-60 hover:text-primary transition-colors" href="#integrations">Amazon</a>
        </div>
        <div class="flex flex-col gap-4">
            <h4 class="font-bold">Resources</h4>
            <a class="text-sm opacity-60 hover:text-primary transition-colors" href="#resources">Help Center</a>
            <a class="text-sm opacity-60 hover:text-primary transition-colors" href="#resources">Blog</a>
            <a class="text-sm opacity-60 hover:text-primary transition-colors" href="#resources">Guides</a>
            <a class="text-sm opacity-60 hover:text-primary transition-colors" href="#resources">API Docs</a>
        </div>
        <div class="flex flex-col gap-4">
            <h4 class="font-bold">Company</h4>
            <a class="text-sm opacity-60 hover:text-primary transition-colors" href="#contact">About Us</a>
            <a class="text-sm opacity-60 hover:text-primary transition-colors" href="#contact">Contact</a>
            @auth
                <a class="text-sm opacity-60 hover:text-primary transition-colors" href="{{ route('dashboard') }}">Dashboard</a>
            @else
                <a class="text-sm opacity-60 hover:text-primary transition-colors" href="{{ route('login') }}">Login</a>
                <a class="text-sm opacity-60 hover:text-primary transition-colors" href="{{ route('register') }}">Register</a>
            @endauth
        </div>
    </div>
    <div class="mt-16 pt-8 border-t border-[#e6e1db] dark:border-white/10 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs opacity-50 max-w-[1280px] mx-auto">
        <p>© {{ date('Y') }} HMFULFILL Inc. All rights reserved.</p>
        <div class="flex gap-8">
            <a class="hover:text-primary" href="#">Privacy Policy</a>
            <a class="hover:text-primary" href="#">Cookie Settings</a>
        </div>
    </div>
</footer>

