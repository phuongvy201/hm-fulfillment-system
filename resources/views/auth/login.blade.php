<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | {{ config('app.name', 'HMFULFILL') }}</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <style type="text/tailwindcss">
        :root {
            --primary: #F7961D;
            --primary-hover: #e08516;
        }
        body {
            font-family: "Inter", sans-serif;
        }
        .bg-pattern {
            background-image: linear-gradient(rgba(248, 247, 245, 0.92), rgba(248, 247, 245, 0.92)), url(https://lh3.googleusercontent.com/aida-public/AB6AXuDO5uQBscO9FP52bb6jS4S0qsAi5RiUT-OCw4todtS56QPfRnZSChgbPxbxeikBuwQQ74T23Ui3rbY5nvrO8jjgAEjlho7b8-0_fCeHcjD-D4069jfJYbeCamZvrHfbdgjr9vaJM_Kfx1SHHYu_vDxoMWAk-qwsQ--oLCxFLTia8AOXQe55Vrr-U3uQtkAckX3I0LQhg6KmxqfELExf1azVhMUA1FVi6YJYmlFTl1a8zl0HlQ0QfPaiq9r87OHfHgZg2yIetQNeDRI);
            background-size: cover;
            background-position: center;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-pattern min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-[440px]">
        <div class="bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="p-10">
                <!-- Logo -->
                <div class="flex items-center justify-center gap-2 mb-10">
                    <div class="bg-black rounded-lg p-2">
                        <span class="material-symbols-outlined text-[var(--primary)] text-3xl font-bold">inventory_2</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-2xl font-black tracking-tighter leading-none">
                            HM<span class="text-[var(--primary)]">FULFILL</span>
                        </span>
                        <span class="text-[10px] uppercase tracking-[0.2em] font-bold text-gray-400 mt-1">POD Fulfillment</span>
                    </div>
                </div>

                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-gray-900">Welcome back</h1>
                    <p class="text-sm text-gray-500 mt-1">Please enter your details to sign in</p>
                </div>

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-lg border border-red-200 bg-red-50">
                        <ul class="text-sm text-red-600">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Form -->
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <!-- Email Field -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5" for="email">Email address</label>
                        <div class="relative">
                            <input 
                                class="block w-full rounded-lg border-gray-300 pl-3 pr-10 py-2.5 text-sm focus:border-[var(--primary)] focus:ring-[var(--primary)] @error('email') border-red-300 bg-red-50/30 @enderror" 
                                id="email" 
                                name="email" 
                                type="email" 
                                value="{{ old('email') }}"
                                required 
                                autofocus
                            />
                            @if(old('email') && !$errors->has('email'))
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <span class="material-symbols-outlined text-green-500 text-xl font-bold">check_circle</span>
                                </div>
                            @endif
                        </div>
                        @if(old('email') && !$errors->has('email'))
                            <p class="mt-1 text-xs text-green-600">Email verified</p>
                        @endif
                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-sm font-semibold text-gray-700" for="password">Password</label>
                            <a class="text-xs font-semibold text-[var(--primary)] hover:text-[var(--primary-hover)]" href="#">Forgot password?</a>
                        </div>
                        <div class="relative">
                            <input 
                                class="block w-full rounded-lg border-gray-300 pl-3 pr-10 py-2.5 text-sm focus:border-[var(--primary)] focus:ring-[var(--primary)] @error('password') border-red-300 bg-red-50/30 @enderror" 
                                id="password" 
                                name="password" 
                                placeholder="••••••••" 
                                type="password"
                                required
                            />
                            <button 
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600" 
                                type="button"
                                onclick="togglePasswordVisibility()"
                            >
                                <span class="material-symbols-outlined text-xl" id="password-toggle-icon">visibility</span>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <input 
                            class="h-4 w-4 rounded border-gray-300 text-[var(--primary)] focus:ring-[var(--primary)]" 
                            id="remember" 
                            name="remember" 
                            type="checkbox"
                            {{ old('remember') ? 'checked' : '' }}
                        />
                        <label class="ml-2 block text-sm text-gray-600" for="remember">
                            Remember me for 30 days
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        class="w-full bg-[var(--primary)] hover:bg-[var(--primary-hover)] text-white font-bold py-3 px-4 rounded-lg shadow-md shadow-orange-200 transition-all duration-200 flex items-center justify-center gap-2" 
                        type="submit"
                    >
                        Login
                        <span class="material-symbols-outlined text-lg">arrow_forward</span>
                    </button>
                </form>

                <!-- Divider -->
                <div class="mt-8 relative">
                    <div aria-hidden="true" class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-xs uppercase">
                        <span class="bg-white px-2 text-gray-400 font-medium">New to HMFULFILL?</span>
                    </div>
                </div>

                <!-- Sign Up Link -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Don't have an account? 
                        <a class="font-bold text-[var(--primary)] hover:underline" href="{{ route('register') }}">Sign up for free</a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer Links -->
        <div class="mt-8 text-center text-xs text-gray-400 font-medium space-x-4">
            <a class="hover:text-gray-600" href="#">Privacy Policy</a>
            <span>•</span>
            <a class="hover:text-gray-600" href="#">Terms of Service</a>
            <span>•</span>
            <a class="hover:text-gray-600" href="#">Help Center</a>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('password-toggle-icon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.textContent = 'visibility_off';
            } else {
                passwordField.type = 'password';
                toggleIcon.textContent = 'visibility';
            }
        }
    </script>
</body>
</html>
