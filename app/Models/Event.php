<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
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

    public function panitia()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }
}