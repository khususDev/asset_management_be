<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        // 1. Ambil data user yang sedang login lewat token Sanctum
        $user = $request->user();

        // 2. Validasi data inputan
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6|confirmed', // 'confirmed' mewajibkan input password_confirmation di frontend
        ]);

        // 3. Proses update nama dan email
        $user->name = $validated['name'];
        $user->email = $validated['email'];

        // 4. Jika user mengisi password baru, enkripsi dan simpan
        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil Anda berhasil diperbarui',
            'data'    => [
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role ? $user->role->name : null
            ]
        ]);
    }
}
