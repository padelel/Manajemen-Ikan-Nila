<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class HarvestLog extends Model
{
    protected $fillable = ['kolam_id', 'siklus_budidaya_id', 'user_id', 'jenis_panen', 'tanggal_panen', 'jumlah_ikan_panen', 'berat_total_kg', 'survival_rate', 'catatan'];
    public function kolam() { return $this->belongsTo(Kolam::class); }
    public function siklusBudidaya() { return $this->belongsTo(SiklusBudidaya::class); }
    public function user() { return $this->belongsTo(User::class); }
}