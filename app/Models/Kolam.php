<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kolam extends Model
{
    use HasFactory;

    // Izinkan kolom-kolom ini diisi melalui form
    protected $fillable = [
        'nama_kolam', 
        'dimensi', 
        'jumlah_ikan', 
        'berat_rata_gram', 
        'deskripsi'
    ];
}