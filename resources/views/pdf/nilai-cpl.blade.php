<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1e293b; background: #fff; }
    .header { background: #0e7a86; color: white; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; }
    .header h1 { font-size: 14px; font-weight: bold; }
    .header p { font-size: 9px; opacity: 0.85; }
    .meta { padding: 10px 20px; background: #f7fbfb; border-bottom: 1px solid #d7e7e9; font-size: 9px; color: #64748b; }
    .content { padding: 14px 20px; }
    table { width: 100%; border-collapse: collapse; font-size: 9px; }
    th { background: #e8f6f7; color: #0e7a86; font-weight: bold; text-transform: uppercase; letter-spacing: 0.04em; padding: 7px 8px; text-align: left; border: 1px solid #d7e7e9; }
    td { padding: 6px 8px; border: 1px solid #d7e7e9; vertical-align: middle; }
    tr:nth-child(even) td { background: #f7fbfb; }
    .badge { display: inline-block; padding: 2px 7px; border-radius: 99px; font-size: 8px; font-weight: bold; }
    .badge-success { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
    .badge-warning { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
    .badge-danger  { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .badge-slate   { background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; }
    .badge-teal    { background: #e8f6f7; color: #0e7a86; border: 1px solid #d7e7e9; }
    .section-title { font-size: 11px; font-weight: bold; color: #0e7a86; margin: 14px 0 6px; border-left: 3px solid #0e7a86; padding-left: 8px; }
    .footer { position: fixed; bottom: 0; left: 0; right: 0; background: #f7fbfb; border-top: 1px solid #d7e7e9; padding: 5px 20px; font-size: 8px; color: #94a3b8; display: flex; justify-content: space-between; }
    .tfoot-row td { background: #e8f6f7; font-weight: bold; color: #0e7a86; }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .text-green { color: #059669; font-weight: bold; }
    .text-red { color: #dc2626; font-weight: bold; }
    .nilai-big { font-size: 12px; font-weight: bold; }
    .cpl-block { margin-bottom: 16px; }
    .cpl-header { background: #0e7a86; color: white; padding: 8px 12px; border-radius: 4px 4px 0 0; }
    .cpl-header .kode { font-size: 13px; font-weight: bold; }
    .cpl-header .rumusan { font-size: 9px; opacity: 0.9; margin-top: 2px; }
    .nilai-summary { display: flex; background: #e8f6f7; padding: 8px 12px; border: 1px solid #d7e7e9; gap: 20px; }
    .nilai-box { flex: 1; }
    .nilai-box .label { font-size: 8px; color: #64748b; text-transform: uppercase; }
    .nilai-box .value { font-size: 16px; font-weight: bold; color: #0e7a86; }
    .nilai-box .status { margin-top: 2px; }
</style><!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Laporan Nilai CPL</title></head>
<body>
<div class="header">
    <div>
        <h1>LAPORAN NILAI CPL</h1>
        <p>Tiga Serangkai University &mdash; Sistem Capaian Pembelajaran Lulusan</p>
    </div>
    <div style="text-align:right">
        <p>{{ $selectedKp['kode_kur_prodi'] ?? '-' }} &ndash; {{ $selectedKp['prodi']['nama'] ?? '-' }}</p>
        <p>Angkatan {{ $angkatan }}</p>
    </div>
</div>

<div class="meta">
    Kurikulum Prodi: <strong>{{ $selectedKp['kode_kur_prodi'] ?? '-' }}</strong> &bull;
    Program Studi: <strong>{{ $selectedKp['prodi']['nama'] ?? '-' }}</strong> &bull;
    Angkatan: <strong>{{ $angkatan }}</strong> &bull;
    Dicetak: {{ $generatedAt }}
</div>

<div class="footer">
    <span>Sistem CPL &mdash; {{ $selectedKp['kode_kur_prodi'] ?? '' }} &bull; Angkatan {{ $angkatan }}</span>
    <span>Dicetak: {{ $generatedAt }}</span>
</div>

<div class="content">
@foreach($items as $item)
@php $cpl = $item['cpl']; $colorMap = ['success' => 'success', 'warning' => 'warning', 'danger' => 'danger', 'slate' => 'slate']; @endphp
<div class="cpl-block">
    <div class="cpl-header">
        <div class="kode">{{ $cpl['kode'] }}</div>
        <div class="rumusan">{{ $cpl['rumusan_cpl'] }}</div>
    </div>
    <div class="nilai-summary">
        <div class="nilai-box">
            <div class="label">Nilai CPL (MK Terselenggara)</div>
            <div class="value">{{ number_format($cpl['nilai_cpl'], 2) }}</div>
            <div class="status"><span class="badge badge-{{ $cpl['status_color'] }}">{{ $cpl['status'] }}</span></div>
        </div>
        <div class="nilai-box">
            <div class="label">Nilai CPL (MK Keseluruhan)</div>
            <div class="value">{{ number_format($cpl['nilai_cpl_keseluruhan'], 2) }}</div>
            <div class="status"><span class="badge badge-{{ $cpl['status_keseluruhan_color'] }}">{{ $cpl['status_keseluruhan'] }}</span></div>
        </div>
    </div>

    @if(count($item['breakdown_terselenggara']) > 0)
    <div class="section-title">Rincian MK Terselenggara</div>
    <table>
        <thead><tr>
            <th>Mata Kuliah</th><th class="text-center">SKS</th>
            <th class="text-center">Nilai MK</th><th class="text-center">Bobot CPL</th><th class="text-center">Kontribusi</th>
        </tr></thead>
        <tbody>
        @foreach($item['breakdown_terselenggara'] as $bd)
        <tr>
            <td>{{ $bd['kode_mk'] ?? '-' }} — {{ $bd['nama_mk'] ?? '-' }}</td>
            <td class="text-center">{{ $bd['sks'] ?? 0 }}</td>
            <td class="text-center">{{ number_format($bd['avg_nilai'] ?? 0, 2) }}</td>
            <td class="text-center">{{ round((float)($bd['bobot'] ?? 0) * 100, 1) }}%</td>
            <td class="text-center">{{ number_format(($bd['avg_nilai'] ?? 0) * ($bd['bobot'] ?? 0), 2) }}</td>
        </tr>
        @endforeach
        </tbody>
        <tfoot><tr class="tfoot-row">
            <td><strong>Total</strong></td>
            <td class="text-center">{{ $item['totals_terselenggara']['sum_sks'] }}</td>
            <td colspan="2"></td>
            <td class="text-center">{{ number_format($item['totals_terselenggara']['nilai_cpl'], 2) }}</td>
        </tr></tfoot>
    </table>
    @endif
</div>
@endforeach
</div>
</body></html>

