<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MortalityLog extends Model
{
    protected $fillable = [
        'kolam_id', 'siklus_budidaya_id', 'user_id', 'tanggal_kematian',
        'jumlah_mati', 'kategori_kematian', 'berat_total_gram', 'ukuran_rata_cm', 'catatan',
    ];

    public function kolam()
    {
        return $this->belongsTo(Kolam::class);
    }

    public function siklusBudidaya()
    {
        return $this->belongsTo(SiklusBudidaya::class, 'siklus_budidaya_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
