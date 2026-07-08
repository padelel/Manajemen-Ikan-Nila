<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TebarLog extends Model
{
    protected $fillable = ['kolam_id', 'user_id', 'tanggal_tebar', 'jumlah_ikan', 'berat_rata_gram', 'sumber_benih'];
    public function kolam() { return $this->belongsTo(Kolam::class); }
    public function user() { return $this->belongsTo(User::class); }
}