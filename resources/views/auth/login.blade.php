<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - {{ config('app.name', 'Laravel') }}</title>
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
    </style>
</head>
<body class="bg-white min-h-screen flex items-center justify-center p-6" style="background: linear-gradient(135deg, #F5F5F5 0%, #FFFFFF 100%);">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-lg shadow-lg p-8 border border-gray-200">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold mb-2" style="color: #000000;">Login</h1>
                <p class="text-sm" style="color: #666666;">Please sign in to your account</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-lg border" style="background-color: #FFF5E6; border-color: #F7961D;">
                    <ul class="text-sm" style="color: #D97706;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-5">
                    <label for="email" class="block text-sm font-semibold mb-2" style="color: #000000;">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}"
                        required 
                        autofocus
                        class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                        style="border-color: #E5E5E5; color: #000000; background-color: #FFFFFF;"
                        onfocus="this.style.borderColor='#F7961D'; this.style.boxShadow='0 0 0 3px rgba(247, 150, 29, 0.1)';"
                        onblur="this.style.borderColor='#E5E5E5'; this.style.boxShadow='none';"
                    >
                </div>

                <div class="mb-5">
                    <label for="password" class="block text-sm font-semibold mb-2" style="color: #000000;">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                        style="border-color: #E5E5E5; color: #000000; background-color: #FFFFFF;"
                        onfocus="this.style.borderColor='#F7961D'; this.style.boxShadow='0 0 0 3px rgba(247, 150, 29, 0.1)';"
                        onblur="this.style.borderColor='#E5E5E5'; this.style.boxShadow='none';"
                    >
                </div>

                <div class="mb-6 flex items-center justify-between">
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="remember" 
                            name="remember"
                            class="mr-2 w-4 h-4"
                            style="accent-color: #F7961D;"
                        >
                        <label for="remember" class="text-sm" style="color: #666666;">Remember me</label>
                    </div>
                </div>

                <button 
                    type="submit"
                    class="w-full py-3 px-5 rounded-lg font-semibold text-white transition-all hover:shadow-lg transform hover:-translate-y-0.5"
                    style="background-color: #F7961D;"
                    onmouseover="this.style.backgroundColor='#E6891A';"
                    onmouseout="this.style.backgroundColor='#F7961D';"
                >
                    Sign In
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm" style="color: #666666;">
                    Don't have an account? 
                    <a href="{{ route('register') }}" class="font-semibold underline underline-offset-2 transition-colors" style="color: #F7961D;" onmouseover="this.style.color='#E6891A';" onmouseout="this.style.color='#F7961D';">
                        Sign up now
                    </a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
