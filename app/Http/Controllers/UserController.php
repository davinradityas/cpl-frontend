<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(protected ApiService $api) {}

    public function index(Request $request)
    {
        $query = array_filter([
            'role'      => $request->query('role'),
            'is_active' => $request->query('is_active'),
            'search'    => $request->query('search'),
            'per_page'  => 20,
        ], fn($v) => $v !== null && $v !== '');

        $resp = $this->api->getUsers($query);

        $usersData    = ($resp['success'] ?? false) ? ($resp['data'] ?? []) : [];
        $users        = $usersData['users']        ?? [];
        $pagination   = [
            'total'        => $usersData['total']        ?? 0,
            'current_page' => $usersData['current_page'] ?? 1,
            'last_page'    => $usersData['last_page']    ?? 1,
        ];
        $fakultasList = $usersData['fakultas_list']     ?? [];
        $prodiList    = $usersData['prodi_list']        ?? [];

        return view('users.index', compact('users', 'pagination', 'fakultasList', 'prodiList'));
    }

    public function store(Request $request)
    {
        $resp = $this->api->createUser($request->only(
            'name', 'email', 'password', 'role', 'fakultas_id', 'prodi_id'
        ));

        if ($resp['success'] ?? false) {
            return redirect()->route('users.index')
                ->with('success', 'User berhasil ditambahkan.');
        }

        return back()->withInput()
            ->with('error', $this->extractError($resp))
            ->with('open_modal', 'tambah');
    }

    public function update(Request $request, string $id)
    {
        $resp = $this->api->updateUser($id, $request->only(
            'name', 'email', 'role', 'fakultas_id', 'prodi_id', 'is_active'
        ));

        if ($resp['success'] ?? false) {
            return redirect()->route('users.index')
                ->with('success', 'User berhasil diperbarui.');
        }

        return back()->withInput()
            ->with('error', $this->extractError($resp))
            ->with('open_edit_id', $id);
    }

    public function destroy(string $id)
    {
        $resp = $this->api->deleteUser($id);

        if ($resp['success'] ?? false) {
            return redirect()->route('users.index')
                ->with('success', 'User berhasil dihapus.');
        }

        return back()->with('error', $this->extractError($resp));
    }

    public function resetPassword(Request $request, string $id)
    {
        $resp = $this->api->resetUserPassword($id, $request->only('password', 'password_confirmation'));

        if ($resp['success'] ?? false) {
            return redirect()->route('users.index')
                ->with('success', 'Password user berhasil direset.');
        }

        return back()->with('error', $this->extractError($resp));
    }

    public function toggleActive(string $id)
    {
        $resp = $this->api->toggleUserActive($id);

        if ($resp['success'] ?? false) {
            $status = ($resp['data']['is_active'] ?? true) ? 'diaktifkan' : 'dinonaktifkan';
            return redirect()->route('users.index')
                ->with('success', "User berhasil {$status}.");
        }

        return back()->with('error', $this->extractError($resp));
    }

    private function extractError(array $resp): string
    {
        if (!empty($resp['errors']) && is_array($resp['errors'])) {
            $messages = [];
            foreach ($resp['errors'] as $fieldErrors) {
                // Bisa berupa string maupun array string
                if (is_array($fieldErrors)) {
                    array_push($messages, ...$fieldErrors);
                } else {
                    $messages[] = (string) $fieldErrors;
                }
            }
            return implode(' ', $messages);
        }
        return $resp['message'] ?? 'Terjadi kesalahan.';
    }
}
