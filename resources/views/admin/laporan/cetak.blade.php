<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan {{ $dari }} s/d {{ $sampai }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
            color: #1f2937;
            margin: 0;
            padding: 24px;
            font-size: 12px;
            line-height: 1.5;
        }
        .container { max-width: 1000px; margin: 0 auto; }

        .header { text-align: center; border-bottom: 3px double #059669; padding-bottom: 12px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 20px; color: #047857; }
        .header p { margin: 2px 0 0; color: #6b7280; font-size: 12px; }

        .toolbar { text-align: right; margin-bottom: 16px; }
        .toolbar button, .toolbar a {
            font: inherit;
            padding: 8px 16px;
            border-radius: 8px;
            border: 1px solid #059669;
            background: #059669;
            color: #fff;
            cursor: pointer;
            text-decoration: none;
            font-size: 12px;
        }
        .toolbar a.secondary { background: #fff; color: #059669; }

        h2 { font-size: 14px; color: #065f46; margin: 24px 0 8px; border-left: 4px solid #059669; padding-left: 8px; }

        .cards { display: flex; flex-wrap: wrap; gap: 10px; }
        .card {
            flex: 1 1 140px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px 12px;
        }
        .card .label { color: #6b7280; font-size: 10px; text-transform: uppercase; letter-spacing: .04em; }
        .card .value { font-size: 16px; font-weight: 700; color: #111827; margin-top: 2px; }
        .card .value.green { color: #047857; }

        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        th, td { border: 1px solid #e5e7eb; padding: 6px 8px; text-align: left; }
        th { background: #f0fdf4; color: #065f46; font-size: 11px; }
        td.num, th.num { text-align: right; }

        .muted { color: #9ca3af; text-align: center; padding: 12px; }
        .footer { margin-top: 28px; font-size: 10px; color: #9ca3af; text-align: center; }

        @media print {
            body { padding: 0; font-size: 11px; }
            .toolbar { display: none; }
            h2 { page-break-after: avoid; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
<div class="container">

    <div class="toolbar">
        <button onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
        <a href="{{ route('admin.laporan.index', ['dari' => $dari, 'sampai' => $sampai]) }}" class="secondary">Kembali</a>
    </div>

    <div class="header">
        <h1>Laporan Penjualan — Nongkrongin Univ</h1>
        <p>Periode {{ \Carbon\Carbon::parse($dari)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($sampai)->format('d M Y') }}</p>
        <p>Dicetak: {{ now()->format('d M Y H:i') }}</p>
    </div>

    {{-- Ringkasan --}}
    <h2>Ringkasan</h2>
    <div class="cards">
        <div class="card"><div class="label">Total Pesanan</div><div class="value">{{ number_format($ringkasan['totalPesanan'], 0, ',', '.') }}</div></div>
        <div class="card"><div class="label">Pesanan Selesai</div><div class="value">{{ number_format($ringkasan['pesananSelesai'], 0, ',', '.') }}</div></div>
        <div class="card"><div class="label">Pesanan Dibatalkan</div><div class="value">{{ number_format($ringkasan['pesananDibatalkan'], 0, ',', '.') }}</div></div>
        <div class="card"><div class="label">Pendapatan (Lunas)</div><div class="value green">Rp {{ number_format($ringkasan['totalPendapatan'], 0, ',', '.') }}</div></div>
        <div class="card"><div class="label">Rata-rata / Pesanan</div><div class="value">Rp {{ number_format($ringkasan['rataRata'], 0, ',', '.') }}</div></div>
    </div>

    {{-- Penjualan harian --}}
    <h2>Penjualan Harian</h2>
    <table>
        <thead>
            <tr><th>Tanggal</th><th class="num">Jumlah Pesanan</th><th class="num">Pendapatan (Lunas)</th></tr>
        </thead>
        <tbody>
            @forelse ($harian as $h)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($h->tanggal)->format('d M Y') }}</td>
                    <td class="num">{{ number_format($h->jumlah, 0, ',', '.') }}</td>
                    <td class="num">Rp {{ number_format($h->pendapatan, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="muted">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- Per kantin --}}
    @if ($perKantin->isNotEmpty())
        <h2>Penjualan per Kantin</h2>
        <table>
            <thead>
                <tr><th>Kantin</th><th class="num">Jumlah Pesanan</th><th class="num">Pendapatan</th></tr>
            </thead>
            <tbody>
                @foreach ($perKantin as $k)
                    <tr>
                        <td>{{ $k->kantin }}</td>
                        <td class="num">{{ number_format($k->jumlah, 0, ',', '.') }}</td>
                        <td class="num">Rp {{ number_format((float) $k->pendapatan, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Per warung --}}
    @if ($perWarung->isNotEmpty())
        <h2>Penjualan per Warung</h2>
        <table>
            <thead>
                <tr><th>Kantin</th><th>Warung</th><th class="num">Jumlah</th><th class="num">Pendapatan</th></tr>
            </thead>
            <tbody>
                @foreach ($perWarung as $w)
                    <tr>
                        <td>{{ $w->kantin }}</td>
                        <td>{{ $w->warung }}</td>
                        <td class="num">{{ number_format($w->jumlah, 0, ',', '.') }}</td>
                        <td class="num">Rp {{ number_format((float) $w->pendapatan, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Menu terlaris --}}
    <h2>Menu Terlaris</h2>
    <table>
        <thead>
            <tr><th>Menu</th><th>Kategori</th><th class="num">Terjual</th><th class="num">Pendapatan</th></tr>
        </thead>
        <tbody>
            @forelse ($menuTerlaris as $m)
                <tr>
                    <td>{{ $m->nama_menu }}</td>
                    <td>{{ $m->kategori }}</td>
                    <td class="num">{{ number_format($m->terjual, 0, ',', '.') }}</td>
                    <td class="num">Rp {{ number_format($m->pendapatan, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- Detail pesanan --}}
    <h2>Detail Pesanan</h2>
    <table>
        <thead>
            <tr>
                <th>No</th><th>No. Pesanan</th><th>Tanggal</th><th>Customer</th>
                <th>NIM</th><th>Warung</th><th>Metode</th><th>Status Bayar</th><th class="num">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($detail as $i => $p)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $p->nomor_pesanan }}</td>
                    <td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y H:i') }}</td>
                    <td>{{ $p->nama_customer_snapshot ?: '-' }}</td>
                    <td>{{ $p->nim_snapshot ?: '-' }}</td>
                    <td>{{ $p->nama_warung ?? '-' }}</td>
                    <td>{{ \App\Models\Pesanan::METODE_BAYAR[$p->metode_bayar] ?? $p->metode_bayar }}</td>
                    <td>{{ $p->status_bayar }}</td>
                    <td class="num">Rp {{ number_format((float) $p->total_harga, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="muted">Tidak ada pesanan pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Laporan ini dihasilkan otomatis oleh sistem Nongkrongin Univ.
    </div>
</div>
</body>
</html>
