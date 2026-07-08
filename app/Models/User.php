<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relasi pivot: Seorang user (operator) bisa ditugaskan ke banyak kolam
    public function kolams()
    {
        return $this->belongsToMany(Kolam::class, 'operator_kolam')
                    ->withPivot('tanggal_penugasan')
                    ->withTimestamps();
    }

    // Relasi Tiket: Tiket apa saja yang harus dikerjakan user ini
    public function tikets()
    {
        return $this->hasMany(Tiket::class, 'operator_id');
    }
}