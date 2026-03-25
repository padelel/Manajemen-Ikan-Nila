<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedLog extends Model
{
    protected $fillable = [
        'tanggal_pakan', 
        'rekomendasi_sistem', 
        'pakan_aktual'
    ];
}