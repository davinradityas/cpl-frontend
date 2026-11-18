@extends('layouts.app')

@section('content')
@php $title = 'Data CPMK'; @endphp
<div x-data="{
    createOpen: false,
    editOpen: false,
    selected: {},

    openEdit(item) {
        this.selected = {
            ...item,
            cpl_id: item.cpl?.id ?? '',
            kurikulum_prodi_id: item.kurikulum_prodi?.id ?? '',
        };
        this.editOpen = true;
    }
}">
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Master Data CPMK</h2>
            <p class="mt-1 text-sm text-slate-500">Kelola daftar CPMK per Kurikulum Prodi. CPMK dapat dipetakan ke banyak Mata Kuliah.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" @click="$dispatch('open-import-modal-cpmk')"
                    class="tsu-secondary-button inline-flex items-center justify-center rounded-lg px-4 py-2.5 text-sm font-semibold">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                </svg>
                Import Excel
            </button>
            <button type="button" @click="createOpen = true"
                    class="tsu-primary-button inline-flex items-center justify-center rounded-lg px-4 py-2.5 text-sm font-semibold">
                Tambah CPMK
            </button>
        </div>
    </div>

    @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-700">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    {{-- Filter Kurikulum Prodi --}}
    <section class="mb-5 tsu-card rounded-lg p-4">
        <form method="GET" action="{{ route('cpmks.index') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label for="kurikulum_prodi_id" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Filter Kurikulum Prodi</label>
                <select id="kurikulum_prodi_id" name="kurikulum_prodi_id"
                        class="w-full rounded-lg border border-[#d7e7e9] bg-white px-3 py-2.5 text-sm outline-none focus:border-[#0e7a86] focus:ring-2 focus:ring-[#e8f6f7]"
                        onchange="this.form.submit()">
                    @foreach($kurikulumProdis as $kp)
                        <option value="{{ $kp['id'] }}" @selected($selectedKurikulumProdiId == $kp['id'])>
                            {{ $kp['kode_kur_prodi'] }} — {{ $kp['prodi']['nama'] ?? '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </section>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="tsu-card rounded-lg p-4">
            <div class="text-sm text-slate-500">Total CPMK</div>
            <div class="mt-2 text-2xl font-bold text-slate-900">{{ count($cpmks) }}</div>
        </div>
        <div class="tsu-card rounded-lg p-4 md:col-span-2">
            <div class="text-sm text-slate-500">Catatan</div>
            <div class="mt-2 text-sm text-slate-700">
                CPMK di sini adalah Master Data. Setelah ditambahkan, petakan CPMK ke Mata Kuliah melalui menu <strong>Mata Kuliah → CPMK</strong> dan tentukan bobot tiap teknik penilaian.
            </div>
        </div>
    </div>


    <div class="overflow-hidden tsu-card rounded-lg">
        <div class="overflow-x-auto">
            <table x-data="tableSort()" class="min-w-full divide-y divide-[#d7e7e9] text-sm">
                <thead class="bg-[#f7fbfb] text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3 cursor-pointer select-none hover:bg-slate-100" @click="sort(0)">No <span x-show="sortCol === 0" x-text="sortAsc ? '↑' : '↓'"></span></th>
                        <th class="px-4 py-3 cursor-pointer select-none hover:bg-slate-100" @click="sort(1)">Kode CPMK <span x-show="sortCol === 1" x-text="sortAsc ? '↑' : '↓'"></span></th>
                        <th class="px-4 py-3 cursor-pointer select-none hover:bg-slate-100" @click="sort(2)">Rumusan CPMK <span x-show="sortCol === 2" x-text="sortAsc ? '↑' : '↓'"></span></th>
                        <th class="px-4 py-3 cursor-pointer select-none hover:bg-slate-100" @click="sort(3)">CPL <span x-show="sortCol === 3" x-text="sortAsc ? '↑' : '↓'"></span></th>
                        <th class="px-4 py-3 cursor-pointer select-none hover:bg-slate-100" @click="sort(4)">Kurikulum Prodi <span x-show="sortCol === 4" x-text="sortAsc ? '↑' : '↓'"></span></th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody x-ref="tbody" class="divide-y divide-slate-100 bg-white">
                    @forelse($cpmks as $index => $cpmk)
                        <tr class="hover:bg-[#f7fbfb]">
                            <td class="px-4 py-3 text-slate-500">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 font-bold text-slate-900">{{ $cpmk['kode'] }}</td>
                            <td class="px-4 py-3 text-slate-700 max-w-xs truncate" title="{{ $cpmk['rumusan_cpmk'] }}">{{ $cpmk['rumusan_cpmk'] }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full border border-teal-200 bg-teal-50 px-2.5 py-1 text-xs font-semibold text-teal-700">
                                    {{ $cpmk['cpl']['kode'] ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600 text-xs">{{ $cpmk['kurikulum_prodi']['kode_kur_prodi'] ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <button type="button" @click='openEdit(@json($cpmk))'
                                            class="tsu-secondary-button rounded-lg px-3 py-1.5 text-xs font-semibold">
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('cpmks.destroy', $cpmk['id']) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" onclick="return confirm('Hapus CPMK ini? CPMK tidak dapat dihapus jika sudah dipetakan ke Mata Kuliah.')"
                                                class="rounded-lg bg-red-50 border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                                Belum ada data CPMK. Tambahkan CPMK terlebih dahulu.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Tambah --}}
    <div x-cloak x-show="createOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
        <div @click.away="createOpen = false" class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl bg-white shadow-2xl">
            <div class="border-b border-[#d7e7e9] px-6 py-4 bg-[#f7fbfb]">
                <h3 class="font-bold text-slate-900">Tambah CPMK</h3>
            </div>
            <form method="POST" action="{{ route('cpmks.store') }}" class="space-y-4 p-6">
                @csrf
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Kurikulum Prodi <span class="text-red-500">*</span></label>
                    <select name="kurikulum_prodi_id" required class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm">
                        <option value="">-- Pilih Kurikulum Prodi --</option>
                        @foreach($kurikulumProdis as $kp)
                            <option value="{{ $kp['id'] }}" @selected($selectedKurikulumProdiId == $kp['id'])>
                                {{ $kp['kode_kur_prodi'] }} — {{ $kp['prodi']['nama'] ?? '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">CPL Terkait <span class="text-red-500">*</span></label>
                    <select name="cpl_id" required class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm">
                        <option value="">-- Pilih CPL --</option>
                        @foreach($cpls as $cpl)
                            <option value="{{ $cpl['id'] }}">{{ $cpl['kode'] }} — {{ Str::limit($cpl['rumusan_cpl'], 60) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Kode CPMK <span class="text-red-500">*</span></label>
                    <input type="text" name="kode" required placeholder="Contoh: CPMK-1" class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm">
                    <p class="mt-1 text-xs text-slate-400">Harus unik per Kurikulum Prodi.</p>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Rumusan CPMK <span class="text-red-500">*</span></label>
                    <textarea name="rumusan_cpmk" required rows="3" placeholder="Rumusan kemampuan yang diukur..." class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="createOpen = false" class="tsu-secondary-button rounded-lg px-4 py-2 text-sm font-medium">Batal</button>
                    <button type="submit" class="tsu-primary-button rounded-lg px-4 py-2 text-sm font-semibold">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div x-cloak x-show="editOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
        <div @click.away="editOpen = false" class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl bg-white shadow-2xl">
            <div class="border-b border-[#d7e7e9] px-6 py-4 bg-[#f7fbfb]">
                <h3 class="font-bold text-slate-900">Edit CPMK — <span x-text="selected.kode" class="text-teal-700"></span></h3>
            </div>
            <form method="POST" :action="`/cpmks/${selected.id}`" class="space-y-4 p-6">
                @csrf @method('PUT')
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">CPL Terkait</label>
                    <select name="cpl_id" x-model="selected.cpl_id" required class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm">
                        <option value="">-- Pilih CPL --</option>
                        @foreach($cpls as $cpl)
                            <option value="{{ $cpl['id'] }}">{{ $cpl['kode'] }} — {{ Str::limit($cpl['rumusan_cpl'], 60) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Kode CPMK</label>
                    <input type="text" name="kode" x-model="selected.kode" required class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Rumusan CPMK</label>
                    <textarea name="rumusan_cpmk" x-model="selected.rumusan_cpmk" required rows="3" class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="editOpen = false" class="tsu-secondary-button rounded-lg px-4 py-2 text-sm font-medium">Batal</button>
                    <button type="submit" class="tsu-primary-button rounded-lg px-4 py-2 text-sm font-semibold">Perbarui</button>
                </div>
            </form>
        </div>
    </div>
    </div>

    @php
        $activeKpLabel = null;
        if (!empty($selectedKurikulumProdiId)) {
            foreach ($kurikulumProdis as $kp) {
                if ($kp['id'] == $selectedKurikulumProdiId) {
                    $activeKpLabel = ($kp['kode_kur_prodi'] ?? '') . ' — ' . ($kp['prodi']['nama'] ?? '');
                    break;
                }
            }
        }
    @endphp

    <x-modal-import 
        id="cpmk" 
        title="Import CPMK" 
        endpoint="{{ route('import.cpmk') }}" 
        templateUrl="{{ !empty($selectedKurikulumProdiId) ? route('import.template-cpmk', ['kurikulum_prodi_id' => $selectedKurikulumProdiId]) : route('import.template-cpmk') }}"
    >
        @if(!empty($selectedKurikulumProdiId))
            <input type="hidden" name="kurikulum_prodi_id" value="{{ $selectedKurikulumProdiId }}">
            <div class="rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 mb-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-blue-500 mb-0.5">Data akan diimport ke Kurikulum Prodi:</p>
                <p class="text-sm font-bold text-blue-900">{{ $activeKpLabel }}</p>
            </div>
        @else
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 mb-3">
                <p class="text-sm font-semibold text-amber-800">⚠ Pilih Kurikulum Prodi terlebih dahulu</p>
                <p class="text-xs text-amber-700 mt-1">Tutup modal ini, pilih Kurikulum Prodi pada filter di atas tabel, lalu klik tombol Import Excel kembali.</p>
            </div>
        @endif
    </x-modal-import>
</div>
@endsection
