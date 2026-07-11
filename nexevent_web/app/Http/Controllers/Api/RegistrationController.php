<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Registration;

class RegistrationController extends Controller
{
    public function register(Request $request)
    {
        $userId = auth()->id();
        $eventID = $request->event_id;

        $targetEvent = Event::find($eventID);

        if (!$targetEvent) {
            return response()->json(['message' => 'Acara tidak ditemukan'], 404);
        }

        $exists = Registration::where('user_id', $userId)->where('event_id', $eventID)->exists();
        
        if ($exists) {
            return response()->json(['message' => 'Sudah terdaftar'], 400);
        }

        $clash = Registration::where('user_id', $userId)
            ->whereHas('event', function($query) use ($targetEvent) {
                $query->where('event_date', $targetEvent->event_date);
            })->exists();

        if ($clash) {
            return response()->json(['message' => 'Jadwal bentrok dengan acara lain!'], 400);
        }

        $count = Registration::where('event_id', $eventID)->where('status', 'terdaftar')->count();
        $status = ($count < $targetEvent->capacity) ? 'terdaftar' : 'waitlist';

        $reg = Registration::create([
            'user_id' => $userId,
            'event_id' => $eventID,
            'status' => $status
        ]);

        return response()->json(['message' => 'Berhasil', 'status' => $status, 'data' => $reg]);
    }

    public function cancelRegistration(Request $request)
    {
        $userId = $request->input('user_id');
        $eventID = $request->event_id;

        $registration = Registration::where('user_id', $userId)
            ->where('event_id', $eventID)
            ->where('status', 'terdaftar')
            ->first();

        if (!$registration) {
            return response()->json(['message' => 'Tiket aktif tidak ditemukan'], 404);
        }

        $registration->update(['status' => 'batal']);

        $nextInLine = Registration::where('event_id', $eventID)
            ->where('status', 'waitlist')
            ->orderBy('created_at', 'asc')
            ->first();

        if ($nextInLine) {
            $nextInLine->update(['status' => 'terdaftar']);
        }

        return response()->json([
            'message' => 'Pendaftaran berhasil dibatalkan',
            'promoted_user' => $nextInLine ? $nextInLine->user_id : null
        ]);
    }
}