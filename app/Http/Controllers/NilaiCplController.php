<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

class NilaiCplController extends Controller
{
    public function __construct(
        protected ApiService $api
    ) {}

    public function index(Request $request)
    {
        $selectedKpId   = $request->query('kurikulum_prodi_id');
        $selectedAngk   = $request->query('angkatan');
        $selectedCplId  = $request->query('cpl_id', 'all');

        // ── Daftar Kurikulum Prodi ───────────────────────────────────────────
        $kurikulumProdis = $this->data($this->api->getKurikulumProdis());

        // ── Daftar Angkatan (bergantung pada KP yang dipilih) ────────────────
        $angkatanResp = $this->api->getAngkatanList(
            $selectedKpId ? ['kurikulum_prodi_id' => $selectedKpId] : []
        );
        $angkatanList = ($angkatanResp['success'] ?? false)
            ? ($angkatanResp['data']['angkatans'] ?? [])
            : [];

        // ── Daftar CPL (bergantung pada KP yang dipilih) ─────────────────────
        $cpls = $this->data($this->api->getCpls(
            $selectedKpId ? ['kurikulum_prodi_id' => $selectedKpId] : []
        ));

        $items        = [];
        $errorMessage = null;
        $isReady      = false;
        $needSelectKp = !$selectedKpId;
        $needSelectAngk = $selectedKpId && !$selectedAngk;

        if ($selectedKpId && $selectedAngk) {
            // ── Mode: Data siap (KP + Angkatan sudah dipilih) ────────────────
            $resp = $this->api->getNilaiCplPerAngkatan([
                'kurikulum_prodi_id' => $selectedKpId,
                'angkatan'           => $selectedAngk,
            ]);

            if (!($resp['success'] ?? false)) {
                $errorMessage = $resp['message'] ?? 'Terjadi kesalahan pada server.';
            } else {
                $cplsData = $resp['data']['cpls'] ?? [];

                // Filter per CPL jika dipilih
                if ($selectedCplId !== 'all') {
                    $cplsData = collect($cplsData)
                        ->filter(fn($c) => (string)($c['cpl_id'] ?? '') === (string)$selectedCplId)
                        ->values()
                        ->all();
                }

                // Ambil nama KP terpilih untuk tampilan
                $selectedKp = collect($kurikulumProdis)->firstWhere('id', $selectedKpId);

                foreach ($cplsData as $cplData) {
                    $breakdownTs = collect($cplData['breakdown_terselenggara'] ?? []);
                    $breakdownKs = collect($cplData['breakdown_keseluruhan'] ?? []);

                    $nilaiTs  = (float) ($cplData['nilai_cpl'] ?? 0);
                    $nilaiKs  = (float) ($cplData['nilai_cpl_keseluruhan'] ?? 0);

                    $totalSksTs  = $breakdownTs->sum('sks');
                    $totalSksKs  = $breakdownKs->sum('sks');

                    $items[] = [
                        'cpl' => [
                            'id'                       => $cplData['cpl_id'] ?? null,
                            'kode'                     => $cplData['kode'] ?? '-',
                            'rumusan_cpl'              => $cplData['rumusan_cpl'] ?? '-',
                            'nilai_cpl'                => $nilaiTs,
                            'nilai_cpl_keseluruhan'    => $nilaiKs,
                            'status'                   => $cplData['status'] ?? 'Belum Ada Data',
                            'status_color'             => $cplData['status_color'] ?? 'slate',
                            'status_keseluruhan'       => $cplData['status_keseluruhan'] ?? 'Belum Ada Data',
                            'status_keseluruhan_color' => $cplData['status_keseluruhan_color'] ?? 'slate',
                        ],
                        'breakdown_terselenggara' => $breakdownTs->all(),
                        'breakdown_keseluruhan'   => $breakdownKs->all(),
                        'totals_terselenggara'    => [
                            'sum_sks'   => $totalSksTs,
                            'nilai_cpl' => $nilaiTs,
                        ],
                        'totals_keseluruhan'      => [
                            'sum_sks'   => $totalSksKs,
                            'nilai_cpl' => $nilaiKs,
                        ],
                    ];
                }

                $isReady = true;
            }
        }

        $filters = [
            'kurikulum_prodi_id' => $selectedKpId,
            'angkatan'           => $selectedAngk,
            'cpl_id'             => $selectedCplId,
        ];

        $selectedKp = $selectedKpId
            ? collect($kurikulumProdis)->firstWhere('id', $selectedKpId)
            : null;

        return view('nilai-cpl.index', [
            'items'           => $items,
            'kurikulumProdis' => $kurikulumProdis,
            'selectedKp'      => $selectedKp,
            'cpls'            => $cpls,
            'angkatanList'    => $angkatanList,
            'filters'         => $filters,
            'errorMessage'    => $errorMessage,
            'needSelectKp'    => $needSelectKp,
            'needSelectAngk'  => $needSelectAngk,
            'isReady'         => $isReady,
        ]);
    }

    private function data(array $response): array
    {
        if (array_key_exists('success', $response)) {
            return ($response['success'] ?? false) ? ($response['data'] ?? []) : [];
        }

        return $response['data'] ?? [];
    }
}
