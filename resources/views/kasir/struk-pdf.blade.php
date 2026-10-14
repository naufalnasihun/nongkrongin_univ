<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Struk {{ $pesanan->nomor_pesanan }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 9px;
            color: #111;
            padding: 6px;
        }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        h1 { font-size: 13px; letter-spacing: 1px; }
        p { font-size: 8px; }
        .divider { border-top: 1px dashed #666; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; font-size: 8px; }
        td { padding: 1px 0; vertical-align: top; }
        .right { text-align: right; }
        .antrian {
            text-align: center;
            border: 2px solid #111;
            padding: 5px;
            margin: 6px 0;
        }
        .antrian .label { font-size: 7px; letter-spacing: 1px; }
        .antrian .nomor { font-size: 22px; font-weight: bold; }
        .total-row td { font-size: 11px; font-weight: bold; }
        .footer { text-align: center; font-size: 7px; margin-top: 6px; }
    </style>
</head>
<body>

<div class="center">
    <h1>NONGKRONGIN UNIV</h1>
    <p>Sistem Pre-Order Kantin Kampus</p>
    @if ($pesanan->warung)
        <p>{{ $pesanan->warung->nama_warung }}</p>
    @endif
</div>

<div class="divider"></div>

<table>
    <tr><td>No. Pesanan</td><td class="right bold">{{ $pesanan->nomor_pesanan }}</td></tr>
    <tr><td>Tanggal</td><td class="right">{{ $pesanan->tanggal->format('d/m/Y H:i') }}</td></tr>
    <tr><td>Customer</td><td class="right">{{ $pesanan->nama_customer }}</td></tr>
    @if ($pesanan->nim_customer)
        <tr><td>NIM</td><td class="right">{{ $pesanan->nim_customer }}</td></tr>
    @endif
</table>

@if ($pesanan->nomor_antrian)
    <div class="antrian">
        <div class="label">NOMOR ANTRIAN</div>
        <div class="nomor">{{ $pesanan->nomor_antrian }}</div>
    </div>
@endif

<div class="divider"></div>

<table>
    @foreach ($pesanan->items as $item)
        <tr><td colspan="2" class="bold">{{ $item->nama_menu }}</td></tr>
        <tr>
            <td>{{ $item->jumlah }} x Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
            <td class="right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
        </tr>
    @endforeach
</table>

<div class="divider"></div>

<table>
    <tr class="total-row">
        <td>TOTAL</td>
        <td class="right">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td>Metode</td>
        <td class="right">{{ \App\Models\Pesanan::METODE_BAYAR[$pesanan->metode_bayar] ?? $pesanan->metode_bayar }}</td>
    </tr>
    <tr><td>Pembayaran</td><td class="right bold">{{ $pesanan->status_bayar }}</td></tr>
    <tr><td>Status</td><td class="right">{{ $pesanan->status }}</td></tr>
</table>

<div class="divider"></div>

<div class="footer">
    <p>Terima kasih telah memesan</p>
    <p>Simpan struk ini sebagai bukti pengambilan</p>
</div>

</body>
</html>
