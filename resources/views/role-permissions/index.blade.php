@extends('layouts.app')

@section('title', 'Manajemen Role & Hak Akses')

@section('content')
<div class="max-w-5xl mx-auto">

    {{-- Header --}}
    <div class="mb-6 flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Manajemen Role & Hak Akses</h1>
            <p class="mt-1 text-sm text-slate-500">Kelola role pengguna dan atur akses fitur untuk setiap role.</p>
        </div>
        <button type="button" onclick="openTambahModal()"
                class="inline-flex items-center gap-2 rounded-lg bg-[#0e7a86] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#075866] transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Role
        </button>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 flex items-center gap-2">
            <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            {{ session('error') }}
        </div>
    @endif

    {{-- Tabel Role --}}
    <div class="rounded-2xl bg-white shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/60">
                    <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Nama Role</th>
                    <th class="px-6 py-3.5 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">User</th>
                    <th class="px-6 py-3.5 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Fitur Aktif</th>
                    <th class="px-6 py-3.5 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Tipe</th>
                    <th class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($roles as $roleData)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-full {{ $roleData['is_system'] ? 'bg-gradient-to-br from-[#0e7a86] to-[#075866]' : 'bg-gradient-to-br from-violet-500 to-violet-700' }} flex items-center justify-center shrink-0">
                                    <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $roleData['name'] }}</p>
                                    @if($roleData['name'] === 'superadmin')
                                        <p class="text-xs text-slate-400">Akses penuh ke semua fitur</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">
                                {{ $roleData['users_count'] }} user
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($roleData['name'] === 'superadmin')
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700">
                                    <span>✦</span> Semua
                                </span>
                            @else
                                <span class="inline-flex items-center justify-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-700">
                                    {{ $roleData['permissions_count'] }} fitur
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($roleData['is_system'])
                                <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">Sistem</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-violet-100 px-2.5 py-0.5 text-xs font-semibold text-violet-700">Custom</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                {{-- Edit (semua kecuali superadmin) --}}
                                @if($roleData['name'] !== 'superadmin')
                                    <a href="{{ route('role-permissions.edit', $roleData['id']) }}"
                                       class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 hover:border-[#0e7a86] hover:text-[#0e7a86] transition-colors">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </a>
                                @endif

                                {{-- Hapus (hanya role custom, bukan sistem) --}}
                                @if(!$roleData['is_system'])
                                    <form method="POST"
                                          action="{{ route('role-permissions.destroy', $roleData['id']) }}"
                                          onsubmit="return confirm('Hapus role \'{{ $roleData['name'] }}\'? Aksi ini tidak bisa dibatalkan.')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 bg-white px-3 py-1.5 text-xs font-medium text-rose-500 hover:bg-rose-50 transition-colors">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Hapus
                                        </button>
                                    </form>
                                @endif

                                {{-- Superadmin: locked badge --}}
                                @if($roleData['name'] === 'superadmin')
                                    <span class="inline-flex items-center gap-1 rounded-lg border border-slate-100 bg-slate-50 px-3 py-1.5 text-xs font-medium text-slate-400">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        Protected
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-400">Tidak ada role tersedia.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Info note --}}
    <p class="mt-4 text-xs text-slate-400 text-center">
        Role bertipe <span class="font-semibold text-amber-600">Sistem</span> tidak dapat dihapus. Role bertipe <span class="font-semibold text-violet-600">Custom</span> dapat dibuat, diedit, dan dihapus.
    </p>

</div>

{{-- ==================== MODAL TAMBAH ROLE ==================== --}}
<div id="modal-tambah" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeTambahModal()"></div>
    <div class="relative z-10 w-full max-w-2xl rounded-2xl bg-white shadow-2xl max-h-[90vh] flex flex-col">

        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 shrink-0">
            <h3 class="text-base font-bold text-slate-900">Tambah Role Baru</h3>
            <button onclick="closeTambahModal()" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100">✕</button>
        </div>

        <form method="POST" action="{{ route('role-permissions.store') }}" class="flex flex-col flex-1 overflow-hidden">
            @csrf
            <div class="overflow-y-auto flex-1 px-6 py-5 space-y-5">

                <div>
                    <label class="tsu-label">Nama Role <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required
                           value="{{ old('name') }}"
                           pattern="[a-z0-9_]+" title="Hanya huruf kecil, angka, dan underscore"
                           placeholder="contoh: admin_laporan"
                           class="tsu-input w-full font-mono">
                    <p class="mt-1 text-xs text-slate-400">Gunakan huruf kecil, angka, dan underscore. Contoh: <code class="bg-slate-100 px-1 rounded">admin_laporan</code></p>
                </div>

                <div>
                    <p class="tsu-label mb-3">Fitur yang dapat diakses</p>
                    @foreach($groups as $groupName => $perms)
                        <div class="mb-4">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $groupName }}</p>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($perms as $perm)
                                    <label class="flex cursor-pointer items-center gap-2.5 rounded-lg border border-slate-200 px-3 py-2.5 hover:border-[#0e7a86]/40 hover:bg-[#0e7a86]/5 transition-all">
                                        <input type="checkbox" name="permissions[]" value="{{ $perm['name'] }}"
                                               class="h-4 w-4 rounded border-slate-300 accent-[#0e7a86]">
                                        <span class="text-sm text-slate-700">{{ $perm['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>

            <div class="flex justify-end gap-3 border-t border-slate-100 px-6 py-4 shrink-0">
                <button type="button" onclick="closeTambahModal()" class="tsu-secondary-button rounded-lg px-4 py-2 text-sm">Batal</button>
                <button type="submit" class="tsu-primary-button rounded-lg px-5 py-2 text-sm font-semibold">Simpan Role</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openTambahModal() {
    document.getElementById('modal-tambah').classList.remove('hidden');
}
function closeTambahModal() {
    document.getElementById('modal-tambah').classList.add('hidden');
}
document.addEventListener('DOMContentLoaded', function () {
    @if(session('open_modal') === 'tambah')
        openTambahModal();
    @endif
});
</script>
@endpush
