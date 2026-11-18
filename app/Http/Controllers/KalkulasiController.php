<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

class KalkulasiController extends Controller
{
    public function __construct(
        protected ApiService $api
    ) {}

    public function index()
    {
        return view('kalkulasi.index', [
            'kurikulumProdis' => $this->data($this->api->getKurikulumProdis()),
            'cpls' => $this->data($this->api->getCpls()),
            'mahasiswas' => $this->data($this->api->getMahasiswas()),
        ]);
    }

    public function recalculate(Request $request)
    {
        $payload = $request->validate([
            'kurikulum_prodi_id' => ['required'],
        ]);

        $response = $this->api->recalculate($payload);

        return $this->backWithResult($response, 'Hasil Recalculate');
    }

    public function angkatan(Request $request)
    {
        $payload = $request->validate([
            'kurikulum_prodi_id' => ['required'],
            'angkatan' => ['required', 'string'],
            'threshold' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $response = $this->api->getKetercapaianAngkatan($payload);

        return $this->backWithResult($response, 'Ketercapaian Angkatan');
    }

    public function detailCpl(Request $request)
    {
        $payload = $request->validate([
            'cpl_id' => ['required'],
        ]);

        $response = $this->api->detailKalkulasiCpl($payload['cpl_id']);

        return $this->backWithResult($response, 'Detail CPL');
    }

    public function mahasiswa(Request $request)
    {
        $payload = $request->validate([
            'mahasiswa_id' => ['required'],
            'kurikulum_prodi_id' => ['required'],
        ]);

        $response = $this->api->getNilaiCplMahasiswa($payload['mahasiswa_id'], [
            'kurikulum_prodi_id' => $payload['kurikulum_prodi_id'],
        ]);

        return $this->backWithResult($response, 'Nilai CPL Mahasiswa');
    }

    private function backWithResult(array $response, string $title)
    {
        if (!($response['success'] ?? false)) {
            return back()->withInput()->with('error', $response['message'] ?? 'Kalkulasi gagal.');
        }

        return back()
            ->with('success', $response['message'] ?? 'Kalkulasi selesai.')
            ->with('kalkulasi_title', $title)
            ->with('kalkulasi_result', $response['data'] ?? []);
    }

    private function data(array $response): array
    {
        if (array_key_exists('success', $response)) {
            return ($response['success'] ?? false) ? ($response['data'] ?? []) : [];
        }

        return $response['data'] ?? [];
    }
}
