<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pesanan extends Model
{
    use HasFactory;

    public const STATUS_MENUNGGU = 'Menunggu';

    public const STATUS_DIPROSES = 'Diproses';

    public const STATUS_SIAP = 'Siap Diambil';

    public const STATUS_SELESAI = 'Selesai';

    public const STATUS_DIBATALKAN = 'Dibatalkan';

    public const STATUSES = [
        self::STATUS_MENUNGGU,
        self::STATUS_DIPROSES,
        self::STATUS_SIAP,
        self::STATUS_SELESAI,
        self::STATUS_DIBATALKAN,
    ];

    /** Status pembayaran yang dikenal. */
    public const BAYAR_BELUM = 'Belum Bayar';

    public const BAYAR_LUNAS = 'Lunas';

    public const BAYAR_DIBATALKAN = 'Dibatalkan';

    public const STATUS_BAYAR = [
        self::BAYAR_BELUM,
        self::BAYAR_LUNAS,
        self::BAYAR_DIBATALKAN,
    ];

    /** Metode pembayaran yang dikenal. */
    public const METODE_BAYAR = [
        'cash' => 'Tunai',
        'qris' => 'QRIS',
        'deposit' => 'Deposit',
    ];

    /**
     * Peta transisi status yang sah. Status hanya boleh maju sesuai alur,
     * kecuali pembatalan yang diizinkan selama pesanan belum selesai.
     */
    public const TRANSITIONS = [
        self::STATUS_MENUNGGU => [self::STATUS_DIPROSES, self::STATUS_DIBATALKAN],
        self::STATUS_DIPROSES => [self::STATUS_SIAP, self::STATUS_DIBATALKAN],
        self::STATUS_SIAP => [self::STATUS_SELESAI, self::STATUS_DIBATALKAN],
        self::STATUS_SELESAI => [],
        self::STATUS_DIBATALKAN => [],
    ];

    protected $fillable = [
        'customer_id',
        'nama_customer_snapshot',
        'nim_snapshot',
        'warung_id',
        'kasir_id',
        'nomor_pesanan',
        'nomor_antrian',
        'tanggal',
        'total_harga',
        'status',
        'metode_bayar',
        'status_bayar',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'total_harga' => 'decimal:2',
    ];

    /**
     * Nama customer saat pesanan dibuat.
     * Fallback ke relasi bila snapshot kosong (data lama / soft delete).
     */
    public function getNamaCustomerAttribute(): string
    {
        return $this->nama_customer_snapshot
            ?: ($this->customer->nama ?? '-');
    }

    /** NIM customer saat pesanan dibuat (null bila tidak ada). */
    public function getNimCustomerAttribute(): ?string
    {
        return $this->nim_snapshot
            ?: $this->customer->nim
            ?: null;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function warung(): BelongsTo
    {
        return $this->belongsTo(Warung::class);
    }

    public function kasir(): BelongsTo
    {
        return $this->belongsTo(Kasir::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PesananItem::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PesananLog::class)->latest();
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }

    /**
     * Daftar status yang boleh dituju dari status saat ini.
     *
     * @return array<int, string>
     */
    public function statusBerikutnya(): array
    {
        return self::TRANSITIONS[$this->status] ?? [];
    }

    public function bolehTransisiKe(string $status): bool
    {
        return in_array($status, $this->statusBerikutnya(), true);
    }

    public function isFinal(): bool
    {
        return in_array($this->status, [self::STATUS_SELESAI, self::STATUS_DIBATALKAN], true);
    }
}
