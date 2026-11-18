<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function __construct(
        protected ApiService $api
    ) {}

    public function index()
    {
        $mataKuliahs = $this->data($this->api->getMataKuliahs());

        return view('import.index', compact('mataKuliahs'));
    }

    public function wide(Request $request)
    {
        $payload = $request->validate([
            'mata_kuliah_id' => ['required'],
            'angkatan' => ['required', 'string'],
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $response = $this->api->importNilaiWide($payload, $request->file('file'));

        return $this->backWithImportResult($response);
    }

    public function long(Request $request)
    {
        $payload = $request->validate([
            'mata_kuliah_id' => ['required'],
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $response = $this->api->importNilaiLong($payload, $request->file('file'));

        return $this->backWithImportResult($response);
    }

    public function templateWide(Request $request)
    {
        $payload = $request->validate([
            'mata_kuliah_id' => ['required'],
        ]);

        $response = $this->api->download('/import/template-wide', $payload);

        return response($response->body(), $response->status(), [
            'Content-Type' => $response->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
            'Content-Disposition' => $response->header('Content-Disposition', 'attachment; filename="template_nilai_wide.xlsx"'),
        ]);
    }

    public function importCpl(Request $request)
    {
        $payload = $request->validate([
            'kurikulum_prodi_id' => ['required'],
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);
        $response = $this->api->importCpl(['kurikulum_prodi_id' => $payload['kurikulum_prodi_id']], $request->file('file'));
        return $this->jsonImportResult($response);
    }

    public function importCpmk(Request $request)
    {
        $payload = $request->validate([
            'kurikulum_prodi_id' => ['required'],
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);
        $response = $this->api->importCpmk(['kurikulum_prodi_id' => $payload['kurikulum_prodi_id']], $request->file('file'));
        return $this->jsonImportResult($response);
    }

    public function importMataKuliah(Request $request)
    {
        $payload = $request->validate([
            'kurikulum_prodi_id' => ['required'],
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);
        $response = $this->api->importMataKuliah(['kurikulum_prodi_id' => $payload['kurikulum_prodi_id']], $request->file('file'));
        return $this->jsonImportResult($response);
    }

    public function importMahasiswa(Request $request)
    {
        $payload = $request->validate([
            'kurikulum_prodi_id' => ['required'],
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);
        $response = $this->api->importMahasiswa(['kurikulum_prodi_id' => $payload['kurikulum_prodi_id']], $request->file('file'));
        return $this->jsonImportResult($response);
    }

    public function templateLong()
    {
        $response = $this->api->download('/import/template-long');

        return response($response->body(), $response->status(), [
            'Content-Type' => $response->header('Content-Type', 'text/csv'),
            'Content-Disposition' => $response->header('Content-Disposition', 'attachment; filename="template_nilai_long.csv"'),
        ]);
    }

    public function templateCpl()
    {
        $response = $this->api->download('/import/template/cpl');

        return response($response->body(), $response->status(), [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="template_cpl.xlsx"',
        ]);
    }

    public function templateCpmk(Request $request)
    {
        $payload = [];
        if ($request->has('kurikulum_prodi_id')) {
            $payload['kurikulum_prodi_id'] = $request->query('kurikulum_prodi_id');
        }
        $response = $this->api->download('/import/template/cpmk', $payload);

        return response($response->body(), $response->status(), [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="template_cpmk.xlsx"',
        ]);
    }

    public function templateMataKuliah()
    {
        $response = $this->api->download('/import/template/mata-kuliah');

        return response($response->body(), $response->status(), [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="template_mata_kuliah.xlsx"',
        ]);
    }

    public function templateMahasiswa()
    {
        $response = $this->api->download('/import/template/mahasiswa');

        return response($response->body(), $response->status(), [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="template_mahasiswa.xlsx"',
        ]);
    }

    private function backWithImportResult(array $response)
    {
        if (!($response['success'] ?? false)) {
            return back()->withInput()->with('error', $response['message'] ?? 'Import gagal.');
        }

        return back()
            ->with('success', $response['message'] ?? 'Import selesai.')
            ->with('import_result', $response['data'] ?? []);
    }

    private function data(array $response): array
    {
        return ($response['success'] ?? false) ? ($response['data'] ?? []) : [];
    }

    private function jsonImportResult(array $response)
    {
        if ($response['success'] ?? false) {
            return response()->json(['success' => true, 'message' => $response['message'] ?? 'Import berhasil.']);
        }

        $errors = $response['errors'] ?? null;
        if (is_array($errors)) {
            return response()->json(['success' => false, 'message' => $response['message'] ?? 'Validasi gagal.', 'errors' => $errors], 422);
        }

        return response()->json(['success' => false, 'message' => $response['message'] ?? 'Import gagal.'], 422);
    }
}
