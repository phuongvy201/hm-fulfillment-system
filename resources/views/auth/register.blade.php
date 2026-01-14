<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | {{ config('app.name', 'HMFULFILL') }}</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script id="tailwind-config">
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "primary": "#F7961D",
                        "primary-hover": "#e08518",
                        "background-light": "#fcfcfc",
                    },
                    fontFamily: {
                        "sans": ["Inter", "sans-serif"]
                    },
                },
            },
        }
    </script>
    <style type="text/tailwindcss">
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .password-strength-bar {
            height: 4px;
            border-radius: 2px;
            background-color: #e5e7eb;
            position: relative;
            overflow: hidden;
        }
        .strength-indicator {
            height: 100%;
            width: 0%;
            background-color: #10b981;
            transition: width 0.3s ease;
        }
        .strength-indicator.weak {
            width: 33%;
            background-color: #ef4444;
        }
        .strength-indicator.medium {
            width: 66%;
            background-color: #f59e0b;
        }
        .strength-indicator.strong {
            width: 100%;
            background-color: #10b981;
        }
    </style>
</head>
<body class="bg-background-light font-sans text-gray-900 overflow-x-hidden">
    <div class="flex min-h-screen">
        <!-- Left Side - Marketing -->
        <div class="hidden lg:flex lg:w-1/2 bg-[#1a140b] relative items-center justify-center p-16 overflow-hidden">
            <div class="absolute inset-0 z-0">
                <img alt="Marketing Image" class="w-full h-full object-cover opacity-40" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCZGo9K_4Z30v82KT2IXGF2bwYo-axQcKTw5uP5J0PK21i6JXcLrFpUyLjLKcAr3yNBUuYmKzQ1WwxCQC6qXOUlbhbYlsxmdJ14c1FOf78Gh9KINSYaShTvG2YRdCd9kZyeaVrOI38kjr6h7XblNlG9Xh0HnI2717oLeKwe8EDM_7U1hm9TcUvtBVY8HJB6XsvU2hlNsbT5Yv4RLBDAqKHtKpUV9lCxn3b-ukuDuphIikXMzkJRyq5HrUdYRHBz0VLDicqOrV5l5VU">
                <div class="absolute inset-0 bg-gradient-to-t from-[#1a140b] via-transparent to-transparent"></div>
            </div>
            <div class="relative z-10 max-w-lg text-center lg:text-left">
                <div class="mb-8 inline-flex items-center gap-3">
                    <div class="bg-primary rounded-lg p-2 text-white">
                        <span class="material-symbols-outlined text-3xl">inventory_2</span>
                    </div>
                    <span class="text-white text-2xl font-black tracking-tighter">HMFULFILL</span>
                </div>
                <h1 class="text-5xl font-black text-white leading-tight mb-6">
                    Start Selling Your <span class="text-primary">Unique</span> POD Products
                </h1>
                <p class="text-gray-300 text-lg leading-relaxed mb-8">
                    Join thousands of creators who scale their businesses with our automated fulfillment network. Focus on design, we handle the rest.
                </p>
                <div class="grid grid-cols-2 gap-6">
                    <div class="flex items-center gap-3 text-white font-medium">
                        <span class="material-symbols-outlined text-primary">check_circle</span>
                        Global Shipping
                    </div>
                    <div class="flex items-center gap-3 text-white font-medium">
                        <span class="material-symbols-outlined text-primary">check_circle</span>
                        Premium Quality
                    </div>
                    <div class="flex items-center gap-3 text-white font-medium">
                        <span class="material-symbols-outlined text-primary">check_circle</span>
                        Shopify Integration
                    </div>
                    <div class="flex items-center gap-3 text-white font-medium">
                        <span class="material-symbols-outlined text-primary">check_circle</span>
                        No Minimums
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 lg:p-16">
            <div class="w-full max-w-md">
                <!-- Mobile Logo -->
                <div class="lg:hidden mb-8 flex items-center gap-2">
                    <div class="bg-primary rounded-lg p-1.5 text-white">
                        <span class="material-symbols-outlined">inventory_2</span>
                    </div>
                    <span class="text-xl font-bold tracking-tight">HMFULFILL</span>
                </div>

                <div class="mb-10">
                    <h2 class="text-3xl font-black text-gray-900">Create your account</h2>
                    <p class="text-gray-500 mt-2">
                        Already have an account? 
                        <a class="text-primary font-semibold hover:underline" href="{{ route('login') }}">Sign in</a>
                    </p>
                </div>

                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-lg border border-red-200 bg-red-50">
                        <ul class="text-sm text-red-600">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5" for="name">Full Name</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xl">person</span>
                            <input 
                                class="w-full pl-10 pr-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all placeholder:text-gray-400 @error('name') border-red-300 @enderror" 
                                id="name" 
                                name="name" 
                                placeholder="John Doe" 
                                value="{{ old('name') }}"
                                required 
                                autofocus
                                type="text"
                            />
                        </div>
                        @error('name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5" for="business_name">
                            Business Name <span class="text-gray-400 font-normal">(Optional)</span>
                        </label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xl">storefront</span>
                            <input 
                                class="w-full pl-10 pr-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all placeholder:text-gray-400" 
                                id="business_name" 
                                name="business_name" 
                                placeholder="Acme Studios" 
                                value="{{ old('business_name') }}"
                                type="text"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5" for="email">Email Address</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xl">mail</span>
                            <input 
                                class="w-full pl-10 pr-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all placeholder:text-gray-400 @error('email') border-red-300 @enderror" 
                                id="email" 
                                name="email" 
                                placeholder="john@example.com" 
                                value="{{ old('email') }}"
                                required 
                                type="email"
                            />
                        </div>
                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5" for="password">Password</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xl">lock</span>
                            <input 
                                class="w-full pl-10 pr-10 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all placeholder:text-gray-400 @error('password') border-red-300 @enderror" 
                                id="password" 
                                name="password" 
                                placeholder="••••••••" 
                                required 
                                type="password"
                                oninput="updatePasswordStrength(this.value)"
                            />
                            <button 
                                type="button" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                onclick="togglePasswordVisibility('password')"
                            >
                                <span class="material-symbols-outlined text-xl" id="password-toggle-icon">visibility</span>
                            </button>
                        </div>
                        <div class="mt-2.5">
                            <div class="password-strength-bar">
                                <div class="strength-indicator" id="strength-indicator"></div>
                            </div>
                            <div class="flex justify-between mt-1.5">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400" id="strength-text">Password Strength</span>
                                <span class="text-[10px] font-medium text-gray-400">At least 8 characters</span>
                            </div>
                        </div>
                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5" for="password_confirmation">Confirm Password</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xl">lock_reset</span>
                            <input 
                                class="w-full pl-10 pr-10 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all placeholder:text-gray-400 @error('password_confirmation') border-red-300 @enderror" 
                                id="password_confirmation" 
                                name="password_confirmation" 
                                placeholder="••••••••" 
                                required 
                                type="password"
                            />
                            <button 
                                type="button" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                onclick="togglePasswordVisibility('password_confirmation')"
                            >
                                <span class="material-symbols-outlined text-xl" id="password_confirmation-toggle-icon">visibility</span>
                            </button>
                        </div>
                        @error('password_confirmation')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-start gap-3 py-2">
                        <div class="flex items-center h-5">
                            <input 
                                class="h-5 w-5 rounded border-gray-300 text-primary focus:ring-primary transition-all cursor-pointer" 
                                id="terms" 
                                name="terms" 
                                required 
                                type="checkbox"
                            />
                        </div>
                        <label class="text-sm text-gray-600 leading-tight" for="terms">
                            I agree to the <a class="text-primary hover:underline font-medium" href="#">Terms of Service</a> and <a class="text-primary hover:underline font-medium" href="#">Privacy Policy</a>.
                        </label>
                    </div>

                    <button 
                        class="w-full bg-primary hover:bg-primary-hover text-white font-bold py-4 rounded-xl shadow-lg shadow-primary/20 transform active:scale-[0.98] transition-all flex items-center justify-center gap-2" 
                        type="submit"
                    >
                        Create Account
                        <span class="material-symbols-outlined text-xl">arrow_forward</span>
                    </button>

                    <div class="relative py-4">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-100"></div>
                        </div>
                        <div class="relative flex justify-center text-xs uppercase tracking-widest font-bold">
                            <span class="bg-background-light px-4 text-gray-400">Or continue with</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <button 
                            type="button" 
                            class="flex items-center justify-center gap-2 py-3 px-4 border border-gray-200 rounded-xl bg-white hover:bg-gray-50 transition-colors text-sm font-semibold text-gray-700"
                        >
                            <img alt="Google" class="w-5 h-5" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBBJUZQBUWiz7p8tsQ8QzYAVymIPbOY7W0MUkbOcgPITz5CMSzQRer0BrU9KkR80L7-km494ByzFAi7ZsY3spw2kbNjSj0MVyns9rF41AHA8Fcd8P5J-C9yRpmVSCDzpH9REg6D3VB1lLWcTQ-cwj_eFKF5qcR8RP-Fu0TUJBVr1ynv18RHTxZpb41Ql6YS-Q923evQS1GU7a9Or-ZunBJ5g8tJcQaaz-99nFWeJKhSN_b2sLM3YgEY99HEL_5OV95_nFq_2a5h9Jk">
                            Google
                        </button>
                        <button 
                            type="button" 
                            class="flex items-center justify-center gap-2 py-3 px-4 border border-gray-200 rounded-xl bg-white hover:bg-gray-50 transition-colors text-sm font-semibold text-gray-700"
                        >
                            <img alt="Facebook" class="w-5 h-5" src="https://lh3.googleusercontent.com/aida-public/AB6AXuA22URubS-L91cHpDTxnbIwEogStSrdVywU4p5x_nsCqfO3uoSgzAUHJPRmiHowIkJgNNb1afA0NM9WTQfZDWC-IKYymmLRYX99_-r90wvmwlDHmbm4kzwFU4s1AIuz7YUVAsLLct2N_evb6GdF91ZGLB3TleEr5gA1Zp5DM4At5GUgwm7_9G2qcgW1DlZZz1343tbQiqUmWmXwpGJn_bvwpzHOT4oI6V2hKfw5Rxe9PbiWW7T5miMX3cGg-b4WHv8uyUGzhAbm_JA">
                            Facebook
                        </button>
                    </div>
                </form>

                <footer class="mt-12 text-center text-xs text-gray-400">
                    © {{ date('Y') }} HMFULFILL Logistics. All rights reserved.
                </footer>
            </div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '-toggle-icon');
            if (field.type === 'password') {
                field.type = 'text';
                icon.textContent = 'visibility_off';
            } else {
                field.type = 'password';
                icon.textContent = 'visibility';
            }
        }

        function updatePasswordStrength(password) {
            const indicator = document.getElementById('strength-indicator');
            const strengthText = document.getElementById('strength-text');
            
            if (!password || password.length === 0) {
                indicator.className = 'strength-indicator';
                indicator.style.width = '0%';
                strengthText.textContent = 'Password Strength';
                strengthText.className = 'text-[10px] font-bold uppercase tracking-wider text-gray-400';
                return;
            }

            let strength = 0;
            let strengthLabel = '';
            let strengthColor = '';

            // Length check
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;

            // Character variety checks
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;

            if (strength <= 2) {
                strengthLabel = 'Weak Password';
                strengthColor = 'text-red-600';
                indicator.className = 'strength-indicator weak';
            } else if (strength <= 4) {
                strengthLabel = 'Medium Password';
                strengthColor = 'text-amber-600';
                indicator.className = 'strength-indicator medium';
            } else {
                strengthLabel = 'Strong Password';
                strengthColor = 'text-green-600';
                indicator.className = 'strength-indicator strong';
            }

            strengthText.textContent = strengthLabel;
            strengthText.className = 'text-[10px] font-bold uppercase tracking-wider ' + strengthColor;
        }
    </script>
</body>
</html>
