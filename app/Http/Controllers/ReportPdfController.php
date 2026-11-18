<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportPdfController extends Controller
{
    public function __construct(protected ApiService $api) {}

    // 1. Laporan Nilai CPL per Angkatan
    public function nilaiCpl(Request $request)
    {
        $kpId  = $request->query('kurikulum_prodi_id');
        $angk  = $request->query('angkatan');
        $cplId = $request->query('cpl_id', 'all');

        abort_if(!$kpId || !$angk, 400, 'Parameter kurikulum_prodi_id dan angkatan wajib diisi.');

        $kurikulumProdis = $this->dataOf($this->api->getKurikulumProdis());
        $selectedKp      = collect($kurikulumProdis)->firstWhere('id', $kpId);

        $resp = $this->api->getNilaiCplPerAngkatan([
            'kurikulum_prodi_id' => $kpId,
            'angkatan'           => $angk,
        ]);
        abort_if(!($resp['success'] ?? false), 500, 'Gagal mengambil data nilai CPL.');

        $cplsData = $resp['data']['cpls'] ?? [];
        if ($cplId !== 'all') {
            $cplsData = collect($cplsData)->filter(fn($c) => (string)($c['cpl_id'] ?? '') === (string)$cplId)->values()->all();
        }

        $items = [];
        foreach ($cplsData as $cplData) {
            $breakdownTs = collect($cplData['breakdown_terselenggara'] ?? []);
            $breakdownKs = collect($cplData['breakdown_keseluruhan'] ?? []);
            $nilaiTs     = (float) ($cplData['nilai_cpl'] ?? 0);
            $nilaiKs     = (float) ($cplData['nilai_cpl_keseluruhan'] ?? 0);
            $items[] = [
                'cpl' => [
                    'kode'                     => $cplData['kode'] ?? '-',
                    'rumusan_cpl'              => $cplData['rumusan_cpl'] ?? '-',
                    'nilai_cpl'                => $nilaiTs,
                    'nilai_cpl_keseluruhan'    => $nilaiKs,
                    'status'                   => $cplData['status'] ?? '-',
                    'status_color'             => $cplData['status_color'] ?? 'slate',
                    'status_keseluruhan'       => $cplData['status_keseluruhan'] ?? '-',
                    'status_keseluruhan_color' => $cplData['status_keseluruhan_color'] ?? 'slate',
                ],
                'breakdown_terselenggara' => $breakdownTs->all(),
                'breakdown_keseluruhan'   => $breakdownKs->all(),
                'totals_terselenggara'    => ['sum_sks' => $breakdownTs->sum('sks'), 'nilai_cpl' => $nilaiTs],
                'totals_keseluruhan'      => ['sum_sks' => $breakdownKs->sum('sks'), 'nilai_cpl' => $nilaiKs],
            ];
        }

        $pdf = Pdf::loadView('pdf.nilai-cpl', [
            'items'        => $items,
            'selectedKp'   => $selectedKp,
            'angkatan'     => $angk,
            'generatedAt'  => now()->format('d/m/Y H:i'),
        ])->setPaper('A4', 'landscape');

        $filename = 'Laporan_Nilai_CPL_' . ($selectedKp['kode_kur_prodi'] ?? 'KP') . '_Angkatan_' . $angk . '.pdf';
        return $pdf->download($filename);
    }

    // 2. Laporan Nilai Mahasiswa per Mata Kuliah
    public function nilaiMahasiswa(Request $request, string $mataKuliahId)
    {
        $angk = $request->query('angkatan');
        abort_if(!$angk, 400, 'Parameter angkatan wajib diisi.');

        $mkResp = $this->api->get('/mata-kuliahs/' . $mataKuliahId);
        $mataKuliah = $mkResp['data'] ?? [];

        $nilaiResp = $this->api->getNilaiGrid($mataKuliahId, ['angkatan' => $angk]);
        $grid = $nilaiResp['data']['grid'] ?? $nilaiResp['data'] ?? [];

        $jenisPenilaians = $this->dataOf($this->api->getJenisPenilaians());

        $pdf = Pdf::loadView('pdf.nilai-mahasiswa', [
            'mataKuliah'      => $mataKuliah,
            'grid'            => $grid,
            'jenisPenilaians' => $jenisPenilaians,
            'angkatan'        => $angk,
            'generatedAt'     => now()->format('d/m/Y H:i'),
        ])->setPaper('A4', 'landscape');

        $filename = 'Laporan_Nilai_MK_' . ($mataKuliah['kode'] ?? $mataKuliahId) . '_Angkatan_' . $angk . '.pdf';
        return $pdf->download($filename);
    }

    // 3. Laporan Matriks Bobot CPMK
    public function matriksBobot(Request $request, string $mataKuliahId)
    {
        $mappingResp     = $this->api->getMkCpmkMapping($mataKuliahId);
        abort_if(!($mappingResp['success'] ?? false), 500, 'Gagal mengambil data mapping.');

        $mataKuliah      = $mappingResp['data']['mata_kuliah'] ?? [];
        $mappedCpmks     = $mappingResp['data']['mapped_cpmks'] ?? [];
        $bobotJenisMk    = $mappingResp['data']['bobot_jenis_mk'] ?? [];
        $jenisPenilaians = $mappingResp['data']['jenis_penilaians'] ?? [];

        $totalBobotPerJenis = [];
        foreach ($jenisPenilaians as $jp) {
            $totalBobotPerJenis[$jp['id']] = collect($mappedCpmks)->reduce(function ($carry, $cpmk) use ($jp) {
                $bt = collect($cpmk['bobot_teknik_cpmk'] ?? [])->firstWhere('jenis_penilaian_id', $jp['id']);
                return $carry + (float) ($bt['bobot'] ?? 0);
            }, 0);
        }

        $pdf = Pdf::loadView('pdf.matriks-bobot', [
            'mataKuliah'         => $mataKuliah,
            'mappedCpmks'        => $mappedCpmks,
            'bobotJenisMk'       => $bobotJenisMk,
            'jenisPenilaians'    => $jenisPenilaians,
            'totalBobotPerJenis' => $totalBobotPerJenis,
            'generatedAt'        => now()->format('d/m/Y H:i'),
        ])->setPaper('A4', 'landscape');

        $filename = 'Laporan_Matriks_Bobot_' . ($mataKuliah['kode'] ?? $mataKuliahId) . '.pdf';
        return $pdf->download($filename);
    }

    // 4. Laporan Detail Nilai Mahasiswa
    public function detailMahasiswa(Request $request, string $mahasiswaId)
    {
        $resp      = $this->api->get('/mahasiswas/' . $mahasiswaId);
        $mahasiswa = $resp['data'] ?? null;
        abort_if(!$mahasiswa, 404, 'Mahasiswa tidak ditemukan.');

        // Ambil nilai CPL mahasiswa
        $cplResp = $this->api->get('/kalkulasi/mahasiswa/' . $mahasiswaId);
        $cplData = ($cplResp['success'] ?? false) ? ($cplResp['data'] ?? []) : [];

        $pdf = Pdf::loadView('pdf.detail-mahasiswa', [
            'mahasiswa'    => $mahasiswa,
            'nilaiCplData' => $cplData,
            'generatedAt'  => now()->format('d/m/Y H:i'),
        ])->setPaper('A4', 'portrait');

        $kode     = $mahasiswa['kode'] ?? $mahasiswaId;
        $filename = 'Laporan_Detail_Mahasiswa_' . $kode . '.pdf';
        return $pdf->download($filename);
    }

    private function dataOf(array $response): array
    {
        return ($response['success'] ?? false) ? ($response['data'] ?? []) : [];
    }
}
