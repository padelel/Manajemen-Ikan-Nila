<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedLog extends Model
{
    protected $fillable = [
        'kolam_id', 
        'rule_id', 
        'user_id',
        'tanggal_pakan', 
        'frekuensi',
        'rekomendasi_sistem', 
        'pakan_aktual'
    ];

    public function kolam() { 
        return $this->belongsTo(Kolam::class); 
    }

    public function user() { 
        return $this->belongsTo(User::class); 
    }
    
    public function feedLogDetails() { 
        return $this->hasMany(FeedLogDetail::class); 
    }
}