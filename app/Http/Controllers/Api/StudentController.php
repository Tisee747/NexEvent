<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    // 1. Mengambil Feed Acara
    public function feed()
    {
        $events = Event::with('panitia:id,organization,name')
            ->where('status', 'approved')
            ->whereDate('event_date', '>=', now())
            ->orderBy('event_date', 'asc')
            ->get();

        return response()->json(['status' => 'success', 'data' => $events]);
    }

    // 2. Mendaftar Acara & Logika Waitlist Otomatis
    public function register(Request $request, $eventId)
    {
        $user = Auth::user();
        $event = Event::find($eventId);

        if (!$event || $event->status !== 'approved') {
            return response()->json(['status' => 'error', 'message' => 'Acara tidak tersedia'], 404);
        }

        // Mencegah pendaftaran ganda
        if (Registration::where('user_id', $user->id)->where('event_id', $eventId)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Anda sudah terdaftar di acara ini'], 400);
        }

        // Cek sisa kuota kapasitas
        $currentRegistrations = Registration::where('event_id', $eventId)->where('status', 'utama')->count();
        $status = ($currentRegistrations < $event->capacity) ? 'utama' : 'waitlist';

        $registration = Registration::create([
            'user_id' => $user->id,
            'event_id' => $eventId,
            'status' => $status,
            'reg_code' => 'TIX-' . strtoupper(substr(md5(time() . $user->id), 0, 6)),
            'attendance_status' => 'belum_hadir'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => $status === 'utama' ? 'Berhasil mendaftar!' : 'Kuota penuh, Anda masuk daftar antrean (Waitlist).',
            'data' => $registration
        ], 201);
    }

    // 3. Melihat Daftar Tiket Saya
    public function myTickets()
    {
        $tickets = Registration::with('event')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();
            
        return response()->json(['status' => 'success', 'data' => $tickets]);
    }

    // 4. Membatalkan Pendaftaran (Oleh Mahasiswa)
    public function cancel($regId)
    {
        $reg = Registration::where('id', $regId)->where('user_id', Auth::id())->first();
        
        if (!$reg) {
            return response()->json(['status' => 'error', 'message' => 'Tiket tidak ditemukan'], 404);
        }

        $eventId = $reg->event_id;
        $wasUtama = $reg->status === 'utama';
        
        $reg->delete();

        // Jika yang batal adalah peserta utama, otomatis naikkan 1 orang dari waitlist
        if ($wasUtama) {
            $nextInLine = Registration::where('event_id', $eventId)
                                    ->where('status', 'waitlist')
                                    ->orderBy('created_at', 'asc')
                                    ->first();
            if ($nextInLine) {
                $nextInLine->update(['status' => 'utama']);
            }
        }

        return response()->json(['status' => 'success', 'message' => 'Pendaftaran dibatalkan.']);
    }

    //5. Mengecek apakah mahasiswa terdaftar sebagai panitia organisasi
    public function checkCommitteeStatus()
    {
        $user = Auth::user();
        
        $isCommittee = \Illuminate\Support\Facades\DB::table('organization_members')
                        ->where('email', $user->email)
                        ->exists();

        return response()->json([
            'status' => 'success',
            'is_committee' => $isCommittee
        ]);
    }
}