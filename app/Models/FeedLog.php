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
    'rekomendasi_sistem', 
    'pakan_aktual'
];

    public function kolam() { return $this->belongsTo(Kolam::class); }
    
    // Relasi baru ke detail pakan
    public function details() { return $this->hasMany(FeedLogDetail::class); }
}