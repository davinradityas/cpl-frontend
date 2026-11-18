<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8"><title>Laporan Matriks Bobot CPMK</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1e293b; }
.header { background: #0e7a86; color: white; padding: 12px 18px; }
.header h1 { font-size: 13px; font-weight: bold; }
.header p { font-size: 9px; opacity: 0.85; margin-top: 2px; }
.meta { padding: 8px 18px; background: #f7fbfb; border-bottom: 1px solid #d7e7e9; font-size: 8px; color: #64748b; }
.content { padding: 12px 18px; }
table { width: 100%; border-collapse: collapse; font-size: 8px; }
th { background: #e8f6f7; color: #0e7a86; font-weight: bold; text-align: center; padding: 6px 5px; border: 1px solid #d7e7e9; font-size: 8px; }
th.left { text-align: left; }
td { padding: 5px 6px; border: 1px solid #d7e7e9; vertical-align: middle; text-align: center; }
td.left { text-align: left; }
tr:nth-child(even) td { background: #f7fbfb; }
.ref-row td { background: #e0f5f7; font-weight: bold; color: #0e7a86; }
.total-row td { background: #e8f6f7; font-weight: bold; }
.text-green { color: #059669; }
.text-red { color: #dc2626; }
.footer { position: fixed; bottom: 0; left: 0; right: 0; background: #f7fbfb; border-top: 1px solid #d7e7e9; padding: 4px 18px; font-size: 7px; color: #94a3b8; }
</style>
</head>
<body>
<div class="header">
    <h1>LAPORAN MATRIKS BOBOT CPMK</h1>
    <p>{{ $mataKuliah['kode'] ?? '-' }} &mdash; {{ $mataKuliah['nama'] ?? '-' }} &bull; Jenis: {{ $mataKuliah['jenis_matkul']['nama'] ?? '-' }}</p>
</div>
<div class="meta">Mata Kuliah: <strong>{{ $mataKuliah['nama'] ?? '-' }}</strong> &bull; Dicetak: {{ $generatedAt }}</div>
<div class="footer"><span>Sistem CPL &mdash; Matriks Bobot CPMK</span><span>Dicetak: {{ $generatedAt }}</span></div>
<div class="content">
<table>
    <thead>
        <tr>
            <th class="left" rowspan="2" style="width:20%">CPMK</th>
            <th rowspan="2">CPL</th>
            @foreach($jenisPenilaians as $jp)
            <th>{{ $jp['nama'] }}</th>
            @endforeach
            <th>Total Bobot</th>
        </tr>
        <tr class="ref-row">
            @foreach($jenisPenilaians as $jp)
            @php $bj = collect($bobotJenisMk)->firstWhere('jenis_penilaian_id', $jp['id']); @endphp
            <td>(max: {{ $bj ? $bj['bobot'] : 0 }})</td>
            @endforeach
            <td>{{ collect($bobotJenisMk)->sum('bobot') }}</td>
        </tr>
    </thead>
    <tbody>
    @foreach($mappedCpmks as $cpmk)
    @php $bobotTeknik = collect($cpmk['bobot_teknik_cpmk'] ?? []); $rowTotal = $bobotTeknik->sum('bobot'); @endphp
    <tr>
        <td class="left"><strong>{{ $cpmk['kode'] }}</strong><br><span style="color:#64748b;font-size:7px">{{ \Illuminate\Support\Str::limit($cpmk['rumusan_cpmk'] ?? '', 60) }}</span></td>
        <td><span style="background:#e8f6f7;color:#0e7a86;padding:1px 5px;border-radius:99px;font-size:7px;font-weight:bold">{{ $cpmk['cpl']['kode'] ?? '-' }}</span></td>
        @foreach($jenisPenilaians as $jp)
        @php $btj = $bobotTeknik->firstWhere('jenis_penilaian_id', $jp['id']); $bv = $btj ? $btj['bobot'] : 0; @endphp
        <td class="{{ $bv > 0 ? 'text-green' : ' }}" style="{{ $bv == 0 ? 'color:#cbd5e1' : '' }}">{{ $bv > 0 ? $bv : '-' }}</td>
        @endforeach
        <td><strong>{{ $rowTotal }}</strong></td>
    </tr>
    @endforeach
    </tbody>
    <tfoot>
    <tr class="total-row">
        <td colspan="2" class="left"><strong>TOTAL PER KOLOM</strong></td>
        @foreach($jenisPenilaians as $jp)
        @php $total = $totalBobotPerJenis[$jp['id']] ?? 0; $bj = collect($bobotJenisMk)->firstWhere('jenis_penilaian_id', $jp['id']); $ref = $bj ? $bj['bobot'] : 0; $valid = abs($total - $ref) < 0.01; @endphp
        <td class="{{ $valid ? 'text-green' : 'text-red' }}">{{ $total }}@if(!$valid) <br><span style="font-size:7px">&#8800; {{ $ref }}</span>@endif</td>
        @endforeach
        <td><strong>{{ array_sum($totalBobotPerJenis) }}</strong></td>
    </tr>
    </tfoot>
</table>
</div>
</body></html>

