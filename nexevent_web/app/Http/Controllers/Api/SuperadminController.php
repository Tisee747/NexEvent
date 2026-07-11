<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\User;

class SuperadminController extends Controller
{

    public function pendingEvents()
    {
        $pendingUsers = User::where('role', 'admin')->where('status', 'pending')->get();
        $pendingEvents = Event::with('panitia')->whereIn('status', ['pending', 'pending_superadmin'])->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'pendingUsers' => $pendingUsers,
                'pendingEvents' => $pendingEvents
            ]
        ]);
    }

    public function updateEventStatus(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approved,rejected',
            'reject_reason' => 'nullable|string'
        ]);

        $event = Event::find($id);

        if (!$event) {
            return response()->json(['status' => 'error', 'message' => 'Acara tidak ditemukan'], 404);
        }

        $event->update([
            'status' => $request->action,
            'reject_reason' => $request->action === 'rejected' ? $request->reject_reason : null
        ]);

        return response()->json(['status' => 'success', 'message' => 'Status acara berhasil diperbarui']);
    }

    public function approveUser($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Pengguna tidak ditemukan'], 404);
        }

        $user->update(['status' => 'active']);
        return response()->json(['status' => 'success', 'message' => 'Akun organisasi diaktifkan']);
    }

    public function organizations()
    {
        $organizations = User::where('role', 'admin')->get();
        return response()->json(['status' => 'success', 'data' => $organizations]);
    }

    public function updateOrganization(Request $request, $id)
    {
        $org = User::find($id);
        if (!$org) return response()->json(['status' => 'error'], 404);

        $org->update($request->only('name', 'organization', 'email'));
        return response()->json(['status' => 'success', 'message' => 'Data organisasi diperbarui']);
    }

    public function deleteOrganization($id)
    {
        $org = User::find($id);
        if (!$org) return response()->json(['status' => 'error'], 404);

        $org->delete();
        return response()->json(['status' => 'success', 'message' => 'Organisasi berhasil dihapus']);
    }

    public function allEvents()
    {
        $events = Event::with('panitia')->latest()->get();
        return response()->json(['status' => 'success', 'data' => $events]);
    }
}