<div class="sticky top-0 z-50 bg-white/80 dark:bg-background-dark/80 backdrop-blur-md border-b border-solid border-[#e6e1db] dark:border-[#3d2f21]">
    <div class="max-w-[1280px] mx-auto px-4 lg:px-10">
        <header class="flex items-center justify-between h-16">
            <div class="flex items-center gap-2 text-primary">
                <div class="size-8">
                    <svg fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                        <path d="M42.1739 20.1739L27.8261 5.82609C29.1366 7.13663 28.3989 10.1876 26.2002 13.7654C24.8538 15.9564 22.9595 18.3449 20.6522 20.6522C18.3449 22.9595 15.9564 24.8538 13.7654 26.2002C10.1876 28.3989 7.13663 29.1366 5.82609 27.8261L20.1739 42.1739C21.4845 43.4845 24.5355 42.7467 28.1133 40.548C30.3042 39.2016 32.6927 37.3073 35 35C37.3073 32.6927 39.2016 30.3042 40.548 28.1133C42.7467 24.5355 43.4845 21.4845 42.1739 20.1739Z" fill="currentColor"></path>
                    </svg>
                </div>
                <h2 class="text-[#181511] dark:text-white text-xl font-black leading-tight tracking-[-0.015em]">{{ config('app.name', 'HMFULFILL') }}</h2>
            </div>
            <div class="hidden md:flex flex-1 justify-end gap-8 items-center">
                <nav class="flex items-center gap-8">
                    <a class="text-sm font-semibold hover:text-primary transition-colors" href="#products">Products</a>
                    <a class="text-sm font-semibold hover:text-primary transition-colors" href="#integrations">Integrations</a>
                    <a class="text-sm font-semibold hover:text-primary transition-colors" href="#pricing">Pricing</a>
                    <a class="text-sm font-semibold hover:text-primary transition-colors" href="#resources">Resources</a>
                </nav>
                @auth
                    <a href="{{ route('dashboard') }}" class="bg-primary hover:bg-primary/90 text-white px-5 py-2 rounded-lg text-sm font-bold transition-all shadow-lg shadow-primary/20">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('register') }}" class="bg-primary hover:bg-primary/90 text-white px-5 py-2 rounded-lg text-sm font-bold transition-all shadow-lg shadow-primary/20">
                        Get Started
                    </a>
                @endauth
            </div>
        </header>
    </div>
</div>

