<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KolamLog extends Model
{
    protected $fillable = ['user_id', 'aksi', 'nama_kolam', 'keterangan'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}