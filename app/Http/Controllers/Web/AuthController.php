<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function loginPost(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            if (Auth::user()->status !== 'active') {
                Auth::logout();
                return back()->with('error', 'Akun kamu masih berstatus PENDING. Tunggu persetujuan dari Superadmin ya!');
            }

            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->with('error', 'Email atau Password salah!');
    }

    public function registerPost(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'organization' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);;

        User::create([
            'name' => $request->name,
            'organization' => $request->organization,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'admin',
            'status' => 'pending', 
        ]);

        return redirect()->route('login')->with('success', 'Pendaftaran berhasil! Silakan tunggu akun kamu di-approve oleh Kampus.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}