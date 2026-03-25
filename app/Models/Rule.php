<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    // Mengizinkan seeder/form mengisi kolom ini
    protected $fillable = [
        'kode_rule', 
        'kondisi_suhu', 
        'kondisi_ph', 
        'kondisi_visual', 
        'rekomendasi_dosis_persen'
    ];
}