<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_code',
        'user_id',
        'admin_id',
        'title',
        'description',
        'event_date',
        'capacity',
        'latitude',
        'longitude',
        'is_online', 
        'meeting_link',
        'poster_path',
        'proposal_path',
        'status',
        'reject_reason',
    ];

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function panitia()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }
}