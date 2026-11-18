@extends('layouts.app')

@section('content')
@php $title = 'Pilih Mata Kuliah'; @endphp
<div class="mb-6">
    <h2 class="text-xl font-bold text-slate-900">Pilih Mata Kuliah untuk Input Nilai</h2>
    <p class="mt-1 text-sm text-slate-500">Pilih mata kuliah yang ingin Anda inputkan nilainya.</p>
</div>

{{-- Filter Kurikulum Prodi --}}
<section class="mb-5 tsu-card rounded-lg p-4">
    <form method="GET" action="{{ route('input-nilai.select') }}" class="flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-[220px]">
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

<div class="overflow-hidden tsu-card rounded-lg">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-[#d7e7e9] text-sm">
            <thead class="bg-[#f7fbfb] text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">No</th>
                    <th class="px-4 py-3">Kode MK</th>
                    <th class="px-4 py-3">Nama Mata Kuliah</th>
                    <th class="px-4 py-3 text-center">SKS</th>
                    <th class="px-4 py-3 text-center">Semester</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse($mataKuliahs as $index => $mk)
                    <tr class="hover:bg-[#f7fbfb]">
                        <td class="px-4 py-3 text-slate-500">{{ $index + 1 }}</td>
                        <td class="px-4 py-3 font-bold text-slate-900">{{ $mk['kode'] }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $mk['nama'] }}</td>
                        <td class="px-4 py-3 text-center text-slate-600">{{ $mk['sks'] ?? '-' }}</td>
                        <td class="px-4 py-3 text-center text-slate-600">{{ $mk['semester'] ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('input-nilai.index', $mk['id']) }}" class="tsu-primary-button rounded-lg px-3 py-1.5 text-xs font-semibold">
                                Input Nilai
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                            Belum ada mata kuliah yang tersedia.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
