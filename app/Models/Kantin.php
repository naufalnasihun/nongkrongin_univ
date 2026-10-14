<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Kantin extends Model
{
    protected $fillable = [
        'nama_kantin',
        'lokasi',
        'deskripsi',
    ];

    public function warungs(): HasMany
    {
        return $this->hasMany(Warung::class);
    }

    public function menus(): HasManyThrough
    {
        return $this->hasManyThrough(Menu::class, Warung::class);
    }
}
