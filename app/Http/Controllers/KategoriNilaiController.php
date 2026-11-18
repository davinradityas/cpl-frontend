<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class KategoriNilaiController extends Controller
{
    public function __construct(protected ApiService $api) {}

    // =========================================================================
    // HALAMAN UTAMA — tampilkan global + override dalam satu view
    // =========================================================================

    public function index()
    {
        // Ambil predikat global
        $globalResp = $this->api->getKategoriNilais();
        $globalData = ($globalResp['success'] ?? false) ? ($globalResp['data'] ?? []) : [];

        // Ambil daftar prodi untuk selector override
        $prodiResp = $this->api->getProdis();
        $prodis    = ($prodiResp['success'] ?? false) ? ($prodiResp['data'] ?? []) : [];

        return view('kategori-nilai.index', compact('globalData', 'prodis'));
    }

    // =========================================================================
    // CRUD GLOBAL
    // =========================================================================

    public function store(Request $request)
    {
        $response = $this->api->storeKategoriNilai($request->only([
            'nama', 'batas_bawah', 'warna',
        ]));

        $this->flashFromResponse($response, 'Predikat global berhasil ditambahkan.', 'Gagal menambahkan predikat.');
        return redirect()->route('kategori-nilais.index');
    }

    public function update(Request $request, string $id)
    {
        $response = $this->api->updateKategoriNilai($id, $request->only([
            'nama', 'batas_bawah', 'warna',
        ]));

        $this->flashFromResponse($response, 'Predikat global berhasil diperbarui.', 'Gagal memperbarui predikat.');
        return redirect()->route('kategori-nilais.index');
    }

    public function destroy(string $id)
    {
        $response = $this->api->deleteKategoriNilai($id);
        $this->flashFromResponse($response, 'Predikat global berhasil dihapus.', 'Gagal menghapus predikat.');
        return redirect()->route('kategori-nilais.index');
    }

    // =========================================================================
    // AJAX — Override per Angkatan + Prodi
    // =========================================================================

    /**
     * GET /kategori-nilais/overrides?prodi_id=X&angkatan=Y
     * Kembalikan JSON override (atau empty + global sebagai referensi).
     */
    public function getOverrides(Request $request)
    {
        $prodiId  = $request->query('prodi_id');
        $angkatan = $request->query('angkatan');

        if (!$prodiId || !$angkatan) {
            return response()->json(['success' => false, 'message' => 'Parameter tidak lengkap.'], 422);
        }

        $response = $this->api->getKategoriNilaiOverrides($prodiId, $angkatan);
        return response()->json($response);
    }

    /**
     * GET /kategori-nilais/angkatans?prodi_id=X
     */
    public function getAngkatans(Request $request)
    {
        $prodiId = $request->query('prodi_id');

        if (!$prodiId) {
            return response()->json(['success' => false, 'message' => 'Parameter prodi_id wajib.'], 422);
        }

        $response = $this->api->getAngkatansByProdi($prodiId);
        return response()->json($response);
    }

    /**
     * POST /kategori-nilais/overrides
     */
    public function storeOverride(Request $request)
    {
        $response = $this->api->storeKategoriNilaiOverride($request->all());
        return response()->json($response, ($response['success'] ?? false) ? 201 : 422);
    }

    /**
     * PUT /kategori-nilais/overrides/{id}
     */
    public function updateOverride(Request $request, string $id)
    {
        $response = $this->api->updateKategoriNilaiOverride($id, $request->all());
        return response()->json($response, ($response['success'] ?? false) ? 200 : 422);
    }

    /**
     * DELETE /kategori-nilais/overrides/{id}
     */
    public function destroyOverride(string $id)
    {
        $response = $this->api->deleteKategoriNilaiOverride($id);
        return response()->json($response, ($response['success'] ?? false) ? 200 : 422);
    }

    /**
     * DELETE /kategori-nilais/overrides/reset
     */
    public function resetOverride(Request $request)
    {
        $prodiId  = $request->input('prodi_id');
        $angkatan = $request->input('angkatan');
        $response = $this->api->resetKategoriNilaiOverride($prodiId, $angkatan);
        return response()->json($response, ($response['success'] ?? false) ? 200 : 422);
    }

    // =========================================================================
    // HELPER
    // =========================================================================

    private function flashFromResponse(array $response, string $successMsg, string $failMsg): void
    {
        if ($response['success'] ?? false) {
            Session::flash('success', $response['message'] ?? $successMsg);
        } else {
            Session::flash('error', $response['message'] ?? $failMsg);
        }
    }
}
