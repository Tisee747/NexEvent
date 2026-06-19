<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'regex:/^[a-zA-Z0-9._%+-]+@student\.telkomuniversity\.ac\.id$/'],
            'password' => 'required'
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email atau password salah.'
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        
        if ($user->role === 'anggota organisasi' && $user->status === 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Keanggotaan organisasi Anda belum disetujui oleh Superadmin.'
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer'
            ]
        ], 200);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'organization' => 'nullable|string|max:255',
            'role' => 'required|in:mahasiswa,anggota_organisasi',
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users', 'regex:/^[a-zA-Z0-9._%+-]+@student\.telkomuniversity\.ac\.id$/' ],
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'organization' => $request->role == 'anggota_organisasi' ? $request->organization: null,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role ?? 'mahasiswa',
            'status' => $request->role == 'anggota_organisasi' ? 'pending' : 'active'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi berhasil. Menunggu persetujuan Superadmin.',
            'data' => $user
        ], 201);
    }

    public function user(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => $request->user()
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil'
        ], 200);
    }
}
