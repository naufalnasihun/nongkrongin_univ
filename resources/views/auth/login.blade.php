<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Nongkrongin Univ</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .login-illustration {
            background: linear-gradient(135deg, #065f46 0%, #047857 50%, #059669 100%);
        }
        .floating {
            animation: floating 6s ease-in-out infinite;
        }
        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-12px); }
        }
        .food-card {
            animation: float-delayed 7s ease-in-out infinite;
        }
        @keyframes float-delayed {
            0%, 100% { transform: translateY(0px) rotate(-2deg); }
            50% { transform: translateY(-18px) rotate(2deg); }
        }
    </style>
</head>
<body class="min-h-screen bg-cream">

    <div class="min-h-screen flex">

        <div class="hidden lg:flex lg:w-1/2 login-illustration relative overflow-hidden items-center justify-center p-12">
            <div class="absolute top-10 right-10 w-72 h-72 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-10 left-10 w-96 h-96 bg-emerald-300/20 rounded-full blur-3xl"></div>

            <div class="relative z-10 text-white max-w-md">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-2xl">
                        🍔
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">Nongkrongin Univ</h1>
                        <p class="text-emerald-100 text-sm">Kantin Kampus Pintar</p>
                    </div>
                </div>

                <h2 class="text-4xl font-bold leading-tight mb-4">
                    Pesan Makanan Kantin, <br>Lebih Cepat & Praktis.
                </h2>
                <p class="text-emerald-100 text-lg mb-10">
                    Pre-order makanan dan minuman favoritmu tanpa mengantre. Cukup pesan, tunggu, lalu ambil di kantin.
                </p>

                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-5 floating">
                        <div class="text-3xl mb-2">📋</div>
                        <h3 class="font-semibold mb-1">Pilih Menu</h3>
                        <p class="text-sm text-emerald-100">Jelajahi menu kantin lengkap dengan harga.</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-5 food-card">
                        <div class="text-3xl mb-2">🛒</div>
                        <h3 class="font-semibold mb-1">Checkout</h3>
                        <p class="text-sm text-emerald-100">Tambah ke keranjang dan pesan langsung.</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-5 food-card">
                        <div class="text-3xl mb-2">⏱️</div>
                        <h3 class="font-semibold mb-1">Lacak Status</h3>
                        <p class="text-sm text-emerald-100">Pantau pesanan dari menunggu hingga siap.</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-5 floating">
                        <div class="text-3xl mb-2">✅</div>
                        <h3 class="font-semibold mb-1">Ambil Pesanan</h3>
                        <p class="text-sm text-emerald-100">Ambil langsung di kantin tanpa antre.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12">
            <div class="w-full max-w-md">

                <div class="lg:hidden flex items-center gap-3 mb-8">
                    <div class="w-11 h-11 rounded-2xl bg-emerald-600 flex items-center justify-center text-2xl">
                        🍔
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Nongkrongin Univ</h1>
                        <p class="text-gray-500 text-xs">Kantin Kampus Pintar</p>
                    </div>
                </div>

                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Selamat datang kembali 👋</h2>
                    <p class="text-gray-500">Masuk ke akun Anda untuk memesan makanan kantin.</p>
                </div>

                @if ($errors->any())
                    <div class="alert-error mb-6">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="font-medium">Login gagal</p>
                            <p class="text-sm">{{ $errors->first() }}</p>
                        </div>
                    </div>
                @endif

                <form action="{{ route('login.process') }}" method="POST" id="loginForm" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="label">Email</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                </svg>
                            </span>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="Masukkan email Anda"
                                class="input-field pl-11"
                                required
                                autofocus
                            >
                        </div>
                        @error('email')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="label">Password</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </span>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Masukkan password"
                                class="input-field pl-11 pr-11"
                                required
                            >
                            <button
                                type="button"
                                id="togglePassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400 hover:text-gray-600 transition-colors"
                                aria-label="Tampilkan password"
                            >
                                <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        id="loginBtn"
                        class="btn-primary w-full py-3 text-base"
                    >
                        <span id="btnText">Masuk</span>
                        <svg id="btnSpinner" class="hidden animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-gray-100">
                    <p class="text-center text-sm text-gray-500">
                        💡 Gunakan akun yang diberikan oleh admin kantin untuk masuk.
                    </p>
                </div>

            </div>
        </div>

    </div>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            if (type === 'text') {
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';
            } else {
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
            }
        });

        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');
        const btnText = document.getElementById('btnText');
        const btnSpinner = document.getElementById('btnSpinner');

        loginForm.addEventListener('submit', function () {
            loginBtn.disabled = true;
            btnText.textContent = 'Memproses...';
            btnSpinner.classList.remove('hidden');
        });
    </script>

</body>
</html>
