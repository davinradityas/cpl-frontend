@extends('layouts.app')

@section('content')
@php $title = 'Kalkulasi CPL'; @endphp
<div class="mb-6">
    <h2 class="text-xl font-bold text-slate-900">Kalkulasi</h2>
    <p class="mt-1 text-sm text-slate-500">Jalankan perhitungan CPL, lihat detail CPL, dan cek ketercapaian angkatan atau mahasiswa.</p>
</div>

@if(session('kalkulasi_result'))
    <section class="mb-4 rounded-lg border border-[#b7dfe3] bg-[#e8f6f7] p-4 text-sm text-[#102a2f]">
        <h3 class="font-semibold">{{ session('kalkulasi_title') }}</h3>
        <pre class="mt-3 overflow-x-auto rounded-lg bg-white p-4 text-xs text-slate-800">{{ json_encode(session('kalkulasi_result'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </section>
@endif

<div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
    <section class="tsu-card rounded-lg p-5">
        <h3 class="font-semibold text-slate-900">Recalculate CPL</h3>
        <form method="POST" action="{{ route('kalkulasi.recalculate') }}" class="mt-4 space-y-3">
            @csrf
            <select name="kurikulum_prodi_id" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm" required>
                <option value="">Pilih Kurikulum Prodi</option>
                @foreach($kurikulumProdis as $kp)
                    <option value="{{ $kp['id'] }}">{{ $kp['kode_kur_prodi'] ?? $kp['id'] }}</option>
                @endforeach
            </select>
            <button class="tsu-primary-button rounded-lg px-4 py-2 text-sm font-semibold">Jalankan</button>
        </form>
    </section>

    <section class="tsu-card rounded-lg p-5">
        <h3 class="font-semibold text-slate-900">Ketercapaian Angkatan</h3>
        <form method="POST" action="{{ route('kalkulasi.angkatan') }}" class="mt-4 space-y-3">
            @csrf
            <select name="kurikulum_prodi_id" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm" required>
                <option value="">Pilih Kurikulum Prodi</option>
                @foreach($kurikulumProdis as $kp)
                    <option value="{{ $kp['id'] }}">{{ $kp['kode_kur_prodi'] ?? $kp['id'] }}</option>
                @endforeach
            </select>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <input name="angkatan" placeholder="Angkatan" class="rounded-lg border border-slate-300 px-3 py-2.5 text-sm" required>
                <input name="threshold" type="number" value="75" min="0" max="100" class="rounded-lg border border-slate-300 px-3 py-2.5 text-sm" required>
            </div>
            <button class="tsu-primary-button rounded-lg px-4 py-2 text-sm font-semibold">Hitung</button>
        </form>
    </section>

    <section class="tsu-card rounded-lg p-5">
        <h3 class="font-semibold text-slate-900">Detail CPL</h3>
        <form method="POST" action="{{ route('kalkulasi.detail-cpl') }}" class="mt-4 space-y-3">
            @csrf
            <select name="cpl_id" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm" required>
                <option value="">Pilih CPL</option>
                @foreach($cpls as $cpl)
                    <option value="{{ $cpl['id'] }}">{{ $cpl['kode'] }} - {{ $cpl['rumusan_cpl'] ?? '' }}</option>
                @endforeach
            </select>
            <button class="tsu-primary-button rounded-lg px-4 py-2 text-sm font-semibold">Lihat Detail</button>
        </form>
    </section>

    <section class="tsu-card rounded-lg p-5">
        <h3 class="font-semibold text-slate-900">Nilai CPL Mahasiswa</h3>
        <form method="POST" action="{{ route('kalkulasi.mahasiswa') }}" class="mt-4 space-y-3">
            @csrf
            <select name="mahasiswa_id" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm" required>
                <option value="">Pilih Mahasiswa</option>
                @foreach($mahasiswas as $mhs)
                    <option value="{{ $mhs['id'] }}">{{ $mhs['kode'] }} - {{ $mhs['nama'] ?? '' }}</option>
                @endforeach
            </select>
            <select name="kurikulum_prodi_id" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm" required>
                <option value="">Pilih Kurikulum Prodi</option>
                @foreach($kurikulumProdis as $kp)
                    <option value="{{ $kp['id'] }}">{{ $kp['kode_kur_prodi'] ?? $kp['id'] }}</option>
                @endforeach
            </select>
            <button class="tsu-primary-button rounded-lg px-4 py-2 text-sm font-semibold">Hitung Mahasiswa</button>
        </form>
    </section>
</div>
@endsection



