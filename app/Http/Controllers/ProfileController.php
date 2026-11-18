<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        protected ApiService $api
    ) {}

    public function show()
    {
        $response = $this->api->me();
        $user = ($response['success'] ?? false) ? ($response['data'] ?? []) : (session('auth_user') ?? []);

        return view('profile.show', compact('user'));
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password'      => ['required', 'string'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
        ], [
            'password.confirmed'    => 'Konfirmasi password baru tidak cocok.',
            'password.min'          => 'Password baru minimal 8 karakter.',
        ]);

        $response = $this->api->changePassword([
            'current_password'      => $request->current_password,
            'password'              => $request->password,
            'password_confirmation' => $request->password_confirmation,
        ]);

        if (!($response['success'] ?? false)) {
            return back()
                ->withInput()
                ->with('error', $response['message'] ?? 'Gagal mengubah password.');
        }

        return back()->with('success', 'Password berhasil diubah. Silakan login kembali jika diperlukan.');
    }
}
