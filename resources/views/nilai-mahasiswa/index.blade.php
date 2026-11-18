@extends('layouts.app')

@section('content')
@php $title = 'Input Nilai'; @endphp
<div x-data="{
    inputOpen: false,
    selectedMhsId: '',
    selectedAngkatan: '',
    nilaiInputs: {},
    allMahasiswas: @js($mahasiswas),

    get filteredMahasiswas() {
        if (!this.selectedAngkatan) return [];
        return this.allMahasiswas.filter(m => String(m.angkatan) === String(this.selectedAngkatan));
    },

    get availableAngkatans() {
        const angkatans = this.allMahasiswas.map(m => m.angkatan).filter(Boolean);
        return [...new Set(angkatans)].sort((a, b) => b.localeCompare(a));
    },

    openInput(mhsId, existingPerJenis) {
        this.selectedMhsId = mhsId;
        if (mhsId) {
            const mhs = this.allMahasiswas.find(m => m.id == mhsId);
            if (mhs) this.selectedAngkatan = mhs.angkatan;
        } else {
            this.selectedAngkatan = '';
        }

        this.nilaiInputs = {};

        // Inisialisasi semua slot nilai per jenis penilaian
        const jeniss = @js($jenisPenilaians);
        jeniss.forEach(jp => {
            this.nilaiInputs[jp.id] = '';
        });

        // Isi dengan data yang sudah ada (flat: jenis_id => nilai)
        if (existingPerJenis) {
            Object.keys(existingPerJenis).forEach(jenisId => {
                if (existingPerJenis[jenisId] !== null) {
                    this.nilaiInputs[jenisId] = existingPerJenis[jenisId];
                }
            });
        }

        this.inputOpen = true;
    }
}">

    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <div class="flex items-center gap-2 mb-2 text-sm">
                <a href="{{ route('input-nilai.select') }}" class="text-slate-500 hover:text-slate-800 font-medium">Input Nilai</a>
                <span class="text-slate-400">/</span>
                <span class="font-medium text-slate-800">{{ $mataKuliah['kode'] }} — {{ $mataKuliah['nama'] }}</span>
            </div>
            <h2 class="text-xl font-bold text-slate-900">{{ $mataKuliah['kode'] }} — {{ $mataKuliah['nama'] }}</h2>
            <p class="mt-1 text-sm text-slate-500">Input nilai mahasiswa per jenis penilaian (0–100)</p>
        </div>
        @if($bobotValid)
            <div class="flex items-center gap-2">
                <button type="button"
                        @click="$dispatch('open-import-modal-nilai-wide')"
                        class="tsu-secondary-button inline-flex items-center justify-center rounded-lg px-4 py-2.5 text-sm font-semibold">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Import Excel
                </button>
                <button type="button" @click="openInput('', null)"
                        class="tsu-primary-button rounded-lg px-4 py-2.5 text-sm font-semibold">
                    + Input Nilai Baru
                </button>
            </div>
        @endif
    </div>


    @if(!$bobotValid)
        <div class="rounded-lg bg-amber-50 border border-amber-200 p-5 text-sm text-amber-700">
            <p class="font-bold mb-1">⚠ Bobot Penilaian Belum Lengkap</p>
            <p>Anda perlu mengatur <strong>Bobot Jenis Penilaian</strong> dan <strong>Bobot CPMK per Jenis</strong> sebelum bisa menginput nilai.</p>
            <a href="{{ route('mk-cpmk.index', $mataKuliahId) }}" class="mt-3 inline-block tsu-primary-button rounded-lg px-4 py-2 text-sm font-semibold">
                Atur Bobot →
            </a>
        </div>
    @else
        {{-- Filter Angkatan --}}
        <section class="mb-5 tsu-card rounded-lg p-4">
            <div class="flex flex-wrap gap-4 items-end justify-between">
                <form method="GET" action="{{ route('input-nilai.index', $mataKuliahId) }}" class="flex gap-4 items-end flex-1">
                    <div class="flex-1">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Filter Angkatan</label>
                        <select name="angkatan" class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2.5 text-sm" onchange="this.form.submit()">
                            <option value="">-- Semua Angkatan --</option>
                            @foreach($angkatanList as $akt)
                                <option value="{{ $akt }}" @selected($angkatan == $akt)>{{ $akt }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
                @if($angkatan)
                <a href="{{ route('report.nilai-mahasiswa', [$mataKuliahId, 'angkatan' => $angkatan]) }}"
                   target="_blank"
                   class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700 transition-colors whitespace-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    Download PDF Angkatan {{ $angkatan }}
                </a>
                @endif
            </div>
        </section>

        {{-- Tabel Grid: baris = mahasiswa, kolom = jenis penilaian --}}
        <div class="overflow-hidden tsu-card rounded-lg mb-8">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#d7e7e9] text-xs">
                    <thead class="bg-[#f7fbfb] text-center font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3 border-r border-[#d7e7e9] text-left align-middle text-sm">Mahasiswa</th>
                            @foreach($jenisPenilaians as $jp)
                                <th class="px-3 py-3 border-r border-[#d7e7e9] text-[11px]">{{ $jp['nama'] }}</th>
                            @endforeach
                            <th class="px-3 py-3 border-r border-[#d7e7e9] align-middle text-teal-700 font-bold text-sm">Nilai MK</th>
                            <th class="px-3 py-3 align-middle text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($grid as $row)
                            <tr class="hover:bg-[#f7fbfb] text-center">
                                <td class="px-4 py-3 border-r border-[#d7e7e9] text-left">
                                    <div class="font-bold text-slate-900 text-sm">{{ $row['mahasiswa']['nama'] }}</div>
                                    <div class="text-xs text-slate-400">{{ $row['mahasiswa']['kode'] }}</div>
                                </td>
                                @foreach($jenisPenilaians as $jp)
                                    @php $nv = $row['nilai_per_jenis'][$jp['id']] ?? null; @endphp
                                    <td class="px-3 py-3 border-r border-[#d7e7e9] {{ $nv !== null ? 'text-slate-800 font-medium' : 'text-slate-300' }}">
                                        {{ $nv ?? '-' }}
                                    </td>
                                @endforeach
                                <td class="px-3 py-3 border-r border-[#d7e7e9] font-bold text-base text-teal-700 bg-teal-50/40">
                                    {{ $row['nilai_mk'] > 0 ? $row['nilai_mk'] : '-' }}
                                </td>
                                <td class="px-3 py-3 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        <button type="button"
                                                @click='openInput("{{ $row["mahasiswa"]["id"] }}", @json($row["nilai_per_jenis"]))'
                                                class="rounded bg-indigo-50 border border-indigo-200 px-2 py-1 text-[11px] font-semibold text-indigo-700 hover:bg-indigo-100">
                                            Edit
                                        </button>
                                        <form method="POST" action="{{ route('input-nilai.destroy', [$mataKuliahId, $row['mahasiswa']['id']]) }}" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" onclick="return confirm('Hapus semua nilai mahasiswa ini dari MK ini?')"
                                                    class="rounded bg-red-50 border border-red-200 px-2 py-1 text-[11px] font-semibold text-red-700 hover:bg-red-100">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($jenisPenilaians) + 3 }}" class="px-4 py-8 text-center text-slate-500">
                                    Belum ada data nilai. Klik <strong>"Input Nilai Baru"</strong>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ===== Modal Input Nilai ===== --}}
    <div x-cloak x-show="inputOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
        <div @click.away="inputOpen = false" class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl bg-white shadow-2xl">
            <div class="border-b border-[#d7e7e9] px-6 py-4 bg-[#f7fbfb] sticky top-0 z-10 flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-slate-900 text-lg">Input Nilai Mahasiswa</h3>
                    <p class="text-xs text-slate-500">{{ $mataKuliah['kode'] }} — {{ $mataKuliah['nama'] }}</p>
                </div>
                <button type="button" @click="inputOpen = false" class="text-slate-400 hover:text-slate-700 text-xl">✕</button>
            </div>

            <form method="POST" action="{{ route('input-nilai.store', $mataKuliahId) }}" class="p-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Angkatan <span class="text-red-500">*</span></label>
                        <select name="angkatan" required class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm"
                                x-model="selectedAngkatan" @change="selectedMhsId = ''">
                            <option value="">-- Pilih Angkatan --</option>
                            <template x-for="akt in availableAngkatans" :key="akt">
                                <option :value="akt" x-text="akt"></option>
                            </template>
                        </select>
                    </div>
                    <div x-data="{ search: '', open: false }" class="relative" @click.away="open = false">
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Mahasiswa <span class="text-red-500">*</span></label>
                        
                        <!-- Dropdown trigger -->
                        <div @click="if(selectedAngkatan) { open = !open; if(open) $nextTick(() => $refs.searchInput.focus()) }" 
                             class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm bg-white flex justify-between items-center"
                             :class="!selectedAngkatan ? 'opacity-50 cursor-not-allowed bg-slate-50' : 'cursor-pointer hover:border-slate-400'">
                            <span class="truncate" x-text="selectedMhsId ? (filteredMahasiswas.find(m => m.id == selectedMhsId)?.kode + ' — ' + filteredMahasiswas.find(m => m.id == selectedMhsId)?.nama) : '-- Pilih Mahasiswa --'"></span>
                            <svg class="h-4 w-4 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                        
                        <!-- Search & Options menu -->
                        <div x-cloak x-show="open" 
                             class="absolute z-50 w-full mt-1 bg-white rounded-lg shadow-xl border border-slate-200 overflow-hidden" 
                             x-transition>
                            <div class="p-2 border-b border-slate-100 bg-slate-50">
                                <input x-ref="searchInput" x-model="search" type="text" 
                                       class="w-full rounded border border-slate-300 px-3 py-1.5 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-400" 
                                       placeholder="Cari NIM atau Nama..." @click.stop @keydown.escape="open = false">
                            </div>
                            <ul class="max-h-56 overflow-y-auto p-1">
                                <template x-for="mhs in filteredMahasiswas.filter(m => (m.kode + ' ' + m.nama).toLowerCase().includes(search.toLowerCase()))" :key="mhs.id">
                                    <li @click="selectedMhsId = mhs.id; open = false; search = ''" 
                                        class="px-3 py-2 text-sm hover:bg-teal-50 cursor-pointer rounded mb-0.5 transition-colors"
                                        :class="selectedMhsId == mhs.id ? 'bg-teal-50 text-teal-700 font-medium' : 'text-slate-700'"
                                        x-text="mhs.kode + ' — ' + mhs.nama"></li>
                                </template>
                                <li x-show="filteredMahasiswas.filter(m => (m.kode + ' ' + m.nama).toLowerCase().includes(search.toLowerCase())).length === 0" 
                                    class="px-3 py-4 text-sm text-center text-slate-500">
                                    Mahasiswa tidak ditemukan
                                </li>
                            </ul>
                        </div>
                        
                        <!-- Native select hidden for form submission and HTML5 validation -->
                        <select name="mahasiswa_id" required x-model="selectedMhsId" class="absolute bottom-0 left-1/2 w-1 h-1 opacity-0 pointer-events-none -z-10" tabindex="-1">
                            <option value="">-- Pilih Mahasiswa --</option>
                            <template x-for="mhs in filteredMahasiswas" :key="mhs.id">
                                <option :value="mhs.id" x-text="mhs.kode"></option>
                            </template>
                        </select>
                    </div>
                </div>

                {{-- Input nilai per jenis penilaian --}}
                <div class="rounded-lg border border-[#d7e7e9] overflow-hidden mb-6">
                    <div class="bg-slate-50 px-4 py-3 border-b border-[#d7e7e9]">
                        <p class="text-sm font-semibold text-slate-700">Nilai per Jenis Penilaian (0 – 100)</p>
                        <p class="text-xs text-slate-400 mt-0.5">Nilai CPMK dan Nilai MK akan dihitung otomatis dari bobot yang sudah diatur</p>
                    </div>
                    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($jenisPenilaians as $jp)
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">{{ $jp['nama'] }}</label>
                                <input type="number" step="any" min="0" max="100"
                                       name="nilai[{{ $jp['id'] }}]"
                                       x-model="nilaiInputs['{{ $jp['id'] }}']"
                                       class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-400"
                                       placeholder="0 – 100">
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-[#d7e7e9] pt-5">
                    <button type="button" @click="inputOpen = false" class="tsu-secondary-button rounded-lg px-6 py-2.5 text-sm font-medium">Batal</button>
                    <button type="submit" class="tsu-primary-button rounded-lg px-6 py-2.5 text-sm font-semibold">Simpan Nilai</button>
                </div>
            </form>
        </div>
    </div>
    
    <x-modal-import 
        id="nilai-wide" 
        title="Import Nilai Mahasiswa" 
        endpoint="{{ route('import.wide') }}" 
        templateUrl="{{ route('import.template-wide', ['mata_kuliah_id' => $mataKuliahId]) }}"
    >
        <input type="hidden" name="mata_kuliah_id" value="{{ $mataKuliahId }}">
        
        <div class="mt-2">
            <label class="block text-sm font-medium text-slate-700 mb-2">Pilih Angkatan</label>
            <select name="angkatan" class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2.5 text-sm bg-white focus:border-[#0e7a86] focus:ring-2 focus:ring-[#e8f6f7]" required>
                @foreach($angkatanList as $akt)
                    <option value="{{ $akt }}" @selected($angkatan == $akt)>{{ $akt }}</option>
                @endforeach
                @if(empty($angkatanList))
                    <!-- Fallback jika belum ada mahasiswa -->
                    <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                    <option value="{{ date('Y') - 1 }}">{{ date('Y') - 1 }}</option>
                @endif
            </select>
        </div>
    </x-modal-import>
</div>
@endsection
