<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeranjangItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'keranjang_id',
        'menu_id',
        'jumlah',
        'subtotal',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
    ];

    public function keranjang()
    {
        return $this->belongsTo(Keranjang::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}