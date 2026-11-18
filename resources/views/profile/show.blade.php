@extends('layouts.app')

@section('content')
@php $title = 'Profil Saya'; @endphp

<div class="mb-6">
    <h2 class="text-xl font-bold text-slate-900">Profil Saya</h2>
    <p class="mt-1 text-sm text-slate-500">Informasi akun dan pengaturan keamanan.</p>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

    {{-- Kartu Info Profil --}}
    <section class="tsu-card rounded-lg p-6">
        <div class="mb-5 flex items-center gap-4">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-[#0e7a86] text-2xl font-bold text-white">
                {{ strtoupper(substr($user['name'] ?? 'U', 0, 1)) }}
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-900">{{ $user['name'] ?? '-' }}</h3>
                <p class="text-sm text-slate-500">{{ $user['email'] ?? '-' }}</p>
            </div>
        </div>

        <div class="space-y-3 divide-y divide-slate-100">
            <div class="flex items-center justify-between py-2">
                <span class="text-sm text-slate-500">Role</span>
                <span class="rounded-full bg-[#e8f6f7] px-3 py-1 text-xs font-semibold text-[#0e7a86] capitalize">
                    {{ str_replace('_', ' ', $user['role'] ?? '-') }}
                </span>
            </div>

            @if(!empty($user['fakultas']))
            <div class="flex items-center justify-between py-2">
                <span class="text-sm text-slate-500">Fakultas</span>
                <span class="text-sm font-medium text-slate-800">{{ $user['fakultas']['nama'] ?? '-' }}</span>
            </div>
            @endif

            @if(!empty($user['prodi']))
            <div class="flex items-center justify-between py-2">
                <span class="text-sm text-slate-500">Program Studi</span>
                <span class="text-sm font-medium text-slate-800">{{ $user['prodi']['nama'] ?? '-' }}</span>
            </div>
            @endif

            <div class="flex items-center justify-between py-2">
                <span class="text-sm text-slate-500">Status Akun</span>
                <span class="rounded-full border border-green-200 bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">
                    ● Aktif
                </span>
            </div>
        </div>
    </section>

    {{-- Kartu Ganti Password --}}
    <section class="tsu-card rounded-lg p-6">
        <div class="mb-5">
            <h3 class="font-semibold text-slate-900">Ganti Password</h3>
            <p class="mt-1 text-sm text-slate-500">Gunakan password yang kuat dan unik untuk keamanan akun Anda.</p>
        </div>

        <form method="POST" action="{{ route('profil.change-password') }}" class="space-y-4">
            @csrf

            <div>
                <label for="current_password" class="mb-1.5 block text-sm font-medium text-slate-700">
                    Password Saat Ini
                </label>
                <input
                    type="password"
                    id="current_password"
                    name="current_password"
                    autocomplete="current-password"
                    placeholder="Masukkan password lama"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-[#0e7a86] focus:ring-2 focus:ring-[#e8f6f7] @error('current_password') border-red-400 @enderror"
                    required>
                @error('current_password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">
                    Password Baru
                </label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="new-password"
                    placeholder="Minimal 8 karakter"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-[#0e7a86] focus:ring-2 focus:ring-[#e8f6f7] @error('password') border-red-400 @enderror"
                    required>
                @error('password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-slate-700">
                    Konfirmasi Password Baru
                </label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    autocomplete="new-password"
                    placeholder="Ulangi password baru"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-[#0e7a86] focus:ring-2 focus:ring-[#e8f6f7]"
                    required>
            </div>

            <div class="pt-1">
                <button type="submit"
                    class="tsu-primary-button w-full rounded-lg px-4 py-2.5 text-sm font-semibold">
                    Simpan Password Baru
                </button>
            </div>
        </form>
    </section>

</div>
@endsection
