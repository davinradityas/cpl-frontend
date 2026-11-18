<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

class CpmkController extends Controller
{
    public function __construct(
        protected ApiService $api
    ) {}

    /**
     * Halaman Master Data CPMK
     */
    public function index(Request $request)
    {
        // Ambil daftar kurikulum prodi sekali saja
        $kpResponse      = $this->api->getKurikulumProdis();
        $kurikulumProdis = ($kpResponse['success'] ?? false) ? $kpResponse['data'] : [];

        // Default ke kurikulum prodi pertama jika belum dipilih
        $selectedKurikulumProdiId = $request->query('kurikulum_prodi_id', '');
        if (empty($selectedKurikulumProdiId) && count($kurikulumProdis) > 0) {
            $selectedKurikulumProdiId = $kurikulumProdis[0]['id'];
        }

        $query = $selectedKurikulumProdiId ? ['kurikulum_prodi_id' => $selectedKurikulumProdiId] : [];

        $response      = $this->api->getCpmks($query);
        $cplsResponse  = $this->api->getCpls($query);

        $cpmks = ($response['success'] ?? false) ? $response['data'] : [];
        $cpls  = ($cplsResponse['success'] ?? false) ? $cplsResponse['data'] : [];

        return view('cpmk.index', compact(
            'cpmks', 'kurikulumProdis', 'cpls', 'selectedKurikulumProdiId'
        ));
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'kurikulum_prodi_id' => 'required',
            'cpl_id'             => 'required',
            'kode'               => 'required|string|max:20',
            'rumusan_cpmk'          => 'required|string',
        ]);

        $response = $this->api->storeCpmk($payload);

        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Gagal menambahkan CPMK.')->withInput();
        }

        return back()->with('success', 'CPMK berhasil ditambahkan.');
    }

    public function update(Request $request, string $id)
    {
        $payload = $request->validate([
            'cpl_id'    => 'required',
            'kode'      => 'required|string|max:20',
            'rumusan_cpmk' => 'required|string',
        ]);

        $response = $this->api->updateCpmk($id, $payload);

        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Gagal memperbarui CPMK.');
        }

        return back()->with('success', 'CPMK berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $response = $this->api->deleteCpmk($id);

        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Gagal menghapus CPMK.');
        }

        return back()->with('success', 'CPMK berhasil dihapus.');
    }
}
