<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ParameterHarian extends Model
{
    protected $fillable = ['kolam_id', 'user_id', 'tanggal_cek', 'suhu', 'ph', 'do_mgl', 'amonia_mgl', 'flok_ml', 'kecerahan_cm'];
    public function kolam() { return $this->belongsTo(Kolam::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function inferensiLog() { return $this->hasOne(InferensiLog::class); }
}