@extends('layouts.app')

@section('content')
@php
    $title    = 'Manajemen User';
    $authUser = session('auth_user');

    $roleLabels = [
        'superadmin'    => 'Superadmin',
        'admin_fakultas'=> 'Admin Fakultas',
        'admin_kaprodi' => 'Admin Kaprodi',
    ];

    $roleBadgeClass = [
        'superadmin'    => 'bg-violet-100 text-violet-700 border-violet-200',
        'admin_fakultas'=> 'bg-blue-100 text-blue-700 border-blue-200',
        'admin_kaprodi' => 'bg-teal-100 text-teal-700 border-teal-200',
    ];

    $openEditId = session('open_edit_id');
    $openModal  = session('open_modal');
@endphp

{{-- ===== HEADER ===== --}}
<div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Manajemen User</h2>
        <p class="mt-1 text-sm text-slate-500">Kelola akun pengguna sistem dan hak aksesnya.</p>
    </div>
    <button type="button" id="btn-tambah-user"
            onclick="openTambahModal()"
            class="inline-flex items-center gap-2 rounded-lg bg-[#0e7a86] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#075866] transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah User
    </button>
</div>

{{-- ===== FILTER ===== --}}
<section class="tsu-card rounded-lg p-4 mb-5">
    <form method="GET" action="{{ route('users.index') }}" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-40">
            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Cari</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Nama atau email..."
                   class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm focus:border-[#0e7a86] focus:ring-1 focus:ring-[#e8f6f7]">
        </div>
        <div class="min-w-40">
            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Role</label>
            <select name="role" class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm focus:border-[#0e7a86]">
                <option value="">Semua Role</option>
                @foreach(['superadmin' => 'Superadmin', 'admin_fakultas' => 'Admin Fakultas', 'admin_kaprodi' => 'Admin Kaprodi'] as $val => $lbl)
                    <option value="{{ $val }}" @selected(request('role') === $val)>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-36">
            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Status</label>
            <select name="is_active" class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm focus:border-[#0e7a86]">
                <option value="">Semua Status</option>
                <option value="1" @selected(request('is_active') === '1')>Aktif</option>
                <option value="0" @selected(request('is_active') === '0')>Nonaktif</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="tsu-primary-button rounded-lg px-4 py-2 text-sm font-semibold">Filter</button>
            <a href="{{ route('users.index') }}" class="tsu-secondary-button rounded-lg px-4 py-2 text-sm font-semibold">Reset</a>
        </div>
    </form>
</section>

{{-- ===== TABEL USER ===== --}}
<section class="tsu-card rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-[#d7e7e9] text-sm">
            <thead class="bg-[#f7fbfb] text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-3 text-left">Nama</th>
                    <th class="px-5 py-3 text-left">Email</th>
                    <th class="px-5 py-3 text-center">Role</th>
                    <th class="px-5 py-3 text-left">Skop Akses</th>
                    <th class="px-5 py-3 text-center">Status</th>
                    <th class="px-5 py-3 text-center">Dibuat</th>
                    <th class="px-5 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users as $user)
                    @php
                        $isSelf  = ($authUser['id'] ?? null) == $user['id'];
                        $isActive= $user['is_active'] ?? true;
                        $role    = $user['role'] ?? '-';
                    @endphp
                    <tr class="hover:bg-[#f7fbfb] transition-colors {{ !$isActive ? 'opacity-60' : '' }}">
                        <td class="px-5 py-3 font-semibold text-slate-900">
                            {{ $user['name'] ?? '-' }}
                            @if($isSelf)
                                <span class="ml-1 text-[10px] font-normal text-slate-400">(Anda)</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-slate-600">{{ $user['email'] ?? '-' }}</td>
                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium {{ $roleBadgeClass[$role] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                {{ $roleLabels[$role] ?? $role }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-slate-600 text-xs">
                            @if($role === 'admin_fakultas')
                                {{ $user['fakultas']['nama'] ?? '—' }}
                            @elseif($role === 'admin_kaprodi')
                                {{ $user['prodi']['nama'] ?? '—' }}
                            @else
                                <span class="text-slate-400">Semua</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium {{ $isActive ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                                {{ $isActive ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-center text-slate-500 text-xs">{{ $user['created_at'] ?? '-' }}</td>
                        <td class="px-5 py-3 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                {{-- Edit --}}
                                <button type="button"
                                        onclick='openEditModal(@json($user), @json($fakultasList), @json($prodiList))'
                                        title="Edit"
                                        class="rounded-md p-1.5 text-slate-500 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>

                                {{-- Reset Password --}}
                                <button type="button"
                                        onclick="openResetModal({{ $user['id'] }}, '{{ addslashes($user['name']) }}')"
                                        title="Reset Password"
                                        class="rounded-md p-1.5 text-slate-500 hover:bg-amber-50 hover:text-amber-600 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                    </svg>
                                </button>

                                @if(!$isSelf)
                                    {{-- Toggle Aktif --}}
                                    <form method="POST" action="{{ route('users.toggle-active', $user['id']) }}" class="inline">
                                        @csrf
                                        <button type="submit"
                                                title="{{ $isActive ? 'Nonaktifkan' : 'Aktifkan' }}"
                                                onclick="return confirm('{{ $isActive ? 'Nonaktifkan' : 'Aktifkan' }} user ini?')"
                                                class="rounded-md p-1.5 transition-colors {{ $isActive ? 'text-slate-500 hover:bg-rose-50 hover:text-rose-600' : 'text-slate-500 hover:bg-emerald-50 hover:text-emerald-600' }}">
                                            @if($isActive)
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                </svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            @endif
                                        </button>
                                    </form>

                                    {{-- Hapus --}}
                                    <form method="POST" action="{{ route('users.destroy', $user['id']) }}" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                title="Hapus"
                                                onclick="return confirm('Hapus permanen user {{ addslashes($user['name']) }}? Tindakan ini tidak dapat dibatalkan.')"
                                                class="rounded-md p-1.5 text-slate-500 hover:bg-rose-50 hover:text-rose-600 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-slate-500">
                            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-2xl">👤</div>
                            <p class="font-medium">Tidak ada user ditemukan.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if(($pagination['last_page'] ?? 1) > 1)
        <div class="flex items-center justify-between border-t border-[#d7e7e9] px-5 py-3 text-sm text-slate-600">
            <span>Total {{ $pagination['total'] ?? 0 }} user</span>
            <div class="flex gap-2">
                @if(($pagination['current_page'] ?? 1) > 1)
                    <a href="{{ request()->fullUrlWithQuery(['page' => ($pagination['current_page'] - 1)]) }}"
                       class="rounded-md border px-3 py-1.5 hover:bg-slate-50">← Prev</a>
                @endif
                <span class="rounded-md border border-[#0e7a86] bg-[#e8f6f7] px-3 py-1.5 font-semibold text-[#0e7a86]">
                    {{ $pagination['current_page'] ?? 1 }}
                </span>
                @if(($pagination['current_page'] ?? 1) < ($pagination['last_page'] ?? 1))
                    <a href="{{ request()->fullUrlWithQuery(['page' => ($pagination['current_page'] + 1)]) }}"
                       class="rounded-md border px-3 py-1.5 hover:bg-slate-50">Next →</a>
                @endif
            </div>
        </div>
    @endif
</section>

{{-- ====================== MODAL TAMBAH USER ====================== --}}
<div id="modal-tambah" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeTambahModal()"></div>
    <div class="relative z-10 w-full max-w-lg rounded-2xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
            <h3 class="text-base font-bold text-slate-900">Tambah User Baru</h3>
            <button type="button" onclick="closeTambahModal()"
                    class="rounded-lg p-1 text-slate-400 hover:bg-slate-100">✕</button>
        </div>
        <form method="POST" action="{{ route('users.store') }}" class="px-6 py-5 space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="tsu-label">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required
                           value="{{ old('name') }}"
                           class="tsu-input w-full" placeholder="Nama lengkap user">
                </div>
                <div class="col-span-2">
                    <label class="tsu-label">Email <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" required
                           value="{{ old('email') }}"
                           class="tsu-input w-full" placeholder="email@domain.com">
                </div>
                <div>
                    <label class="tsu-label">Password <span class="text-rose-500">*</span></label>
                    <input type="password" name="password" required minlength="8"
                           class="tsu-input w-full" placeholder="Min. 8 karakter">
                </div>
                <div>
                    <label class="tsu-label">Role <span class="text-rose-500">*</span></label>
                    <select name="role" id="tambah-role" required
                            onchange="onTambahRoleChange(this.value)"
                            class="tsu-input w-full">
                        <option value="">— Pilih Role —</option>
                        <option value="superadmin"    @selected(old('role')==='superadmin')>Superadmin</option>
                        <option value="admin_fakultas" @selected(old('role')==='admin_fakultas')>Admin Fakultas</option>
                        <option value="admin_kaprodi"  @selected(old('role')==='admin_kaprodi')>Admin Kaprodi</option>
                    </select>
                </div>

                {{-- Fakultas (muncul saat admin_fakultas) --}}
                <div class="col-span-2" id="tambah-fakultas-wrap" style="display:none">
                    <label class="tsu-label">Fakultas <span class="text-rose-500">*</span></label>
                    <select name="fakultas_id" id="tambah-fakultas" class="tsu-input w-full">
                        <option value="">— Pilih Fakultas —</option>
                        @foreach($fakultasList as $fak)
                            <option value="{{ $fak['id'] }}" @selected(old('fakultas_id')==$fak['id'])>{{ $fak['nama'] }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Prodi (muncul saat admin_kaprodi) --}}
                <div class="col-span-2" id="tambah-prodi-wrap" style="display:none">
                    <label class="tsu-label">Program Studi <span class="text-rose-500">*</span></label>
                    <select name="prodi_id" id="tambah-prodi" class="tsu-input w-full">
                        <option value="">— Pilih Prodi —</option>
                        @foreach($prodiList as $prodi)
                            <option value="{{ $prodi['id'] }}" @selected(old('prodi_id')==$prodi['id'])>{{ $prodi['nama'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeTambahModal()"
                        class="tsu-secondary-button rounded-lg px-4 py-2 text-sm">Batal</button>
                <button type="submit" class="tsu-primary-button rounded-lg px-5 py-2 text-sm font-semibold">Simpan User</button>
            </div>
        </form>
    </div>
</div>

{{-- ====================== MODAL EDIT USER ====================== --}}
<div id="modal-edit" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeEditModal()"></div>
    <div class="relative z-10 w-full max-w-lg rounded-2xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
            <h3 class="text-base font-bold text-slate-900">Edit User</h3>
            <button type="button" onclick="closeEditModal()" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100">✕</button>
        </div>
        <form id="form-edit" method="POST" class="px-6 py-5 space-y-4">
            @csrf @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="tsu-label">Nama Lengkap</label>
                    <input type="text" name="name" id="edit-name" required class="tsu-input w-full">
                </div>
                <div class="col-span-2">
                    <label class="tsu-label">Email</label>
                    <input type="email" name="email" id="edit-email" required class="tsu-input w-full">
                </div>
                <div>
                    <label class="tsu-label">Role</label>
                    <select name="role" id="edit-role" required class="tsu-input w-full"
                            onchange="onEditRoleChange(this.value)">
                        <option value="superadmin">Superadmin</option>
                        <option value="admin_fakultas">Admin Fakultas</option>
                        <option value="admin_kaprodi">Admin Kaprodi</option>
                    </select>
                </div>
                <div>
                    <label class="tsu-label">Status</label>
                    <select name="is_active" id="edit-is-active" class="tsu-input w-full">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>

                <div class="col-span-2" id="edit-fakultas-wrap">
                    <label class="tsu-label">Fakultas</label>
                    <select name="fakultas_id" id="edit-fakultas" class="tsu-input w-full">
                        <option value="">— Pilih Fakultas —</option>
                        @foreach($fakultasList as $fak)
                            <option value="{{ $fak['id'] }}">{{ $fak['nama'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-span-2" id="edit-prodi-wrap">
                    <label class="tsu-label">Program Studi</label>
                    <select name="prodi_id" id="edit-prodi" class="tsu-input w-full">
                        <option value="">— Pilih Prodi —</option>
                        @foreach($prodiList as $prodi)
                            <option value="{{ $prodi['id'] }}">{{ $prodi['nama'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeEditModal()" class="tsu-secondary-button rounded-lg px-4 py-2 text-sm">Batal</button>
                <button type="submit" class="tsu-primary-button rounded-lg px-5 py-2 text-sm font-semibold">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- ====================== MODAL RESET PASSWORD ====================== --}}
<div id="modal-reset" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="document.getElementById('modal-reset').classList.add('hidden')"></div>
    <div class="relative z-10 w-full max-w-md rounded-2xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
            <h3 class="text-base font-bold text-slate-900">Reset Password</h3>
            <button type="button" onclick="document.getElementById('modal-reset').classList.add('hidden')"
                    class="rounded-lg p-1 text-slate-400 hover:bg-slate-100">✕</button>
        </div>
        <form id="form-reset" method="POST" class="px-6 py-5 space-y-4">
            @csrf
            <p class="text-sm text-slate-600">
                Set password baru untuk: <span id="reset-user-name" class="font-semibold text-slate-900"></span>
            </p>
            <div>
                <label class="tsu-label">Password Baru <span class="text-rose-500">*</span></label>
                <input type="password" name="password" required minlength="8"
                       class="tsu-input w-full" placeholder="Min. 8 karakter">
            </div>
            <div>
                <label class="tsu-label">Konfirmasi Password <span class="text-rose-500">*</span></label>
                <input type="password" name="password_confirmation" required minlength="8"
                       class="tsu-input w-full" placeholder="Ulangi password baru">
            </div>
            <div class="rounded-lg bg-amber-50 border border-amber-200 px-3 py-2 text-xs text-amber-700">
                ⚠️ User akan otomatis logout dan harus login ulang setelah password direset.
            </div>
            <div class="flex justify-end gap-3 pt-2 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-reset').classList.add('hidden')"
                        class="tsu-secondary-button rounded-lg px-4 py-2 text-sm">Batal</button>
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-amber-500 px-5 py-2 text-sm font-semibold text-white hover:bg-amber-600 transition-colors">
                    Reset Password
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function onTambahRoleChange(role) {
    const fakWrap   = document.getElementById('tambah-fakultas-wrap');
    const prodiWrap = document.getElementById('tambah-prodi-wrap');
    const selFak    = document.getElementById('tambah-fakultas');
    const selProdi  = document.getElementById('tambah-prodi');

    fakWrap.style.display   = role === 'admin_fakultas' ? '' : 'none';
    prodiWrap.style.display = role === 'admin_kaprodi'  ? '' : 'none';

    // Reset required agar tidak ikut validasi saat tersembunyi
    selFak.required   = role === 'admin_fakultas';
    selProdi.required = role === 'admin_kaprodi';
}

function openTambahModal() {
    // Reset form
    document.querySelector('#modal-tambah form').reset();
    onTambahRoleChange('');
    document.getElementById('modal-tambah').classList.remove('hidden');
}

function closeTambahModal() {
    document.getElementById('modal-tambah').classList.add('hidden');
}

function openEditModal(user, fakultasList, prodiList) {
    document.getElementById('edit-name').value      = user.name  || '';
    document.getElementById('edit-email').value     = user.email || '';
    document.getElementById('edit-role').value      = user.role  || '';
    document.getElementById('edit-is-active').value = user.is_active ? '1' : '0';

    // Pilih fakultas
    const selFak = document.getElementById('edit-fakultas');
    selFak.innerHTML = '<option value="">— Pilih Fakultas —</option>';
    fakultasList.forEach(f => {
        const opt = new Option(f.nama, f.id, false, f.id == user.fakultas_id);
        selFak.add(opt);
    });

    // Pilih prodi
    const selProdi = document.getElementById('edit-prodi');
    selProdi.innerHTML = '<option value="">— Pilih Prodi —</option>';
    prodiList.forEach(p => {
        const opt = new Option(p.nama, p.id, false, p.id == user.prodi_id);
        selProdi.add(opt);
    });

    // Atur action form
    document.getElementById('form-edit').action = '/users/' + user.id;

    // Tampilkan/sembunyikan field
    onEditRoleChange(user.role || '');

    document.getElementById('modal-edit').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('modal-edit').classList.add('hidden');
}

function onEditRoleChange(role) {
    const fakWrap   = document.getElementById('edit-fakultas-wrap');
    const prodiWrap = document.getElementById('edit-prodi-wrap');
    const selFak    = document.getElementById('edit-fakultas');
    const selProdi  = document.getElementById('edit-prodi');

    const showFak   = role === 'admin_fakultas';
    const showProdi = role === 'admin_kaprodi';

    fakWrap.style.display   = showFak   ? '' : 'none';
    prodiWrap.style.display = showProdi ? '' : 'none';

    // Reset nilai select yang tersembunyi agar tidak ter-submit
    if (!showFak)   selFak.value   = '';
    if (!showProdi) selProdi.value = '';
}

function openResetModal(userId, userName) {
    document.getElementById('reset-user-name').textContent = userName;
    document.getElementById('form-reset').action = '/users/' + userId + '/reset-password';
    document.getElementById('modal-reset').classList.remove('hidden');
}

// Auto-open modals jika kembali dari server error
document.addEventListener('DOMContentLoaded', function () {
    @if($openModal === 'tambah')
        openTambahModal();
        // Restore nilai old() jika ada
        @if(old('role'))
            document.getElementById('tambah-role').value = '{{ old("role") }}';
            onTambahRoleChange('{{ old("role") }}');
        @endif
    @endif
});
</script>
@endpush
