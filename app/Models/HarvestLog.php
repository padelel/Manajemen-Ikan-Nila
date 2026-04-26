<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HarvestLog extends Model
{
    protected $guarded = ['id'];

    public function kolam() { return $this->belongsTo(Kolam::class); }
    public function user() { return $this->belongsTo(User::class); }
}