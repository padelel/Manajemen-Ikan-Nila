<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kolam extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_kolam', 'lokasi', 'panjang_m', 'lebar_m', 'kedalaman_m',
        'jumlah_ikan', 'berat_rata_gram', 'status_kolam',
    ];

    public function operators()
    {
        return $this->belongsToMany(User::class, 'operator_kolam', 'kolam_id', 'user_id')
            ->withPivot('tanggal_penugasan')
            ->withTimestamps();
    }

    public function siklusBudidayas()
    {
        return $this->hasMany(SiklusBudidaya::class);
    }

    public function tebarLogs()
    {
        return $this->hasMany(TebarLog::class);
    }

    public function mortalityLogs()
    {
        return $this->hasMany(MortalityLog::class);
    }

    public function harvestLogs()
    {
        return $this->hasMany(HarvestLog::class);
    }

    public function parameterHarians()
    {
        return $this->hasMany(ParameterHarian::class);
    }

    public function inferensiLogs()
    {
        return $this->hasMany(InferensiLog::class);
    }

    public function tikets()
    {
        return $this->hasMany(Tiket::class);
    }
}
