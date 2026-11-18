@php
    $user = session('auth_user');
@endphp

<header class="sticky top-0 z-30 h-16 border-b border-[#d7e7e9] bg-white/95 backdrop-blur flex items-center justify-between gap-3 px-4 lg:px-6">
    <div class="flex min-w-0 items-center gap-3">
        <button type="button"
                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-[#d7e7e9] text-[#075866] hover:bg-[#e8f6f7] lg:hidden"
                aria-label="Buka menu"
                @click="sidebarOpen = true">
            <span class="space-y-1.5">
                <span class="block h-0.5 w-5 rounded bg-current"></span>
                <span class="block h-0.5 w-5 rounded bg-current"></span>
                <span class="block h-0.5 w-5 rounded bg-current"></span>
            </span>
        </button>

        <div class="min-w-0">
            <div class="flex items-center gap-2">
                <span class="hidden h-2 w-2 rounded-full bg-[#f8c21c] sm:block"></span>
                <h1 class="text-base font-semibold text-slate-900">
                    {{ $title ?? 'Sistem Capaian Pembelajaran Lulusan' }}
                </h1>
            </div>
            <p class="truncate text-xs text-[#0e7a86]">
                Tiga Serangkai University
            </p>
        </div>
    </div>

    <div class="flex shrink-0 items-center gap-3">
        <a href="{{ route('profil.show') }}"
           class="hidden rounded-lg border border-[#d7e7e9] bg-[#f7fbfb] px-3 py-2 text-right transition hover:border-[#0e7a86] hover:bg-[#e8f6f7] sm:block">
            <div class="text-sm font-medium text-slate-900">
                {{ $user['name'] ?? '-' }}
            </div>
            <div class="text-xs font-medium text-[#0e7a86]">
                {{ $user['role'] ?? '-' }}
            </div>
        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="tsu-secondary-button rounded-lg px-3 py-2 text-sm font-medium sm:px-4">
                Logout
            </button>
        </form>
    </div>
</header>



