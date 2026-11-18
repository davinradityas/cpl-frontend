<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

class FakultasController extends Controller
{
    public function __construct(
        protected ApiService $api
    ) {}

    public function index()
    {
        $response = $this->api->getFakultas();

        if (! ($response['success'] ?? false)) {
            return view('fakultas.index', [
                'fakultas' => [],
            ])->with('error', $response['message'] ?? 'Gagal memuat data fakultas.');
        }

        return view('fakultas.index', [
            'fakultas' => $response['data'] ?? [],
        ]);
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'kode' => ['required', 'string', 'max:20'],
            'nama' => ['required', 'string', 'max:255'],
        ]);

        $response = $this->api->storeFakultas($payload);

        if (! ($response['success'] ?? false)) {
            return back()
                ->withInput()
                ->with('error', $response['message'] ?? 'Gagal menambahkan fakultas.')
                ->with('errors_api', $response['errors'] ?? []);
        }

        return redirect()
            ->route('fakultas.index')
            ->with('success', $response['message'] ?? 'Fakultas berhasil ditambahkan.');
    }

    public function update(Request $request, string $id)
    {
        $payload = $request->validate([
            'kode' => ['required', 'string', 'max:20'],
            'nama' => ['required', 'string', 'max:255'],
        ]);

        $response = $this->api->updateFakultas($id, $payload);

        if (! ($response['success'] ?? false)) {
            return back()
                ->withInput()
                ->with('error', $response['message'] ?? 'Gagal memperbarui fakultas.')
                ->with('errors_api', $response['errors'] ?? []);
        }

        return redirect()
            ->route('fakultas.index')
            ->with('success', $response['message'] ?? 'Fakultas berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $response = $this->api->deleteFakultas($id);

        if (! ($response['success'] ?? false)) {
            return back()
                ->with('error', $response['message'] ?? 'Gagal menghapus fakultas.');
        }

        return redirect()
            ->route('fakultas.index')
            ->with('success', $response['message'] ?? 'Fakultas berhasil dihapus.');
    }
}