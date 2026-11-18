@extends('layouts.app')

@section('content')
@php
    $warnaOptions = [
        'success' => ['label' => 'Hijau', 'cls' => 'bg-emerald-100 text-emerald-800 border-emerald-200'],
        'warning' => ['label' => 'Kuning', 'cls' => 'bg-amber-100 text-amber-800 border-amber-200'],
        'danger'  => ['label' => 'Merah', 'cls' => 'bg-red-100 text-red-800 border-red-200'],
        'info'    => ['label' => 'Biru', 'cls' => 'bg-blue-100 text-blue-800 border-blue-200'],
        'slate'   => ['label' => 'Abu-abu', 'cls' => 'bg-slate-100 text-slate-600 border-slate-200'],
    ];
@endphp

<div class="space-y-8">

    {{-- ── Header ── --}}
    <div>
        <h1 class="text-xl font-bold text-slate-900">Predikat / Kategori Nilai CPL</h1>
        <p class="mt-1 text-sm text-slate-500">
            Atur batas nilai predikat: <strong>Global</strong> berlaku untuk semua angkatan &amp; prodi,
            <strong>Override</strong> berlaku khusus untuk angkatan + prodi tertentu.
        </p>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- SECTION 1 — PREDIKAT GLOBAL                                           --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div class="tsu-card rounded-lg overflow-hidden">
        <div class="flex items-center justify-between border-b border-[#d7e7e9] px-6 py-4">
            <div>
                <h2 class="font-semibold text-slate-800">Predikat Global</h2>
                <p class="text-xs text-slate-500 mt-0.5">Dipakai bila belum ada override untuk angkatan + prodi tertentu</p>
            </div>
            <button onclick="openGlobalModal()"
                    class="tsu-primary-button rounded-lg px-4 py-2 text-sm font-semibold">
                + Tambah Predikat
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-[#f7fbfb] text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Nama Predikat</th>
                        <th class="px-4 py-3 text-center">Batas Nilai (Min)</th>
                        <th class="px-4 py-3 text-center">Warna</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($globalData as $item)
                        <tr class="hover:bg-[#f7fbfb]">
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $item['nama'] }}</td>
                            <td class="px-4 py-3 text-center font-bold tabular-nums text-slate-700">
                                &ge; {{ number_format($item['batas_bawah'], 2) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php $w = $warnaOptions[$item['warna']] ?? $warnaOptions['slate']; @endphp
                                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium {{ $w['cls'] }}">
                                    {{ $w['label'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="inline-flex gap-2">
                                    <button onclick='openGlobalModal({{ json_encode($item) }})'
                                            class="rounded border border-[#d7e7e9] px-3 py-1 text-xs text-[#0e7a86] hover:bg-[#e8f6f7] transition">
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('kategori-nilais.destroy', $item['id']) }}"
                                          onsubmit="return confirm('Hapus predikat {{ $item['nama'] }}?')">
                                        @csrf @method('DELETE')
                                        <button class="rounded border border-red-200 px-3 py-1 text-xs text-red-600 hover:bg-red-50 transition">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-400">
                                Belum ada predikat global. Klik tombol tambah untuk memulai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- SECTION 2 — OVERRIDE PER ANGKATAN + PRODI                            --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div x-data="overrideSection()" class="tsu-card rounded-lg overflow-hidden">
        <div class="border-b border-[#d7e7e9] px-6 py-4">
            <h2 class="font-semibold text-slate-800">Override Predikat per Angkatan &amp; Prodi</h2>
            <p class="text-xs text-slate-500 mt-0.5">
                Override menimpa predikat global untuk kombinasi angkatan + prodi yang dipilih.
            </p>
        </div>

        {{-- Selector --}}
        <div class="border-b border-[#d7e7e9] bg-[#f7fbfb] px-6 py-4">
            <div class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-600">Prodi</label>
                    <select x-model="selectedProdi" @change="onProdiChange()"
                            class="rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm focus:outline-none focus:border-[#0e7a86]"
                            id="sel-prodi">
                        <option value="">-- Pilih Prodi --</option>
                        @foreach($prodis as $prodi)
                            <option value="{{ $prodi['id'] }}">{{ $prodi['nama'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-600">Angkatan</label>
                    <select x-model="selectedAngkatan" @change="loadOverrides()"
                            :disabled="!selectedProdi || loadingAngkatan"
                            class="rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm focus:outline-none focus:border-[#0e7a86] disabled:opacity-50"
                            id="sel-angkatan">
                        <option value="">-- Pilih Angkatan --</option>
                        <template x-for="a in angkatans" :key="a">
                            <option :value="a" x-text="a"></option>
                        </template>
                    </select>
                </div>
                <div class="text-xs">
                    <template x-if="loadingAngkatan">
                        <span class="text-slate-500">Memuat angkatan...</span>
                    </template>
                    <template x-if="!loadingAngkatan && angkatan_loaded && angkatans.length === 0">
                        <span class="text-amber-600 font-medium">Tidak ada angkatan ditemukan untuk prodi ini.</span>
                    </template>
                </div>
            </div>
        </div>

        {{-- Override Table --}}
        <div x-show="selectedAngkatan" style="display:none">

            {{-- Status Bar --}}
            <div class="flex items-center justify-between bg-[#e8f6f7] px-6 py-3 border-b border-[#d7e7e9]">
                <div class="text-sm">
                    <span x-show="hasOverride" class="font-medium text-[#0e7a86]">
                        Menggunakan predikat override untuk angkatan ini.
                    </span>
                    <span x-show="!hasOverride" class="font-medium text-amber-600">
                        Belum ada override — menggunakan predikat global.
                    </span>
                </div>
                <div class="flex gap-2">
                    <button @click="openOverrideModal(null)"
                            class="tsu-primary-button rounded-lg px-3 py-1.5 text-xs font-semibold">
                        + Tambah Predikat
                    </button>
                    <button x-show="hasOverride"
                            @click="resetOverrides()"
                            class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 transition">
                        Reset ke Global
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-[#f7fbfb] text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Nama Predikat</th>
                            <th class="px-4 py-3 text-center">Batas Nilai (Min)</th>
                            <th class="px-4 py-3 text-center">Warna</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <template x-if="loadingOverrides">
                            <tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">Memuat data...</td></tr>
                        </template>
                        <template x-if="!loadingOverrides && overrides.length === 0">
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-slate-400">
                                    Belum ada override untuk kombinasi ini.
                                    Klik <strong>Tambah Predikat</strong> untuk membuat override,
                                    atau biarkan kosong untuk menggunakan predikat global.
                                </td>
                            </tr>
                        </template>
                        <template x-for="item in overrides" :key="item.id">
                            <tr class="hover:bg-[#f7fbfb]">
                                <td class="px-4 py-3 font-medium text-slate-800" x-text="item.nama"></td>
                                <td class="px-4 py-3 text-center font-bold tabular-nums text-slate-700">
                                    &ge; <span x-text="parseFloat(item.batas_bawah).toFixed(2)"></span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span :class="warnaClass(item.warna)"
                                          class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium"
                                          x-text="warnaLabel(item.warna)"></span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="inline-flex gap-2">
                                        <button @click="openOverrideModal(item)"
                                                class="rounded border border-[#d7e7e9] px-3 py-1 text-xs text-[#0e7a86] hover:bg-[#e8f6f7] transition">
                                            Edit
                                        </button>
                                        <button @click="deleteOverride(item.id)"
                                                class="rounded border border-red-200 px-3 py-1 text-xs text-red-600 hover:bg-red-50 transition">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Placeholder saat belum pilih --}}
        <div x-show="!selectedAngkatan" class="px-6 py-10 text-center text-slate-400">
            Pilih Prodi dan Angkatan untuk melihat atau mengatur predikat override.
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL: Predikat Global                                                     --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div id="modal-global" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 hidden">
    <div class="w-full max-w-md rounded-xl bg-white shadow-2xl">
        <div class="border-b border-slate-100 px-6 py-4">
            <h3 class="font-semibold text-slate-800" id="modal-global-title">Tambah Predikat Global</h3>
        </div>
        <form id="form-global" method="POST" action="{{ route('kategori-nilais.store') }}">
            @csrf
            <input type="hidden" name="_method" id="global-method" value="POST">
            <div class="space-y-4 px-6 py-5">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-600">Nama Predikat</label>
                    <input type="text" name="nama" id="global-nama" required
                           placeholder="Contoh: Sangat Baik"
                           class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm focus:outline-none focus:border-[#0e7a86]">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-600">Batas Bawah Nilai (Min)</label>
                    <input type="number" name="batas_bawah" id="global-batas" required
                           min="0" max="100" step="0.01"
                           placeholder="Contoh: 80"
                           class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm focus:outline-none focus:border-[#0e7a86]">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-600">Warna Lencana</label>
                    <select name="warna" id="global-warna" required
                            class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm focus:outline-none focus:border-[#0e7a86]">
                        <option value="success">Hijau</option>
                        <option value="warning">Kuning</option>
                        <option value="danger">Merah</option>
                        <option value="info">Biru</option>
                        <option value="slate">Abu-abu</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-3 border-t border-slate-100 px-6 py-4">
                <button type="button" onclick="closeGlobalModal()"
                        class="tsu-secondary-button rounded-lg px-4 py-2 text-sm">Batal</button>
                <button type="submit" class="tsu-primary-button rounded-lg px-4 py-2 text-sm font-semibold">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL: Override per Angkatan (Alpine.js driven)                            --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div x-data="overrideModal()" id="modal-override-root">
    <div x-show="open" x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" style="display:none">
        <div class="w-full max-w-md rounded-xl bg-white shadow-2xl" @click.outside="open=false">
            <div class="border-b border-slate-100 px-6 py-4">
                <h3 class="font-semibold text-slate-800" x-text="editId ? 'Edit Predikat Override' : 'Tambah Predikat Override'"></h3>
            </div>
            <div class="space-y-4 px-6 py-5">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-600">Nama Predikat</label>
                    <input type="text" x-model="form.nama" required
                           placeholder="Contoh: Sangat Baik"
                           class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm focus:outline-none focus:border-[#0e7a86]">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-600">Batas Bawah Nilai (Min)</label>
                    <input type="number" x-model="form.batas_bawah" required
                           min="0" max="100" step="0.01"
                           placeholder="Contoh: 80"
                           class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm focus:outline-none focus:border-[#0e7a86]">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-600">Warna Lencana</label>
                    <select x-model="form.warna"
                            class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm focus:outline-none focus:border-[#0e7a86]">
                        <option value="success">Hijau</option>
                        <option value="warning">Kuning</option>
                        <option value="danger">Merah</option>
                        <option value="info">Biru</option>
                        <option value="slate">Abu-abu</option>
                    </select>
                </div>
                <p x-show="errorMsg" x-text="errorMsg" class="text-xs text-red-600" style="display:none"></p>
            </div>
            <div class="flex justify-end gap-3 border-t border-slate-100 px-6 py-4">
                <button type="button" @click="open=false"
                        class="tsu-secondary-button rounded-lg px-4 py-2 text-sm">Batal</button>
                <button type="button" @click="save()"
                        :disabled="saving"
                        class="tsu-primary-button rounded-lg px-4 py-2 text-sm font-semibold">
                    <span x-text="saving ? 'Menyimpan...' : 'Simpan'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ── Warna helpers (shared) ──────────────────────────────────────────────────
const WARNA_MAP = {
    success: { label: 'Hijau',   cls: 'bg-emerald-100 text-emerald-800 border-emerald-200' },
    warning: { label: 'Kuning',  cls: 'bg-amber-100   text-amber-800   border-amber-200'   },
    danger:  { label: 'Merah',   cls: 'bg-red-100     text-red-800     border-red-200'     },
    info:    { label: 'Biru',    cls: 'bg-blue-100    text-blue-800    border-blue-200'    },
    slate:   { label: 'Abu-abu', cls: 'bg-slate-100   text-slate-600   border-slate-200'   },
};

// ── Modal Global (Tambah/Edit) ──────────────────────────────────────────────
let globalEditId = null;
function openGlobalModal(item = null) {
    globalEditId = item?.id ?? null;
    document.getElementById('modal-global-title').textContent = item ? 'Edit Predikat Global' : 'Tambah Predikat Global';
    document.getElementById('global-nama').value  = item?.nama ?? '';
    document.getElementById('global-batas').value = item?.batas_bawah ?? '';
    document.getElementById('global-warna').value = item?.warna ?? 'success';

    const form   = document.getElementById('form-global');
    const method = document.getElementById('global-method');
    if (item) {
        form.action = `/kategori-nilais/${item.id}`;
        method.value = 'PUT';
    } else {
        form.action = '{{ route('kategori-nilais.store') }}';
        method.value = 'POST';
    }
    document.getElementById('modal-global').classList.remove('hidden');
}
function closeGlobalModal() {
    document.getElementById('modal-global').classList.add('hidden');
}

// ── Alpine: Section Override ────────────────────────────────────────────────
function overrideSection() {
    return {
        selectedProdi: '',
        selectedAngkatan: '',
        angkatans: [],
        angkatan_loaded: false,   // true setelah fetch selesai (berhasil/gagal)
        overrides: [],
        hasOverride: false,
        loadingAngkatan: false,
        loadingOverrides: false,

        async onProdiChange() {
            this.selectedAngkatan = '';
            this.angkatans        = [];
            this.angkatan_loaded  = false;
            this.overrides        = [];
            this.hasOverride      = false;
            if (!this.selectedProdi) return;

            this.loadingAngkatan = true;
            try {
                const res = await fetch(`/kategori-nilais/angkatans?prodi_id=${this.selectedProdi}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const json = await res.json();
                this.angkatans = Array.isArray(json.data) ? json.data : [];
            } catch(e) {
                this.angkatans = [];
            }
            this.loadingAngkatan = false;
            this.angkatan_loaded = true;
        },

        async loadOverrides() {
            if (!this.selectedProdi || !this.selectedAngkatan) return;
            this.loadingOverrides = true;
            this.overrides = [];
            try {
                const res = await fetch(
                    `/kategori-nilais/overrides?prodi_id=${this.selectedProdi}&angkatan=${this.selectedAngkatan}`,
                    { headers: { 'X-Requested-With': 'XMLHttpRequest' } }
                );
                const json = await res.json();
                this.overrides   = json.data?.data   ?? [];
                this.hasOverride = json.data?.has_override ?? false;
            } catch(e) { this.overrides = []; }
            this.loadingOverrides = false;
        },

        async resetOverrides() {
            if (!confirm('Reset semua override untuk angkatan ini? Predikat global akan dipakai kembali.')) return;
            const res = await fetch('/kategori-nilais/overrides/reset', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ prodi_id: this.selectedProdi, angkatan: this.selectedAngkatan })
            });
            const json = await res.json();
            if (json.success) { await this.loadOverrides(); }
            else { alert(json.message ?? 'Gagal mereset override.'); }
        },

        openOverrideModal(item) {
            // Dispatch ke Alpine component modal
            window._overrideModalData = {
                item,
                prodiId: this.selectedProdi,
                angkatan: this.selectedAngkatan,
                reload: () => this.loadOverrides(),
            };
            window.dispatchEvent(new CustomEvent('open-override-modal', { detail: item }));
        },

        deleteOverride(id) {
            if (!confirm('Hapus predikat override ini?')) return;
            fetch(`/kategori-nilais/overrides/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                }
            }).then(r => r.json()).then(json => {
                if (json.success) { this.loadOverrides(); }
                else { alert(json.message ?? 'Gagal menghapus.'); }
            });
        },

        warnaClass(w) { return WARNA_MAP[w]?.cls ?? WARNA_MAP.slate.cls; },
        warnaLabel(w) { return WARNA_MAP[w]?.label ?? w; },
    };
}

// ── Alpine: Modal Override ──────────────────────────────────────────────────
function overrideModal() {
    return {
        open: false,
        saving: false,
        editId: null,
        errorMsg: '',
        form: { nama: '', batas_bawah: '', warna: 'success' },

        init() {
            window.addEventListener('open-override-modal', (e) => {
                const item = e.detail;
                this.editId   = item?.id ?? null;
                this.errorMsg = '';
                this.form = {
                    nama:        item?.nama ?? '',
                    batas_bawah: item?.batas_bawah ?? '',
                    warna:       item?.warna ?? 'success',
                };
                this.open = true;
            });
        },

        async save() {
            this.errorMsg = '';
            this.saving   = true;
            const ctx = window._overrideModalData;
            const body = {
                ...this.form,
                prodi_id: ctx.prodiId,
                angkatan: ctx.angkatan,
            };
            const url    = this.editId ? `/kategori-nilais/overrides/${this.editId}` : '/kategori-nilais/overrides';
            const method = this.editId ? 'PUT' : 'POST';

            try {
                const res = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(body),
                });
                const json = await res.json();
                if (json.success) {
                    this.open = false;
                    ctx.reload();
                } else {
                    this.errorMsg = json.message ?? 'Terjadi kesalahan.';
                }
            } catch(e) {
                this.errorMsg = 'Gagal menghubungi server.';
            }
            this.saving = false;
        },
    };
}
</script>
@endpush
@endsection
