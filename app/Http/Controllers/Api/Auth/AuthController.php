<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Services\Authorization\RoleAccessService;
use Illuminate\Http\Request;
use App\Models\Administration\User; // Sesuaikan path model User Anda jika berbeda
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(private readonly RoleAccessService $roleAccessService)
    {
    }

    public function login(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Cari User berdasarkan Email
        $user = User::where('email', $request->email)->first();

        // 3. Cek apakah User ada dan Password cocok
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau Password salah!'
            ], 401); // 401 = Unauthorized
        }

        $user->loadMissing(['role.permissions']);
        $access = $this->roleAccessService->resolveForAuth($user);

        // 5. Cetak Tiket Masuk (Token)
        $token = $user->createToken('erp-token')->plainTextToken;

        // 6. Kembalikan Respons ke Vue
        return response()->json([
            'success' => true,
            'message' => 'Login berhasil!',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $access['role_names'],
                    'permissions' => $access['permission_names']
                ],
                'token' => $token
            ]
        ]);
    }

    public function logout(Request $request)
    {
        // Hapus token yang sedang digunakan saat ini
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil!'
        ]);
    }
}
