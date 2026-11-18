<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    public function __construct(protected ApiService $api) {}

    /** Halaman tabel daftar role */
    public function index()
    {
        $resp = $this->api->getRolePermissions();
        $data = ($resp['success'] ?? false) ? ($resp['data'] ?? []) : [];

        $roles       = $data['roles']       ?? [];
        $permissions = $data['permissions'] ?? [];

        // Kelompokkan permissions per group untuk modal Tambah
        $groups = [];
        foreach ($permissions as $perm) {
            $groups[$perm['group']][] = $perm;
        }

        $openModal = session('open_modal', null);

        return view('role-permissions.index', compact('roles', 'groups', 'openModal'));
    }

    /** Simpan role baru */
    public function store(Request $request)
    {
        $permissions = $request->input('permissions', []);

        $resp = $this->api->createRole([
            'name'        => $request->input('name'),
            'permissions' => $permissions,
        ]);

        if ($resp['success'] ?? false) {
            return redirect()->route('role-permissions.index')
                ->with('success', 'Role berhasil dibuat.');
        }

        return back()->withInput()
            ->with('error', $this->extractError($resp))
            ->with('open_modal', 'tambah');
    }

    /** Halaman edit permission untuk satu role */
    public function edit(string $roleId)
    {
        $resp = $this->api->getRoleById($roleId);
        if (!($resp['success'] ?? false)) {
            return redirect()->route('role-permissions.index')
                ->with('error', 'Role tidak ditemukan.');
        }

        $data        = $resp['data'] ?? [];
        $role        = $data['role']        ?? [];
        $permissions = $data['permissions'] ?? [];

        // Kelompokkan
        $groups = [];
        foreach ($permissions as $perm) {
            $groups[$perm['group']][] = $perm;
        }

        return view('role-permissions.edit', compact('role', 'groups'));
    }

    /** Simpan perubahan permission + nama role */
    public function update(Request $request, string $roleId)
    {
        $resp = $this->api->updateRolePermissions($roleId, [
            'name'        => $request->input('name') ?: null,
            'permissions' => $request->input('permissions', []),
        ]);

        if ($resp['success'] ?? false) {
            return redirect()->route('role-permissions.index')
                ->with('success', 'Role berhasil diperbarui.');
        }

        return back()->with('error', $this->extractError($resp));
    }

    /** Hapus role */
    public function destroy(string $roleId)
    {
        $resp = $this->api->deleteRole($roleId);

        if ($resp['success'] ?? false) {
            return redirect()->route('role-permissions.index')
                ->with('success', 'Role berhasil dihapus.');
        }

        return back()->with('error', $this->extractError($resp));
    }

    private function extractError(array $resp): string
    {
        if (!empty($resp['errors']) && is_array($resp['errors'])) {
            $msgs = [];
            foreach ($resp['errors'] as $v) {
                is_array($v) ? array_push($msgs, ...$v) : ($msgs[] = $v);
            }
            return implode(' ', $msgs);
        }
        return $resp['message'] ?? 'Terjadi kesalahan.';
    }
}
