<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventController extends Controller
{
    // Mengambil daftar acara
    public function index(Request $request)
    {
        $adminId = $request->query('admin_id');
        $searchKeyword = $request->query('search'); 

        $query = Event::withCount('registrations');

        if ($adminId) {
            $query->where('admin_id', $adminId);
        }

        if ($searchKeyword) {
            $query->where('title', 'like', '%' . $searchKeyword . '%');
        }

        $events = $query->latest()->get();
                       
        return response()->json([
            'status' => 'success',
            'data' => $events
        ], 200);
    }

    // Mengambil detail satu acara
    public function show($id)
    {
        $event = Event::with('panitia')->withCount('registrations')->find($id);

        if (!$event) {
            return response()->json(['status' => 'error', 'message' => 'Acara tidak ditemukan'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $event
        ], 200);
    }

    // Menyimpan acara baru
    public function store(Request $request)
    {
        $request->validate([
            'admin_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'event_date' => 'required|date',
            'capacity' => 'required|integer|min:1',
            'description' => 'required|string',
            'is_online' => 'required|boolean',
            'proposal_path' => 'required|mimes:pdf|max:5120',
            'poster_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->except(['poster_path', 'proposal_path']);
        $data['user_id'] = $request->admin_id; 
        $data['event_code'] = 'EVT-' . strtoupper(Str::random(6));
        $data['status'] = 'pending';

        if ($request->is_online == 1) {
            $data['latitude'] = null;
            $data['longitude'] = null;
        } else {
            $data['meeting_link'] = null;
        }

        if ($request->hasFile('poster_path')) {
            $data['poster_path'] = $request->file('poster_path')->store('posters', 'public');
        }

        if ($request->hasFile('proposal_path')) {
            $data['proposal_path'] = $request->file('proposal_path')->store('proposals', 'public');
        }

        $event = Event::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Acara berhasil diajukan',
            'data' => $event
        ], 201);
    }

    // Memperbarui acara yang ada
    public function update(Request $request, $id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['status' => 'error', 'message' => 'Acara tidak ditemukan'], 404);
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'event_date' => 'required|date',
            'capacity' => 'required|integer|min:1',
            'description' => 'required|string',
            'is_online' => 'required|boolean',
            'proposal_path' => 'nullable|mimes:pdf|max:5120',
            'poster_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->except(['poster_path', 'proposal_path']);
        $data['status'] = 'pending';

        if ($request->is_online == 1) {
            $data['latitude'] = null;
            $data['longitude'] = null;
        } else {
            $data['meeting_link'] = null;
        }

        if ($request->hasFile('poster_path')) {
            if ($event->poster_path) Storage::disk('public')->delete($event->poster_path);
            $data['poster_path'] = $request->file('poster_path')->store('posters', 'public');
        }

        if ($request->hasFile('proposal_path')) {
            if ($event->proposal_path) Storage::disk('public')->delete($event->proposal_path);
            $data['proposal_path'] = $request->file('proposal_path')->store('proposals', 'public');
        }

        $event->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Perubahan disimpan',
            'data' => $event
        ], 200);
    }

    // Menghapus acara
    public function destroy($id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['status' => 'error', 'message' => 'Acara tidak ditemukan'], 404);
        }
        
        if ($event->poster_path) Storage::disk('public')->delete($event->poster_path);
        if ($event->proposal_path) Storage::disk('public')->delete($event->proposal_path);
        
        $event->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Acara berhasil dihapus'
        ], 200);
    }

    // Memperbarui status proposal internal dari Anggota (Oleh Ketua/Admin)
    public function updateInternalStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending_superadmin,rejected',
            'reject_reason' => 'nullable|string'
        ]);

        $event = Event::find($id);

        if (!$event) {
            return response()->json(['status' => 'error', 'message' => 'Acara tidak ditemukan'], 404);
        }

        $event->update([
            'status' => $request->status,
            'reject_reason' => $request->status === 'rejected' ? $request->reject_reason : null
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Proposal berhasil diproses.'
        ], 200);
    }
}