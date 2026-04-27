<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiklusBudidaya extends Model
{
    protected $fillable = [
        'kolam_id', 
        'tanggal_mulai', 
        'tanggal_selesai', 
        'status_aktif', 
        'jumlah_tebar_awal'
    ];

    public function kolam() {
        return $this->belongsTo(Kolam::class);
    }
}