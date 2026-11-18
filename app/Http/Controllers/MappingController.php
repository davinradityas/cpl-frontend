<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

class MappingController extends Controller
{
    public function __construct(
        protected ApiService $api
    ) {}

    public function index(Request $request)
    {
        $cpls = $this->data($this->api->getCpls());
        $selectedCplId = $request->query('cpl_id', $cpls[0]['id'] ?? null);
        $selectedCpl = collect($cpls)->first(fn ($cpl) => (string) ($cpl['id'] ?? '') === (string) $selectedCplId);
        $kurikulumProdiId = data_get($selectedCpl, 'kurikulum_prodi.id');
        $mataKuliahs = $this->data($this->api->getMataKuliahs(
            $kurikulumProdiId ? ['kurikulum_prodi_id' => $kurikulumProdiId] : []
        ));
        $attached = $selectedCplId ? $this->attachedMataKuliahs($selectedCplId) : [];

        return view('mapping.index', compact('cpls', 'mataKuliahs', 'selectedCplId', 'selectedCpl', 'attached'));
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'cpl_id' => ['required'],
            'mata_kuliah_ids' => ['required', 'array'],
        ]);

        $response = $this->api->attachMataKuliah($payload['cpl_id'], $payload['mata_kuliah_ids']);

        if (!($response['success'] ?? false)) {
            return back()->withInput()->with('error', $response['message'] ?? 'Gagal menyimpan mapping.');
        }

        return redirect()
            ->route('mapping.index', ['cpl_id' => $payload['cpl_id']])
            ->with('success', $response['message'] ?? 'Mapping berhasil disimpan.');
    }

    public function destroy(string $cplId, string $mataKuliahId)
    {
        $response = $this->api->detachMataKuliah($cplId, $mataKuliahId);

        if (!($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Gagal menghapus mapping.');
        }

        return redirect()
            ->route('mapping.index', ['cpl_id' => $cplId])
            ->with('success', $response['message'] ?? 'Mapping berhasil dihapus.');
    }

    private function data(array $response): array
    {
        if (array_key_exists('success', $response)) {
            return ($response['success'] ?? false) ? ($response['data'] ?? []) : [];
        }

        return $response['data'] ?? [];
    }

    private function attachedMataKuliahs(int|string $cplId): array
    {
        $response = $this->api->getMataKuliahByCpl($cplId);
        $data = $this->data($response);

        if (isset($data['mata_kuliahs']) && is_array($data['mata_kuliahs'])) {
            return $data['mata_kuliahs'];
        }

        return array_is_list($data) ? $data : [];
    }
}
