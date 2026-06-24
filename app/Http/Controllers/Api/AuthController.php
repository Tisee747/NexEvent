<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Models\OtpCode;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['status' => 'error', 'message' => 'Email atau password salah.'], 401);
        }

        if ($user->role === 'admin' && $user->status === 'pending') {
            return response()->json(['status' => 'error', 'message' => 'Akun organisasi Anda belum disetujui oleh Superadmin.'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ]);
    }

    public function register(Request $request)
    {
        return response()->json(['message' => 'Fungsi registrasi organisasi']);
    }

    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $otp = rand(100000, 999999);

        \App\Models\OtpCode::updateOrCreate(
            ['email' => $request->email],
            ['otp' => $otp, 'expires_at' => \Carbon\Carbon::now()->addMinutes(5)]
        );

        \Illuminate\Support\Facades\Mail::raw("Kode OTP Anda adalah $otp", function ($message) use ($request) {
            $message->to($request->email)->subject('Kode OTP Registrasi NexEvent');
        });

        return response()->json(['status' => 'success', 'message' => 'OTP terkirim ke email']);
    }

    public function registerStudent(Request $request)
    {
        $validOtp = \App\Models\OtpCode::where('email', $request->email)
                           ->where('otp', $request->otp)
                           ->where('expires_at', '>', \Carbon\Carbon::now())
                           ->first();

        if (!$validOtp) {
            return response()->json(['status' => 'error', 'message' => 'Kode OTP salah atau sudah kedaluwarsa'], 400);
        }

        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'student',
            'status' => 'active',
            'nim' => $request->nim,
            'fakultas' => $request->fakultas,
            'program_studi' => $request->program_studi,
        ]);

        $validOtp->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Akun mahasiswa berhasil dibuat. Silakan masuk.',
            'data' => $user
        ], 201);
    }

    public function user(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        return response()->json($user, 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['status' => 'success', 'message' => 'Logout berhasil']);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $data = $request->only('name', 'fakultas', 'program_studi', 'angkatan');

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Profil berhasil diperbarui.',
            'data' => $user
        ]);
    }
}