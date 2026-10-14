<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama',
        'nim',
        'kelas',
        'saldo',
    ];

    protected function casts(): array
    {
        return [
            'saldo' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function topups()
    {
        return $this->hasMany(Topup::class);
    }

    public function keranjang()
    {
        return $this->hasOne(Keranjang::class);
    }

    public function pesanans()
    {
        return $this->hasMany(Pesanan::class);
    }
}
