<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

class MataKuliahCpmkController extends Controller
{
    public function __construct(
        protected ApiService $api
    ) {}

    /**
     * Halaman utama: Mapping CPMK + Bobot Jenis + Bobot CPMK per Jenis
     */
    public function index(string $mataKuliahId)
    {
        $mappingResp    = $this->api->getMkCpmkMapping($mataKuliahId);
        $bobotJenisResp = $this->api->getBobotJenisMk($mataKuliahId);

        if (!($mappingResp['success'] ?? false)) {
            return back()->with('error', 'Gagal mengambil data mapping CPMK.');
        }

        $mataKuliah          = $mappingResp['data']['mata_kuliah'];
        $mappedCpmks         = $mappingResp['data']['mapped_cpmks'];
        $availableCpmks      = $mappingResp['data']['available_cpmks'];
        $jenisPenilaians     = $mappingResp['data']['jenis_penilaians'];
        $bobotJenisMk        = $mappingResp['data']['bobot_jenis_mk'] ?? [];
        $totalBobotJenisMk   = $mappingResp['data']['total_bobot_jenis_mk'] ?? 0;
        $bobotJenisMkValid   = $mappingResp['data']['bobot_jenis_mk_valid'] ?? false;
        $totalBobotPerJenis  = $mappingResp['data']['total_bobot_per_jenis'] ?? [];

        return view('mk-cpmk.index', compact(
            'mataKuliahId', 'mataKuliah',
            'mappedCpmks', 'availableCpmks',
            'jenisPenilaians', 'bobotJenisMk',
            'totalBobotJenisMk', 'bobotJenisMkValid',
            'totalBobotPerJenis'
        ));
    }

    /**
     * Tambahkan CPMK ke Mapping Mata Kuliah (tanpa bobot — otomatis)
     */
    public function store(Request $request, string $mataKuliahId)
    {
        $payload = $request->validate([
            'cpmk_id' => 'required',
        ]);

        $response = $this->api->storeMkCpmkMapping($mataKuliahId, $payload);

        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Gagal menambahkan CPMK ke Mata Kuliah.');
        }

        return back()->with('success', 'CPMK berhasil ditambahkan ke Mata Kuliah.');
    }

    /**
     * Lepaskan CPMK dari Mata Kuliah
     */
    public function destroy(string $mataKuliahId, string $cpmkId)
    {
        $response = $this->api->deleteMkCpmkMapping($mataKuliahId, $cpmkId);

        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Gagal melepas CPMK dari Mata Kuliah.');
        }

        return back()->with('success', 'CPMK berhasil dilepas dari Mata Kuliah.');
    }

    /**
     * Simpan Bobot Jenis Penilaian per MK (Lapis 1)
     * total semua jenis dalam 1 MK = 100
     */
    public function storeBobotJenis(Request $request, string $mataKuliahId)
    {
        $payload = $request->validate([
            'bobots'                      => 'required|array',
            'bobots.*.jenis_penilaian_id' => 'required',
            'bobots.*.bobot'              => 'required|numeric|min:0',
        ]);

        $response = $this->api->storeBobotJenisMk($mataKuliahId, $payload);

        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Gagal menyimpan bobot jenis penilaian.');
        }

        return back()->with('success', 'Bobot jenis penilaian berhasil disimpan.');
    }

    /**
     * Simpan Bobot CPMK per Jenis Penilaian (Lapis 2 — matriks, semua CPMK sekaligus)
     * validasi: total per kolom = bobot jenis MK
     */
    public function storeBobotTeknik(Request $request, string $mataKuliahId)
    {
        $payload = $request->validate([
            'bobots'                      => 'required|array',
            'bobots.*.cpmk_id'            => 'required',
            'bobots.*.jenis_penilaian_id' => 'required',
            'bobots.*.bobot'              => 'required|numeric|min:0',
        ]);

        $response = $this->api->storeBobotTeknikCpmk($mataKuliahId, $payload);

        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Gagal menyimpan bobot CPMK per jenis penilaian.');
        }

        return back()->with('success', 'Bobot CPMK per jenis penilaian berhasil disimpan.');
    }
}
