<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 

class EventController extends Controller
{
    public function index(Request $request)
    {
        $searchKeyword = $request->input('search'); 
        $query = Event::where('user_id', Auth::id())
                      ->withCount('registrations');

        if ($searchKeyword) {
            $query->where('title', 'like', '%' . $searchKeyword . '%');
        }

        $events = $query->latest()->get();
                       
        return view('events.index', compact('events', 'searchKeyword'));
    }

    public function create()
    {
        return view('events.create');
    }

    public function edit($id)
    {
        $event = Event::where('user_id', Auth::id())->findOrFail($id);
        return view('events.edit', compact('event'));
    }

    public function update(Request $request, $id)
    {
        $event = Event::where('user_id', Auth::id())->findOrFail($id);
        $data = $request->all();
        $data['status'] = 'pending';

        if ($request->hasFile('poster')) {
            $data['poster'] = $request->file('poster')->store('posters', 'public');
        }

        if ($request->hasFile('proposal')) {
            $data['proposal'] = $request->file('proposal')->store('proposals', 'public');
        }

        $event->update($data);
        return redirect()->route('events.index')->with('success', 'Perubahan disimpan dan masuk antrean review!');
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $data['user_id'] = Auth::id();
        $data['event_code'] = 'EVT-' . strtoupper(Str::random(5));

        if ($request->hasFile('poster')) {
            $data['poster'] = $request->file('poster')->store('posters', 'public');
        }

        if ($request->hasFile('proposal')) {
            $data['proposal'] = $request->file('proposal')->store('proposals', 'public');
        }

        Event::create($data);
        return redirect()->route('events.index')->with('success', 'Acara berhasil diajukan!');
    }

    public function destroy($id)
    {
        $event = Event::where('user_id', Auth::id())->findOrFail($id);
        $event->delete();
        
        return redirect()->route('events.index')->with('success', 'Acara berhasil dihapus!');
    }
}