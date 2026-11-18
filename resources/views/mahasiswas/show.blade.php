@extends('layouts.app')

@section('content')
@php
    $title = 'Detail Mahasiswa — ' . ($mahasiswa['nama'] ?? 'Tanpa Nama');

    $cpls       = $nilaiCplData['cpls']    ?? [];
    $rataRataTs = $nilaiCplData['rata_rata_ts'] ?? 0;
    $rataRataKs = $nilaiCplData['rata_rata_ks'] ?? 0;
    $nilaiList  = $mahasiswa['nilai_mhs_mk'] ?? [];

    $semanticClass = function ($color) {
        return match ($color) {
            'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'warning' => 'bg-amber-50 text-amber-700 border-amber-200',
            'danger'  => 'bg-rose-50 text-rose-700 border-rose-200',
            'info'    => 'bg-blue-50 text-blue-700 border-blue-200',
            default   => 'bg-slate-50 text-slate-500 border-slate-200',
        };
    };
    $semanticIcon = function ($color) {
        return match ($color) {
            'success' => '✅',
            'warning' => '⚠️',
            'danger'  => '❌',
            default   => '—',
        };
    };

    // Data untuk chart
    $chartLabels   = collect($cpls)->pluck('kode')->values()->toArray();
    $chartValuesTs = collect($cpls)->map(fn($c) => (float)($c['nilai_cpl'] ?? 0))->values()->toArray();
    $chartValuesKs = collect($cpls)->map(fn($c) => (float)($c['nilai_cpl_keseluruhan'] ?? 0))->values()->toArray();
    $chartTarget   = (float) ($nilaiCplData['target'] ?? 75);
    $hasCplData    = count($chartLabels) > 0;

    // Jumlah CPL tercapai (status bukan slate)
    $jumlahTercapaiTs = collect($cpls)->filter(fn($c) => ($c['status_color'] ?? 'slate') !== 'slate' && ($c['status_color'] ?? 'slate') !== 'danger' && ($c['nilai_cpl'] ?? 0) > 0)->count();
    $jumlahTercapaiKs = collect($cpls)->filter(fn($c) => ($c['status_keseluruhan_color'] ?? 'slate') !== 'slate' && ($c['status_keseluruhan_color'] ?? 'slate') !== 'danger' && ($c['nilai_cpl_keseluruhan'] ?? 0) > 0)->count();
@endphp

{{-- ===== HEADER ===== --}}
<div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div>
        <div class="flex items-center gap-2 mb-2 text-sm">
            <a href="{{ route('mahasiswas.index') }}" class="text-slate-500 hover:text-slate-800 font-medium">Data Mahasiswa</a>
            <span class="text-slate-400">/</span>
            <span class="font-medium text-slate-800">Detail Nilai CPL</span>
        </div>
        <h2 class="text-xl font-bold text-slate-900">{{ $mahasiswa['kode'] }} — {{ $mahasiswa['nama'] ?? '-' }}</h2>
        <p class="mt-1 text-sm text-slate-500">
            Angkatan {{ $mahasiswa['angkatan'] }}
            &nbsp;·&nbsp; Prodi {{ $mahasiswa['kurikulum_prodi']['prodi']['nama'] ?? ($mahasiswa['prodi']['nama'] ?? '-') }}
            &nbsp;·&nbsp; {{ $mahasiswa['kurikulum_prodi']['prodi']['fakultas']['nama'] ?? ($mahasiswa['prodi']['fakultas']['nama'] ?? '-') }}
        </p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('report.detail-mahasiswa', $mahasiswa['id']) }}"
           target="_blank"
           class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Download PDF
        </a>
        <a href="{{ route('mahasiswas.index') }}" class="tsu-secondary-button rounded-lg px-4 py-2.5 text-sm font-semibold">
            Kembali
        </a>
    </div>
</div>

{{-- ===== BAGIAN CPL ===== --}}
@if(empty($cpls))
    <div class="tsu-card rounded-lg p-10 text-center mb-6">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-amber-50 text-2xl">📊</div>
        <h3 class="text-base font-semibold text-slate-800">Belum Ada Data Nilai CPL</h3>
        <p class="mt-2 text-sm text-slate-500 max-w-sm mx-auto">
            Mahasiswa ini belum memiliki nilai mata kuliah yang tercatat, atau belum ada CPMK & bobot teknik yang dikonfigurasi.
        </p>
    </div>
@else

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 gap-4 md:grid-cols-4 mb-6">
        <div class="tsu-card rounded-lg p-5">
            <div class="text-sm text-slate-500">Total CPL</div>
            <div class="mt-2 text-3xl font-bold text-slate-900">{{ count($cpls) }}</div>
        </div>
        <div class="tsu-card rounded-lg p-5">
            <div class="text-sm text-slate-500">MK Diambil</div>
            <div class="mt-2 text-3xl font-bold text-slate-900">{{ count($nilaiList) }}</div>
        </div>
        <div class="tsu-card rounded-lg p-5">
            <div class="text-sm text-slate-500">Rata-rata CPL (Terselenggara)</div>
            <div class="mt-2 text-3xl font-bold text-[#0e7a86]">{{ number_format($rataRataTs, 2) }}</div>
        </div>
        <div class="tsu-card rounded-lg p-5">
            <div class="text-sm text-slate-500">Rata-rata CPL (Keseluruhan)</div>
            <div class="mt-2 text-3xl font-bold text-violet-600">{{ number_format($rataRataKs, 2) }}</div>
        </div>
    </div>

    {{-- Spider Charts --}}
    @if($hasCplData)
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2 mb-6">
        {{-- Chart 1: MK Terselenggara --}}
        <section class="tsu-card rounded-lg p-5">
            <div class="mb-4 flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                <div>
                    <h3 class="font-semibold text-slate-900">Spider Graph CPL — MK Terselenggara</h3>
                    <p class="mt-1 text-xs text-slate-400">Dihitung dari MK yang sudah ada nilainya (MK belum bernilai diabaikan)</p>
                    <p class="mt-0.5 text-sm text-slate-500">{{ $mahasiswa['kode'] }} · {{ $mahasiswa['nama'] ?? '-' }}</p>
                </div>
                <div class="rounded-lg bg-[#e8f6f7] px-3 py-2 text-sm text-[#075866] whitespace-nowrap shrink-0">
                    Rata-rata {{ number_format($rataRataTs, 2) }}
                </div>
            </div>
            <div class="relative h-80">
                <canvas id="cplChartTs"></canvas>
            </div>
        </section>

        {{-- Chart 2: MK Keseluruhan --}}
        <section class="tsu-card rounded-lg p-5">
            <div class="mb-4 flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                <div>
                    <h3 class="font-semibold text-slate-900">Spider Graph CPL — MK Keseluruhan</h3>
                    <p class="mt-1 text-xs text-slate-400">Dihitung dari semua MK kurikulum (MK belum bernilai dihitung 0)</p>
                    <p class="mt-0.5 text-sm text-slate-500">{{ $mahasiswa['kode'] }} · {{ $mahasiswa['nama'] ?? '-' }}</p>
                </div>
                <div class="rounded-lg bg-violet-50 border border-violet-200 px-3 py-2 text-sm text-violet-700 whitespace-nowrap shrink-0">
                    Rata-rata {{ number_format($rataRataKs, 2) }}
                </div>
            </div>
            <div class="relative h-80">
                <canvas id="cplChartKs"></canvas>
            </div>
        </section>
    </div>
    @endif

    {{-- Tabel Ringkasan CPL --}}
    <section class="tsu-card rounded-lg mb-6">
        <div class="border-b border-[#d7e7e9] px-5 py-4">
            <h3 class="font-semibold text-slate-900">Ringkasan Nilai CPL</h3>
            <p class="mt-1 text-xs text-slate-500">Perbandingan nilai berdasarkan MK yang terselenggara vs seluruh MK kurikulum</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[#d7e7e9] text-sm">
                <thead class="bg-[#f7fbfb] text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th rowspan="2" class="px-5 py-3 border-b border-[#d7e7e9] text-left align-middle">Kode CPL</th>
                        <th rowspan="2" class="px-5 py-3 border-b border-[#d7e7e9] text-left align-middle">Rumusan CPL</th>
                        <th colspan="2" class="px-5 py-3 border-b border-[#d7e7e9] text-center">Nilai CPL</th>
                        <th colspan="2" class="px-5 py-3 border-b border-[#d7e7e9] text-center">Status</th>
                    </tr>
                    <tr>
                        <th class="px-5 py-2 border-b border-[#d7e7e9] text-center">MK Terselenggara</th>
                        <th class="px-5 py-2 border-b border-[#d7e7e9] text-center">MK Keseluruhan</th>
                        <th class="px-5 py-2 border-b border-[#d7e7e9] text-center">MK Terselenggara</th>
                        <th class="px-5 py-2 border-b border-[#d7e7e9] text-center">MK Keseluruhan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($cpls as $cpl)
                        <tr class="hover:bg-[#f7fbfb] transition-colors">
                            <td class="px-5 py-3 font-semibold text-slate-900">{{ $cpl['kode'] ?? '-' }}</td>
                            <td class="px-5 py-3 text-slate-600 max-w-xs">{{ Str::limit($cpl['rumusan_cpl'] ?? '-', 80) }}</td>
                            <td class="px-5 py-3 text-center font-mono font-semibold text-[#0e7a86]">
                                {{ number_format((float)($cpl['nilai_cpl'] ?? 0), 2) }}
                            </td>
                            <td class="px-5 py-3 text-center font-mono font-semibold text-violet-600">
                                {{ number_format((float)($cpl['nilai_cpl_keseluruhan'] ?? 0), 2) }}
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-medium {{ $semanticClass($cpl['status_color'] ?? 'slate') }}">
                                    {{ $semanticIcon($cpl['status_color'] ?? 'slate') }}
                                    {{ $cpl['status'] ?? 'Belum Ada Data' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-medium {{ $semanticClass($cpl['status_keseluruhan_color'] ?? 'slate') }}">
                                    {{ $semanticIcon($cpl['status_keseluruhan_color'] ?? 'slate') }}
                                    {{ $cpl['status_keseluruhan'] ?? 'Belum Ada Data' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-[#f7fbfb] text-sm font-semibold text-slate-700">
                    <tr>
                        <td colspan="2" class="px-5 py-3">Rata-rata</td>
                        <td class="px-5 py-3 text-center font-mono text-[#0e7a86]">{{ number_format($rataRataTs, 2) }}</td>
                        <td class="px-5 py-3 text-center font-mono text-violet-600">{{ number_format($rataRataKs, 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </section>

    {{-- Breakdown per CPL (accordion) --}}
    <div class="mb-6 space-y-3" x-data="{ openCpl: null }">
        <h3 class="font-semibold text-slate-800 mb-3">Rincian Kontribusi MK per CPL</h3>

        @foreach($cpls as $i => $cpl)
            @php $breakdown = $cpl['breakdown'] ?? []; @endphp
            <section class="overflow-hidden tsu-card rounded-lg">
                {{-- Accordion Header --}}
                <button type="button"
                        @click="openCpl = (openCpl === {{ $i }}) ? null : {{ $i }}"
                        class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-[#f7fbfb] transition-colors">
                    <div class="flex items-center gap-3">
                        <span class="rounded bg-[#0e7a86] px-2 py-1 text-xs font-bold text-white shrink-0">{{ $cpl['kode'] ?? '-' }}</span>
                        <span class="text-sm text-slate-600 line-clamp-1">{{ $cpl['rumusan_cpl'] ?? '-' }}</span>
                    </div>
                    <div class="flex items-center gap-3 shrink-0 ml-4">
                        <span class="text-xs text-slate-400">{{ count($breakdown) }} MK</span>
                        <div class="flex gap-2">
                            <span class="rounded-lg bg-[#e8f6f7] px-2.5 py-1 text-xs font-semibold text-[#075866]">
                                TS: {{ number_format((float)($cpl['nilai_cpl'] ?? 0), 2) }}
                            </span>
                            <span class="rounded-lg bg-violet-50 border border-violet-200 px-2.5 py-1 text-xs font-semibold text-violet-700">
                                KS: {{ number_format((float)($cpl['nilai_cpl_keseluruhan'] ?? 0), 2) }}
                            </span>
                        </div>
                        <svg class="h-4 w-4 text-slate-400 transition-transform duration-200"
                             :class="openCpl === {{ $i }} ? 'rotate-180' : ''"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </button>

                {{-- Accordion Body --}}
                <div x-show="openCpl === {{ $i }}"
                     x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="border-t border-[#d7e7e9] overflow-x-auto">
                        <table class="min-w-full divide-y divide-[#d7e7e9] text-sm">
                            <thead class="bg-[#f7fbfb] text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-4 py-3 text-left">Kode MK</th>
                                    <th class="px-4 py-3 text-left">Nama Mata Kuliah</th>
                                    <th class="px-4 py-3 text-center">SKS</th>
                                    <th class="px-4 py-3 text-center">Bobot MK-CPL</th>
                                    <th class="px-4 py-3 text-center">Nilai MK (Mhs)</th>
                                    <th class="px-4 py-3 text-center">Kontribusi ke CPL</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($breakdown as $row)
                                    @php
                                        $aktif       = $row['terselenggara'] ?? false;
                                        $sks         = (int)($row['sks'] ?? 0);
                                        $bobot       = (float)($row['bobot'] ?? 0);
                                        $nilaiMkMhs  = (float)($row['nilai_mk_mhs'] ?? 0);
                                        $kontribusi  = round($bobot * $nilaiMkMhs, 4);
                                    @endphp
                                    <tr class="hover:bg-[#f7fbfb] {{ !$aktif ? 'opacity-55' : '' }}">
                                        <td class="px-4 py-3 font-medium text-slate-900">{{ $row['kode_mk'] ?? '-' }}</td>
                                        <td class="px-4 py-3 text-slate-700">{{ $row['nama_mk'] ?? '-' }}</td>
                                        <td class="px-4 py-3 text-center text-slate-700">{{ $sks }}</td>
                                        <td class="px-4 py-3 text-center text-slate-700">
                                            {{ round($bobot * 100, 1) }}%
                                        </td>
                                        <td class="px-4 py-3 text-center {{ $aktif ? 'font-semibold text-slate-800' : 'text-slate-400' }}">
                                            {{ $aktif ? number_format($nilaiMkMhs, 2) : '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-center {{ $aktif ? 'font-semibold text-[#0e7a86]' : 'text-slate-400' }}">
                                            {{ $aktif ? number_format($kontribusi, 2) : '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if($aktif)
                                                <span class="inline-flex items-center rounded-full bg-teal-50 border border-teal-200 px-2 py-0.5 text-xs font-medium text-teal-700">
                                                    Bernilai
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-slate-100 border border-slate-200 px-2 py-0.5 text-xs font-medium text-slate-500">
                                                    Belum
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-6 text-center text-slate-400 text-sm">
                                            Belum ada MK yang dipetakan ke CPL ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if(count($breakdown) > 0)
                            <tfoot class="bg-[#f7fbfb] text-sm font-semibold text-slate-800">
                                <tr>
                                    <td colspan="2" class="px-4 py-3">Total</td>
                                    <td class="px-4 py-3 text-center">{{ collect($breakdown)->sum('sks') }}</td>
                                    <td class="px-4 py-3 text-center">{{ round(collect($breakdown)->sum('bobot') * 100, 1) }}%</td>
                                    <td class="px-4 py-3"></td>
                                    <td class="px-4 py-3 text-center text-[#0e7a86]">
                                        {{ number_format((float)($cpl['nilai_cpl'] ?? 0), 2) }}
                                        <span class="text-xs font-normal text-slate-400 ml-1">(TS)</span>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </section>
        @endforeach
    </div>

@endif {{-- end if cpls --}}

{{-- ===== TABEL NILAI MK (existing) ===== --}}
<div x-data="{
    searchName: '',
    searchSemester: '',
    nilaiList: @js($nilaiList),
    get filteredList() {
        return this.nilaiList.filter(item => {
            let mkName = (item.mata_kuliah?.nama || '').toLowerCase();
            let mkSemester = String(item.mata_kuliah?.semester || '');
            let matchName = mkName.includes(this.searchName.toLowerCase());
            let matchSemester = this.searchSemester === '' || mkSemester === this.searchSemester;
            return matchName && matchSemester;
        });
    },
    get availableSemesters() {
        let sems = this.nilaiList.map(item => item.mata_kuliah?.semester).filter(Boolean);
        return [...new Set(sems)].sort((a,b) => a - b);
    }
}" class="mb-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-3 gap-3">
        <div>
            <h3 class="font-bold text-slate-800">Nilai Akhir per Mata Kuliah</h3>
            <p class="text-xs text-slate-400 mt-0.5">Nilai agregat MK setelah dihitung dari bobot CPMK</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-2" x-show="nilaiList.length > 0" x-cloak>
            <input type="text" x-model="searchName" placeholder="Cari Nama Mata Kuliah..."
                   class="w-full sm:w-64 rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-400">
            <select x-model="searchSemester" class="w-full sm:w-40 rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-400">
                <option value="">Semua Semester</option>
                <template x-for="sem in availableSemesters" :key="sem">
                    <option :value="String(sem)" x-text="'Semester ' + sem"></option>
                </template>
            </select>
        </div>
    </div>

    <div class="overflow-hidden tsu-card rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[#d7e7e9] text-sm">
                <thead class="bg-[#f7fbfb] text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">No</th>
                        <th class="px-5 py-3">Kode MK</th>
                        <th class="px-5 py-3">Nama Mata Kuliah</th>
                        <th class="px-5 py-3 text-center">Semester</th>
                        <th class="px-5 py-3 text-center">SKS</th>
                        <th class="px-5 py-3 text-right">Nilai Akhir (MK)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="(item, index) in filteredList" :key="item.id || index">
                        <tr class="hover:bg-[#f7fbfb]">
                            <td class="px-5 py-3 text-slate-500" x-text="index + 1"></td>
                            <td class="px-5 py-3 font-medium text-slate-900" x-text="item.mata_kuliah?.kode || '-'"></td>
                            <td class="px-5 py-3 text-slate-700" x-text="item.mata_kuliah?.nama || '-'"></td>
                            <td class="px-5 py-3 text-center text-slate-600" x-text="item.mata_kuliah?.semester || '-'"></td>
                            <td class="px-5 py-3 text-center text-slate-600" x-text="item.mata_kuliah?.sks || '-'"></td>
                            <td class="px-5 py-3 text-right font-bold text-slate-900" x-text="Number(item.nilai || 0).toFixed(2)"></td>
                        </tr>
                    </template>
                    <tr x-show="filteredList.length === 0" x-cloak>
                        <td colspan="6" class="px-5 py-8 text-center text-slate-500">
                            <span x-show="nilaiList.length === 0">Mahasiswa ini belum memiliki data nilai mata kuliah.</span>
                            <span x-show="nilaiList.length > 0">Tidak ada mata kuliah yang cocok dengan filter.</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const labels   = @json($chartLabels);
    const valuesTs = @json($chartValuesTs);
    const valuesKs = @json($chartValuesKs);
    const target   = @json($chartTarget);

    if (!labels || labels.length === 0) return;

    function radarOptions() {
        return {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    min: 0,
                    max: 100,
                    ticks: { stepSize: 20, backdropColor: 'transparent' },
                    pointLabels: {
                        color: '#334155',
                        font: { size: 12, weight: '600' },
                    },
                    grid: { color: 'rgba(148, 163, 184, 0.35)' },
                    angleLines: { color: 'rgba(148, 163, 184, 0.35)' },
                },
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 14, boxHeight: 14 },
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return context.dataset.label + ': ' + context.formattedValue;
                        },
                    },
                },
            },
        };
    }

    function initCharts() {
        if (!window.Chart) return;

        // Chart 1: Terselenggara
        const canvas1 = document.getElementById('cplChartTs');
        if (canvas1) {
            const existing1 = Chart.getChart('cplChartTs');
            if (existing1) existing1.destroy();
            new Chart(canvas1, {
                type: 'radar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Nilai CPL (MK Terselenggara)',
                            data: valuesTs,
                            fill: true,
                            backgroundColor: 'rgba(14, 122, 134, 0.16)',
                            borderColor: 'rgb(14, 122, 134)',
                            pointBackgroundColor: 'rgb(14, 122, 134)',
                            pointBorderColor: '#ffffff',
                            pointHoverBackgroundColor: '#ffffff',
                            pointHoverBorderColor: 'rgb(14, 122, 134)',
                            borderWidth: 2,
                        },
                        {
                            label: 'Target ' + target,
                            data: labels.map(() => target),
                            fill: false,
                            borderColor: 'rgb(248, 194, 28)',
                            pointRadius: 0,
                            borderDash: [6, 4],
                            borderWidth: 1.5,
                        },
                    ],
                },
                options: radarOptions(),
            });
        }

        // Chart 2: Keseluruhan
        const canvas2 = document.getElementById('cplChartKs');
        if (canvas2) {
            const existing2 = Chart.getChart('cplChartKs');
            if (existing2) existing2.destroy();
            new Chart(canvas2, {
                type: 'radar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Nilai CPL (MK Keseluruhan)',
                            data: valuesKs,
                            fill: true,
                            backgroundColor: 'rgba(124, 58, 237, 0.14)',
                            borderColor: 'rgb(124, 58, 237)',
                            pointBackgroundColor: 'rgb(124, 58, 237)',
                            pointBorderColor: '#ffffff',
                            pointHoverBackgroundColor: '#ffffff',
                            pointHoverBorderColor: 'rgb(124, 58, 237)',
                            borderWidth: 2,
                        },
                        {
                            label: 'Target ' + target,
                            data: labels.map(() => target),
                            fill: false,
                            borderColor: 'rgb(248, 194, 28)',
                            pointRadius: 0,
                            borderDash: [6, 4],
                            borderWidth: 1.5,
                        },
                    ],
                },
                options: radarOptions(),
            });
        }
    }

    if (document.readyState !== 'loading' && window.Chart) {
        initCharts();
    } else {
        document.addEventListener('turbo:load', initCharts, { once: true });
        document.addEventListener('DOMContentLoaded', initCharts, { once: true });
    }
})();
</script>
@endpush
