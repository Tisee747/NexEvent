<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    // Mengambil data untuk dashboard Admin
    public function organization(Request $request)
    {
        $userId = Auth::id(); 
        $totalAcara = Event::where('admin_id', $userId)->count();
        $totalPendaftarBulanIni = Registration::whereHas('event', function($query) use ($userId) {
            $query->where('admin_id', $userId);
        })->whereMonth('created_at', now()->month)->count();

        $rejectedEvents = Event::where('admin_id', $userId)->where('status', 'rejected')->get();

        return response()->json([
            'status' => 'success',
            'totalAcara' => $totalAcara,
            'totalPendaftarBulanIni' => $totalPendaftarBulanIni,
            'rejectedEvents' => $rejectedEvents
        ], 200);
    }

    // Mengambil data untuk dashboard Superadmin
    public function superadmin(Request $request)
    {
        $pendingReviews = Event::whereIn('status', ['pending', 'pending_superadmin'])->count();
        $totalOrganizations = User::where('role', 'admin')->where('status', 'active')->count();
        $totalAcaraKampus = Event::count();
        
        $pendingEvents = Event::with('panitia')
                            ->whereIn('status', ['pending', 'pending_superadmin'])
                            ->latest()
                            ->take(5)
                            ->get();

        return response()->json([
            'status' => 'success',
            'pendingReviews' => $pendingReviews,
            'totalOrganizations' => $totalOrganizations,
            'totalAcaraKampus' => $totalAcaraKampus,
            'pendingEvents' => $pendingEvents
        ], 200);
    }
}