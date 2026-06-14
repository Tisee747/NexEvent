<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParticipantController extends Controller
{
    public function index(Request $request)
    {
        $events = Event::where('user_id', Auth::id())->get(); 
        $selectedEventId = $request->input('event_id', $events->first()->id ?? null);
        $searchKeyword = $request->input('search'); 
        
        $selectedEvent = null;
        $registrations = collect();
        $totalUtama = 0;
        $totalWaitlist = 0;

        if ($selectedEventId) {
            $selectedEvent = Event::find($selectedEventId);
            
            $query = Registration::with('user')->where('event_id', $selectedEventId);

            if ($searchKeyword) {
                $query->whereHas('user', function($q) use ($searchKeyword) {
                    $q->where('name', 'like', '%' . $searchKeyword . '%')
                      ->orWhere('email', 'like', '%' . $searchKeyword . '%');
                });
            }

            $registrations = $query->get();
                                
            $totalUtama = Registration::where('event_id', $selectedEventId)->where('status', 'utama')->count();
            $totalWaitlist = Registration::where('event_id', $selectedEventId)->where('status', 'waitlist')->count();
        }
        return view('participants.index', compact('events', 'selectedEvent', 'selectedEventId', 'registrations', 'totalUtama', 'totalWaitlist', 'searchKeyword'));
    }

    public function attendance(Request $request)
    {
        $events = Event::where('user_id', Auth::id())->where('status', 'approved')->get();
        $selectedEventId = $request->input('event_id', $events->first()->id ?? null);
        
        $registrations = collect();
        if ($selectedEventId) {
            $registrations = Registration::with('user')
                                ->where('event_id', $selectedEventId)
                                ->where('status', 'utama')
                                ->get();
        }

        return view('participants.attendance', compact('events', 'selectedEventId', 'registrations'));
    }

    public function markAttendance(Request $request, $id)
    {
        $reg = Registration::findOrFail($id);
        $reg->update([
            'attendance_status' => $request->attendance_status
        ]);

        return back()->with('success', 'Status kehadiran peserta berhasil diupdate!');
    }
}