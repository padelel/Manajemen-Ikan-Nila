<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TebarLog extends Model
{
    use HasFactory;

    // Mengizinkan semua kolom diisi secara massal kecuali 'id'
    protected $guarded = ['id'];

    /**
     * Relasi ke Kolam (Setiap log tebar adalah milik 1 kolam)
     */
    public function kolam()
    {
        return $this->belongsTo(Kolam::class);
    }

    /**
     * Relasi ke User (Setiap log tebar dicatat oleh 1 user/pelapor)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}