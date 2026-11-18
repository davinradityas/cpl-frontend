@extends('layouts.app')

@section('content')
@php $title = 'Laporan & Cetak'; @endphp
<div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Pusat Laporan & Cetak PDF</h2>
        <p class="mt-1 text-sm text-slate-500">Pilih jenis laporan yang ingin Anda unduh, lengkapi parameternya, lalu klik Download PDF.</p>
    </div>
</div>

<div x-data="reportPortal()" class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Laporan 1: Nilai CPL per Angkatan --}}
    <div class="tsu-card rounded-lg p-5 flex flex-col h-full">
        <div class="flex items-start gap-3 mb-4">
            <div class="rounded-lg bg-teal-50 p-2 text-teal-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            </div>
            <div>
                <h3 class="font-bold text-slate-800">1. Nilai CPL per Angkatan/Prodi</h3>
                <p class="text-xs text-slate-500">Rekapitulasi pencapaian CPL seluruh mata kuliah dalam satu angkatan.</p>
            </div>
        </div>
        <div class="flex-1 space-y-4">
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-500 mb-1.5">Kurikulum Prodi</label>
                <select x-model="formCpl.kurikulum_prodi_id" class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none">
                    <option value="">-- Pilih Kurikulum Prodi --</option>
                    @foreach($kurikulumProdis as $kp)
                    <option value="{{ $kp['id'] }}">{{ $kp['kode_kur_prodi'] }} - {{ $kp['prodi']['nama'] ?? '' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-500 mb-1.5">Angkatan</label>
                <select x-model="formCpl.angkatan" class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none">
                    <option value="">-- Pilih Angkatan --</option>
                    @foreach($angkatanList as $akt)
                    <option value="{{ $akt }}">{{ $akt }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-6 pt-4 border-t border-slate-100 flex justify-end">
            <button type="button" @click="downloadCpl()" :disabled="!formCpl.kurikulum_prodi_id || !formCpl.angkatan"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                Download PDF
            </button>
        </div>
    </div>

    {{-- Laporan 2: Detail Nilai per Mahasiswa --}}
    <div class="tsu-card rounded-lg p-5 flex flex-col h-full">
        <div class="flex items-start gap-3 mb-4">
            <div class="rounded-lg bg-indigo-50 p-2 text-indigo-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
            </div>
            <div>
                <h3 class="font-bold text-slate-800">2. Detail Nilai per Mahasiswa</h3>
                <p class="text-xs text-slate-500">Transkrip nilai CPL/KHS mahasiswa lengkap dengan semua mata kuliah yang diambil.</p>
            </div>
        </div>
        <div class="flex-1 space-y-4 relative">
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-500 mb-1.5">Pilih Mahasiswa</label>
                <div class="relative">
                    <input type="text" x-model="mhsSearch" @focus="mhsDropdown = true" @click.away="mhsDropdown = false" placeholder="Cari NIM atau Nama..."
                           class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none">
                    
                    <div x-show="mhsDropdown" x-cloak class="absolute z-10 w-full mt-1 bg-white border border-slate-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                        <template x-for="m in filteredMahasiswas" :key="m.id">
                            <div @click="selectMahasiswa(m)" class="px-3 py-2 cursor-pointer hover:bg-slate-50 text-sm border-b border-slate-50 last:border-0">
                                <div class="font-semibold text-slate-700" x-text="m.kode"></div>
                                <div class="text-xs text-slate-500" x-text="m.nama + ' (Angkatan: ' + m.angkatan + ')'"></div>
                            </div>
                        </template>
                        <div x-show="filteredMahasiswas.length === 0" class="px-3 py-4 text-center text-xs text-slate-400">Mahasiswa tidak ditemukan</div>
                    </div>
                </div>
                <div x-show="formMhs.mahasiswa_id" class="mt-2 text-sm font-medium text-teal-700 flex items-center justify-between bg-teal-50 px-3 py-2 rounded">
                    <span>Terpilih: <span x-text="formMhs.kode + ' - ' + formMhs.nama"></span></span>
                    <button type="button" @click="formMhs.mahasiswa_id = ''; mhsSearch = ''" class="text-red-500 hover:text-red-700 text-xs">Batal</button>
                </div>
            </div>
        </div>
        <div class="mt-6 pt-4 border-t border-slate-100 flex justify-end">
            <button type="button" @click="downloadMhs()" :disabled="!formMhs.mahasiswa_id"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                Download PDF
            </button>
        </div>
    </div>

    {{-- Laporan 3: Nilai Mahasiswa per MK --}}
    <div class="tsu-card rounded-lg p-5 flex flex-col h-full">
        <div class="flex items-start gap-3 mb-4">
            <div class="rounded-lg bg-orange-50 p-2 text-orange-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
            </div>
            <div>
                <h3 class="font-bold text-slate-800">3. Nilai Mahasiswa per Mata Kuliah</h3>
                <p class="text-xs text-slate-500">Daftar nilai mahasiswa pada mata kuliah tertentu per komponen penilaian.</p>
            </div>
        </div>
        <div class="flex-1 space-y-4">
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-500 mb-1.5">Mata Kuliah</label>
                <div class="relative">
                    <input type="text" x-model="mkSearch3" @focus="mkDropdown3 = true" @click.away="mkDropdown3 = false" placeholder="Cari Kode atau Nama MK..."
                           class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none">
                    
                    <div x-show="mkDropdown3" x-cloak class="absolute z-10 w-full mt-1 bg-white border border-slate-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                        <template x-for="m in filteredMk3" :key="m.id">
                            <div @click="selectMk3(m)" class="px-3 py-2 cursor-pointer hover:bg-slate-50 text-sm border-b border-slate-50 last:border-0">
                                <div class="font-semibold text-slate-700" x-text="m.kode"></div>
                                <div class="text-xs text-slate-500" x-text="m.nama"></div>
                            </div>
                        </template>
                        <div x-show="filteredMk3.length === 0" class="px-3 py-4 text-center text-xs text-slate-400">Mata Kuliah tidak ditemukan</div>
                    </div>
                </div>
                <div x-show="formNilaiMk.mata_kuliah_id" class="mt-2 text-sm font-medium text-orange-700 flex items-center justify-between bg-orange-50 px-3 py-2 rounded">
                    <span>Terpilih: <span x-text="formNilaiMk.kode_nama"></span></span>
                    <button type="button" @click="formNilaiMk.mata_kuliah_id = ''; mkSearch3 = ''" class="text-red-500 hover:text-red-700 text-xs">Batal</button>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-500 mb-1.5">Angkatan</label>
                <select x-model="formNilaiMk.angkatan" class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none">
                    <option value="">-- Pilih Angkatan --</option>
                    @foreach($angkatanList as $akt)
                    <option value="{{ $akt }}">{{ $akt }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-6 pt-4 border-t border-slate-100 flex justify-end">
            <button type="button" @click="downloadNilaiMk()" :disabled="!formNilaiMk.mata_kuliah_id || !formNilaiMk.angkatan"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                Download PDF
            </button>
        </div>
    </div>

    {{-- Laporan 4: Matriks Bobot CPMK --}}
    <div class="tsu-card rounded-lg p-5 flex flex-col h-full">
        <div class="flex items-start gap-3 mb-4">
            <div class="rounded-lg bg-blue-50 p-2 text-blue-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
            </div>
            <div>
                <h3 class="font-bold text-slate-800">4. Matriks Bobot CPMK</h3>
                <p class="text-xs text-slate-500">Matriks relasi dan bobot antara CPMK dengan berbagai teknik penilaian pada sebuah Mata Kuliah.</p>
            </div>
        </div>
        <div class="flex-1 space-y-4">
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-500 mb-1.5">Mata Kuliah</label>
                <div class="relative">
                    <input type="text" x-model="mkSearch4" @focus="mkDropdown4 = true" @click.away="mkDropdown4 = false" placeholder="Cari Kode atau Nama MK..."
                           class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none">
                    
                    <div x-show="mkDropdown4" x-cloak class="absolute z-10 w-full mt-1 bg-white border border-slate-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                        <template x-for="m in filteredMk4" :key="m.id">
                            <div @click="selectMk4(m)" class="px-3 py-2 cursor-pointer hover:bg-slate-50 text-sm border-b border-slate-50 last:border-0">
                                <div class="font-semibold text-slate-700" x-text="m.kode"></div>
                                <div class="text-xs text-slate-500" x-text="m.nama"></div>
                            </div>
                        </template>
                        <div x-show="filteredMk4.length === 0" class="px-3 py-4 text-center text-xs text-slate-400">Mata Kuliah tidak ditemukan</div>
                    </div>
                </div>
                <div x-show="formMatriks.mata_kuliah_id" class="mt-2 text-sm font-medium text-blue-700 flex items-center justify-between bg-blue-50 px-3 py-2 rounded">
                    <span>Terpilih: <span x-text="formMatriks.kode_nama"></span></span>
                    <button type="button" @click="formMatriks.mata_kuliah_id = ''; mkSearch4 = ''" class="text-red-500 hover:text-red-700 text-xs">Batal</button>
                </div>
            </div>
        </div>
        <div class="mt-6 pt-4 border-t border-slate-100 flex justify-end">
            <button type="button" @click="downloadMatriks()" :disabled="!formMatriks.mata_kuliah_id"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                Download PDF
            </button>
        </div>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    window.reportPortal = function() {
        return {
            formCpl: { kurikulum_prodi_id: '', angkatan: '' },
            
            mahasiswas: @json($mahasiswas),
            mhsSearch: '',
            mhsDropdown: false,
            formMhs: { mahasiswa_id: '', kode: '', nama: '' },
            get filteredMahasiswas() {
                if(this.mhsSearch === '') return this.mahasiswas.slice(0, 100);
                return this.mahasiswas.filter(m => 
                    (m.nama || '').toLowerCase().includes(this.mhsSearch.toLowerCase()) || 
                    (m.kode || '').toLowerCase().includes(this.mhsSearch.toLowerCase())
                ).slice(0, 50);
            },
            selectMahasiswa(m) {
                this.formMhs.mahasiswa_id = m.id;
                this.formMhs.kode = m.kode;
                this.formMhs.nama = m.nama;
                this.mhsDropdown = false;
                this.mhsSearch = '';
            },

            mkList: @json($mataKuliahs),
            
            // Untuk form 3
            mkSearch3: '',
            mkDropdown3: false,
            formNilaiMk: { mata_kuliah_id: '', angkatan: '', kode_nama: '' },
            get filteredMk3() {
                if(this.mkSearch3 === '') return this.mkList;
                return this.mkList.filter(m => 
                    (m.nama || '').toLowerCase().includes(this.mkSearch3.toLowerCase()) || 
                    (m.kode || '').toLowerCase().includes(this.mkSearch3.toLowerCase())
                );
            },
            selectMk3(m) {
                this.formNilaiMk.mata_kuliah_id = m.id;
                this.formNilaiMk.kode_nama = m.kode + ' - ' + m.nama;
                this.mkDropdown3 = false;
                this.mkSearch3 = '';
            },

            // Untuk form 4
            mkSearch4: '',
            mkDropdown4: false,
            formMatriks: { mata_kuliah_id: '', kode_nama: '' },
            get filteredMk4() {
                if(this.mkSearch4 === '') return this.mkList;
                return this.mkList.filter(m => 
                    (m.nama || '').toLowerCase().includes(this.mkSearch4.toLowerCase()) || 
                    (m.kode || '').toLowerCase().includes(this.mkSearch4.toLowerCase())
                );
            },
            selectMk4(m) {
                this.formMatriks.mata_kuliah_id = m.id;
                this.formMatriks.kode_nama = m.kode + ' - ' + m.nama;
                this.mkDropdown4 = false;
                this.mkSearch4 = '';
            },

            downloadCpl() {
                const url = `{{ url('/report/nilai-cpl') }}?kurikulum_prodi_id=${this.formCpl.kurikulum_prodi_id}&angkatan=${this.formCpl.angkatan}`;
                window.open(url, '_blank');
            },
            downloadMhs() {
                const url = `{{ url('/report/detail-mahasiswa') }}/${this.formMhs.mahasiswa_id}`;
                window.open(url, '_blank');
            },
            downloadNilaiMk() {
                const url = `{{ url('/report/nilai-mahasiswa') }}/${this.formNilaiMk.mata_kuliah_id}?angkatan=${this.formNilaiMk.angkatan}`;
                window.open(url, '_blank');
            },
            downloadMatriks() {
                const url = `{{ url('/report/matriks-bobot') }}/${this.formMatriks.mata_kuliah_id}`;
                window.open(url, '_blank');
            }
        };
    };
});
</script>
@endpush
@endsection
