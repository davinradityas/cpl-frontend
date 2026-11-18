<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected ApiService $api
    ) {}

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $response = $this->api->login($request->only('email', 'password'));

        if (! ($response['success'] ?? false)) {
            return back()
                ->withInput()
                ->with('error', $response['message'] ?? 'Login gagal.')
                ->with('errors_api', $response['errors'] ?? []);
        }

        $data = $response['data'] ?? [];
        $user = $data['user'] ?? [];

        session([
            'auth_token'       => $data['token']                ?? null,
            'auth_user'        => $user,
            'auth_permissions' => $user['permissions']          ?? [],
        ]);

        return redirect()
            ->route('dashboard.index')
            ->with('success', 'Login berhasil.');
    }

    public function logout()
    {
        $this->api->logout();

        session()->forget(['auth_token', 'auth_user', 'auth_permissions']);
        session()->invalidate();
        session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Logout berhasil.');
    }
}
