<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warung extends Model
{
    protected $fillable = [
        'kantin_id',
        'nama_warung',
        'pemilik',
        'deskripsi',
    ];

    public function kantin(): BelongsTo
    {
        return $this->belongsTo(Kantin::class);
    }

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }
}
