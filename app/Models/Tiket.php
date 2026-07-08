<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Tiket extends Model
{
    protected $fillable = ['kolam_id', 'inferensi_log_id', 'operator_id', 'supervisor_id', 'judul', 'deskripsi_tindakan', 'status', 'deadline', 'bukti_penyelesaian', 'catatan_supervisor', 'diselesaikan_at', 'diverifikasi_at'];
    
    protected $casts = [
        'deadline' => 'datetime',
        'diselesaikan_at' => 'datetime',
        'diverifikasi_at' => 'datetime',
    ];

    public function kolam() { return $this->belongsTo(Kolam::class); }
    public function inferensiLog() { return $this->belongsTo(InferensiLog::class); }
    public function operator() { return $this->belongsTo(User::class, 'operator_id'); }
    public function supervisor() { return $this->belongsTo(User::class, 'supervisor_id'); }
}