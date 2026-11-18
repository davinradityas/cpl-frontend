<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

class NilaiMahasiswaController extends Controller
{
    public function __construct(
        protected ApiService $api
    ) {}

    public function selectMataKuliah(Request $request)
    {
        $kpResponse      = $this->api->getKurikulumProdis();
        $kurikulumProdis = ($kpResponse['success'] ?? false) ? $kpResponse['data'] : [];

        // Default ke kurikulum prodi pertama jika belum dipilih
        $selectedKurikulumProdiId = $request->query('kurikulum_prodi_id', '');
        if (empty($selectedKurikulumProdiId) && count($kurikulumProdis) > 0) {
            $selectedKurikulumProdiId = $kurikulumProdis[0]['id'];
        }

        $query = $selectedKurikulumProdiId ? ['kurikulum_prodi_id' => $selectedKurikulumProdiId] : [];
        $response    = $this->api->getMataKuliahs($query);
        $mataKuliahs = ($response['success'] ?? false) ? $response['data'] : [];

        return view('nilai-mahasiswa.select', compact(
            'mataKuliahs', 'kurikulumProdis', 'selectedKurikulumProdiId'
        ));
    }

    public function index(Request $request, string $mataKuliahId)
    {
        $angkatan = $request->query('angkatan');

        $response     = $this->api->getNilaiGrid($mataKuliahId, ['angkatan' => $angkatan]);
        $angkatanResp = $this->api->getAngkatanNilai($mataKuliahId);
        $mhsResp      = $this->api->getMahasiswas();

        if (!($response['success'] ?? false)) {
            return back()->with('error', 'Gagal mengambil data nilai mahasiswa.');
        }

        $data            = $response['data'];
        $mataKuliah      = $data['mata_kuliah'];
        $jenisPenilaians = $data['jenis_penilaians'];
        $grid            = $data['grid'];
        $bobotValid      = $data['bobot_valid'] ?? false;

        $angkatanList = ($angkatanResp['success'] ?? false) ? $angkatanResp['data'] : [];
        $mahasiswas   = isset($mhsResp['data']) && !isset($mhsResp['success']) ? $mhsResp['data'] : (isset($mhsResp['success']) && $mhsResp['success'] ? $mhsResp['data'] : []);

        return view('nilai-mahasiswa.index', compact(
            'mataKuliahId', 'mataKuliah',
            'jenisPenilaians', 'grid', 'angkatanList',
            'angkatan', 'mahasiswas', 'bobotValid'
        ));
    }

    public function store(Request $request, string $mataKuliahId)
    {
        $payload = $request->validate([
            'mahasiswa_id' => 'required',
            'angkatan'     => 'required|string',
            'nilai'        => 'required|array',
        ]);

        // Transform input [jenis_id => nilai] => flat array of objects
        $nilaiArray = [];
        foreach ($payload['nilai'] as $jenisId => $nilaiInput) {
            if ($nilaiInput !== null && $nilaiInput !== '') {
                $nilaiArray[] = [
                    'jenis_penilaian_id' => $jenisId,
                    'nilai'              => $nilaiInput,
                ];
            }
        }

        $apiPayload = [
            'mahasiswa_id' => $payload['mahasiswa_id'],
            'angkatan'     => $payload['angkatan'],
            'nilai'        => $nilaiArray,
        ];

        $response = $this->api->storeNilaiGranular($mataKuliahId, $apiPayload);

        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Gagal menyimpan nilai mahasiswa.');
        }

        return back()->with('success', 'Nilai mahasiswa berhasil disimpan.');
    }

    public function destroy(string $mataKuliahId, string $mahasiswaId)
    {
        $response = $this->api->deleteNilaiGranular($mataKuliahId, $mahasiswaId);

        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Gagal menghapus nilai mahasiswa.');
        }

        return back()->with('success', 'Nilai mahasiswa berhasil dihapus.');
    }
}
