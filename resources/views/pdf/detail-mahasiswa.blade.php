<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8"><title>Laporan Detail Mahasiswa</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1e293b; }
.header { background: #0e7a86; color: white; padding: 14px 20px; }
.header h1 { font-size: 13px; font-weight: bold; }
.header p { font-size: 9px; opacity: 0.85; margin-top: 2px; }
.meta { padding: 10px 20px; background: #f7fbfb; border-bottom: 1px solid #d7e7e9; }
.meta table { font-size: 9px; }
.meta td { padding: 2px 8px 2px 0; color: #475569; }
.meta td:first-child { font-weight: bold; color: #0e7a86; width: 130px; }
.content { padding: 14px 20px; }
.section-title { font-size: 11px; font-weight: bold; color: #0e7a86; margin: 14px 0 6px; border-left: 3px solid #0e7a86; padding-left: 8px; }
.section-title-violet { font-size: 11px; font-weight: bold; color: #7c3aed; margin: 14px 0 6px; border-left: 3px solid #7c3aed; padding-left: 8px; }
table { width: 100%; border-collapse: collapse; font-size: 9px; }
th { background: #e8f6f7; color: #0e7a86; font-weight: bold; padding: 6px 8px; border: 1px solid #d7e7e9; text-align: left; }
th.th-violet { background: #ede9fe; color: #7c3aed; }
td { padding: 5px 8px; border: 1px solid #d7e7e9; }
tr:nth-child(even) td { background: #f7fbfb; }
.badge { display: inline-block; padding: 2px 7px; border-radius: 99px; font-size: 8px; font-weight: bold; }
.badge-success { background: #ecfdf5; color: #059669; }
.badge-warning { background: #fffbeb; color: #d97706; }
.badge-danger  { background: #fef2f2; color: #dc2626; }
.badge-info    { background: #eff6ff; color: #2563eb; }
.badge-slate   { background: #f1f5f9; color: #64748b; }
.two-col { display: table; width: 100%; margin-bottom: 8px; }
.col-half { display: table-cell; width: 50%; vertical-align: top; padding-right: 8px; }
.col-half:last-child { padding-right: 0; padding-left: 8px; }
.summary-box { background: #f7fbfb; border: 1px solid #d7e7e9; border-radius: 4px; padding: 8px 12px; margin-bottom: 8px; }
.summary-label { font-size: 8px; color: #64748b; text-transform: uppercase; }
.summary-value { font-size: 16px; font-weight: bold; color: #0e7a86; margin-top: 2px; }
.summary-value-violet { font-size: 16px; font-weight: bold; color: #7c3aed; margin-top: 2px; }
.footer { position: fixed; bottom: 0; left: 0; right: 0; background: #f7fbfb; border-top: 1px solid #d7e7e9; padding: 4px 20px; font-size: 7px; color: #94a3b8; display: flex; justify-content: space-between; }
</style>
</head>
<body>
<div class="header">
    <h1>LAPORAN DETAIL NILAI MAHASISWA</h1>
    <p>Sistem Capaian Pembelajaran Lulusan</p>
</div>
<div class="meta">
    <table>
        <tr><td>NIM</td><td>: {{ $mahasiswa['kode'] ?? '-' }}</td></tr>
        <tr><td>Nama</td><td>: {{ $mahasiswa['nama'] ?? '-' }}</td></tr>
        <tr><td>Angkatan</td><td>: {{ $mahasiswa['angkatan'] ?? '-' }}</td></tr>
        <tr><td>Kurikulum Prodi</td><td>: {{ $mahasiswa['kurikulum_prodi']['kode_kur_prodi'] ?? '-' }}</td></tr>
        <tr><td>Program Studi</td><td>: {{ $mahasiswa['kurikulum_prodi']['prodi']['nama'] ?? '-' }}</td></tr>
    </table>
</div>
<div class="footer">
    <span>Sistem CPL — {{ $mahasiswa['kode'] ?? '' }}</span>
    <span>Dicetak: {{ $generatedAt }}</span>
</div>
<div class="content">

@php
    $nilaiMhsMk = $mahasiswa['nilai_mhs_mk'] ?? [];
    $cpls       = $nilaiCplData['cpls']      ?? [];
    $rataRataTs = $nilaiCplData['rata_rata_ts'] ?? 0;
    $rataRataKs = $nilaiCplData['rata_rata_ks'] ?? 0;
@endphp

{{-- BAGIAN NILAI CPL --}}
@if(count($cpls) > 0)
<div class="section-title">Capaian Pembelajaran Lulusan (CPL)</div>

{{-- Summary rata-rata --}}
<div class="two-col">
    <div class="col-half">
        <div class="summary-box">
            <div class="summary-label">Rata-rata CPL (MK Terselenggara)</div>
            <div class="summary-value">{{ number_format($rataRataTs, 2) }}</div>
        </div>
    </div>
    <div class="col-half">
        <div class="summary-box" style="border-color:#ddd6fe;">
            <div class="summary-label" style="color:#7c3aed;">Rata-rata CPL (MK Keseluruhan)</div>
            <div class="summary-value-violet">{{ number_format($rataRataKs, 2) }}</div>
        </div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th rowspan="2" style="vertical-align:middle;width:60px">Kode CPL</th>
            <th rowspan="2" style="vertical-align:middle">Rumusan CPL</th>
            <th colspan="2" style="text-align:center">Nilai CPL</th>
            <th colspan="2" style="text-align:center">Status</th>
        </tr>
        <tr>
            <th style="text-align:center;width:80px">MK Terselenggara</th>
            <th class="th-violet" style="text-align:center;width:80px">MK Keseluruhan</th>
            <th style="text-align:center;width:90px">MK Terselenggara</th>
            <th class="th-violet" style="text-align:center;width:90px">MK Keseluruhan</th>
        </tr>
    </thead>
    <tbody>
    @foreach($cpls as $cpl)
        @php
            $sTs = $cpl['status_color'] ?? 'slate';
            $sKs = $cpl['status_keseluruhan_color'] ?? 'slate';
        @endphp
        <tr>
            <td style="font-weight:bold">{{ $cpl['kode'] ?? '-' }}</td>
            <td>{{ \Illuminate\Support\Str::limit($cpl['rumusan_cpl'] ?? '-', 100) }}</td>
            <td style="text-align:center;font-weight:bold;color:#0e7a86">{{ number_format((float)($cpl['nilai_cpl'] ?? 0), 2) }}</td>
            <td style="text-align:center;font-weight:bold;color:#7c3aed">{{ number_format((float)($cpl['nilai_cpl_keseluruhan'] ?? 0), 2) }}</td>
            <td style="text-align:center"><span class="badge badge-{{ $sTs }}">{{ $cpl['status'] ?? '-' }}</span></td>
            <td style="text-align:center"><span class="badge badge-{{ $sKs }}">{{ $cpl['status_keseluruhan'] ?? '-' }}</span></td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
        <tr style="background:#e8f6f7">
            <td colspan="2" style="font-weight:bold">Rata-rata</td>
            <td style="text-align:center;font-weight:bold;color:#0e7a86">{{ number_format($rataRataTs, 2) }}</td>
            <td style="text-align:center;font-weight:bold;color:#7c3aed">{{ number_format($rataRataKs, 2) }}</td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>
@endif

{{-- BAGIAN NILAI MK --}}
@if(count($nilaiMhsMk) > 0)
<div class="section-title" style="margin-top:18px">Nilai Akhir per Mata Kuliah</div>
<table>
    <thead>
        <tr>
            <th style="width:70px">Kode MK</th>
            <th>Nama Mata Kuliah</th>
            <th style="text-align:center;width:35px">Sem</th>
            <th style="text-align:center;width:30px">SKS</th>
            <th style="text-align:center;width:65px">Nilai MK</th>
        </tr>
    </thead>
    <tbody>
    @foreach($nilaiMhsMk as $nmk)
    <tr>
        <td>{{ $nmk['mata_kuliah']['kode'] ?? '-' }}</td>
        <td>{{ $nmk['mata_kuliah']['nama'] ?? '-' }}</td>
        <td style="text-align:center">{{ $nmk['mata_kuliah']['semester'] ?? '-' }}</td>
        <td style="text-align:center">{{ $nmk['mata_kuliah']['sks'] ?? '-' }}</td>
        <td style="text-align:center"><strong>{{ number_format($nmk['nilai'] ?? 0, 2) }}</strong></td>
    </tr>
    @endforeach
    </tbody>
</table>
@else
<p style="color:#94a3b8;text-align:center;padding:20px">Belum ada data nilai mata kuliah untuk mahasiswa ini.</p>
@endif

</div>
</body></html>
