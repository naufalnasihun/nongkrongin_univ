<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesananItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pesanan_id',
        'menu_id',
        'nama_menu_snapshot',
        'jumlah',
        'harga',
        'harga_snapshot',
        'subtotal',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'harga_snapshot' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class);
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Nama menu untuk tampilan: snapshot (historis) atau relasi menu bila masih ada.
     */
    public function getNamaMenuAttribute(): string
    {
        if (! empty($this->nama_menu_snapshot)) {
            return $this->nama_menu_snapshot;
        }

        return $this->menu?->nama_menu ?? '(menu dihapus)';
    }

    /**
     * Harga satuar historis untuk tampilan: snapshot atau harga saat ini.
     */
    public function getHargaSatuanAttribute(): string
    {
        return $this->harga_snapshot ?? $this->harga;
    }
}
