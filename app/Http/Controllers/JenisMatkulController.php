<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

class JenisMatkulController extends Controller
{
    public function __construct(protected ApiService $api) {}

    public function index()
    {
        $response = $this->api->getJenisMatkulis();

        if (!($response['success'] ?? false)) {
            return back()->with('error', 'Gagal mengambil data Jenis Matkul.');
        }

        $jenisMatkulis  = $response['data']['jenis_matkulis'] ?? [];
        $jenisPenilaians = $response['data']['jenis_penilaians'] ?? [];

        return view('jenis-matkul.index', compact('jenisMatkulis', 'jenisPenilaians'));
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'nama'                        => 'required|string',
            'keterangan'                  => 'nullable|string',
            'bobots'                      => 'required|array',
            'bobots.*.jenis_penilaian_id' => 'required',
            'bobots.*.bobot'              => 'required|numeric|min:0',
        ]);

        $response = $this->api->storeJenisMatkul($payload);

        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Gagal menyimpan Jenis Matkul.');
        }

        return back()->with('success', 'Jenis Matkul berhasil ditambahkan.');
    }

    public function update(Request $request, string $id)
    {
        $payload = $request->validate([
            'nama'                        => 'required|string',
            'keterangan'                  => 'nullable|string',
            'bobots'                      => 'required|array',
            'bobots.*.jenis_penilaian_id' => 'required',
            'bobots.*.bobot'              => 'required|numeric|min:0',
        ]);

        $response = $this->api->updateJenisMatkul($id, $payload);

        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Gagal memperbarui Jenis Matkul.');
        }

        return back()->with('success', 'Jenis Matkul berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $response = $this->api->deleteJenisMatkul($id);

        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Gagal menghapus Jenis Matkul.');
        }

        return back()->with('success', 'Jenis Matkul berhasil dihapus.');
    }
}
