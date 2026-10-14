<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk {{ $pesanan->nomor_pesanan }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            background: #f3f4f6;
            padding: 20px;
            color: #111;
        }
        .struk {
            width: 300px;
            margin: 0 auto;
            background: #fff;
            padding: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,.1);
        }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .header h1 { font-size: 16px; letter-spacing: 1px; }
        .header p { font-size: 11px; color: #444; }
        .divider { border-top: 1px dashed #999; margin: 8px 0; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        td { padding: 2px 0; vertical-align: top; }
        .right { text-align: right; }
        .antrian {
            text-align: center;
            border: 2px solid #111;
            padding: 8px;
            margin: 10px 0;
        }
        .antrian .label { font-size: 10px; letter-spacing: 2px; }
        .antrian .nomor { font-size: 30px; font-weight: bold; letter-spacing: 3px; }
        .total-row td { font-size: 14px; font-weight: bold; padding-top: 4px; }
        .footer { text-align: center; font-size: 10px; color: #555; margin-top: 10px; }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border: 1px solid #111;
            font-size: 10px;
            font-weight: bold;
        }
        .no-print { text-align: center; margin: 16px auto; max-width: 300px; }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: #059669;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 12px;
            font-family: sans-serif;
            margin: 2px;
            border: none;
            cursor: pointer;
        }
        .btn-gray { background: #6b7280; }
        @media print {
            body { background: #fff; padding: 0; }
            .struk { box-shadow: none; width: 100%; padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="struk">
    <div class="header center">
        <h1>NONGKRONGIN UNIV</h1>
        <p>Sistem Pre-Order Kantin Kampus</p>
        @if ($pesanan->warung)
            <p>{{ $pesanan->warung->nama_warung }}</p>
            <p>{{ $pesanan->warung->kantin->nama_kantin ?? '' }}</p>
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
            <tr>
                <td colspan="2" class="bold">{{ $item->nama_menu }}</td>
            </tr>
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
        <tr>
            <td>Pembayaran</td>
            <td class="right bold">{{ $pesanan->status_bayar }}</td>
        </tr>
        <tr>
            <td>Status</td>
            <td class="right">{{ $pesanan->status }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <div class="footer">
        <p>Terima kasih telah memesan 🙏</p>
        <p>Simpan struk ini sebagai bukti pengambilan</p>
        <p style="margin-top:6px;">{{ now()->format('d/m/Y H:i') }}</p>
    </div>
</div>

<div class="no-print">
    <button class="btn" onclick="window.print()">🖨️ Cetak</button>
    <a class="btn" href="{{ (auth()->user()->kasir ? route('kasir.orders.struk.pdf', $pesanan) : route('customer.orders.struk.pdf', $pesanan)) }}">⬇️ PDF</a>
    <a class="btn btn-gray" href="{{ auth()->user()->kasir ? route('kasir.orders.index') : route('customer.orders.index') }}">Kembali</a>
</div>

</body>
</html>
