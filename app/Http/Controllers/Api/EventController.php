<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use App\Models\Event; // Nanti dipakai saat datanya sudah dinamis

class EventController extends Controller
{
    public function index()
    {
        // Untuk sementara kita panggil view-nya saja dulu
        return view('events.index');
    }
}