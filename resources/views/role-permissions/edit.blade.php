@extends('layouts.app')

@section('title', 'Edit Role: ' . ($role['name'] ?? ''))

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- Breadcrumb --}}
    <div class="mb-6 flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('role-permissions.index') }}" class="hover:text-[#0e7a86] transition-colors">Manajemen Role</a>
        <span>/</span>
        <span class="font-medium text-slate-700">Edit: <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-xs">{{ $role['name'] }}</code></span>
    </div>

    {{-- Flash --}}
    @if(session('error'))
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('role-permissions.update', $role['id']) }}">
        @csrf @method('PUT')

        <div class="rounded-2xl bg-white shadow-sm border border-slate-100 overflow-hidden">

            {{-- Header role info --}}
            <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-5">
                <div class="flex items-center gap-4">
                    <div class="h-10 w-10 rounded-full {{ $role['is_system'] ? 'bg-gradient-to-br from-[#0e7a86] to-[#075866]' : 'bg-gradient-to-br from-violet-500 to-violet-700' }} flex items-center justify-center shrink-0">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        @if(!$role['is_system'])
                            {{-- Role custom: nama bisa diedit --}}
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Nama Role</label>
                            <input type="text" name="name"
                                   value="{{ $role['name'] }}"
                                   pattern="[a-z0-9_]+" title="Hanya huruf kecil, angka, dan underscore"
                                   class="tsu-input font-mono text-sm w-full max-w-xs">
                            <p class="mt-1 text-xs text-slate-400">Huruf kecil, angka, underscore saja.</p>
                        @else
                            {{-- Role sistem: nama tidak bisa diubah --}}
                            <p class="text-base font-bold text-slate-900">{{ $role['name'] }}</p>
                            <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700 mt-0.5">Role Sistem</span>
                        @endif
                    </div>
                    <div class="text-right text-sm text-slate-500 shrink-0">
                        <p><span class="font-semibold text-slate-800">{{ $role['users_count'] }}</span> user</p>
                        <p><span class="font-semibold text-slate-800">{{ $role['permissions_count'] }}</span> fitur aktif</p>
                    </div>
                </div>
            </div>

            {{-- Permission matrix --}}
            <div class="px-6 py-5">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-700">Pilih fitur yang dapat diakses</p>
                    <div class="flex gap-3 text-xs">
                        <button type="button" onclick="setAll(true)" class="text-[#0e7a86] hover:underline font-medium">Pilih Semua</button>
                        <span class="text-slate-300">|</span>
                        <button type="button" onclick="setAll(false)" class="text-slate-400 hover:underline">Hapus Semua</button>
                    </div>
                </div>

                @foreach($groups as $groupName => $perms)
                    <div class="mb-5">
                        <h3 class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $groupName }}</h3>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                            @foreach($perms as $perm)
                                @php
                                    $checked = in_array($perm['name'], (array) ($role['permissions'] ?? []));
                                @endphp
                                <label class="perm-label group flex cursor-pointer items-center gap-2.5 rounded-lg border px-3 py-2.5 transition-all
                                              {{ $checked ? 'border-[#0e7a86]/30 bg-[#0e7a86]/5' : 'border-slate-200 hover:border-[#0e7a86]/40 hover:bg-[#0e7a86]/5' }}">
                                    <input type="checkbox"
                                           name="permissions[]"
                                           value="{{ $perm['name'] }}"
                                           class="perm-check h-4 w-4 rounded border-slate-300 accent-[#0e7a86] cursor-pointer"
                                           onchange="updateLabel(this)"
                                           {{ $checked ? 'checked' : '' }}>
                                    <span class="text-sm font-medium text-slate-700 leading-tight">{{ $perm['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Footer actions --}}
            <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50/50 px-6 py-4">
                <a href="{{ route('role-permissions.index') }}"
                   class="tsu-secondary-button rounded-lg px-4 py-2 text-sm">← Kembali</a>
                <button type="submit"
                        class="tsu-primary-button inline-flex items-center gap-2 rounded-lg px-5 py-2 text-sm font-semibold">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
function setAll(checked) {
    document.querySelectorAll('.perm-check').forEach(cb => {
        cb.checked = checked;
        updateLabel(cb);
    });
}
function updateLabel(cb) {
    const label = cb.closest('label');
    if (cb.checked) {
        label.classList.add('border-[#0e7a86]/30', 'bg-[#0e7a86]/5');
        label.classList.remove('border-slate-200');
    } else {
        label.classList.remove('border-[#0e7a86]/30', 'bg-[#0e7a86]/5');
        label.classList.add('border-slate-200');
    }
}
</script>
@endpush
