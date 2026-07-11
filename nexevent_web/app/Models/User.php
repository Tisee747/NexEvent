<?php

namespace App\Models;

// 1. Tambahkan baris pemanggilan Sanctum ini
use Laravel\Sanctum\HasApiTokens;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
    'name', 
    'email', 
    'password', 
    'role', 
    'status', 
    'organization',
    'nim', 
    'fakultas', 
    'program_studi', 
    'angkatan'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $prefix = 'USR-';
            if ($model->role === 'admin') {
                $prefix = 'ADM-';
            } elseif ($model->role === 'student') {
                $prefix = 'STD-';
            } elseif ($model->role === 'superadmin') {
                $prefix = 'SUP-';
            }
            
            $model->user_code = $prefix . strtoupper(Str::random(6));
        });
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}