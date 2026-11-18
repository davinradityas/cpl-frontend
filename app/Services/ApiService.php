<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class ApiService
{
    protected string $baseUrl;
    protected array $requestCache = [];

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.backend.url'), '/');
    }

    protected function client()
    {
        $client = Http::acceptJson();

        if (session()->has('auth_token')) {
            $client = $client->withToken(session('auth_token'));
        }

        return $client;
    }

    protected function url(string $endpoint): string
    {
        return $this->baseUrl . '/' . ltrim($endpoint, '/');
    }

    protected function handle(Response $response): array
    {
        if ($response->status() === 401) {
            session()->forget(['auth_token', 'auth_user']);
        }

        return $response->json() ?? [
            'success' => false,
            'message' => 'Response backend tidak valid.',
            'errors' => null,
        ];
    }

    public function get(string $endpoint, array $query = []): array
    {
        $cacheKey = $endpoint . '?' . http_build_query($query);

        if (array_key_exists($cacheKey, $this->requestCache)) {
            return $this->requestCache[$cacheKey];
        }

        $response = $this->handle(
            $this->client()->get($this->url($endpoint), $query)
        );

        $this->requestCache[$cacheKey] = $response;

        return $response;
    }

    public function post(string $endpoint, array $data = []): array
    {
        return $this->handle(
            $this->client()->post($this->url($endpoint), $data)
        );
    }

    public function put(string $endpoint, array $data = []): array
    {
        return $this->handle(
            $this->client()->put($this->url($endpoint), $data)
        );
    }

    public function delete(string $endpoint): array
    {
        return $this->handle(
            $this->client()->delete($this->url($endpoint))
        );
    }

    public function multipart(string $endpoint, array $data, string $fileKey, $file): array
    {
        $request = $this->client()->asMultipart();

        $request = $request->attach(
            $fileKey,
            fopen($file->getRealPath(), 'r'),
            $file->getClientOriginalName()
        );

        return $this->handle(
            $request->post($this->url($endpoint), $data)
        );
    }

    public function download(string $endpoint, array $query = []): Response
    {
        return $this->client()->get($this->url($endpoint), $query);
    }

    // Auth
    public function login(array $data): array
    {
        return $this->post('/login', $data);
    }

    public function logout(): array
    {
        return $this->post('/logout');
    }

    public function me(): array
    {
        return $this->get('/me');
    }

    public function changePassword(array $data): array
    {
        return $this->put('/me/password', $data);
    }

    // Settings
    public function getSettings(): array
    {
        return $this->get('/settings');
    }

    public function storeSetting(array $data): array
    {
        return $this->post('/settings', $data);
    }

    public function updateSetting(int|string $id, array $data): array
    {
        return $this->put("/settings/{$id}", $data);
    }

    public function deleteSetting(int|string $id): array
    {
        return $this->delete("/settings/{$id}");
    }

    // Kategori Nilai — Global
    public function getKategoriNilais(): array
    {
        return $this->get('/kategori-nilais');
    }

    public function storeKategoriNilai(array $data): array
    {
        return $this->post('/kategori-nilais', $data);
    }

    public function updateKategoriNilai(int|string $id, array $data): array
    {
        return $this->put("/kategori-nilais/{$id}", $data);
    }

    public function deleteKategoriNilai(int|string $id): array
    {
        return $this->delete("/kategori-nilais/{$id}");
    }

    // Kategori Nilai Override — Per Angkatan + Prodi
    public function getKategoriNilaiOverrides(int|string $prodiId, string $angkatan): array
    {
        return $this->get('/kategori-nilai-overrides', [
            'prodi_id' => $prodiId,
            'angkatan' => $angkatan,
        ]);
    }

    public function getAngkatansByProdi(int|string $prodiId): array
    {
        return $this->get('/kategori-nilai-overrides/angkatans', [
            'prodi_id' => $prodiId,
        ]);
    }

    public function storeKategoriNilaiOverride(array $data): array
    {
        return $this->post('/kategori-nilai-overrides', $data);
    }

    public function updateKategoriNilaiOverride(int|string $id, array $data): array
    {
        return $this->put("/kategori-nilai-overrides/{$id}", $data);
    }

    public function deleteKategoriNilaiOverride(int|string $id): array
    {
        return $this->delete("/kategori-nilai-overrides/{$id}");
    }

    public function resetKategoriNilaiOverride(int|string $prodiId, string $angkatan): array
    {
        return $this->handle(
            $this->client()->delete($this->url('/kategori-nilai-overrides/reset'), [
                'prodi_id' => $prodiId,
                'angkatan' => $angkatan,
            ])
        );
    }

    // Fakultas
    public function getFakultas(): array
    {
        return $this->get('/fakultas');
    }

    public function storeFakultas(array $data): array
    {
        return $this->post('/fakultas', $data);
    }

    public function updateFakultas(int|string $id, array $data): array
    {
        return $this->put("/fakultas/{$id}", $data);
    }

    public function deleteFakultas(int|string $id): array
    {
        return $this->delete("/fakultas/{$id}");
    }

    // Jenis Matkul
    public function getJenisMatkulis(): array
    {
        return $this->get('/jenis-matkulis');
    }

    public function getJenisMatkul(int|string $id): array
    {
        return $this->get("/jenis-matkulis/{$id}");
    }

    public function storeJenisMatkul(array $data): array
    {
        return $this->post('/jenis-matkulis', $data);
    }

    public function updateJenisMatkul(int|string $id, array $data): array
    {
        return $this->put("/jenis-matkulis/{$id}", $data);
    }

    public function deleteJenisMatkul(int|string $id): array
    {
        return $this->delete("/jenis-matkulis/{$id}");
    }

    // Prodi
    public function getProdis(array $query = []): array
    {
        return $this->get('/prodis', $query);
    }

    public function storeProdi(array $data): array
    {
        return $this->post('/prodis', $data);
    }

    public function updateProdi(int|string $id, array $data): array
    {
        return $this->put("/prodis/{$id}", $data);
    }

    public function deleteProdi(int|string $id): array
    {
        return $this->delete("/prodis/{$id}");
    }

    // Kurikulum
    public function getKurikulums(): array
    {
        return $this->get('/kurikulums');
    }

    public function storeKurikulum(array $data): array
    {
        return $this->post('/kurikulums', $data);
    }

    public function updateKurikulum(int|string $id, array $data): array
    {
        return $this->put("/kurikulums/{$id}", $data);
    }

    public function deleteKurikulum(int|string $id): array
    {
        return $this->delete("/kurikulums/{$id}");
    }

    // Kurikulum Prodi
    public function getKurikulumProdis(array $query = []): array
    {
        return $this->get('/kurikulum-prodis', $query);
    }

    public function storeKurikulumProdi(array $data): array
    {
        return $this->post('/kurikulum-prodis', $data);
    }

    public function updateKurikulumProdi(int|string $id, array $data): array
    {
        return $this->put("/kurikulum-prodis/{$id}", $data);
    }

    public function deleteKurikulumProdi(int|string $id): array
    {
        return $this->delete("/kurikulum-prodis/{$id}");
    }

    // CPL
    public function getCpls(array $query = []): array
    {
        return $this->get('/cpls', $query);
    }

    public function storeCpl(array $data): array
    {
        return $this->post('/cpls', $data);
    }

    public function updateCpl(int|string $id, array $data): array
    {
        return $this->put("/cpls/{$id}", $data);
    }

    public function deleteCpl(int|string $id): array
    {
        return $this->delete("/cpls/{$id}");
    }

    public function getCplBobotDetail(int|string $cplId): array
    {
        return $this->get("/cpls/{$cplId}/bobot-detail");
    }

    public function updateCplBobot(int|string $cplId, array $data): array
    {
        return $this->put("/cpls/{$cplId}/bobot", $data);
    }

    public function detailKalkulasiCpl(int|string $id): array
    {
        return $this->get("/kalkulasi/cpl/{$id}");
    }

    // Mata Kuliah
    public function getMataKuliahs(array $query = []): array
    {
        return $this->get('/mata-kuliahs', $query);
    }

    public function storeMataKuliah(array $data): array
    {
        return $this->post('/mata-kuliahs', $data);
    }

    public function updateMataKuliah(int|string $id, array $data): array
    {
        return $this->put("/mata-kuliahs/{$id}", $data);
    }

    public function deleteMataKuliah(int|string $id): array
    {
        return $this->delete("/mata-kuliahs/{$id}");
    }

    // Mapping
    public function getMataKuliahByCpl(int|string $cplId): array
    {
        return $this->get("/cpls/{$cplId}/mata-kuliahs");
    }

    public function attachMataKuliah(int|string $cplId, array $mataKuliahIds): array
    {
        return $this->post("/cpls/{$cplId}/mata-kuliahs", [
            'mata_kuliah_ids' => $mataKuliahIds,
        ]);
    }

    public function detachMataKuliah(int|string $cplId, int|string $mkId): array
    {
        return $this->delete("/cpls/{$cplId}/mata-kuliahs/{$mkId}");
    }

    // Mahasiswa
    public function getMahasiswas(array $query = []): array
    {
        return $this->get('/mahasiswas', $query);
    }

    public function storeMahasiswa(array $data): array
    {
        return $this->post('/mahasiswas', $data);
    }

    public function updateMahasiswa(int|string $id, array $data): array
    {
        return $this->put("/mahasiswas/{$id}", $data);
    }

    public function deleteMahasiswa(int|string $id): array
    {
        return $this->delete("/mahasiswas/{$id}");
    }

    // Jenis Penilaian
    public function getJenisPenilaians(array $query = []): array
    {
        return $this->get('/jenis-penilaians', $query);
    }

    public function storeJenisPenilaian(array $data): array
    {
        return $this->post('/jenis-penilaians', $data);
    }

    public function updateJenisPenilaian(int|string $id, array $data): array
    {
        return $this->put("/jenis-penilaians/{$id}", $data);
    }

    public function deleteJenisPenilaian(int|string $id): array
    {
        return $this->delete("/jenis-penilaians/{$id}");
    }

    // === Master Data CPMK ===
    public function getCpmks(array $query = []): array
    {
        return $this->get('/cpmks', $query);
    }

    public function storeCpmk(array $data): array
    {
        return $this->post('/cpmks', $data);
    }

    public function getCpmk(int|string $id): array
    {
        return $this->get("/cpmks/{$id}");
    }

    public function updateCpmk(int|string $id, array $data): array
    {
        return $this->put("/cpmks/{$id}", $data);
    }

    public function deleteCpmk(int|string $id): array
    {
        return $this->delete("/cpmks/{$id}");
    }

    // === Mapping CPMK ↔ Mata Kuliah ===
    public function getMkCpmkMapping(int|string $mataKuliahId): array
    {
        return $this->get("/mata-kuliahs/{$mataKuliahId}/cpmk-mapping");
    }

    public function storeMkCpmkMapping(int|string $mataKuliahId, array $data): array
    {
        return $this->post("/mata-kuliahs/{$mataKuliahId}/cpmk-mapping", $data);
    }

    public function updateMkCpmkMapping(int|string $mataKuliahId, int|string $cpmkId, array $data): array
    {
        return $this->put("/mata-kuliahs/{$mataKuliahId}/cpmk-mapping/{$cpmkId}", $data);
    }

    public function deleteMkCpmkMapping(int|string $mataKuliahId, int|string $cpmkId): array
    {
        return $this->delete("/mata-kuliahs/{$mataKuliahId}/cpmk-mapping/{$cpmkId}");
    }

    public function storeBobotTeknikCpmk(int|string $mataKuliahId, array $data): array
    {
        return $this->post("/mata-kuliahs/{$mataKuliahId}/bobot-teknik", $data);
    }

    public function getCpmkBobotSummary(int|string $mataKuliahId): array
    {
        return $this->get("/mata-kuliahs/{$mataKuliahId}/cpmk-bobot-summary");
    }

    // === Bobot Jenis Penilaian per MK (Lapis 1) ===
    public function getBobotJenisMk(int|string $mataKuliahId): array
    {
        return $this->get("/mata-kuliahs/{$mataKuliahId}/bobot-jenis");
    }

    public function storeBobotJenisMk(int|string $mataKuliahId, array $data): array
    {
        return $this->post("/mata-kuliahs/{$mataKuliahId}/bobot-jenis", $data);
    }

    public function getCplsByMataKuliah(int|string $mataKuliahId): array
    {
        return $this->get("/mata-kuliahs/{$mataKuliahId}/cpls");
    }

    // Nilai Mahasiswa
    public function getNilaiGrid(int|string $mataKuliahId, array $query = []): array
    {
        return $this->get("/mata-kuliahs/{$mataKuliahId}/nilai-grid", $query);
    }

    public function storeNilaiGranular(int|string $mataKuliahId, array $data): array
    {
        return $this->post("/mata-kuliahs/{$mataKuliahId}/nilai", $data);
    }

    public function deleteNilaiGranular(int|string $mataKuliahId, int|string $mahasiswaId): array
    {
        return $this->delete("/mata-kuliahs/{$mataKuliahId}/nilai/{$mahasiswaId}");
    }

    public function getAngkatanNilai(int|string $mataKuliahId): array
    {
        return $this->get("/mata-kuliahs/{$mataKuliahId}/angkatan");
    }

    // Import
    public function importNilaiWide(array $data, $file): array
    {
        return $this->multipart('/import/nilai-wide', $data, 'file', $file);
    }

    public function importNilaiLong(array $data, $file): array
    {
        return $this->multipart('/import/nilai-long', $data, 'file', $file);
    }

    public function importCpl(array $data, $file): array
    {
        return $this->multipart('/import/cpl', $data, 'file', $file);
    }

    public function importCpmk(array $data, $file): array
    {
        return $this->multipart('/import/cpmk', $data, 'file', $file);
    }

    public function importMataKuliah(array $data, $file): array
    {
        return $this->multipart('/import/mata-kuliah', $data, 'file', $file);
    }

    public function importMahasiswa(array $data, $file): array
    {
        return $this->multipart('/import/mahasiswa', $data, 'file', $file);
    }

    // Dashboard & Kalkulasi
    public function getDashboardSummary(): array
    {
        return $this->get('/dashboard/summary');
    }

    public function getDashboardSummaryByAngkatan(array $query = []): array
    {
        return $this->get('/dashboard/summary', $query);
    }

    public function recalculate(array $data): array
    {
        return $this->post('/kalkulasi/recalculate', $data);
    }

    public function getKetercapaianAngkatan(array $query): array
    {
        return $this->get('/kalkulasi/angkatan', $query);
    }

    public function getNilaiCplMahasiswa(int|string $mahasiswaId, array $query = []): array
    {
        return $this->get("/kalkulasi/mahasiswa/{$mahasiswaId}", $query);
    }

    public function getNilaiCplPerAngkatan(array $query): array
    {
        return $this->get('/kalkulasi/nilai-cpl-angkatan', $query);
    }

    public function getAngkatanList(array $query = []): array
    {
        return $this->get('/kalkulasi/angkatan-list', $query);
    }

    // ─── User Management ──────────────────────────────────────────────────────

    public function getUsers(array $query = []): array
    {
        return $this->get('/users', $query);
    }

    public function createUser(array $data): array
    {
        return $this->post('/users', $data);
    }

    public function updateUser(int|string $id, array $data): array
    {
        return $this->put("/users/{$id}", $data);
    }

    public function deleteUser(int|string $id): array
    {
        return $this->delete("/users/{$id}");
    }

    public function resetUserPassword(int|string $id, array $data): array
    {
        return $this->post("/users/{$id}/reset-password", $data);
    }

    public function toggleUserActive(int|string $id): array
    {
        return $this->handle(
            $this->client()->patch($this->url("/users/{$id}/toggle-active"))
        );
    }

    // ─── Role Permission Management ───────────────────────────────────────────

    public function getRolePermissions(): array
    {
        return $this->get('/role-permissions');
    }

    public function createRole(array $data): array
    {
        return $this->post('/role-permissions', $data);
    }

    public function getRoleById(int|string $roleId): array
    {
        return $this->get("/role-permissions/{$roleId}");
    }

    public function updateRolePermissions(int|string $roleId, array $data): array
    {
        return $this->put("/role-permissions/{$roleId}", $data);
    }

    public function deleteRole(int|string $roleId): array
    {
        return $this->handle(
            $this->client()->delete($this->url("/role-permissions/{$roleId}"))
        );
    }
}
