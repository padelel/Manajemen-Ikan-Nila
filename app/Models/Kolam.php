<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kolam extends Model
{
    use HasFactory;

    // Izinkan kolom-kolom ini diisi melalui form
    protected $fillable = [
    'nama_kolam', 'lokasi', 'bentuk_kolam', 'status_kolam', 
    'panjang_m', 'lebar_m', 'kedalaman_m', 
    'tanggal_tebar', 'jumlah_ikan', 'berat_rata_gram'
    ];
    
    public function riwayatSiklus() {
        return $this->hasMany(SiklusBudidaya::class);
    }

    public function siklusAktif() {
        return $this->hasOne(SiklusBudidaya::class)->where('status_aktif', 'Aktif');
    }
}