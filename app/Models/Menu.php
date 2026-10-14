<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    public const KATEGORI = [
        'makanan' => 'Makanan',
        'minuman' => 'Minuman',
        'snack' => 'Snack',
        'dessert' => 'Dessert',
    ];

    protected $fillable = [
        'warung_id',
        'nama_menu',
        'kategori',
        'foto',
        'harga',
        'stok',
        'deskripsi',
        'ketersediaan',
    ];

    protected function casts(): array
    {
        return [
            'harga' => 'integer',
            'stok' => 'integer',
            'ketersediaan' => 'boolean',
        ];
    }

    public function warung(): BelongsTo
    {
        return $this->belongsTo(Warung::class);
    }

    public function keranjangItems(): HasMany
    {
        return $this->hasMany(KeranjangItem::class);
    }

    public function scopeTersedia(Builder $query): Builder
    {
        return $query->where('ketersediaan', true)->where('stok', '>', 0);
    }

    public function scopeKategori(Builder $query, ?string $kategori): Builder
    {
        return $kategori ? $query->where('kategori', $kategori) : $query;
    }

    public function labelKategori(): string
    {
        return self::KATEGORI[$this->kategori] ?? ucfirst((string) $this->kategori);
    }

    /**
     * Apakah menu bisa dipesan sebanyak $jumlah?
     */
    public function cukupStok(int $jumlah): bool
    {
        return $this->ketersediaan && $this->stok >= $jumlah;
    }
}
