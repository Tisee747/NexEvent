<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;

class SuperadminController extends Controller
{
    public function index()
    {
        if (Auth::user()->role !== 'superadmin') {
            return redirect('/')->with('error', 'Akses ditolak! Anda bukan Superadmin.');
        }

        $pendingUsers = User::where('status', 'pending')->get();
        $pendingEvents = Event::with('panitia')->where('status', 'pending')->get();

        return view('superadmin.index', compact('pendingUsers', 'pendingEvents'));
    }

    public function approveUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['status' => 'active']);
        return back()->with('success', 'Akun organisasi berhasil diaktifkan!');
    }

    public function updateEventStatus(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $event->update([
            'status' => $request->action,
            'reject_reason' => $request->reject_reason ?? null
        ]);
        
        return redirect()->route('superadmin.index')->with('success', 'Status acara berhasil diperbarui.');
    }

    public function allEvents()
    {
        $events = Event::with('panitia')->latest()->get();
        return view('superadmin.events', compact('events'));
    }

    public function showEvent($id)
    {
        $event = Event::with('panitia')->findOrFail($id);
        return view('superadmin.event_detail', compact('event'));
    }

    public function dashboard()
    {
        $totalEvent = Event::where('status', 'approved')->count();
        $totalMhs = User::where('role', 'user')->count();
        $totalOrg = User::where('role', 'admin')->count();

        return view('superadmin.dashboard', compact('totalEvent', 'totalMhs', 'totalOrg'));
    }

    public function organizations()
    {
        $organizations = User::where('role', 'admin')->where('status', 'active')->get();
        return view('superadmin.organizations', compact('organizations'));
    }

    public function updateOrganization(Request $request, $id)
    {
        $org = User::findOrFail($id);
        $org->update([
            'name' => $request->name,
            'organization' => $request->organization,
            'email' => $request->email
        ]);
        return back()->with('success', 'Data organisasi berhasil diperbarui!');
    }

    public function deleteOrganization($id)
    {
        User::findOrFail($id)->delete();
        return back()->with('success', 'Organisasi berhasil dihapus dari sistem!');
    }

}