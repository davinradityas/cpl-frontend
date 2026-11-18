@extends('layouts.app')

@section('content')
@php
    $title = 'Mapping CPL - Mata Kuliah';
    $attachedIds = collect($attached)->pluck('id')->map(fn ($id) => (string) $id)->all();
    $availableCount = collect($mataKuliahs)->reject(fn ($mk) => in_array((string) ($mk['id'] ?? ''), $attachedIds, true))->count();
    $totalBobot = collect($attached)->sum(fn ($mk) => (float) ($mk['bobot'] ?? 0));
@endphp

<div class="mb-6">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Mapping CPL-MK</h2>
        <p class="mt-1 text-sm text-slate-500">Hubungkan CPL dengan mata kuliah yang berkontribusi.</p>
    </div>
</div>

<div class="grid grid-cols-1 gap-4 lg:grid-cols-[360px_1fr]">
    <section class="space-y-4">
        <div class="tsu-card rounded-lg p-5">
            <h3 class="font-semibold text-slate-900">Pilih CPL</h3>

            <form method="GET" action="{{ route('mapping.index') }}" class="space-y-3">
                <select name="cpl_id" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm">
                @if(empty($cpls))
                    <option value="">Belum ada CPL</option>
                @endif
                @foreach($cpls as $cpl)
                    <option value="{{ $cpl['id'] }}" @selected((string) $selectedCplId === (string) $cpl['id'])>
                        {{ $cpl['kode_kur_prodi_cpl'] ?? $cpl['kode'] }} - {{ $cpl['rumusan_cpl'] ?? '' }}
                    </option>
                @endforeach
            </select>
            <button class="tsu-primary-button w-full rounded-lg px-4 py-2 text-sm font-semibold">Tampilkan</button>
        </form>
        </div>

        @if($selectedCplId)
            <form method="POST" action="{{ route('mapping.store') }}" class="tsu-card rounded-lg p-5">
                @csrf
                <input type="hidden" name="cpl_id" value="{{ $selectedCplId }}">
                <h3 class="font-semibold text-slate-900">Tambah Mata Kuliah</h3>
                <p class="mt-1 text-sm text-slate-500">Centang mata kuliah ke bawah untuk ditambahkan ke CPL ini.</p>

                @if(empty($mataKuliahs))
                    <div class="mt-4 rounded-lg border border-dashed border-[#d7e7e9] bg-[#f7fbfb] p-6 text-center text-sm text-slate-500">
                        Belum ada mata kuliah untuk kurikulum prodi CPL ini.
                    </div>
                @else
                    <div class="mt-4 max-h-[360px] space-y-2 overflow-y-auto pr-1">
                        @foreach($mataKuliahs as $mk)
                            @php $isAttached = in_array((string) ($mk['id'] ?? ''), $attachedIds, true); @endphp
                            <label class="flex cursor-pointer items-start gap-3 rounded-lg border px-3 py-2.5 text-sm transition {{ $isAttached ? 'border-[#d7e7e9] bg-[#f7fbfb] opacity-70' : 'border-[#d7e7e9] bg-white hover:border-[#b7dfe3] hover:bg-[#f7fbfb]' }}">
                                <input type="checkbox"
                                       name="mata_kuliah_ids[]"
                                       value="{{ $mk['id'] }}"
                                       class="mt-1 rounded border-[#d7e7e9] text-[#0e7a86] focus:ring-[#0e7a86]"
                                       @disabled($isAttached)>
                                <span class="min-w-0 flex-1 leading-5">
                                    <span class="block font-semibold text-slate-900">{{ $mk['kode'] ?? '-' }} - {{ $mk['nama'] ?? '-' }}</span>
                                    <span class="mt-0.5 block text-xs text-slate-500">
                                        {{ $mk['sks'] ?? '-' }} SKS
                                        @if($isAttached)
                                            <span class="ml-2 font-semibold text-green-700">Sudah terhubung</span>
                                        @endif
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif

                <button class="tsu-primary-button mt-4 w-full rounded-lg px-4 py-2 text-sm font-semibold">
                    Simpan Mapping
                </button>
            </form>
        @else
            <div class="tsu-card rounded-lg p-5 text-sm text-slate-600">
                Buat CPL terlebih dahulu sebelum melakukan mapping CPL-MK.
            </div>
        @endif
    </section>

    <section>
        <div class="overflow-hidden tsu-card rounded-lg">
        <div class="border-b border-[#d7e7e9] p-5">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="font-semibold text-slate-900">Mata Kuliah Terhubung</h3>
                    <p class="mt-1 text-sm text-slate-500">Daftar MK yang sudah dipetakan ke CPL terpilih.</p>
                </div>
                <span class="rounded-lg bg-[#e8f6f7] px-3 py-2 text-sm font-semibold text-[#075866]">
                    Total bobot {{ $totalBobot > 0 ? round($totalBobot * 100) . '%' : '-' }}
                </span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[#d7e7e9] text-sm">
                <thead class="bg-[#f7fbfb] text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Nama MK</th>
                        <th class="px-4 py-3">SKS</th>
                        <th class="px-4 py-3">Bobot</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attached as $mk)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $mk['kode'] ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $mk['nama'] ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $mk['sks'] ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ isset($mk['bobot']) ? round(((float) $mk['bobot']) * 100) . '%' : '-' }}</td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('mapping.destroy', [$selectedCplId, $mk['id']]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button onclick="return confirm('Hapus mapping ini?')" class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada mata kuliah terhubung.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        </div>
    </section>
</div>
@endsection



