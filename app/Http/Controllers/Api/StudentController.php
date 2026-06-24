<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    public function feed()
    {
        // Menambahkan relasi panitia dan menghitung pendaftar untuk sisa kuota
        $events = Event::with('panitia')
            ->withCount(['registrations' => function($q) {
                $q->where('status', 'utama');
            }])
            ->where('status', 'approved')
            ->orderBy('event_date', 'asc')
            ->get();
            
        return response()->json(['status' => 'success', 'data' => $events]);
    }

    public function register(Request $request, $eventId)
    {
        $user = Auth::user();
        $event = Event::find($eventId);

        if (!$event || $event->status !== 'approved') {
            return response()->json(['status' => 'error', 'message' => 'Acara tidak tersedia'], 404);
        }

        if (Registration::where('user_id', $user->id)->where('event_id', $eventId)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Anda sudah terdaftar di acara ini'], 400);
        }

        // PERBAIKAN LOGIKA KUOTA
        $utamaCount = Registration::where('event_id', $eventId)->where('status', 'utama')->count();
        $status = ($utamaCount >= $event->capacity) ? 'waitlist' : 'utama';

        Registration::create([
            'reg_code' => 'REG-' . strtoupper(Str::random(6)),
            'event_id' => $eventId,
            'user_id' => $user->id,
            'status' => $status,
            'attendance_status' => 'belum_hadir'
        ]);

        return response()->json(['status' => 'success', 'message' => 'Berhasil mendaftar acara.']);
    }

    public function myTickets(Request $request)
    {
        $user = Auth::user();
        $tickets = Registration::with('event')->where('user_id', $user->id)->latest()->get();
        
        $data = $tickets->map(function($ticket) {
            return [
                'event_id' => $ticket->event_id,
                'event_title' => $ticket->event->title ?? 'Acara',
                'event_date' => $ticket->event->event_date ?? '-',
                'status' => $ticket->status,
            ];
        });

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    public function cancel(Request $request, $regId)
    {
        $user = Auth::user();
        $registration = Registration::where('user_id', $user->id)->where('event_id', $regId)->first();

        if (!$registration) {
            return response()->json(['status' => 'error', 'message' => 'Tiket tidak ditemukan.']);
        }

        $registration->delete();

        $nextInLine = Registration::where('event_id', $regId)
                                    ->where('status', 'waitlist')
                                    ->orderBy('created_at', 'asc')
                                    ->first();
        if ($nextInLine) {
            $nextInLine->update(['status' => 'utama']);
        }

        return response()->json(['status' => 'success', 'message' => 'Pendaftaran dibatalkan.']);
    }

    public function checkCommitteeStatus(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['status' => 'success', 'is_committee' => false]);
        $isCommittee = \App\Models\OrganizationMember::where('user_id', $user->id)->exists();
        return response()->json(['status' => 'success', 'is_committee' => $isCommittee]);
    }

    public function myOrganizations(Request $request)
    {
        $user = $request->user();
        $memberships = \App\Models\OrganizationMember::where('user_id', $user->id)->get();

        $data = [];
        $pesanDebug = "Relasi: " . $memberships->count() . " | ";

        foreach ($memberships as $member) {
            $adminId = $member->admin_id;
            $pesanDebug .= "AdminID: " . ($adminId ?? 'KOSONG') . " -> ";

            if ($adminId) {
                $admin = \App\Models\User::find($adminId);
                if ($admin) {
                    $pesanDebug .= "Ketemu ({$admin->name}) | ";
                    $data[] = [
                        'id' => $admin->id,
                        'name' => $admin->organization ?? $admin->name ?? 'Organisasi Mahasiswa'
                    ];
                } else {
                    $pesanDebug .= "Admin Tidak Ada Di Database | ";
                }
            }
        }

        return response()->json([
            'message' => 'Berhasil', 
            'data' => $data,
            'debug' => $pesanDebug
        ], 200);
    }
}