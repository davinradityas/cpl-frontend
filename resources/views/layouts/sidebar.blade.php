@php
    $user        = session('auth_user');
    $role        = $user['role'] ?? null;
    $sessionPerms= session('auth_permissions', []);
    // Superadmin dapat wildcard '*'
    $isSuperadmin = in_array('*', $sessionPerms) || $role === 'superadmin';

    // Helper: cek apakah user punya permission
    $hasPermission = fn(string $perm) => $isSuperadmin || in_array($perm, $sessionPerms);

    $roleLabels = [
        'superadmin'    => 'Superadmin',
        'admin_fakultas' => 'Admin Fakultas',
        'admin_kaprodi'  => 'Admin Kaprodi',
    ];

    $menuGroups = [
        [
            'title' => 'Utama',
            'items' => [
                [
                    'label'      => 'Dashboard',
                    'route'      => 'dashboard.index',
                    'active'     => 'dashboard.*',
                    'roles'      => ['superadmin', 'admin_fakultas', 'admin_kaprodi'],
                    'permission' => null, // selalu tampil
                ],
            ],
        ],
        [
            'title' => 'Master Institusi',
            'items' => [
                [
                    'label'      => 'Data Jenis Penilaian',
                    'route'      => 'jenis-penilaians.index',
                    'active'     => 'jenis-penilaians.*',
                    'roles'      => ['superadmin', 'admin_fakultas'],
                    'permission' => 'manage.jenis-penilaian',
                ],
                [
                    'label'      => 'Jenis Mata Kuliah',
                    'route'      => 'jenis-matkul.index',
                    'active'     => 'jenis-matkul.*',
                    'roles'      => ['superadmin', 'admin_fakultas', 'admin_kaprodi'],
                    'permission' => 'manage.jenis-matkul',
                ],
                [
                    'label'      => 'Data Fakultas',
                    'route'      => 'fakultas.index',
                    'active'     => 'fakultas.*',
                    'roles'      => ['superadmin'],
                    'permission' => null, // superadmin saja
                ],
                [
                    'label'      => 'Data Prodi',
                    'route'      => 'prodis.index',
                    'active'     => 'prodis.*',
                    'roles'      => ['superadmin', 'admin_fakultas'],
                    'permission' => 'manage.prodi',
                ],
                [
                    'label'      => 'Data Kurikulum',
                    'route'      => 'kurikulums.index',
                    'active'     => 'kurikulums.*',
                    'roles'      => ['superadmin', 'admin_fakultas'],
                    'permission' => 'manage.kurikulum',
                ],
                [
                    'label'      => 'Data Kurikulum Prodi',
                    'route'      => 'kurikulum-prodis.index',
                    'active'     => 'kurikulum-prodis.*',
                    'roles'      => ['superadmin', 'admin_fakultas'],
                    'permission' => 'manage.kurikulum-prodi',
                ],
            ],
        ],
        [
            'title' => 'Akademik OBE',
            'items' => [
                [
                    'label'      => 'Data CPL',
                    'route'      => 'cpls.index',
                    'active'     => 'cpls.*',
                    'roles'      => ['superadmin', 'admin_fakultas', 'admin_kaprodi'],
                    'permission' => 'manage.cpls',
                ],
                [
                    'label'      => 'Data CPMK',
                    'route'      => 'cpmks.index',
                    'active'     => 'cpmks.*',
                    'roles'      => ['superadmin', 'admin_fakultas', 'admin_kaprodi'],
                    'permission' => 'manage.cpmks',
                ],
                [
                    'label'      => 'Data Mata Kuliah',
                    'route'      => 'mata-kuliahs.index',
                    'active'     => 'mata-kuliahs.*',
                    'roles'      => ['superadmin', 'admin_fakultas', 'admin_kaprodi'],
                    'permission' => 'manage.mata-kuliahs',
                ],
                [
                    'label'      => 'Nilai CPL',
                    'route'      => 'nilai-cpl.index',
                    'active'     => 'nilai-cpl.*',
                    'roles'      => ['superadmin', 'admin_fakultas', 'admin_kaprodi'],
                    'permission' => 'manage.nilai-cpl',
                ],
            ],
        ],
        [
            'title' => 'Operasional Nilai',
            'items' => [
                [
                    'label'      => 'Data Mahasiswa',
                    'route'      => 'mahasiswas.index',
                    'active'     => 'mahasiswas.*',
                    'roles'      => ['superadmin', 'admin_fakultas', 'admin_kaprodi'],
                    'permission' => 'manage.mahasiswas',
                ],
                [
                    'label'      => 'Laporan & Cetak',
                    'route'      => 'reports.index',
                    'active'     => 'reports.*',
                    'roles'      => ['superadmin', 'admin_fakultas', 'admin_kaprodi'],
                    'permission' => 'manage.laporan',
                ],
                [
                    'label'      => 'Input Nilai',
                    'route'      => 'input-nilai.select',
                    'active'     => 'input-nilai.*',
                    'roles'      => ['superadmin', 'admin_fakultas', 'admin_kaprodi'],
                    'permission' => 'manage.input-nilai',
                ],
                [
                    'label'      => 'Import Data',
                    'route'      => 'import.index',
                    'active'     => 'import.*',
                    'roles'      => ['superadmin', 'admin_fakultas', 'admin_kaprodi'],
                    'permission' => 'manage.import',
                ],
                [
                    'label'      => 'Kalkulasi',
                    'route'      => 'kalkulasi.index',
                    'active'     => 'kalkulasi.*',
                    'roles'      => ['superadmin', 'admin_fakultas', 'admin_kaprodi'],
                    'permission' => 'manage.kalkulasi',
                ],
            ],
        ],
        [
            'title' => 'KONFIGURASI',
            'items' => [
                [
                    'label'  => 'Data Kategori Nilai',
                    'route'  => 'kategori-nilais.index',
                    'active' => 'kategori-nilais.*',
                    'roles'  => ['superadmin'],
                    'permission' => 'manage.kategori-nilai',
                    'icon'   => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>',
                ],
                [
                    'label'  => 'Manajemen User',
                    'route'  => 'users.index',
                    'active' => 'users.*',
                    'roles'  => ['superadmin'],
                    'permission' => null,
                    'icon'   => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
                ],
                [
                    'label'  => 'Hak Akses Role',
                    'route'  => 'role-permissions.index',
                    'active' => 'role-permissions.*',
                    'roles'  => ['superadmin'],
                    'permission' => null,
                    'icon'   => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>',
                ],
            ],
        ],
    ];
@endphp

<div x-cloak x-show="sidebarOpen" class="fixed inset-0 z-40 lg:hidden">
    <button type="button"
            class="absolute inset-0 h-full w-full bg-slate-900/40"
            aria-label="Tutup menu"
            @click="sidebarOpen = false"></button>

    <aside class="relative z-50 flex h-full max-h-screen w-72 max-w-[85vw] flex-col border-r border-[#d7e7e9] bg-white shadow-xl">
        <div class="border-b border-[#d7e7e9] px-5 py-4 shrink-0">
            <div class="flex min-w-0 items-center gap-3">
                <div class="rounded-lg border border-[#d7e7e9] bg-white p-1.5 shadow-[0_10px_30px_rgba(14,122,134,0.08)]">
                    <img src="{{ asset('images/tsu logo.png') }}" alt="TSU" class="h-10 w-auto shrink-0">
                </div>
                <div class="min-w-0">
                    <div class="font-bold text-sm text-[#102a2f] leading-tight">Capaian Pembelajaran<br>Lulusan</div>
                </div>
            </div>
        </div>

        <nav class="flex-1 min-h-0 space-y-5 overflow-y-auto p-4">
            @include('layouts._sidebar-menu', ['menuGroups' => $menuGroups, 'role' => $role])
        </nav>

        <div class="border-t border-[#d7e7e9] p-4 shrink-0">
            <div class="rounded-lg bg-[#e8f6f7] px-3 py-2 text-xs leading-5 text-[#075866]">
                Menu mengikuti hak akses {{ $roleLabels[$role] ?? 'Pengguna' }}.
            </div>
        </div>
    </aside>
</div>

<aside class="hidden h-screen max-h-screen shrink-0 lg:flex lg:w-72 lg:flex-col border-r border-[#d7e7e9] bg-white/95 backdrop-blur">
    <div class="border-b border-[#d7e7e9] px-5 py-4 shrink-0">
        <div class="flex min-w-0 items-center gap-3">
            <div class="rounded-lg border border-[#d7e7e9] bg-white p-1.5 shadow-[0_10px_30px_rgba(14,122,134,0.08)]">
                <img src="{{ asset('images/tsu logo.png') }}" alt="TSU" class="h-10 w-auto shrink-0">
            </div>
            <div class="min-w-0">
                <div class="font-bold text-sm text-[#102a2f] leading-tight">Capaian Pembelajaran<br>Lulusan</div>
            </div>
        </div>
    </div>

    <nav class="flex-1 min-h-0 space-y-5 overflow-y-auto p-4">
        @include('layouts._sidebar-menu', ['menuGroups' => $menuGroups, 'role' => $role])
    </nav>

    <div class="border-t border-[#d7e7e9] p-4 shrink-0">
        <div class="rounded-lg bg-[#e8f6f7] px-3 py-2 text-xs leading-5 text-[#075866]">
            Menu mengikuti hak akses {{ $roleLabels[$role] ?? 'Pengguna' }}.
        </div>
    </div>
</aside>


