<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Support\Facades\Auth;

class ParticipantController extends Controller
{
    // Mengambil daftar acara dan pendaftar untuk dashboard panitia
    public function index(Request $request)
    {
        $adminId = Auth::id();
        $eventId = $request->query('event_id');
        $search = $request->query('search');

        $events = Event::where('admin_id', $adminId)->select('id', 'title', 'capacity')->get();
        
        $registrations = [];
        $selectedEvent = null;
        $totalWaitlist = 0;

        if ($eventId) {
            $selectedEvent = Event::find($eventId);
            
            $query = Registration::with('user')->where('event_id', $eventId);
            
            if ($search) {
                $query->whereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }
            
            $registrations = $query->latest()->get();
            $totalWaitlist = Registration::where('event_id', $eventId)->where('status', 'waitlist')->count();
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'events' => $events,
                'selectedEvent' => $selectedEvent,
                'registrations' => $registrations,
                'totalWaitlist' => $totalWaitlist
            ]
        ], 200);
    }

    // Memperbarui status kehadiran (Hadir / Belum Hadir)
    public function markAttendance(Request $request, $id)
    {
        $request->validate([
            'attendance_status' => 'required|in:hadir,belum_hadir'
        ]);

        $registration = Registration::find($id);

        if (!$registration) {
            return response()->json(['status' => 'error', 'message' => 'Data pendaftar tidak ditemukan'], 404);
        }

        $registration->update([
            'attendance_status' => $request->attendance_status
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Status kehadiran berhasil diperbarui.',
            'data' => $registration
        ], 200);
    }

    // Membatalkan/menghapus pendaftar oleh panitia
    public function cancel(Request $request, $id)
    {
        $registration = Registration::find($id);
        
        if (!$registration) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
        }

        $registration->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Peserta berhasil didiskualifikasi/dihapus.'
        ], 200);
    }

    
}