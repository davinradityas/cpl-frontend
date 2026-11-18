<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

class DetailMahasiswaController extends Controller
{
    public function __construct(
        protected ApiService $api
    ) {}

    public function show($id)
    {
        // Ambil data mahasiswa
        $mahasiswaResp = $this->api->get("/mahasiswas/{$id}");

        if (!isset($mahasiswaResp['data']) || isset($mahasiswaResp['message'])) {
            return redirect()->route('mahasiswas.index')
                ->with('error', $mahasiswaResp['message'] ?? 'Mahasiswa tidak ditemukan.');
        }

        $mahasiswa = $mahasiswaResp['data'];

        // Ambil nilai CPL mahasiswa (non-fatal: halaman tetap tampil walau gagal)
        $cplResp = $this->api->get("/kalkulasi/mahasiswa/{$id}");
        $cplData = ($cplResp['success'] ?? false) ? ($cplResp['data'] ?? []) : [];

        return view('mahasiswas.show', [
            'mahasiswa'    => $mahasiswa,
            'nilaiCplData' => $cplData,
        ]);
    }
}
