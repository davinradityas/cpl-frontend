<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem CPL OBE</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen p-4 text-slate-800">
    <main class="mx-auto flex min-h-[calc(100vh-2rem)] w-full max-w-5xl items-center justify-center">
        <div class="grid w-full max-w-4xl overflow-hidden rounded-lg border border-[#d7e7e9] bg-white shadow-[0_22px_60px_rgba(14,122,134,0.16)] md:grid-cols-2">
            <section class="relative flex min-h-[280px] flex-col items-center justify-center bg-gradient-to-br from-[#075866] via-[#0e7a86] to-[#1597a6] px-8 py-12 text-center text-white">
                <div class="absolute left-[-52px] top-[-56px] h-40 w-40 rounded-full border-[26px] border-[#f8c21c] opacity-95"></div>
                <div class="absolute bottom-0 right-0 h-32 w-32 rounded-tl-full bg-[#f8c21c]/20"></div>
                <div class="relative flex flex-col items-center">
                    <img src="{{ asset('images/tsu logo.png') }}" alt="TSU Tiga Serangkai University" class="h-24 w-auto rounded bg-white/95 px-4 py-2 shadow-[0_10px_30px_rgba(14,122,134,0.08)]">
                    <h1 class="mt-7 text-3xl font-bold md:text-4xl">Selamat Datang</h1>
                    <p class="mx-auto mt-4 max-w-xs text-sm leading-6 text-white/85">
                        Sistem pengelolaan capaian pembelajaran lulusan berbasis outcome.
                    </p>
                </div>
            </section>

            <section class="px-6 py-8 sm:px-10">
                <div class="mb-7 text-center">
                    <img src="{{ asset('images/tsu logo.png') }}" alt="TSU" class="mx-auto h-16 w-auto">
                    <h2 class="mt-5 text-xl font-bold text-slate-900">Login Akun</h2>
                    <p class="mt-1 text-xs text-slate-500">Gunakan akun sesuai role yang sudah dibuat.</p>
                </div>

                @if(session('error'))
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ session('error') }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="mb-1.5 block text-xs font-medium text-slate-600">Email</label>
                        <input id="email"
                               type="email"
                               name="email"
                               value="{{ old('email') }}"
                               placeholder="Masukkan email Anda"
                               class="tsu-input w-full rounded-lg px-3 py-2.5 text-sm transition placeholder:text-slate-400"
                               required>

                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror

                        @if(session('errors_api.email'))
                            <p class="mt-1 text-xs text-red-600">{{ session('errors_api.email.0') }}</p>
                        @endif
                    </div>

                    <div>
                        <label for="password" class="mb-1.5 block text-xs font-medium text-slate-600">Password</label>
                        <input id="password"
                               type="password"
                               name="password"
                               placeholder="Masukkan password Anda"
                               class="tsu-input w-full rounded-lg px-3 py-2.5 text-sm transition placeholder:text-slate-400"
                               required>

                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror

                        @if(session('errors_api.password'))
                            <p class="mt-1 text-xs text-red-600">{{ session('errors_api.password.0') }}</p>
                        @endif
                    </div>

                    <button type="submit"
                            class="tsu-primary-button w-full rounded-lg px-4 py-2.5 text-sm font-semibold shadow-[0_10px_30px_rgba(14,122,134,0.08)] transition">
                        Masuk
                    </button>
                </form>
            </section>
        </div>
    </main>
</body>
</html>



