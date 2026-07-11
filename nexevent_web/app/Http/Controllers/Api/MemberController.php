<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\OrganizationMember;

class MemberController extends Controller
{
    public function index()
    {
        $members = OrganizationMember::with('user')->get();
        return view('member.index', compact('members'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'admin_id' => 'required'
        ]);
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Mahasiswa dengan email tersebut tidak ditemukan di sistem.']);
        }

        OrganizationMember::firstOrCreate([
            'user_id' => $user->id,
            'admin_id' => $request->admin_id,
        ], [
            'position' => 'Anggota Panitia'
        ]);

        return back()->with('success', 'Panitia berhasil ditambahkan.');
    }

    public function destroy(OrganizationMember $member)
    {
        $member->delete();
        return back()->with('success', 'Panitia berhasil dihapus.');
    }
}