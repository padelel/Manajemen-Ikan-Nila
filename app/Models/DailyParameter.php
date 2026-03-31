<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyParameter extends Model
{
    use HasFactory;

    protected $fillable = [
        'kolam_id', 'user_id', 'tanggal_cek', 'suhu', 'ph', 'kondisi_visual'
    ];

    // Relasi untuk mengambil nama kolam
    public function kolam()
    {
        return $this->belongsTo(Kolam::class);
    }

    // Relasi untuk mengambil nama petugas (user)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
