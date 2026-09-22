<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'kasir_id',
        'nomor_pesanan',
        'tanggal',
        'total_harga',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'total_harga' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function kasir()
    {
        return $this->belongsTo(Kasir::class);
    }

    public function items()
    {
        return $this->hasMany(PesananItem::class);
    }
}