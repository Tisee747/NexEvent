<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $totalAcara = Event::where('user_id', $userId)->count();

        $totalPendaftarBulanIni = Registration::whereHas('event', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })->whereMonth('created_at', now()->month)->count();

        $rejectedEvents = Event::where('user_id', $userId)->where('status', 'rejected')->get();

        return view('dashboard', compact('totalAcara', 'totalPendaftarBulanIni', 'rejectedEvents'));
    }
}