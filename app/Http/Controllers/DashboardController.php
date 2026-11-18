<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function __construct(
        protected ApiService $api
    ) {}

    public function index(Request $request)
    {
        $user = session('auth_user');
        $role = $user['role'] ?? ($user['roles'][0] ?? null);

        // Ambil semua kurikulum prodi yang accessible
        $kpResp  = $this->api->getKurikulumProdis();
        $allKps  = ($kpResp['success'] ?? false) ? ($kpResp['data'] ?? []) : [];

        if (empty($allKps)) {
            return view('dashboard.index', $this->emptyState(
                'Belum ada Kurikulum Prodi yang terdaftar. Setup data terlebih dahulu.',
                $request
            ));
        }

        // === Tentukan Prodi ===
        // admin_kaprodi: auto-pilih KP pertama (hanya punya 1 prodi)
        // role lain: ambil dari query param saja (jangan auto-select)
        $selectedKpId = $request->query('kurikulum_prodi_id');

        if (!$selectedKpId && $role === 'admin_kaprodi') {
            $selectedKpId = $allKps[0]['id'] ?? null;
        }

        // Validasi: pastikan kpId yang dipilih ada di list
        $selectedKp = collect($allKps)->firstWhere('id', (int) $selectedKpId);
        if (!$selectedKp) {
            $selectedKpId = null;
        }

        // Ambil daftar angkatan HANYA jika KP sudah dipilih
        $angkatanList = [];
        if ($selectedKpId) {
            $angkatanResp = $this->api->getAngkatanList(['kurikulum_prodi_id' => $selectedKpId]);
            $angkatanList = ($angkatanResp['success'] ?? false)
                ? ($angkatanResp['data']['angkatans'] ?? [])
                : [];
        }

        // === Tentukan Angkatan ===
        // JANGAN auto-select angkatan. Flow harus: Pilih Prodi -> Pilih Angkatan -> Klik Tampilkan.
        $selectedAngkatan = $request->query('angkatan') ?: null;
        if ($selectedAngkatan && !in_array((string) $selectedAngkatan, array_map('strval', $angkatanList), true)) {
            $selectedAngkatan = null;
        }

        // === Ambil Data Dashboard ===
        $items = [];
        $errorMessage = null;

        if ($selectedKpId && $selectedAngkatan) {
            $resp = $this->api->getDashboardSummaryByAngkatan([
                'kurikulum_prodi_id' => $selectedKpId,
                'angkatan'           => $selectedAngkatan,
            ]);

            if ($resp['success'] ?? false) {
                $rawData = $resp['data'] ?? [];

                // Backend return 'items' (bisa single untuk kaprodi atau grouped)
                $rawItems = $rawData['items'] ?? [];

                // Normalisasi ke array flat [{prodi, kurikulum, summary_cpl}]
                $items = $this->normalizeItems($rawItems, $role);
            } else {
                $errorMessage = $resp['message'] ?? 'Gagal mengambil data dashboard.';
            }
        }

        $filters = [
            'kurikulum_prodi_id' => $selectedKpId,
            'angkatan'           => $selectedAngkatan,
        ];

        // Settings untuk label dinamis
        $settingsResp = $this->api->getSettings();
        $settings = ($settingsResp['success'] ?? false) ? ($settingsResp['data'] ?? []) : [];
        $settingsMap = collect($settings)->pluck('value', 'key')->toArray();

        return view('dashboard.index', [
            'allKps'          => $allKps,
            'selectedKp'      => $selectedKp,
            'items'           => $items,
            'angkatanList'    => $angkatanList,
            'filters'         => $filters,
            'totals'          => $this->totals($items),
            'errorMessage'    => $errorMessage,
            'needSelectKp'    => !$selectedKpId,
            'needSelectAngk'  => $selectedKpId && !$selectedAngkatan,
            'isReady'         => $selectedKpId && $selectedAngkatan && !empty($items),
            'role'            => $role,
            'settingsMap'     => $settingsMap,
        ]);
    }

    /**
     * GET /dashboard/angkatan-json?kurikulum_prodi_id=X
     * Dipakai AJAX dari view untuk mengisi dropdown angkatan saat KP berubah.
     */
    public function angkatanJson(Request $request)
    {
        $kpId = $request->query('kurikulum_prodi_id');
        if (!$kpId) {
            return response()->json(['angkatans' => []]);
        }

        $resp  = $this->api->getAngkatanList(['kurikulum_prodi_id' => $kpId]);
        $list  = ($resp['success'] ?? false) ? ($resp['data']['angkatans'] ?? []) : [];

        return response()->json(['angkatans' => array_values($list)]);
    }



    /**
     * Normalkan response API ke format flat array items
     * [{ prodi, kurikulum, summary_cpl, angkatan }]
     */
    private function normalizeItems(mixed $rawItems, ?string $role): array
    {
        if (empty($rawItems)) {
            return [];
        }

        // admin_kaprodi: rawItems = single object (bukan array of arrays)
        if ($role === 'admin_kaprodi' && isset($rawItems['prodi'])) {
            return [$rawItems];
        }

        $items = [];

        // role lain: rawItems = { "Nama Fakultas": [item, item], ... }
        if (is_array($rawItems)) {
            foreach ($rawItems as $fakultasNama => $prodiItems) {
                if (is_array($prodiItems) && isset($prodiItems[0])) {
                    // ini array of items
                    foreach ($prodiItems as $item) {
                        $item['fakultas_group'] = is_string($fakultasNama) ? $fakultasNama : ($item['prodi']['fakultas']['nama'] ?? '-');
                        $items[] = $item;
                    }
                } elseif (is_array($prodiItems) && isset($prodiItems['prodi'])) {
                    // single item terbungkus di key
                    $prodiItems['fakultas_group'] = is_string($fakultasNama) ? $fakultasNama : ($prodiItems['prodi']['fakultas']['nama'] ?? '-');
                    $items[] = $prodiItems;
                }
            }
        }

        return $items;
    }

    private function emptyState(string $message, Request $request): array
    {
        return [
            'allKps'         => [],
            'selectedKp'     => null,
            'items'          => [],
            'angkatanList'   => [],
            'filters'        => ['kurikulum_prodi_id' => null, 'angkatan' => null],
            'totals'         => ['total_mk' => 0, 'total_cpl' => 0, 'tercapai_aktual' => 0, 'tercapai_keseluruhan' => 0],
            'errorMessage'   => $message,
            'needSelectKp'   => false,
            'needSelectAngk' => false,
            'isReady'        => false,
            'role'           => null,
        ];
    }

    private function totals(array $items): array
    {
        $totalMk = 0;
        $totalCpl = 0;
        $tercapaiAktual = 0;
        $tercapaiKeseluruhan = 0;

        foreach ($items as $item) {
            $summary = $item['summary_cpl'] ?? [];
            $totalMk += (int) ($summary['jumlah_total_mk'] ?? 0);
            $totalCpl += (int) ($summary['jumlah_total_cpl'] ?? 0);
            $tercapaiAktual += (int) ($summary['jumlah_tercapai_aktual'] ?? 0);
            $tercapaiKeseluruhan += (int) ($summary['jumlah_tercapai_keseluruhan'] ?? 0);
        }

        return [
            'total_mk'             => $totalMk,
            'total_cpl'            => $totalCpl,
            'tercapai_aktual'      => $tercapaiAktual,
            'tercapai_keseluruhan' => $tercapaiKeseluruhan,
        ];
    }
}
