<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topup extends Model
{
    public const STATUS_MENUNGGU = 'Menunggu';

    public const STATUS_DISETUJUI = 'Disetujui';

    public const STATUS_DITOLAK = 'Ditolak';

    public const METODE = [
        'qris' => 'QRIS',
        'cash' => 'Tunai',
        'transfer' => 'Transfer',
    ];

    protected $fillable = [
        'customer_id',
        'jumlah',
        'metode',
        'status',
        'referensi',
        'dikonfirmasi_oleh',
        'dikonfirmasi_pada',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
            'dikonfirmasi_pada' => 'datetime',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function dikonfirmasiOleh()
    {
        return $this->belongsTo(User::class, 'dikonfirmasi_oleh');
    }
}
