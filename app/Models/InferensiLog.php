<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InferensiLog extends Model
{
    protected $fillable = ['kolam_id', 'parameter_harian_id', 'tanggal_inferensi', 'fakta_input', 'fakta_baru', 'rule_terpicu', 'kode_diagnosa', 'label_diagnosa', 'kode_kesimpulan', 'tindakan_mitigasi', 'peringatan'];

    protected $casts = [
        'fakta_input' => 'array',
        'fakta_baru' => 'array',
        'rule_terpicu' => 'array',
        'kode_diagnosa' => 'array',
        'label_diagnosa' => 'array',
        'kode_kesimpulan' => 'array',
        'tindakan_mitigasi' => 'array',
        'peringatan' => 'array',
    ];

    public function kolam()
    {
        return $this->belongsTo(Kolam::class);
    }

    public function parameterHarian()
    {
        return $this->belongsTo(ParameterHarian::class);
    }

    public function tiket()
    {
        return $this->hasOne(Tiket::class);
    }
}
