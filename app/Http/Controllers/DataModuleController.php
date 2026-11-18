<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

class DataModuleController extends Controller
{
    public function __construct(
        protected ApiService $api
    ) {}

    public function index(string $module)
    {
        $config = $this->moduleConfig($module);
        $filters = $this->filters($module);

        // Default ke kurikulum prodi pertama jika belum dipilih (tanpa redirect)
        if (in_array($module, ['cpls', 'mata-kuliahs', 'mahasiswas']) && empty($filters['kurikulum_prodi_id'])) {
            $kpResponse = $this->api->getKurikulumProdis();
            $kurikulumProdis = $this->dataFromResponse($kpResponse);
            if (count($kurikulumProdis) > 0) {
                $filters['kurikulum_prodi_id'] = $kurikulumProdis[0]['id'];
            }
        }

        $rawResponse = $this->rawList($module, $filters);
        $data = $this->dataFromResponse($rawResponse);
        $paginationMeta = $this->paginationMeta($module, $rawResponse);
        $filterOptions = $this->filterOptions($module, $data);
        $data = $this->applyFilters($module, $data, $filters);
        $options = $this->options($config['option_sources'] ?? []);

        return view('modules.index', compact('module', 'config', 'data', 'options', 'filters', 'filterOptions', 'paginationMeta'));
    }

    public function store(Request $request, string $module)
    {
        $config = $this->moduleConfig($module);
        $payload = $request->validate($config['rules']['store']);
        $response = $this->callStore($module, $payload);

        return $this->redirectAfterWrite($module, $response, $config['messages']['store_error'], array_merge($request->all(), $payload));
    }

    public function update(Request $request, string $module, string $id)
    {
        $config = $this->moduleConfig($module);
        $payload = $request->validate($config['rules']['update']);
        $response = $this->callUpdate($module, $id, $payload);

        return $this->redirectAfterWrite($module, $response, $config['messages']['update_error'], array_merge($request->all(), $payload));
    }

    public function destroy(string $module, string $id)
    {
        $config = $this->moduleConfig($module);
        $response = $this->callDelete($module, $id);

        return $this->redirectAfterWrite($module, $response, $config['messages']['delete_error'], request()->all());
    }

    private function rawList(string $module, array $filters = []): array
    {
        $kurikulumProdiId = $filters['kurikulum_prodi_id'] ?? '';

        if ($module === 'mahasiswas') {
            $query = [];
            if (!empty($filters['kurikulum_prodi_id'])) $query['kurikulum_prodi_id']  = $filters['kurikulum_prodi_id'];
            if (!empty($filters['angkatan']) && $filters['angkatan'] !== 'all') $query['angkatan'] = $filters['angkatan'];
            if (!empty($filters['search']))      $query['nama']      = $filters['search'];
            $query['page']     = $filters['page'] ?? 1;
            $query['per_page'] = 15;
            return $this->api->getMahasiswas($query);
        }

        return match ($module) {
            'jenis-penilaians' => $this->api->getJenisPenilaians(),
            'prodis'           => $this->api->getProdis(),
            'kurikulums'       => $this->api->getKurikulums(),
            'kurikulum-prodis' => $this->api->getKurikulumProdis(),
            'cpls'             => $this->api->getCpls($kurikulumProdiId ? ['kurikulum_prodi_id' => $kurikulumProdiId] : []),
            'mata-kuliahs'     => $this->api->getMataKuliahs($kurikulumProdiId ? ['kurikulum_prodi_id' => $kurikulumProdiId] : []),
            'kategori-nilais'  => $this->api->getKategoriNilais(),
            default            => ['success' => false, 'data' => []],
        };
    }

    private function list(string $module, array $filters = []): array
    {
        return $this->dataFromResponse($this->rawList($module, $filters));
    }

    private function paginationMeta(string $module, array $raw): array
    {
        if ($module !== 'mahasiswas') return [];

        // Laravel paginate() dengan ResourceCollection mengembalikan 'meta' & 'links'
        return [
            'current_page' => $raw['meta']['current_page'] ?? 1,
            'last_page'    => $raw['meta']['last_page']    ?? 1,
            'per_page'     => $raw['meta']['per_page']     ?? 15,
            'total'        => $raw['meta']['total']        ?? count($this->dataFromResponse($raw)),
            'from'         => $raw['meta']['from']         ?? null,
            'to'           => $raw['meta']['to']           ?? null,
        ];
    }

    private function options(array $sources): array
    {
        $options = [];

        foreach ($sources as $key => $source) {
            $response = match ($source) {
                'fakultas' => $this->api->getFakultas(),
                'prodis' => $this->api->getProdis(),
                'kurikulums' => $this->api->getKurikulums(),
                'kurikulum-prodis' => $this->api->getKurikulumProdis(),
                'cpls' => $this->api->getCpls(),
                'mata-kuliahs' => $this->api->getMataKuliahs(),
                'mahasiswas' => $this->api->getMahasiswas(),
                'jenis-matkulis' => $this->getJenisMatkulisList(),
                default => ['success' => false, 'data' => []],
            };

            $options[$key] = $this->dataFromResponse($response);
        }

        return $options;
    }

    private function dataFromResponse(array $response): array
    {
        if (array_key_exists('success', $response)) {
            return ($response['success'] ?? false) ? ($response['data'] ?? []) : [];
        }

        return $response['data'] ?? [];
    }

    private function filters(string $module): array
    {
        if ($module === 'mahasiswas') {
            return [
                'angkatan' => request()->query('angkatan', 'all'),
                'search'   => trim((string) request()->query('search', '')),
                'kurikulum_prodi_id' => request()->query('kurikulum_prodi_id', ''),
                'page'     => (int) request()->query('page', 1),
            ];
        }

        if (in_array($module, ['cpls', 'mata-kuliahs'])) {
            return [
                'kurikulum_prodi_id' => request()->query('kurikulum_prodi_id', ''),
            ];
        }

        return [];
    }

    private function filterOptions(string $module, array $data): array
    {
        if ($module === 'mahasiswas') {
            $angkatans = collect($data)
                ->pluck('angkatan')
                ->filter()
                ->unique()
                ->sort()
                ->values()
                ->all();

            $kpResponse = $this->api->getKurikulumProdis();
            $kurikulumProdis = $this->dataFromResponse($kpResponse);

            return ['angkatans' => $angkatans, 'kurikulumProdis' => $kurikulumProdis];
        }

        if (in_array($module, ['cpls', 'mata-kuliahs'])) {
            $kpResponse = $this->api->getKurikulumProdis();
            $kurikulumProdis = $this->dataFromResponse($kpResponse);

            return ['kurikulumProdis' => $kurikulumProdis];
        }

        return [];
    }

    private function applyFilters(string $module, array $data, array $filters): array
    {
        // cpls & mata-kuliahs: filter sudah dilakukan saat pemanggilan API (di list()),
        // tidak perlu filter lagi di sini
        if (in_array($module, ['cpls', 'mata-kuliahs'])) {
            return $data;
        }

        if ($module !== 'mahasiswas') {
            return $data;
        }

        // mahasiswas: filter sudah dilakukan server-side
        return $data;
    }

    private function callStore(string $module, array $payload): array
    {
        return match ($module) {
            'jenis-penilaians' => $this->api->storeJenisPenilaian($payload),
            'prodis' => $this->api->storeProdi($payload),
            'kurikulums' => $this->api->storeKurikulum($payload),
            'kurikulum-prodis' => $this->api->storeKurikulumProdi($payload),
            'cpls' => $this->api->storeCpl($payload),
            'mata-kuliahs' => $this->api->storeMataKuliah($payload),
            'mahasiswas'       => $this->api->storeMahasiswa($payload),
            'kategori-nilais'  => $this->api->storeKategoriNilai($payload),
            default            => ['success' => false, 'message' => 'Modul tidak valid.'],
        };
    }

    private function callUpdate(string $module, string $id, array $payload): array
    {
        return match ($module) {
            'jenis-penilaians' => $this->api->updateJenisPenilaian($id, $payload),
            'prodis' => $this->api->updateProdi($id, $payload),
            'kurikulums' => $this->api->updateKurikulum($id, $payload),
            'kurikulum-prodis' => $this->api->updateKurikulumProdi($id, $payload),
            'cpls' => $this->api->updateCpl($id, $payload),
            'mata-kuliahs' => $this->api->updateMataKuliah($id, $payload),
            'mahasiswas'       => $this->api->updateMahasiswa($id, $payload),
            'kategori-nilais'  => $this->api->updateKategoriNilai($id, $payload),
            default            => ['success' => false, 'message' => 'Modul tidak valid.'],
        };
    }

    private function callDelete(string $module, string $id): array
    {
        return match ($module) {
            'jenis-penilaians' => $this->api->deleteJenisPenilaian($id),
            'prodis' => $this->api->deleteProdi($id),
            'kurikulums' => $this->api->deleteKurikulum($id),
            'kurikulum-prodis' => $this->api->deleteKurikulumProdi($id),
            'cpls' => $this->api->deleteCpl($id),
            'mata-kuliahs' => $this->api->deleteMataKuliah($id),
            'mahasiswas'       => $this->api->deleteMahasiswa($id),
            'kategori-nilais'  => $this->api->deleteKategoriNilai($id),
            default            => ['success' => false, 'message' => 'Modul tidak valid.'],
        };
    }

    private function redirectAfterWrite(string $module, array $response, string $fallback, array $params = [])
    {
        if (array_key_exists('success', $response) && !($response['success'] ?? false)) {
            return back()
                ->withInput()
                ->with('error', $response['message'] ?? $fallback)
                ->with('errors_api', $response['errors'] ?? []);
        }

        $redirectParams = [];
        $kpId = $params['kurikulum_prodi_id'] ?? $params['filter_kurikulum_prodi_id'] ?? request('kurikulum_prodi_id') ?? request('filter_kurikulum_prodi_id') ?? null;
        if (!empty($kpId)) {
            $redirectParams['kurikulum_prodi_id'] = $kpId;
        }

        if ($module === 'mahasiswas') {
            $akt = $params['angkatan'] ?? request('angkatan') ?? null;
            if (!empty($akt) && $akt !== 'all') {
                $redirectParams['angkatan'] = $akt;
            }
        }

        return redirect()
            ->route($module . '.index', $redirectParams)
            ->with('success', $response['message'] ?? 'Data berhasil disimpan.');
    }

    public function getCplsByMataKuliah(string $id)
    {
        $response = $this->api->getCplsByMataKuliah($id);
        return response()->json($response);
    }

    private function moduleConfig(string $module): array
    {
        $configs = [
            'jenis-penilaians' => [
                'title' => 'Jenis Penilaian',
                'description' => 'Kelola jenis penilaian (contoh: Aktifitas Partisipasi, Tugas, Quiz, UTS, UAS, Proyek).',
                'route' => 'jenis-penilaians',
                'columns' => [
                    ['label' => 'Kode', 'key' => 'kode'],
                    ['label' => 'Nama Jenis Penilaian', 'key' => 'nama'],
                ],
                'fields' => [
                    ['name' => 'kode', 'label' => 'Kode', 'type' => 'text'],
                    ['name' => 'nama', 'label' => 'Nama Jenis Penilaian', 'type' => 'text'],
                ],
                'rules' => [
                    'store' => ['kode' => ['required', 'string', 'max:50'], 'nama' => ['required', 'string', 'max:100']],
                    'update' => ['kode' => ['required', 'string', 'max:50'], 'nama' => ['required', 'string', 'max:100']],
                ],
            ],
            'prodis' => [
                'title' => 'Prodi',
                'description' => 'Kelola program studi dan relasinya ke fakultas.',
                'route' => 'prodis',
                'option_sources' => ['fakultas_id' => 'fakultas'],
                'columns' => [
                    ['label' => 'Kode', 'key' => 'kode'],
                    ['label' => 'Nama Prodi', 'key' => 'nama'],
                    ['label' => 'Fakultas', 'key' => 'fakultas.nama'],
                ],
                'fields' => [
                    ['name' => 'fakultas_id', 'label' => 'Fakultas', 'type' => 'select', 'option_label' => 'nama'],
                    ['name' => 'kode', 'label' => 'Kode Prodi', 'type' => 'text'],
                    ['name' => 'nama', 'label' => 'Nama Prodi', 'type' => 'text'],
                ],
                'rules' => [
                    'store' => ['fakultas_id' => ['required'], 'kode' => ['required', 'string', 'max:50'], 'nama' => ['required', 'string']],
                    'update' => ['kode' => ['required', 'string', 'max:50'], 'nama' => ['required', 'string']],
                ],
            ],
            'kurikulums' => [
                'title' => 'Kurikulum',
                'description' => 'Kelola kurikulum (semua kurikulum yang ada akan dianggap aktif).',
                'route' => 'kurikulums',
                'columns' => [
                    ['label' => 'Kode', 'key' => 'kode'],
                    ['label' => 'Tahun', 'key' => 'tahun'],
                ],
                'fields' => [
                    ['name' => 'kode', 'label' => 'Kode Kurikulum', 'type' => 'text'],
                    ['name' => 'tahun', 'label' => 'Tahun', 'type' => 'text'],
                ],
                'rules' => [
                    'store' => ['kode' => ['required', 'string'], 'tahun' => ['required', 'string']],
                    'update' => ['kode' => ['required', 'string'], 'tahun' => ['required', 'string']],
                ],
            ],
            'kurikulum-prodis' => [
                'title' => 'Kurikulum Prodi',
                'description' => 'Hubungkan kurikulum dengan program studi.',
                'route' => 'kurikulum-prodis',
                'option_sources' => ['kurikulum_id' => 'kurikulums', 'prodi_id' => 'prodis'],
                'columns' => [
                    ['label' => 'Kode', 'key' => 'kode_kur_prodi'],
                    ['label' => 'Kurikulum', 'key' => 'kurikulum.kode'],
                    ['label' => 'Prodi', 'key' => 'prodi.nama'],
                ],
                'fields' => [
                    ['name' => 'kurikulum_id', 'label' => 'Kurikulum', 'type' => 'select', 'option_label' => 'kode'],
                    ['name' => 'prodi_id', 'label' => 'Prodi', 'type' => 'select', 'option_label' => 'nama', 'store_only' => true],
                ],
                'rules' => [
                    'store' => ['kurikulum_id' => ['required'], 'prodi_id' => ['required']],
                    'update' => ['kurikulum_id' => ['required']],
                ],
            ],
            'cpls' => [
                'title' => 'CPL',
                'description' => 'Kelola capaian pembelajaran lulusan per kurikulum prodi.',
                'route' => 'cpls',
                'option_sources' => ['kurikulum_prodi_id' => 'kurikulum-prodis'],
                'columns' => [
                    ['label' => 'Kode', 'key' => 'kode'],
                    ['label' => 'Rumusan CPL', 'key' => 'rumusan_cpl'],
                    ['label' => 'Nilai', 'key' => 'nilai_cpl'],
                    ['label' => 'Status', 'key' => 'status', 'type' => 'status'],
                ],
                'fields' => [
                    ['name' => 'kurikulum_prodi_id', 'label' => 'Kurikulum Prodi', 'type' => 'select', 'option_label' => 'kode_kur_prodi', 'store_only' => true],
                    ['name' => 'kode', 'label' => 'Kode CPL', 'type' => 'text'],
                    ['name' => 'rumusan_cpl', 'label' => 'Rumusan CPL', 'type' => 'textarea'],
                ],
                'rules' => [
                    'store' => ['kurikulum_prodi_id' => ['required'], 'kode' => ['required', 'string'], 'rumusan_cpl' => ['required', 'string']],
                    'update' => ['kode' => ['required', 'string'], 'rumusan_cpl' => ['required', 'string']],
                ],
            ],
            'mata-kuliahs' => [
                'title' => 'Mata Kuliah',
                'description' => 'Kelola mata kuliah, SKS, dan nilai rata-rata hasil import/input.',
                'route' => 'mata-kuliahs',
                'option_sources' => ['kurikulum_prodi_id' => 'kurikulum-prodis', 'jenis_matkul_id' => 'jenis-matkulis'],
                'columns' => [
                    ['label' => 'Kode', 'key' => 'kode'],
                    ['label' => 'Nama', 'key' => 'nama'],
                    ['label' => 'Jenis Matkul', 'key' => 'jenis_matkul.nama'],
                    ['label' => 'SKS', 'key' => 'sks'],
                    ['label' => 'Semester', 'key' => 'semester'],
                    ['label' => 'Keterangan', 'key' => 'keterangan'],
                    ['label' => 'Avg Nilai', 'key' => 'avg_nilai'],
                ],
                'fields' => [
                    ['name' => 'kurikulum_prodi_id', 'label' => 'Kurikulum Prodi', 'type' => 'select', 'option_label' => 'kode_kur_prodi', 'store_only' => true],
                    ['name' => 'kode', 'label' => 'Kode MK', 'type' => 'text'],
                    ['name' => 'nama', 'label' => 'Nama Mata Kuliah', 'type' => 'text'],
                    ['name' => 'jenis_matkul_id', 'label' => 'Jenis Matkul', 'type' => 'select', 'option_label' => 'nama', 'required' => false],
                    ['name' => 'sks', 'label' => 'SKS', 'type' => 'number'],
                    ['name' => 'semester', 'label' => 'Semester', 'type' => 'text'],
                    ['name' => 'keterangan', 'label' => 'Keterangan (Opsional)', 'type' => 'select', 'options' => ['Wajib' => 'Wajib', 'Pilihan' => 'Pilihan'], 'required' => false],
                ],
                'rules' => [
                    'store' => ['kurikulum_prodi_id' => ['required'], 'kode' => ['required', 'string'], 'nama' => ['required', 'string'], 'jenis_matkul_id' => ['nullable'], 'sks' => ['required', 'integer'], 'semester' => ['required', 'string'], 'keterangan' => ['nullable']],
                    'update' => ['kode' => ['required', 'string'], 'nama' => ['required', 'string'], 'jenis_matkul_id' => ['nullable'], 'sks' => ['required', 'integer'], 'semester' => ['required', 'string'], 'keterangan' => ['nullable']],
                ],
            ],
            'mahasiswas' => [
                'title' => 'Mahasiswa',
                'description' => 'Kelola mahasiswa berdasarkan kurikulum prodi dan angkatan.',
                'route' => 'mahasiswas',
                'option_sources' => ['kurikulum_prodi_id' => 'kurikulum-prodis'],
                'columns' => [
                    ['label' => 'Kode', 'key' => 'kode'],
                    ['label' => 'Nama', 'key' => 'nama'],
                    ['label' => 'Angkatan', 'key' => 'angkatan'],
                    ['label' => 'Kurikulum Prodi', 'key' => 'kurikulum_prodi.prodi.nama'],
                ],
                'fields' => [
                    ['name' => 'kurikulum_prodi_id', 'label' => 'Kurikulum Prodi', 'type' => 'select', 'option_label' => 'kode_kur_prodi'],
                    ['name' => 'kode', 'label' => 'Kode Mahasiswa', 'type' => 'text'],
                    ['name' => 'nama', 'label' => 'Nama Mahasiswa', 'type' => 'text'],
                    ['name' => 'angkatan', 'label' => 'Angkatan', 'type' => 'text'],
                ],
                'rules' => [
                    'store' => ['kurikulum_prodi_id' => ['required'], 'kode' => ['required', 'string'], 'nama' => ['nullable', 'string'], 'angkatan' => ['required', 'string']],
                    'update' => ['kurikulum_prodi_id' => ['required'], 'kode' => ['required', 'string'], 'nama' => ['nullable', 'string'], 'angkatan' => ['required', 'string']],
                ],
            ],
            'nilai' => [
                'title' => 'Nilai',
                'description' => 'Input nilai mahasiswa per mata kuliah. Perubahan nilai otomatis memicu kalkulasi CPL.',
                'route' => 'nilai',
                'option_sources' => ['mahasiswa_id' => 'mahasiswas', 'mata_kuliah_id' => 'mata-kuliahs'],
                'columns' => [
                    ['label' => 'Mahasiswa', 'key' => 'mahasiswa.kode'],
                    ['label' => 'Mata Kuliah', 'key' => 'mata_kuliah.kode'],
                    ['label' => 'Angkatan', 'key' => 'angkatan'],
                    ['label' => 'Nilai', 'key' => 'nilai'],
                ],
                'fields' => [
                    ['name' => 'mahasiswa_id', 'label' => 'Mahasiswa', 'type' => 'select', 'option_label' => 'kode', 'store_only' => true],
                    ['name' => 'mata_kuliah_id', 'label' => 'Mata Kuliah', 'type' => 'select', 'option_label' => 'kode', 'store_only' => true],
                    ['name' => 'angkatan', 'label' => 'Angkatan', 'type' => 'text', 'store_only' => true],
                    ['name' => 'nilai', 'label' => 'Nilai', 'type' => 'number'],
                ],
                'rules' => [
                    'store' => ['mahasiswa_id' => ['required'], 'mata_kuliah_id' => ['required'], 'angkatan' => ['required', 'string'], 'nilai' => ['required', 'numeric', 'min:0', 'max:100']],
                    'update' => ['nilai' => ['required', 'numeric', 'min:0', 'max:100']],
                ],
            ],

            'kategori-nilais' => [
                'title' => 'Predikat/Kategori Nilai CPL',
                'description' => 'Atur kategori ambang batas lulus dan warna lencananya (bisa ditambah/dihapus bebas).',
                'route' => 'kategori-nilais',
                'columns' => [
                    ['label' => 'Nama Predikat', 'key' => 'nama'],
                    ['label' => 'Batas Nilai (Min)', 'key' => 'batas_bawah'],
                    ['label' => 'Warna Lencana', 'key' => 'warna'],
                ],
                'fields' => [
                    ['name' => 'nama', 'label' => 'Nama Predikat (Misal: Baik Sekali)', 'type' => 'text'],
                    ['name' => 'batas_bawah', 'label' => 'Batas Bawah Nilai (Min)', 'type' => 'number', 'step' => '0.01'],
                    ['name' => 'warna', 'label' => 'Tema Warna', 'type' => 'select', 'options' => [
                        ['id' => 'success', 'name' => 'Hijau'],
                        ['id' => 'warning', 'name' => 'Kuning'],
                        ['id' => 'danger', 'name' => 'Merah'],
                        ['id' => 'info', 'name' => 'Biru'],
                        ['id' => 'slate', 'name' => 'Abu-abu'],
                    ]],
                ],
                'rules' => [
                    'store' => [
                        'nama' => ['required', 'string'], 
                        'batas_bawah' => ['required', 'numeric', 'min:0', 'max:100'],
                        'warna' => ['required', 'in:success,warning,danger,info,slate']
                    ],
                    'update' => [
                        'nama' => ['required', 'string'], 
                        'batas_bawah' => ['required', 'numeric', 'min:0', 'max:100'],
                        'warna' => ['required', 'in:success,warning,danger,info,slate']
                    ],
                ],
            ],
        ];

        abort_unless(isset($configs[$module]), 404);

        $configs[$module]['messages'] = [
            'store_error' => 'Gagal menambahkan data.',
            'update_error' => 'Gagal memperbarui data.',
            'delete_error' => 'Gagal menghapus data.',
        ];

        return $configs[$module];
    }

    /**
     * Get flat list of Jenis Matkul for use as select option source
     */
    private function getJenisMatkulisList(): array
    {
        $response = $this->api->getJenisMatkulis();
        $data = ($response['success'] ?? false) ? ($response['data']['jenis_matkulis'] ?? []) : [];
        return ['success' => true, 'data' => $data];
    }
}
