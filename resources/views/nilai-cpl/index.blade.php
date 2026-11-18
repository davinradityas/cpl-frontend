@extends('layouts.app')

@section('content')
@php $title = 'Nilai CPL'; @endphp

{{-- ====== HEADER ====== --}}
<div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Nilai CPL</h2>
        <p class="mt-1 text-sm text-slate-500">
            Rekap nilai CPL per mata kuliah berdasarkan MK Terselenggara dan MK Keseluruhan.
        </p>
    </div>
    @if($isReady)
    <a href="{{ route('mahasiswas.index', array_filter(['kurikulum_prodi_id' => $filters['kurikulum_prodi_id'], 'angkatan' => $filters['angkatan']])) }}"
       class="inline-flex items-center gap-2 rounded-lg bg-[#e8f6f7] border border-[#b5dde2] px-4 py-2.5 text-sm font-semibold text-[#0e7a86] hover:bg-[#d7e7e9] transition-colors shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        Lihat Mahasiswa Angkatan {{ $filters['angkatan'] }}
    </a>
    @endif
</div>

{{-- ====== FILTER FORM ====== --}}
<section class="mb-6 tsu-card rounded-lg p-5">
    <form method="GET" action="{{ route('nilai-cpl.index') }}"
          class="grid gap-4 lg:grid-cols-[1fr_1fr_1fr_auto] lg:items-end">

        {{-- Kurikulum Prodi --}}
        <div>
            <label for="kurikulum_prodi_id"
                   class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                Kurikulum Prodi
            </label>
            <select id="kurikulum_prodi_id" name="kurikulum_prodi_id" required
                    class="w-full rounded-lg border border-[#d7e7e9] bg-white/95 px-3 py-2.5 text-sm
                           outline-none focus:border-[#0e7a86] focus:ring-2 focus:ring-[#e8f6f7]">
                <option value="">— Pilih Kurikulum Prodi —</option>
                @foreach($kurikulumProdis as $kp)
                    <option value="{{ $kp['id'] }}"
                            @selected((string) $filters['kurikulum_prodi_id'] === (string) $kp['id'])>
                        {{ $kp['kode_kur_prodi'] ?? '-' }} – {{ $kp['prodi']['nama'] ?? '-' }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Angkatan --}}
        <div>
            <label for="angkatan"
                   class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                Angkatan
            </label>
            <select id="angkatan" name="angkatan"
                    class="w-full rounded-lg border border-[#d7e7e9] bg-white/95 px-3 py-2.5 text-sm
                           outline-none focus:border-[#0e7a86] focus:ring-2 focus:ring-[#e8f6f7]">
                <option value="">— Pilih Angkatan —</option>
                @foreach($angkatanList as $akt)
                    <option value="{{ $akt }}"
                            @selected((string) $filters['angkatan'] === (string) $akt)>{{ $akt }}</option>
                @endforeach
            </select>
        </div>

        {{-- CPL --}}
        <div>
            <label for="cpl_id"
                   class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                CPL
            </label>
            <select id="cpl_id" name="cpl_id"
                    class="w-full rounded-lg border border-[#d7e7e9] bg-white/95 px-3 py-2.5 text-sm
                           outline-none focus:border-[#0e7a86] focus:ring-2 focus:ring-[#e8f6f7]">
                <option value="all">Semua CPL</option>
                @foreach($cpls as $cpl)
                    <option value="{{ $cpl['id'] }}"
                            @selected((string) $filters['cpl_id'] === (string) $cpl['id'])>
                        {{ $cpl['kode_kur_prodi_cpl'] ?? $cpl['kode'] }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Tombol --}}
        <div class="flex gap-2">
            <button type="submit"
                    class="tsu-primary-button rounded-lg px-4 py-2.5 text-sm font-semibold">
                Tampilkan
            </button>
            <a href="{{ route('nilai-cpl.index') }}"
               class="tsu-secondary-button rounded-lg px-4 py-2.5 text-sm font-semibold">
                Reset
            </a>
            @if($isReady)
            <a href="{{ route('report.nilai-cpl', array_filter(['kurikulum_prodi_id' => $filters['kurikulum_prodi_id'], 'angkatan' => $filters['angkatan'], 'cpl_id' => $filters['cpl_id'] !== 'all' ? $filters['cpl_id'] : null])) }}"
               target="_blank"
               class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                Download PDF
            </a>
            @endif
        </div>
    </form>
</section>

{{-- ====== STATE: Pilih KP dulu ====== --}}
@if($needSelectKp)
    <div class="tsu-card rounded-lg p-10 text-center">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-[#e8f6f7] text-2xl">
            🎓
        </div>
        <h3 class="text-base font-semibold text-slate-800">Pilih Kurikulum Prodi</h3>
        <p class="mt-2 text-sm text-slate-500 max-w-sm mx-auto">
            Pilih Kurikulum Prodi dan Angkatan terlebih dahulu untuk melihat data Nilai CPL.
        </p>
    </div>

{{-- ====== STATE: Pilih Angkatan ====== --}}
@elseif($needSelectAngk)
    <div class="tsu-card rounded-lg p-10 text-center">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-amber-50 text-2xl">
            📅
        </div>
        <h3 class="text-base font-semibold text-slate-800">Pilih Angkatan</h3>
        <p class="mt-2 text-sm text-slate-500 max-w-sm mx-auto">
            Kurikulum Prodi sudah dipilih. Sekarang pilih angkatan untuk menampilkan nilai CPL.
        </p>
        @if(empty($angkatanList))
            <p class="mt-3 text-xs text-amber-600">
                Belum ada data nilai untuk kurikulum prodi ini. Pastikan nilai mahasiswa sudah diinput.
            </p>
        @endif
    </div>

{{-- ====== STATE: Error ====== --}}
@elseif($errorMessage)
    <div class="rounded-xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
        {{ $errorMessage }}
    </div>

{{-- ====== STATE: Data siap ====== --}}
@elseif($isReady)

    @php
        $semanticClass = function ($color) {
            return match ($color) {
                'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'warning' => 'bg-amber-50 text-amber-700 border-amber-200',
                'danger'  => 'bg-rose-50 text-rose-700 border-rose-200',
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
    @endphp

    {{-- Info Angkatan --}}
    <div class="mb-5 rounded-xl bg-teal-50 border border-teal-200 px-5 py-3 flex items-center gap-3">
        <div class="rounded-full bg-teal-100 p-1.5 text-teal-700 shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="text-sm text-teal-800">
            Menampilkan Nilai CPL untuk
            <strong>{{ $selectedKp['kode_kur_prodi'] ?? '-' }} – {{ $selectedKp['prodi']['nama'] ?? '-' }}</strong>
            · Angkatan <strong>{{ $filters['angkatan'] }}</strong>.
        </p>
    </div>

    {{-- Daftar CPL --}}
    <div class="space-y-6">
        @forelse($items as $item)
            @php
                $cpl    = $item['cpl'];
                $brkTs  = $item['breakdown_terselenggara'];
                $brkKs  = $item['breakdown_keseluruhan'];
                $totTs  = $item['totals_terselenggara'];
                $totKs  = $item['totals_keseluruhan'];
            @endphp

            <section class="overflow-hidden tsu-card rounded-lg">

                {{-- Header CPL --}}
                <div class="border-b border-[#d7e7e9] p-5">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                        <div class="max-w-3xl">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="font-semibold text-slate-900">{{ $cpl['kode'] ?? '-' }}</h3>
                            </div>
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ $cpl['rumusan_cpl'] }}</p>
                        </div>

                        {{-- Kotak Nilai CPL Ganda --}}
                        <div class="flex gap-3 shrink-0">
                            <div class="rounded-lg bg-[#e8f6f7] px-5 py-3 min-w-[130px] text-center">
                                <div class="text-xs font-semibold text-[#075866] uppercase tracking-wide">
                                    MK Terselenggara
                                </div>
                                <div class="font-bold text-2xl text-[#075866] mt-1">
                                    {{ number_format($cpl['nilai_cpl'], 2) }}
                                </div>
                                <div class="mt-1">
                                    <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs font-medium {{ $semanticClass($cpl['status_color']) }}">
                                        {{ $semanticIcon($cpl['status_color']) }} {{ $cpl['status'] }}
                                    </span>
                                </div>
                            </div>
                            <div class="rounded-lg bg-violet-50 border border-violet-200 px-5 py-3 min-w-[130px] text-center">
                                <div class="text-xs font-semibold text-violet-700 uppercase tracking-wide">
                                    MK Keseluruhan
                                </div>
                                <div class="font-bold text-2xl text-violet-700 mt-1">
                                    {{ number_format($cpl['nilai_cpl_keseluruhan'], 2) }}
                                </div>
                                <div class="mt-1">
                                    <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs font-medium {{ $semanticClass($cpl['status_keseluruhan_color']) }}">
                                        {{ $semanticIcon($cpl['status_keseluruhan_color']) }} {{ $cpl['status_keseluruhan'] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== TABEL MK ===== --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#d7e7e9] text-sm">
                        <thead class="bg-[#f7fbfb] text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Kode MK</th>
                                <th class="px-4 py-3">Mata Kuliah</th>
                                <th class="px-4 py-3 text-center">SKS</th>
                                <th class="px-4 py-3 text-center">Bobot MK-CPL</th>
                                <th class="px-4 py-3 text-center">Avg Nilai MK</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($brkKs as $row)
                                @php $aktif = $row['terselenggara'] ?? false; @endphp
                                <tr class="hover:bg-[#f7fbfb] {{ !$aktif ? 'opacity-60' : '' }}">
                                    <td class="px-4 py-3 font-medium text-slate-900">{{ $row['kode_mk'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $row['nama_mk'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center text-slate-700">{{ $row['sks'] ?? 0 }}</td>
                                    <td class="px-4 py-3 text-center text-slate-700">
                                        {{ round(((float)($row['bobot'] ?? 0)) * 100, 1) }}%
                                    </td>
                                    <td class="px-4 py-3 text-center {{ $aktif ? 'font-semibold text-slate-800' : 'text-slate-400' }}">
                                        {{ $aktif ? number_format((float)($row['avg_nilai'] ?? 0), 2) : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($aktif)
                                            <span class="inline-flex items-center rounded-full bg-teal-50 border border-teal-200 px-2 py-0.5 text-xs font-medium text-teal-700">
                                                Terselenggara
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
                                    <td colspan="6" class="px-4 py-6 text-center text-slate-400 text-sm">
                                        Belum ada MK yang dipetakan ke CPL ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(count($brkKs) > 0)
                        <tfoot class="bg-[#f7fbfb] text-sm font-semibold text-slate-900">
                            <tr>
                                <td colspan="2" class="px-4 py-3">Total</td>
                                <td class="px-4 py-3 text-center">{{ $totKs['sum_sks'] }}</td>
                                <td class="px-4 py-3 text-center">{{ round(((float)collect($brkKs)->sum('bobot')) * 100, 1) }}%</td>
                                <td class="px-4 py-3 text-center"></td>
                                <td></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>

            </section>
        @empty
            <div class="tsu-card rounded-lg p-8 text-center text-sm text-slate-500">
                Tidak ada data CPL untuk filter yang dipilih. Pastikan nilai mahasiswa angkatan
                <strong>{{ $filters['angkatan'] }}</strong> sudah diinput.
            </div>
        @endforelse
    </div>

@endif

@endsection
