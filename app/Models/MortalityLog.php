<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MortalityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'kolam_id', 'user_id', 'tanggal_kematian', 'jumlah_mati', 'catatan'
    ];

    public function kolam() { return $this->belongsTo(Kolam::class); }
    public function user() { return $this->belongsTo(User::class); }
}
