@extends('layouts.app')

@section('content')
@php
    $modeManual = $cpl['bobot_mode'] === 'manual';
    $mataKuliahs = $cpl['mata_kuliahs'] ?? [];
    $totalSks = $cpl['total_sks'] ?? 0;
@endphp

<div x-data="{
    mode: '{{ $cpl['bobot_mode'] }}',
    bobots: {{ json_encode(array_column($mataKuliahs, 'bobot_persen_input', 'id')) }},
    get totalBobot() {
        return Object.values(this.bobots).reduce((sum, v) => sum + (parseFloat(v) || 0), 0);
    },
    get isValid() {
        return this.mode === 'sks' || Math.abs(this.totalBobot - 100) < 0.01;
    },
    get totalColor() {
        const t = this.totalBobot;
        if (Math.abs(t - 100) < 0.01) return 'text-emerald-600';
        if (t > 100) return 'text-red-600';
        return 'text-amber-600';
    }
}" class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('cpls.index') }}"
               class="mb-2 inline-flex items-center gap-1.5 text-sm text-[#0e7a86] hover:underline">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali ke Data CPL
            </a>
            <h1 class="text-xl font-bold text-slate-900">Pengaturan Bobot — {{ $cpl['kode'] }}</h1>
            <p class="mt-0.5 text-sm text-slate-500 line-clamp-2 max-w-2xl">{{ $cpl['rumusan_cpl'] }}</p>
        </div>
        <div class="shrink-0 text-right">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Kurikulum Prodi</div>
            <div class="mt-0.5 font-semibold text-slate-700">{{ $cpl['kurikulum_prodi']['kode'] ?? '-' }}</div>
            <div class="text-xs text-slate-500">{{ $cpl['kurikulum_prodi']['prodi'] ?? '-' }}</div>
        </div>
    </div>

    <form method="POST" action="{{ route('cpls.bobot.update', $cpl['id']) }}" id="form-bobot">
        @csrf

        {{-- Panel: Pilih Mode --}}
        <div class="tsu-card rounded-lg p-6">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">Mode Pembobotan</h2>
            <div class="grid gap-4 sm:grid-cols-2">

                {{-- SKS --}}
                <label :class="mode === 'sks'
                        ? 'border-[#0e7a86] bg-[#e8f6f7] ring-2 ring-[#0e7a86]/20'
                        : 'border-[#d7e7e9] bg-white hover:border-[#0e7a86]/50'"
                       class="flex cursor-pointer items-start gap-4 rounded-xl border p-4 transition">
                    <input type="radio" name="bobot_mode" value="sks" x-model="mode" class="mt-0.5 accent-[#0e7a86]">
                    <div>
                        <div class="font-semibold text-slate-800">Otomatis (SKS)</div>
                        <p class="mt-1 text-xs text-slate-500">
                            Bobot dihitung otomatis berdasarkan proporsi SKS tiap mata kuliah.
                            Tidak perlu input manual.
                        </p>
                    </div>
                </label>

                {{-- Manual --}}
                <label :class="mode === 'manual'
                        ? 'border-[#0e7a86] bg-[#e8f6f7] ring-2 ring-[#0e7a86]/20'
                        : 'border-[#d7e7e9] bg-white hover:border-[#0e7a86]/50'"
                       class="flex cursor-pointer items-start gap-4 rounded-xl border p-4 transition">
                    <input type="radio" name="bobot_mode" value="manual" x-model="mode" class="mt-0.5 accent-[#0e7a86]">
                    <div>
                        <div class="font-semibold text-slate-800">Manual</div>
                        <p class="mt-1 text-xs text-slate-500">
                            Anda menentukan sendiri bobot (%) untuk setiap mata kuliah.
                            Total harus tepat 100%.
                        </p>
                    </div>
                </label>
            </div>
        </div>

        {{-- Panel: Tabel Bobot --}}
        <div class="tsu-card overflow-hidden rounded-lg">
            <div class="flex items-center justify-between border-b border-[#d7e7e9] px-6 py-4">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">
                    Mata Kuliah dalam CPL ini
                    <span class="ml-2 rounded-full bg-[#e8f6f7] px-2 py-0.5 text-xs font-bold text-[#0e7a86]">
                        {{ count($mataKuliahs) }} MK &middot; Total {{ $totalSks }} SKS
                    </span>
                </h2>

                {{-- Live counter total bobot (hanya saat manual) --}}
                <div x-show="mode === 'manual'" class="text-right" style="display:none">
                    <div class="text-xs text-slate-500">Total Bobot</div>
                    <div :class="totalColor" class="text-lg font-bold tabular-nums transition-colors"
                         x-text="totalBobot.toFixed(2) + '%'"></div>
                    <div x-show="!isValid" class="text-xs text-red-500" style="display:none">
                        Harus tepat 100%
                    </div>
                    <div x-show="isValid" class="text-xs text-emerald-500" style="display:none">
                        Valid
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#d7e7e9] text-sm">
                    <thead class="bg-[#f7fbfb] text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">No</th>
                            <th class="px-4 py-3">Kode MK</th>
                            <th class="px-4 py-3">Nama Mata Kuliah</th>
                            <th class="px-4 py-3 text-center">SKS</th>
                            <th class="px-4 py-3 text-center">Bobot SKS (%)</th>
                            <th class="px-4 py-3 text-center">Bobot Manual (%)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($mataKuliahs as $idx => $mk)
                            <tr class="hover:bg-[#f7fbfb]">
                                <td class="px-4 py-3 text-slate-400 tabular-nums">{{ $idx + 1 }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded bg-[#e8f6f7] px-2 py-0.5 text-xs font-bold text-[#0e7a86]">
                                        {{ $mk['kode'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-800">
                                    {{ $mk['nama'] }}
                                    @if($mk['jenis_matkul'])
                                        <span class="ml-1 text-xs text-slate-400">({{ $mk['jenis_matkul'] }})</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center font-semibold text-slate-700">{{ $mk['sks'] }}</td>
                                <td class="px-4 py-3 text-center text-slate-600 tabular-nums">
                                    {{ number_format($mk['bobot_persen'], 2) }}%
                                </td>
                                <td class="px-4 py-3 text-center">
                                    {{-- Hidden field untuk mata_kuliah_id, selalu di-submit --}}
                                    <input type="hidden" name="mata_kuliah_ids[]" value="{{ $mk['id'] }}">

                                    {{-- Input manual: aktif saat mode manual --}}
                                    <input type="number"
                                           x-show="mode === 'manual'"
                                           name="bobot_persen[]"
                                           min="0" max="100" step="0.01"
                                           x-model.number="bobots[{{ $mk['id'] }}]"
                                           @input="bobots[{{ $mk['id'] }}] = parseFloat($event.target.value) || 0"
                                           class="w-24 rounded-lg border border-[#d7e7e9] px-2 py-1.5 text-center text-sm outline-none focus:border-[#0e7a86] focus:ring-2 focus:ring-[#e8f6f7] tabular-nums"
                                           :class="{ 'border-red-300 bg-red-50': !isValid && mode === 'manual' }"
                                           value="{{ $mk['bobot_persen_input'] }}"
                                           style="display:none">

                                    {{-- Placeholder saat mode sks --}}
                                    <span x-show="mode === 'sks'" class="text-slate-400 text-xs italic">
                                        Otomatis
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-slate-500">
                                    CPL ini belum memiliki Mata Kuliah yang terpetakan.<br>
                                    <a href="{{ route('cpls.index') }}" class="mt-2 inline-block text-sm text-[#0e7a86] hover:underline">
                                        Pergi ke halaman Data CPL untuk mengecek pemetaan CPMK.
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tombol Simpan --}}
        @if(count($mataKuliahs) > 0)
            <div class="flex items-center justify-between rounded-lg border border-[#d7e7e9] bg-white px-6 py-4 shadow-sm">
                <div x-show="mode === 'manual'" class="text-sm" style="display:none">
                    <span x-show="!isValid" class="font-medium text-red-600" style="display:none">
                        Total bobot manual harus 100%. Saat ini:
                        <span x-text="totalBobot.toFixed(2) + '%'" class="font-bold"></span>
                    </span>
                    <span x-show="isValid" class="font-medium text-emerald-600" style="display:none">
                        Total bobot sudah tepat 100%, siap disimpan.
                    </span>
                </div>
                <div x-show="mode === 'sks'" class="text-sm text-slate-500">
                    Mode SKS: bobot akan dihitung ulang otomatis saat disimpan.
                </div>

                <div class="flex gap-3">
                    <a href="{{ route('cpls.index') }}"
                       class="tsu-secondary-button rounded-lg px-5 py-2.5 text-sm font-medium">
                        Batal
                    </a>
                    <button type="submit"
                            :disabled="!isValid"
                            :class="isValid
                                ? 'tsu-primary-button'
                                : 'cursor-not-allowed bg-slate-200 text-slate-400'"
                            class="rounded-lg px-5 py-2.5 text-sm font-semibold transition"
                            id="btn-simpan-bobot">
                        <span x-show="mode === 'sks'">Simpan & Kalkulasi Ulang</span>
                        <span x-show="mode === 'manual'" style="display:none">Simpan Bobot Manual</span>
                    </button>
                </div>
            </div>
        @endif
    </form>
</div>
@endsection
