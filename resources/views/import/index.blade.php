@extends('layouts.app')

@section('content')
@php $title = 'Import Nilai'; @endphp
<div class="mb-6">
    <h2 class="text-xl font-bold text-slate-900">Import Nilai</h2>
    <p class="mt-1 text-sm text-slate-500">Unggah file nilai format wide atau long, lalu backend akan menghitung ulang CPL.</p>
</div>

@if($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
        <ul class="list-inside list-disc">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(session('import_result'))
    @php $importResult = session('import_result'); $hasErrors = !empty($importResult['errors']); @endphp
    <div class="mb-4 rounded-lg border {{ $hasErrors ? 'border-amber-200 bg-amber-50 text-amber-800' : 'border-green-200 bg-green-50 text-green-800' }} p-4 text-sm">
        <div class="font-semibold mb-2">{{ $hasErrors ? '⚠️ Import selesai dengan beberapa kesalahan' : '✅ Import berhasil' }}</div>
        @if(isset($importResult['imported']))
            <p>Berhasil diimport: <strong>{{ $importResult['imported'] }}</strong> baris</p>
        @endif
        @if($hasErrors)
            <ul class="mt-2 list-inside list-disc text-xs">
                @foreach(array_slice($importResult['errors'], 0, 10) as $err)
                    <li>{{ is_array($err) ? json_encode($err, JSON_UNESCAPED_UNICODE) : $err }}</li>
                @endforeach
                @if(count($importResult['errors']) > 10)
                    <li>... dan {{ count($importResult['errors']) - 10 }} kesalahan lainnya</li>
                @endif
            </ul>
        @endif
    </div>
@endif

<div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
    <section class="tsu-card rounded-lg p-5">
        <div class="mb-4">
            <h3 class="font-semibold text-slate-900">Import Wide</h3>
            <p class="mt-1 text-sm text-slate-500">Format kolom: <code class="bg-slate-100 px-1 rounded text-xs">NIM | Nama | [Jenis Penilaian...]</code></p>
            <p class="mt-1 text-xs text-amber-600 font-medium">⚠ Download template dari sini dulu agar format kolomnya sesuai mata kuliah yang dipilih.</p>
        </div>
        <form method="POST" action="{{ route('import.wide') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <select name="mata_kuliah_id" id="wide_mk_id" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm" required>
                <option value="">Pilih Mata Kuliah</option>
                @foreach($mataKuliahs as $mk)
                    <option value="{{ $mk['id'] }}">{{ $mk['kode'] }} — {{ $mk['nama'] }}</option>
                @endforeach
            </select>
            <select name="angkatan" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm" required>
                <option value="">Pilih Angkatan</option>
                @php $currentYear = date('Y'); @endphp
                @for($i = $currentYear + 1; $i >= $currentYear - 4; $i--)
                    <option value="{{ $i }}">{{ $i }}</option>
                @endfor
            </select>
            <input type="file" name="file" accept=".xlsx,.xls,.csv"
                class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#e8f6f7] file:text-[#0e7a86] hover:file:bg-[#d7e7e9] border border-slate-300 rounded-lg p-1" required>
            <div class="flex flex-wrap gap-2">
                <button type="submit" class="tsu-primary-button rounded-lg px-4 py-2 text-sm font-semibold">Import Wide</button>
                <button type="button" id="btn-dl-wide" class="tsu-secondary-button rounded-lg px-4 py-2 text-sm font-semibold">Download Template</button>
            </div>
        </form>
        {{-- Form khusus download template wide --}}
        <form id="form-template-wide" method="GET" action="{{ route('import.template-wide') }}">
            <input type="hidden" name="mata_kuliah_id" id="wide_template_mk_id" value="">
        </form>
    </section>

    <section class="tsu-card rounded-lg p-5">
        <div class="mb-4">
            <h3 class="font-semibold text-slate-900">Import Long</h3>
            <p class="mt-1 text-sm text-slate-500">Format baris: <code class="bg-slate-100 px-1 rounded text-xs">nim_mahasiswa | angkatan | jenis_penilaian | nilai</code></p>
            <p class="mt-1 text-xs text-slate-400">Satu baris = satu nilai untuk satu jenis penilaian.</p>
        </div>
        <form method="POST" action="{{ route('import.long') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <select name="mata_kuliah_id" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm" required>
                <option value="">Pilih Mata Kuliah</option>
                @foreach($mataKuliahs as $mk)
                    <option value="{{ $mk['id'] }}">{{ $mk['kode'] }} — {{ $mk['nama'] }}</option>
                @endforeach
            </select>
            <input type="file" name="file" accept=".xlsx,.xls,.csv"
                class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#e8f6f7] file:text-[#0e7a86] hover:file:bg-[#d7e7e9] border border-slate-300 rounded-lg p-1" required>
            <div class="flex flex-wrap gap-2">
                <button type="submit" class="tsu-primary-button rounded-lg px-4 py-2 text-sm font-semibold">Import Long</button>
                <a href="{{ route('import.template-long') }}" class="tsu-secondary-button rounded-lg px-4 py-2 text-sm font-semibold">Download Template</a>
            </div>
        </form>
    </section>
</div>

@push('scripts')
<script>
    // Sync mata_kuliah_id ke form template-wide saat dropdown berubah
    const wideSelect = document.getElementById('wide_mk_id');
    const templateInput = document.getElementById('wide_template_mk_id');
    const dlBtn = document.getElementById('btn-dl-wide');

    wideSelect.addEventListener('change', function () {
        templateInput.value = this.value;
    });

    dlBtn.addEventListener('click', function () {
        if (!wideSelect.value) {
            alert('Pilih Mata Kuliah terlebih dahulu sebelum download template!');
            return;
        }
        templateInput.value = wideSelect.value;
        document.getElementById('form-template-wide').submit();
    });
</script>
@endpush
@endsection
