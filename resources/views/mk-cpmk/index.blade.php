@extends('layouts.app')

@section('content')
@php
    $title = 'Mapping Mata Kuliah - CPMK';
    $jenisMatkul    = $mataKuliah['jenis_matkul'] ?? null;
    $bobotJenisMapped = collect($jenisPenilaians)->map(function($jp) use ($bobotJenisMk) {
        $existing = collect($bobotJenisMk)->firstWhere('jenis_penilaian_id', $jp['id']);
        return [
            'jenis_penilaian_id' => $jp['id'],
            'nama' => $jp['nama'],
            'bobot' => $existing ? $existing['bobot'] : 0
        ];
    })->values();
@endphp
<div x-data="cpmkSettings()">

    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <div class="flex items-center gap-2 mb-2 text-sm">
                <a href="{{ route('mata-kuliahs.index') }}" class="text-slate-500 hover:text-slate-800 font-medium">Mata Kuliah</a>
                <span class="text-slate-400">/</span>
                <span class="font-medium text-slate-800">Pengaturan CPMK & Bobot</span>
            </div>
            <h2 class="text-xl font-bold text-slate-900">{{ $mataKuliah['kode'] }} — {{ $mataKuliah['nama'] }}</h2>
            <p class="mt-1 text-sm text-slate-500">Pilih Jenis Matkul → Petakan CPMK → Atur Matriks Bobot CPMK per Jenis</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('report.matriks-bobot', $mataKuliahId) }}"
               target="_blank"
               class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                Download PDF
            </a>
            <button type="button" @click="addCpmkOpen = true"
                    class="tsu-primary-button rounded-lg px-4 py-2.5 text-sm font-semibold">
                + Tambah CPMK
            </button>
        </div>
    </div>


    {{-- Status Cards --}}
    <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Lapis 1: Jenis Matkul (Read-only) --}}
        <div class="tsu-card rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
                <div class="text-xs text-slate-500 uppercase font-semibold tracking-wide">① Jenis Matkul</div>
                @if($jenisMatkul)
                    <span class="text-xs font-bold text-green-600">✓ Terpilih</span>
                @else
                    <span class="text-xs font-bold text-amber-600">⚠ Belum dipilih</span>
                @endif
            </div>
            @if($jenisMatkul)
                <div class="font-bold text-teal-700 text-base">{{ $jenisMatkul['nama'] }}</div>
                <p class="text-xs text-slate-400 mt-1">Bobot diambil dari template Jenis Matkul ini</p>
            @else
                <p class="text-sm text-slate-500 mb-2">Mata kuliah ini belum memiliki Jenis Matkul.</p>
                <a href="{{ route('mata-kuliahs.index') }}"
                   class="inline-block text-xs font-semibold text-teal-700 hover:underline">
                    → Edit MK untuk memilih Jenis Matkul
                </a>
            @endif
        </div>

        {{-- Lapis 2: Mapping CPMK --}}
        <div class="tsu-card rounded-lg p-4">
            <div class="text-xs text-slate-500 uppercase font-semibold tracking-wide mb-1">② CPMK Terpetakan</div>
            <div class="text-2xl font-bold text-slate-900">{{ count($mappedCpmks) }}</div>
            <p class="text-xs text-slate-400 mt-1">Mapping CPL ↔ MK otomatis dari CPMK ini</p>
        </div>

        {{-- Petunjuk --}}
        <div class="tsu-card rounded-lg p-4 md:col-span-1">
            <div class="text-xs text-slate-500 uppercase font-semibold tracking-wide mb-2">Urutan Setup</div>
            <ol class="text-sm text-slate-700 space-y-1 list-decimal list-inside">
                <li class="{{ $jenisMatkul ? 'text-green-700 font-medium' : '' }}">Pilih Jenis Matkul di halaman Edit MK</li>
                <li>Petakan CPMK ke MK ini</li>
                <li>Atur matriks bobot CPMK per jenis</li>
            </ol>
        </div>
    </div>

    {{-- Tampilkan Bobot Jenis Matkul (read-only) --}}
    @if($jenisMatkul && count($bobotJenisMk) > 0)
    <div class="tsu-card rounded-lg mb-6 overflow-hidden">
        <div class="bg-teal-50 border-b border-[#d7e7e9] px-5 py-3 flex items-center gap-2">
            <span class="text-teal-700 font-semibold text-sm">Bobot dari Jenis Matkul: {{ $jenisMatkul['nama'] }}</span>
            <span class="text-xs text-teal-500">(read-only — ubah di halaman <a href="{{ route('jenis-matkul.index') }}" class="underline">Jenis Matkul</a>)</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-[#f7fbfb] text-xs uppercase tracking-wide text-slate-500">
                        @foreach($jenisPenilaians as $jp)
                            <th class="px-4 py-2 text-center border-r border-[#d7e7e9]">{{ $jp['nama'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr class="text-center">
                        @foreach($jenisPenilaians as $jp)
                            @php $bj = collect($bobotJenisMk)->firstWhere('jenis_penilaian_id', $jp['id']); @endphp
                            <td class="px-4 py-2 border-r border-[#d7e7e9] font-bold text-teal-700">
                                {{ $bj ? $bj['bobot'] : 0 }}%
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- === TABEL MATRIKS BOBOT CPMK PER JENIS === --}}
    <div class="mb-6">
        <div class="mb-3 flex items-center justify-between">
            <h3 class="font-bold text-slate-800">② Matriks Bobot CPMK per Jenis Penilaian</h3>
            @if(count($mappedCpmks) > 0 && $bobotJenisMkValid)
                <button type="button" @click="bobotMatriksOpen = true"
                        class="tsu-primary-button rounded-lg px-4 py-2 text-sm font-semibold">
                    Atur Matriks Bobot
                </button>
            @endif
        </div>
        <div class="overflow-hidden tsu-card rounded-lg">
            @if(!$jenisMatkul)
                <div class="p-6 text-center text-amber-700 bg-amber-50 rounded-lg">
                    ⚠ Pilih <strong>Jenis Matkul</strong> terlebih dahulu di halaman <a href="{{ route('mata-kuliahs.index') }}" class="underline font-semibold">Edit Mata Kuliah</a>.
                </div>
            @elseif(!$bobotJenisMkValid)
                <div class="p-6 text-center text-amber-700 bg-amber-50 rounded-lg">
                    ⚠ Bobot Jenis Matkul "<strong>{{ $jenisMatkul['nama'] }}</strong>" belum valid (total ≠ 100). Perbaiki di halaman <a href="{{ route('jenis-matkul.index') }}" class="underline font-semibold">Jenis Matkul</a>.
                </div>
            @elseif(count($mappedCpmks) === 0)
                <div class="p-6 text-center text-slate-500">
                    Belum ada CPMK yang dipetakan. Klik <strong>"Tambah CPMK"</strong>.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table x-data="tableSort()" class="min-w-full divide-y divide-[#d7e7e9] text-sm">
                        <thead class="bg-[#e8f6f7] text-left text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3 border-r border-[#d7e7e9] text-left">CPMK</th>
                                <th class="px-3 py-3 border-r border-[#d7e7e9]">CPL</th>
                                @foreach($jenisPenilaians as $jp)
                                    <th class="px-3 py-3 border-r border-[#d7e7e9] text-[11px]">{{ $jp['nama'] }}</th>
                                @endforeach
                                <th class="px-3 py-3">Aksi</th>
                            </tr>
                            {{-- Baris bobot dari JenisMatkul (referensi) --}}
                            <tr class="bg-teal-50 text-teal-700 text-[11px]">
                                <td class="px-4 py-2 border-r border-[#d7e7e9] font-medium text-slate-700">
                                    Bobot dari "{{ $jenisMatkul['nama'] }}"
                                </td>
                                @foreach($jenisPenilaians as $index => $jp)
                                    @php $bj = collect($bobotJenisMk)->firstWhere('jenis_penilaian_id', $jp['id']); @endphp
                                    <td class="px-3 py-2 border-r border-[#d7e7e9] font-bold">{{ $bj ? $bj['bobot'] : 0 }}</td>
                                @endforeach
                                <td class="px-3 py-2 border-r border-[#d7e7e9] font-bold">{{ collect($bobotJenisMk)->sum('bobot') }}</td>
                                <td></td>
                            </tr>
                        </thead>
                        <thead class="bg-[#f7fbfb] text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3 border-r border-[#d7e7e9] cursor-pointer select-none hover:bg-slate-100" @click="sort(0)">CPMK <span x-show="sortCol === 0" x-text="sortAsc ? '↑' : '↓'"></span></th>
                                <th class="px-3 py-3 border-r border-[#d7e7e9] cursor-pointer select-none hover:bg-slate-100" @click="sort(1)">CPL <span x-show="sortCol === 1" x-text="sortAsc ? '↑' : '↓'"></span></th>
                                @foreach($jenisPenilaians as $index => $jp)
                                    <th class="px-3 py-3 border-r border-[#d7e7e9] text-center cursor-pointer select-none hover:bg-slate-100" @click="sort({{ $index + 2 }})">
                                        {{ $jp['nama'] }} <span x-show="sortCol === {{ $index + 2 }}" x-text="sortAsc ? '↑' : '↓'"></span>
                                    </th>
                                @endforeach
                                <th class="px-3 py-3 border-r border-[#d7e7e9] text-center cursor-pointer select-none hover:bg-slate-100" @click="sort({{ count($jenisPenilaians) + 2 }})">Total Bobot <span x-show="sortCol === {{ count($jenisPenilaians) + 2 }}" x-text="sortAsc ? '↑' : '↓'"></span></th>
                                <th class="px-3 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody x-ref="tbody" class="divide-y divide-slate-100 bg-white">
                            @foreach($mappedCpmks as $cpmk)
                                @php $bobotTeknik = collect($cpmk['bobot_teknik_cpmk'] ?? []); @endphp
                                <tr class="hover:bg-[#f7fbfb] text-center">
                                    <td class="px-4 py-3 text-left border-r border-[#d7e7e9]">
                                        <div class="font-bold text-slate-900">{{ $cpmk['kode'] }}</div>
                                        <div class="text-xs text-slate-400 truncate max-w-[180px]" title="{{ $cpmk['rumusan_cpmk'] }}">{{ $cpmk['rumusan_cpmk'] }}</div>
                                    </td>
                                    <td class="px-3 py-3 border-r border-[#d7e7e9]">
                                        <span class="inline-flex rounded-full border border-teal-200 bg-teal-50 px-2.5 py-0.5 text-xs font-semibold text-teal-700">
                                            {{ $cpmk['cpl']['kode'] ?? '-' }}
                                        </span>
                                    </td>
                                    @foreach($jenisPenilaians as $jp)
                                        @php
                                            $btj = $bobotTeknik->firstWhere('jenis_penilaian_id', $jp['id']);
                                            $bv  = $btj ? $btj['bobot'] : 0;
                                        @endphp
                                        <td class="px-3 py-3 border-r border-[#d7e7e9] {{ $bv > 0 ? 'text-slate-800 font-semibold' : 'text-slate-300' }}">
                                            {{ $bv > 0 ? $bv : '-' }}
                                        </td>
                                    @endforeach
                                    <td class="px-3 py-3 border-r border-[#d7e7e9] font-bold text-slate-800">
                                        {{ $bobotTeknik->sum('bobot') }}
                                    </td>
                                    <td class="px-3 py-3">
                                        <button type="button"
                                                @click="deleteUrl = '{{ route('mk-cpmk.destroy', [$mataKuliahId, $cpmk['id']]) }}'; deleteCpmkCode = '{{ $cpmk['kode'] }}'; confirmDeleteOpen = true"
                                                class="rounded bg-red-50 border border-red-200 px-2 py-1 text-[11px] font-semibold text-red-700 hover:bg-red-100 transition-colors">
                                            Lepas
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-[#f7fbfb] font-bold text-slate-700 text-center text-xs">
                            <tr>
                                <td colspan="2" class="px-4 py-3 text-right border-r border-[#d7e7e9]">TOTAL PER KOLOM</td>
                                @foreach($jenisPenilaians as $jp)
                                    @php
                                        $total = $totalBobotPerJenis[$jp['id']] ?? 0;
                                        $bj    = collect($bobotJenisMk)->firstWhere('jenis_penilaian_id', $jp['id']);
                                        $ref   = $bj ? $bj['bobot'] : 0;
                                        $valid = abs($total - $ref) < 0.01;
                                    @endphp
                                    <td class="px-3 py-3 border-r border-[#d7e7e9] {{ $valid ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $total }}
                                        @if(!$valid) <div class="text-[9px] font-normal">≠ {{ $ref }}</div> @endif
                                    </td>
                                @endforeach
                                @php
                                    $allTotal = array_sum($totalBobotPerJenis);
                                    $allRef = collect($bobotJenisMk)->sum('bobot');
                                    $allValid = abs($allTotal - $allRef) < 0.01;
                                @endphp
                                <td class="px-3 py-3 border-r border-[#d7e7e9] {{ $allValid ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $allTotal }}
                                    @if(!$allValid) <div class="text-[9px] font-normal">≠ {{ $allRef }}</div> @endif
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- ===== MODAL: Tambah CPMK ===== --}}
    <div x-cloak x-show="addCpmkOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
        <div @click.away="addCpmkOpen = false" class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-xl bg-white shadow-2xl">
            <div class="border-b border-[#d7e7e9] px-6 py-4 bg-[#f7fbfb]">
                <h3 class="font-bold text-slate-900">Tambah CPMK ke Mata Kuliah</h3>
                <p class="text-xs text-slate-500 mt-0.5">Pilih CPMK — mapping CPL↔MK akan dibuat otomatis</p>
            </div>
            <form method="POST" action="{{ route('mk-cpmk.store', $mataKuliahId) }}" class="space-y-4 p-6">
                @csrf
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">CPMK <span class="text-red-500">*</span></label>
                    @if(count($availableCpmks) > 0)
                        <select name="cpmk_id" required class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm">
                            <option value="">-- Pilih CPMK --</option>
                            @foreach($availableCpmks as $cpmk)
                                <option value="{{ $cpmk['id'] }}">{{ $cpmk['kode'] }} — {{ Str::limit($cpmk['rumusan_cpmk'], 60) }}</option>
                            @endforeach
                        </select>
                    @else
                        <div class="rounded-lg bg-amber-50 border border-amber-200 p-3 text-sm text-amber-700">
                            Semua CPMK sudah dipetakan. Tambahkan CPMK baru di menu <strong>Master CPMK</strong>.
                        </div>
                    @endif
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="addCpmkOpen = false" class="tsu-secondary-button rounded-lg px-4 py-2 text-sm">Batal</button>
                    <button type="submit" class="tsu-primary-button rounded-lg px-4 py-2 text-sm font-semibold"
                            @if(!count($availableCpmks)) disabled @endif>Tambahkan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== MODAL: Matriks Bobot CPMK per Jenis ===== --}}
    <div x-cloak x-show="bobotMatriksOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
        <div @click.away="bobotMatriksOpen = false" class="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-xl bg-white shadow-2xl">
            <div class="border-b border-[#d7e7e9] px-6 py-4 bg-[#f7fbfb]">
                <h3 class="font-bold text-slate-900">② Matriks Bobot CPMK per Jenis Penilaian</h3>
                <p class="text-sm text-slate-500 mt-0.5">Total <strong>setiap kolom</strong> harus = bobot dari Jenis Matkul <strong>"{{ $jenisMatkul['nama'] ?? '' }}"</strong></p>
            </div>
            <form method="POST" action="{{ route('mk-cpmk.bobot-teknik', $mataKuliahId) }}" class="p-6" @submit.prevent="submitMatriks">
                @csrf
                @php $idx = 0; @endphp

                {{-- Alert Error Validation --}}
                <div x-cloak x-show="errorMessages.length > 0" x-transition
                     class="mb-5 rounded-xl bg-red-50 border border-red-200 p-4 shadow-sm">
                    <div class="flex items-center gap-2 text-red-700 font-bold mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        Gagal Menyimpan Matriks Bobot
                    </div>
                    <ul class="list-disc list-inside text-sm text-red-600 space-y-1 ml-1">
                        <template x-for="err in errorMessages" :key="err">
                            <li x-text="err"></li>
                        </template>
                    </ul>
                    <p class="text-xs text-red-500 mt-3 ml-1">Silakan sesuaikan angka yang memiliki total berwarna merah di bagian bawah tabel.</p>
                </div>

                <div class="overflow-x-auto mb-5">
                    <table class="min-w-full text-sm border border-[#d7e7e9] rounded-lg overflow-hidden">
                        <thead class="bg-[#f7fbfb] text-xs font-semibold uppercase text-slate-500">
                            <tr>
                                <th class="px-4 py-3 text-left border-r border-[#d7e7e9]">CPMK</th>
                                @foreach($jenisPenilaians as $jp)
                                    @php $bj = collect($bobotJenisMk)->firstWhere('jenis_penilaian_id', $jp['id']); @endphp
                                    <th class="px-3 py-3 text-center border-r border-[#d7e7e9]">
                                        {{ $jp['nama'] }}<br>
                                        <span class="text-teal-600 font-bold text-[11px]">(max: {{ $bj ? $bj['bobot'] : 0 }})</span>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#d7e7e9]">
                            @foreach($mappedCpmks as $cpmk)
                                @php $bobotTeknik = collect($cpmk['bobot_teknik_cpmk'] ?? []); @endphp
                                <tr>
                                    <td class="px-4 py-3 font-bold border-r border-[#d7e7e9]">
                                        {{ $cpmk['kode'] }}<br>
                                        <span class="text-xs text-slate-400 font-normal">{{ Str::limit($cpmk['rumusan_cpmk'], 40) }}</span>
                                    </td>
                                    @foreach($jenisPenilaians as $jp)
                                        @php
                                            $btj = $bobotTeknik->firstWhere('jenis_penilaian_id', $jp['id']);
                                            $val = $btj ? $btj['bobot'] : 0;
                                            $key = "{$cpmk['id']}_{$jp['id']}";
                                        @endphp
                                        <td class="px-3 py-2 border-r border-[#d7e7e9]">
                                            <input type="hidden" name="bobots[{{ $idx }}][cpmk_id]" value="{{ $cpmk['id'] }}">
                                            <input type="hidden" name="bobots[{{ $idx }}][jenis_penilaian_id]" value="{{ $jp['id'] }}">
                                            <input type="number" step="any" min="0"
                                                   name="bobots[{{ $idx }}][bobot]"
                                                   x-model.number="matriks['{{ $key }}']"
                                                   class="w-20 rounded border border-[#d7e7e9] px-2 py-1 text-sm text-center focus:border-teal-400 focus:ring-1 focus:ring-teal-400">
                                            @php $idx++; @endphp
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-[#f7fbfb] font-bold text-xs">
                            <tr>
                                <td class="px-4 py-3 text-right border-r border-[#d7e7e9] text-slate-600">TOTAL PER KOLOM</td>
                                @foreach($jenisPenilaians as $jp)
                                    @php $bj = collect($bobotJenisMk)->firstWhere('jenis_penilaian_id', $jp['id']); $ref = $bj ? $bj['bobot'] : 0; @endphp
                                    <td class="px-3 py-3 border-r border-[#d7e7e9] text-center"
                                        :class="isKolomValid({{ $jp['id'] }}) ? 'text-green-600' : 'text-red-600'">
                                        <span x-text="totalPerJenis({{ $jp['id'] }}).toFixed(1)"></span>
                                        <span class="text-slate-400 font-normal"> / {{ $ref }}</span>
                                    </td>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" @click="bobotMatriksOpen = false" class="tsu-secondary-button rounded-lg px-4 py-2 text-sm">Batal</button>
                    <button type="submit" class="tsu-primary-button rounded-lg px-4 py-2 text-sm font-semibold">
                        Simpan Matriks Bobot
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== MODAL: Konfirmasi Lepas CPMK ===== --}}
    <div x-cloak x-show="confirmDeleteOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
        <div @click.away="confirmDeleteOpen = false" class="w-full max-w-sm rounded-xl bg-white shadow-2xl overflow-hidden"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-90">
            <div class="bg-red-50 p-6 flex flex-col items-center text-center border-b border-red-100">
                <div class="rounded-full bg-red-100 p-3 mb-3 text-red-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900">Lepas CPMK <span x-text="deleteCpmkCode" class="text-red-600"></span>?</h3>
                <p class="text-sm text-slate-500 mt-2">
                    Tindakan ini akan membatalkan pemetaan CPMK ini dari Mata Kuliah. Semua pengaturan matriks bobot terkait CPMK ini juga akan <strong class="text-slate-700">terhapus</strong> secara permanen.
                </p>
            </div>
            <div class="p-4 bg-slate-50 flex justify-end gap-2">
                <button type="button" @click="confirmDeleteOpen = false" class="rounded-lg bg-white border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Batal
                </button>
                <form method="POST" :action="deleteUrl" class="inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 shadow-sm shadow-red-200">
                        Ya, Lepaskan
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@push('scripts')
<script>
    function cpmkSettings() {
        return {
            addCpmkOpen: false,
            bobotMatriksOpen: false,
            confirmDeleteOpen: false,
            deleteUrl: '',
            deleteCpmkCode: '',
            errorMessages: [],

            bobotJenis: @json($bobotJenisMapped),

            get totalBobotJenis() {
                return this.bobotJenis.reduce((sum, b) => sum + Number(b.bobot || 0), 0);
            },

            matriks: {},

            init() {
                const cpmks   = @json($mappedCpmks);
                const jeniss  = @json($jenisPenilaians);
                cpmks.forEach(cpmk => {
                    jeniss.forEach(jp => {
                        const existing = (cpmk.bobot_teknik_cpmk || []).find(b => b.jenis_penilaian_id === jp.id);
                        const key = `${cpmk.id}_${jp.id}`;
                        this.matriks[key] = existing ? Number(existing.bobot) : 0;
                    });
                });
            },

            totalPerJenis(jenisId) {
                const cpmks = @json($mappedCpmks);
                return cpmks.reduce((sum, cpmk) => sum + Number(this.matriks[`${cpmk.id}_${jenisId}`] || 0), 0);
            },

            getBobotJenisMk(jenisId) {
                const b = this.bobotJenis.find(x => x.jenis_penilaian_id === jenisId);
                return b ? Number(b.bobot) : 0;
            },

            isKolomValid(jenisId) {
                return Math.abs(this.totalPerJenis(jenisId) - this.getBobotJenisMk(jenisId)) < 0.01;
            },

            submitMatriks(e) {
                const jeniss = @json($jenisPenilaians);
                let isValid = true;
                this.errorMessages = [];

                jeniss.forEach(jp => {
                    if (!this.isKolomValid(jp.id)) {
                        isValid = false;
                        let max = this.getBobotJenisMk(jp.id);
                        let current = this.totalPerJenis(jp.id).toFixed(1);
                        this.errorMessages.push(`Kolom '${jp.nama}' memiliki total ${current}%, seharusnya ${max}%`);
                    }
                });

                if (!isValid) {
                    return;
                }

                e.target.submit();
            }
        };
    }
</script>
@endpush
@endsection
