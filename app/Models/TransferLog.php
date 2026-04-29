<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferLog extends Model
{
    use HasFactory;

    // Mengizinkan semua kolom diisi (mass assignment) kecuali kolom 'id'
    protected $guarded = ['id'];

    /**
     * Relasi ke model Kolam (sebagai Kolam Asal)
     * Karena nama kolomnya 'kolam_asal_id', kita harus mendefinisikannya secara eksplisit.
     */
    public function kolamAsal()
    {
        return $this->belongsTo(Kolam::class, 'kolam_asal_id');
    }

    /**
     * Relasi ke model Kolam (sebagai Kolam Tujuan)
     * Karena nama kolomnya 'kolam_tujuan_id', kita harus mendefinisikannya secara eksplisit.
     */
    public function kolamTujuan()
    {
        return $this->belongsTo(Kolam::class, 'kolam_tujuan_id');
    }

    /**
     * Relasi ke model User (Siapa yang melakukan transfer)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}