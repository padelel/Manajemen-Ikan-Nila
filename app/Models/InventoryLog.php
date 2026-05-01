<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryLog extends Model
{
    use HasFactory;

    // Mengizinkan semua kolom diisi (mass assignment) kecuali kolom 'id'
    protected $guarded = ['id'];

    /**
     * Relasi ke data Master Gudang (Pakan)
     */
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    /**
     * Relasi ke User (Siapa yang mencatat log ini)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}