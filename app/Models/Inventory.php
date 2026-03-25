<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = [
        'nama_pakan', 
        'total_stok_kg', 
        'terakhir_update'
    ];
}