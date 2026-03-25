<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kolam extends Model
{
    protected $fillable = [
        'nama_kolam', 
        'dimensi', 
        'jumlah_ikan', 
        'deskripsi'
    ];
}