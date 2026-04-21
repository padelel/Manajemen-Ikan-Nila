<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedLogDetail extends Model
{
    protected $fillable = ['feed_log_id', 'inventory_id', 'rasio', 'jumlah_kg'];

    public function inventory() 
    { 
        return $this->belongsTo(Inventory::class); 
    }

    // -> TAMBAHKAN BAGIAN INI <-
    public function feedLog() 
    { 
        return $this->belongsTo(FeedLog::class); 
    }
}