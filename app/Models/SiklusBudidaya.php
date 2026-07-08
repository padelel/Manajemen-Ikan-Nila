<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SiklusBudidaya extends Model
{
    protected $fillable = ['kolam_id', 'tanggal_mulai', 'tanggal_selesai', 'jumlah_tebar_awal', 'status_aktif'];
    public function kolam() { return $this->belongsTo(Kolam::class); }
    public function harvestLogs() { return $this->hasMany(HarvestLog::class); }
}