<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CplBobotController;
use App\Http\Controllers\CpmkController;
use App\Http\Controllers\DataModuleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FakultasController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\JenisMatkulController;
use App\Http\Controllers\KalkulasiController;
use App\Http\Controllers\KategoriNilaiController;
use App\Http\Controllers\MappingController;
use App\Http\Controllers\MataKuliahCpmkController;
use App\Http\Controllers\NilaiCplController;
use App\Http\Controllers\NilaiMahasiswaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DetailMahasiswaController;
use App\Http\Controllers\ReportPdfController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('frontend.guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/login/{role}', fn () => redirect()->route('login'))
        ->whereIn('role', ['superadmin', 'admin_fakultas', 'admin_kaprodi']);
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::middleware('frontend.auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', fn () => redirect()->route('dashboard.index'));

    // Portal Laporan
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard.index');
    Route::get('/dashboard/angkatan-json', [DashboardController::class, 'angkatanJson'])
        ->name('dashboard.angkatan-json');

    // Profil & Ganti Password
    Route::get('/profil', [ProfileController::class, 'show'])->name('profil.show');
    Route::post('/profil/ganti-password', [ProfileController::class, 'changePassword'])->name('profil.change-password');
    Route::resource('fakultas', FakultasController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::get('/mahasiswas/{id}/detail', [DetailMahasiswaController::class, 'show'])->name('mahasiswas.detail');

    // === Data Master Berbasis Tabel (menggunakan DataModuleController) ===
    // 'kategori-nilais' dipisahkan karena punya halaman khusus dengan 2 section
    foreach (['jenis-penilaians', 'prodis', 'kurikulums', 'kurikulum-prodis', 'cpls', 'mata-kuliahs', 'mahasiswas', 'nilai'] as $module) {
        Route::get("/{$module}", fn (DataModuleController $controller) => $controller->index($module))
            ->name("{$module}.index");
        Route::post("/{$module}", fn (Illuminate\Http\Request $request, DataModuleController $controller) => $controller->store($request, $module))
            ->name("{$module}.store");
        Route::put("/{$module}/{id}", fn (Illuminate\Http\Request $request, DataModuleController $controller, string $id) => $controller->update($request, $module, $id))
            ->name("{$module}.update");
        Route::delete("/{$module}/{id}", fn (DataModuleController $controller, string $id) => $controller->destroy($module, $id))
            ->name("{$module}.destroy");
    }

    // === Predikat/Kategori Nilai — Dedicated Controller (Global + Override per Angkatan) ===
    Route::get('/kategori-nilais',                          [KategoriNilaiController::class, 'index'])        ->name('kategori-nilais.index');
    Route::post('/kategori-nilais',                         [KategoriNilaiController::class, 'store'])        ->name('kategori-nilais.store');
    Route::put('/kategori-nilais/{id}',                     [KategoriNilaiController::class, 'update'])       ->name('kategori-nilais.update');
    Route::delete('/kategori-nilais/{id}',                  [KategoriNilaiController::class, 'destroy'])      ->name('kategori-nilais.destroy');
    // AJAX endpoints untuk override
    Route::get('/kategori-nilais/overrides',                [KategoriNilaiController::class, 'getOverrides']) ->name('kategori-nilais.overrides.get');
    Route::get('/kategori-nilais/angkatans',                [KategoriNilaiController::class, 'getAngkatans']) ->name('kategori-nilais.angkatans');
    Route::post('/kategori-nilais/overrides',               [KategoriNilaiController::class, 'storeOverride'])->name('kategori-nilais.overrides.store');
    Route::put('/kategori-nilais/overrides/{id}',           [KategoriNilaiController::class, 'updateOverride'])->name('kategori-nilais.overrides.update');
    Route::delete('/kategori-nilais/overrides/reset',       [KategoriNilaiController::class, 'resetOverride'])->name('kategori-nilais.overrides.reset');
    Route::delete('/kategori-nilais/overrides/{id}',        [KategoriNilaiController::class, 'destroyOverride'])->name('kategori-nilais.overrides.destroy');

    Route::get('/mata-kuliahs/{id}/cpls', [DataModuleController::class, 'getCplsByMataKuliah'])
        ->name('mata-kuliahs.cpls');

    // === Pengaturan Bobot CPL per Mata Kuliah ===
    Route::get('/cpls/{id}/bobot',  [CplBobotController::class, 'index'])->name('cpls.bobot.index');
    Route::post('/cpls/{id}/bobot', [CplBobotController::class, 'update'])->name('cpls.bobot.update');

    Route::get('/mapping', [MappingController::class, 'index'])->name('mapping.index');
    Route::post('/mapping', [MappingController::class, 'store'])->name('mapping.store');
    Route::delete('/mapping/{cplId}/{mataKuliahId}', [MappingController::class, 'destroy'])->name('mapping.destroy');

    // === Master Data CPMK ===
    Route::get('/cpmks', [CpmkController::class, 'index'])->name('cpmks.index');
    Route::post('/cpmks', [CpmkController::class, 'store'])->name('cpmks.store');
    Route::put('/cpmks/{id}', [CpmkController::class, 'update'])->name('cpmks.update');
    Route::delete('/cpmks/{id}', [CpmkController::class, 'destroy'])->name('cpmks.destroy');

    // === Jenis Matkul (master template bobot) ===
    Route::get('/jenis-matkul', [JenisMatkulController::class, 'index'])->name('jenis-matkul.index');
    Route::post('/jenis-matkul', [JenisMatkulController::class, 'store'])->name('jenis-matkul.store');
    Route::put('/jenis-matkul/{id}', [JenisMatkulController::class, 'update'])->name('jenis-matkul.update');
    Route::delete('/jenis-matkul/{id}', [JenisMatkulController::class, 'destroy'])->name('jenis-matkul.destroy');

    // === Mapping CPMK ↔ Mata Kuliah ===
    Route::get('/mata-kuliahs/{mataKuliahId}/cpmk-mapping',        [MataKuliahCpmkController::class, 'index'])  ->name('mk-cpmk.index');
    Route::post('/mata-kuliahs/{mataKuliahId}/cpmk-mapping',       [MataKuliahCpmkController::class, 'store'])  ->name('mk-cpmk.store');
    Route::delete('/mata-kuliahs/{mataKuliahId}/cpmk-mapping/{cpmkId}', [MataKuliahCpmkController::class, 'destroy'])->name('mk-cpmk.destroy');
    // Bobot CPMK per Jenis Penilaian (matriks semua CPMK sekaligus)
    Route::post('/mata-kuliahs/{mataKuliahId}/bobot-teknik',       [MataKuliahCpmkController::class, 'storeBobotTeknik'])->name('mk-cpmk.bobot-teknik');

    // === Nilai Mahasiswa ===
    Route::get('/input-nilai', [NilaiMahasiswaController::class, 'selectMataKuliah'])->name('input-nilai.select');
    Route::get('/input-nilai/{mataKuliahId}', [NilaiMahasiswaController::class, 'index'])->name('input-nilai.index');
    Route::post('/input-nilai/{mataKuliahId}', [NilaiMahasiswaController::class, 'store'])->name('input-nilai.store');
    Route::delete('/input-nilai/{mataKuliahId}/{mahasiswaId}', [NilaiMahasiswaController::class, 'destroy'])->name('input-nilai.destroy');


    Route::get('/nilai-cpl', [NilaiCplController::class, 'index'])->name('nilai-cpl.index');

    Route::get('/import', [ImportController::class, 'index'])->name('import.index');
    Route::post('/import/wide', [ImportController::class, 'wide'])->name('import.wide');
    Route::post('/import/long', [ImportController::class, 'long'])->name('import.long');
    Route::get('/import/template-wide', [ImportController::class, 'templateWide'])->name('import.template-wide');
    Route::get('/import/template-long', [ImportController::class, 'templateLong'])->name('import.template-long');
    Route::get('/import/template/cpl', [ImportController::class, 'templateCpl'])->name('import.template-cpl');
    Route::get('/import/template/cpmk', [ImportController::class, 'templateCpmk'])->name('import.template-cpmk');
    Route::get('/import/template/mata-kuliah', [ImportController::class, 'templateMataKuliah'])->name('import.template-mata-kuliah');
    Route::get('/import/template/mahasiswa', [ImportController::class, 'templateMahasiswa'])->name('import.template-mahasiswa');

    Route::post('/import/cpl', [ImportController::class, 'importCpl'])->name('import.cpl');
    Route::post('/import/cpmk', [ImportController::class, 'importCpmk'])->name('import.cpmk');
    Route::post('/import/mata-kuliah', [ImportController::class, 'importMataKuliah'])->name('import.mata-kuliah');
    Route::post('/import/mahasiswa', [ImportController::class, 'importMahasiswa'])->name('import.mahasiswa');

    Route::get('/kalkulasi', [KalkulasiController::class, 'index'])->name('kalkulasi.index');
    Route::post('/kalkulasi/recalculate', [KalkulasiController::class, 'recalculate'])->name('kalkulasi.recalculate');
    Route::post('/kalkulasi/angkatan', [KalkulasiController::class, 'angkatan'])->name('kalkulasi.angkatan');
    Route::post('/kalkulasi/detail-cpl', [KalkulasiController::class, 'detailCpl'])->name('kalkulasi.detail-cpl');
    Route::post('/kalkulasi/mahasiswa', [KalkulasiController::class, 'mahasiswa'])->name('kalkulasi.mahasiswa');

    // === Laporan PDF ===
    Route::get('/report/nilai-cpl',                              [ReportPdfController::class, 'nilaiCpl'])->name('report.nilai-cpl');
    Route::get('/report/nilai-mahasiswa/{mataKuliahId}',         [ReportPdfController::class, 'nilaiMahasiswa'])->name('report.nilai-mahasiswa');
    Route::get('/report/matriks-bobot/{mataKuliahId}',           [ReportPdfController::class, 'matriksBobot'])->name('report.matriks-bobot');
    Route::get('/report/detail-mahasiswa/{mahasiswaId}',         [ReportPdfController::class, 'detailMahasiswa'])->name('report.detail-mahasiswa');

    // === Manajemen User (superadmin only, diproteksi di backend) ===
    Route::get('/users',                         [UserController::class, 'index'])->name('users.index');
    Route::post('/users',                        [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{id}',                    [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}',                 [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{id}/reset-password',    [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('/users/{id}/toggle-active',     [UserController::class, 'toggleActive'])->name('users.toggle-active');

    // === Hak Akses Role — full CRUD (superadmin only) ===
    Route::get('/role-permissions',                   [RolePermissionController::class, 'index'])->name('role-permissions.index');
    Route::post('/role-permissions',                  [RolePermissionController::class, 'store'])->name('role-permissions.store');
    Route::get('/role-permissions/{roleId}/edit',     [RolePermissionController::class, 'edit'])->name('role-permissions.edit');
    Route::put('/role-permissions/{roleId}',          [RolePermissionController::class, 'update'])->name('role-permissions.update');
    Route::delete('/role-permissions/{roleId}',       [RolePermissionController::class, 'destroy'])->name('role-permissions.destroy');
});

