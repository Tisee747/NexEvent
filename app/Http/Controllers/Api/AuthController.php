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
        
        if ($user->role === 'anggota_organisasi' && $user->status === 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun Anda masih menunggu persetujuan.'
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
            'nim' => 'required|string|max:20',
            'fakultas' => 'nullable|string|max:255',
            'program_studi' => 'nullable|string|max:255',
            'angkatan' => 'nullable|string|max:10',
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
            'role' => $request->role,
            'status' => $request->role == 'anggota_organisasi' ? 'pending' : 'active',
            'nim' => $request->nim,
            'fakultas' => $request->fakultas,
            'program_studi' => $request->program_studi,
            'angkatan' => $request->angkatan,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => $request->role == 'anggota_organisasi' ? 'Registrasi berhasil. Menunggu persetujuan Superadmin.' : 'Registrasi berhasil.',
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
