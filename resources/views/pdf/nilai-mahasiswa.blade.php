<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8"><title>Laporan Nilai Mahasiswa</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1e293b; }
.header { background: #0e7a86; color: white; padding: 12px 18px; }
.header h1 { font-size: 13px; font-weight: bold; }
.header p { font-size: 9px; opacity: 0.85; margin-top: 2px; }
.meta { padding: 8px 18px; background: #f7fbfb; border-bottom: 1px solid #d7e7e9; font-size: 8px; color: #64748b; }
.content { padding: 12px 18px; }
table { width: 100%; border-collapse: collapse; font-size: 8px; }
th { background: #e8f6f7; color: #0e7a86; font-weight: bold; text-align: center; padding: 6px 5px; border: 1px solid #d7e7e9; }
th.left { text-align: left; }
td { padding: 5px 6px; border: 1px solid #d7e7e9; vertical-align: middle; text-align: center; }
td.left { text-align: left; }
tr:nth-child(even) td { background: #f7fbfb; }
.footer { position: fixed; bottom: 0; left: 0; right: 0; background: #f7fbfb; border-top: 1px solid #d7e7e9; padding: 4px 18px; font-size: 7px; color: #94a3b8; }
</style>
</head>
<body>
<div class="header">
    <h1>LAPORAN NILAI MAHASISWA PER MATA KULIAH</h1>
    <p>{{ $mataKuliah['kode'] ?? '-' }} &mdash; {{ $mataKuliah['nama'] ?? '-' }} &bull; Angkatan {{ $angkatan }}</p>
</div>
<div class="meta">
    Mata Kuliah: <strong>{{ $mataKuliah['nama'] ?? '-' }}</strong> &bull;
    Kode: <strong>{{ $mataKuliah['kode'] ?? '-' }}</strong> &bull;
    Angkatan: <strong>{{ $angkatan }}</strong> &bull;
    Total Mahasiswa: <strong>{{ count($grid) }}</strong> &bull;
    Dicetak: {{ $generatedAt }}
</div>
<div class="footer"><span>Sistem CPL &mdash; Nilai Mahasiswa &bull; MK {{ $mataKuliah['kode'] ?? '' }}</span><span>Dicetak: {{ $generatedAt }}</span></div>
<div class="content">
<table>
    <thead>
        <tr>
            <th class="left" style="width:5px">No</th>
            <th class="left">NIM</th>
            <th class="left">Nama Mahasiswa</th>
            @foreach($jenisPenilaians as $jp)
            <th>{{ $jp['nama'] }}</th>
            @endforeach
            <th>Nilai MK</th>
        </tr>
    </thead>
    <tbody>
    @forelse($grid as $i => $row)
    <tr>
        <td>{{ $i + 1 }}</td>
        <td class="left">{{ $row['mahasiswa']['kode'] ?? '-' }}</td>
        <td class="left">{{ $row['mahasiswa']['nama'] ?? '-' }}</td>
        @foreach($jenisPenilaians as $jp)
        @php $nv = $row['nilai_per_jenis'][$jp['id']] ?? null; @endphp
        <td>{{ $nv !== null ? $nv : '-' }}</td>
        @endforeach
        <td><strong>{{ $row['nilai_mk'] > 0 ? number_format($row['nilai_mk'], 2) : '-' }}</strong></td>
    </tr>
    @empty
    <tr><td colspan="{{ count($jenisPenilaians) + 4 }}" style="text-align:center;padding:20px;color:#94a3b8">Belum ada data nilai untuk angkatan ini.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
</body></html>

