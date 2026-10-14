<?php

namespace App\Http\Controllers;

use App\Models\Kantin;
use App\Models\Pesanan;
use App\Models\PesananItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanController extends Controller
{
    /**
     * Halaman laporan penjualan dengan filter rentang tanggal.
     */
    public function index(Request $request)
    {
        [$dari, $sampai] = $this->rentang($request);

        $ringkasan = $this->ringkasan($dari, $sampai);
        $harian = $this->penjualanHarian($dari, $sampai);
        $menuTerlaris = $this->menuTerlaris($dari, $sampai);
        $perWarung = $this->penjualanPerWarung($dari, $sampai);
        $perKantin = $this->penjualanPerKantin($dari, $sampai);
        $kantins = Kantin::orderBy('nama_kantin')->get();

        return view('admin.laporan.index', compact(
            'dari',
            'sampai',
            'ringkasan',
            'harian',
            'menuTerlaris',
            'perWarung',
            'perKantin',
            'kantins'
        ));
    }

    /**
     * Export laporan ke Excel (multi-sheet).
     */
    public function exportExcel(Request $request)
    {
        [$dari, $sampai] = $this->rentang($request);

        $spreadsheet = new Spreadsheet;
        $spreadsheet->getProperties()
            ->setCreator('Nongkrongin Univ')
            ->setTitle('Laporan Penjualan')
            ->setDescription('Laporan penjualan '.$dari.' s/d '.$sampai);

        // Sheet 1: Ringkasan
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Ringkasan');
        $ringkasan = $this->ringkasan($dari, $sampai);
        $sheet->fromArray([
            ['LAPORAN PENJUALAN - NONGKRONGIN UNIV'],
            ['Periode', $dari.' s/d '.$sampai],
            [],
            ['Total Pesanan', $ringkasan['totalPesanan']],
            ['Pesanan Selesai', $ringkasan['pesananSelesai']],
            ['Pesanan Dibatalkan', $ringkasan['pesananDibatalkan']],
            ['Pendapatan (Lunas)', $ringkasan['totalPendapatan']],
            ['Rata-rata per Pesanan', $ringkasan['rataRata']],
        ], null, 'A1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A4:A8')->getFont()->setBold(true);
        $sheet->getStyle('B7')->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getColumnDimension('A')->setWidth(24);
        $sheet->getColumnDimension('B')->setWidth(28);

        // Sheet 2: Penjualan Harian
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Harian');
        $sheet2->fromArray([['Tanggal', 'Jumlah Pesanan', 'Pendapatan (Lunas)']], null, 'A1');
        $rows = [];
        $row = 2;
        foreach ($this->penjualanHarian($dari, $sampai) as $h) {
            $rows[] = [$h->tanggal, (int) $h->jumlah, (float) $h->pendapatan];
            $row++;
        }
        if ($rows) {
            $sheet2->fromArray($rows, null, 'A2');
        }
        $sheet2->getStyle('A1:C1')->getFont()->setBold(true);
        if ($row > 2) {
            $sheet2->getStyle('C2:C'.$row)->getNumberFormat()->setFormatCode('#,##0');
        }
        $sheet2->getColumnDimension('A')->setWidth(14);
        $sheet2->getColumnDimension('B')->setWidth(16);
        $sheet2->getColumnDimension('C')->setWidth(20);

        // Sheet 3: Menu Terlaris
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Menu Terlaris');
        $sheet3->fromArray([['Menu', 'Kategori', 'Terjual', 'Pendapatan']], null, 'A1');
        $rows3 = [];
        foreach ($this->menuTerlaris($dari, $sampai) as $m) {
            $rows3[] = [$m->nama_menu, $m->kategori, (int) $m->terjual, (float) $m->pendapatan];
        }
        if ($rows3) {
            $sheet3->fromArray($rows3, null, 'A2');
        }
        $sheet3->getStyle('A1:D1')->getFont()->setBold(true);
        $sheet3->getColumnDimension('A')->setWidth(28);
        $sheet3->getColumnDimension('B')->setWidth(14);
        $sheet3->getColumnDimension('C')->setWidth(12);
        $sheet3->getColumnDimension('D')->setWidth(18);

        // Sheet 4: Per Warung
        $sheet4 = $spreadsheet->createSheet();
        $sheet4->setTitle('Per Warung');
        $sheet4->fromArray([['Kantin', 'Warung', 'Jumlah Pesanan', 'Pendapatan']], null, 'A1');
        $rows4 = [];
        foreach ($this->penjualanPerWarung($dari, $sampai) as $w) {
            $rows4[] = [$w->kantin, $w->warung, (int) $w->jumlah, (float) $w->pendapatan];
        }
        if ($rows4) {
            $sheet4->fromArray($rows4, null, 'A2');
        }
        $sheet4->getStyle('A1:D1')->getFont()->setBold(true);
        foreach (['A' => 22, 'B' => 28, 'C' => 16, 'D' => 18] as $col => $w) {
            $sheet4->getColumnDimension($col)->setWidth($w);
        }

        // Sheet 5: Detail Pesanan
        $sheet5 = $spreadsheet->createSheet();
        $sheet5->setTitle('Detail Pesanan');
        $sheet5->fromArray([['No', 'No. Pesanan', 'Tanggal', 'Customer', 'NIM', 'Warung', 'Metode', 'Status Bayar', 'Status', 'Total']], null, 'A1');
        $rows5 = [];
        $no = 1;
        foreach ($this->detailPesanan($dari, $sampai) as $p) {
            $rows5[] = [
                $no++,
                $p->nomor_pesanan,
                Carbon::parse($p->tanggal)->format('d/m/Y H:i'),
                $p->nama_customer_snapshot ?: '-',
                $p->nim_snapshot ?: '-',
                $p->nama_warung ?? '-',
                Pesanan::METODE_BAYAR[$p->metode_bayar] ?? $p->metode_bayar,
                $p->status_bayar,
                $p->status,
                (float) $p->total_harga,
            ];
        }
        if ($rows5) {
            $sheet5->fromArray($rows5, null, 'A2');
        }
        $sheet5->getStyle('A1:J1')->getFont()->setBold(true);
        if ($no > 1) {
            $sheet5->getStyle('J2:J'.$no)->getNumberFormat()->setFormatCode('#,##0');
        }
        foreach (['A' => 6, 'B' => 20, 'C' => 18, 'D' => 22, 'E' => 14, 'F' => 24, 'G' => 12, 'H' => 14, 'I' => 14, 'J' => 14] as $col => $w) {
            $sheet5->getColumnDimension($col)->setWidth($w);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $namaFile = 'laporan-penjualan-'.$dari.'-sd-'.$sampai.'.xlsx';
        $tmp = tempnam(sys_get_temp_dir(), 'laporan').'.xlsx';
        (new Xlsx($spreadsheet))->save($tmp);

        return response()->download($tmp, $namaFile)->deleteFileAfterSend(true);
    }

    /**
     * Export laporan ke CSV (ringkasan + harian digabung).
     */
    public function exportCsv(Request $request)
    {
        [$dari, $sampai] = $this->rentang($request);

        $namaFile = 'laporan-penjualan-'.$dari.'-sd-'.$sampai.'.csv';

        return response()->streamDownload(function () use ($dari, $sampai) {
            $out = fopen('php://output', 'w');

            // BOM agar Excel membaca UTF-8 dengan benar.
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['Laporan Penjualan - Nongkrongin Univ']);
            fputcsv($out, ['Periode', $dari.' s/d '.$sampai]);
            fputcsv($out, []);

            $ringkasan = $this->ringkasan($dari, $sampai);
            fputcsv($out, ['Ringkasan']);
            fputcsv($out, ['Total Pesanan', $ringkasan['totalPesanan']]);
            fputcsv($out, ['Pesanan Selesai', $ringkasan['pesananSelesai']]);
            fputcsv($out, ['Pesanan Dibatalkan', $ringkasan['pesananDibatalkan']]);
            fputcsv($out, ['Pendapatan (Lunas)', $ringkasan['totalPendapatan']]);
            fputcsv($out, ['Rata-rata per Pesanan', $ringkasan['rataRata']]);
            fputcsv($out, []);

            fputcsv($out, ['Penjualan Harian']);
            fputcsv($out, ['Tanggal', 'Jumlah Pesanan', 'Pendapatan']);
            foreach ($this->penjualanHarian($dari, $sampai) as $h) {
                fputcsv($out, [$h->tanggal, $h->jumlah, $h->pendapatan]);
            }
            fputcsv($out, []);

            fputcsv($out, ['Menu Terlaris']);
            fputcsv($out, ['Menu', 'Kategori', 'Terjual', 'Pendapatan']);
            foreach ($this->menuTerlaris($dari, $sampai) as $m) {
                fputcsv($out, [$m->nama_menu, $m->kategori, $m->terjual, $m->pendapatan]);
            }
            fputcsv($out, []);

            fputcsv($out, ['Detail Pesanan']);
            fputcsv($out, ['No', 'No. Pesanan', 'Tanggal', 'Customer', 'NIM', 'Warung', 'Metode', 'Status Bayar', 'Status', 'Total']);
            $no = 1;
            foreach ($this->detailPesanan($dari, $sampai) as $p) {
                fputcsv($out, [
                    $no++,
                    $p->nomor_pesanan,
                    Carbon::parse($p->tanggal)->format('d/m/Y H:i'),
                    $p->nama_customer_snapshot ?: '-',
                    $p->nim_snapshot ?: '-',
                    $p->nama_warung ?? '-',
                    Pesanan::METODE_BAYAR[$p->metode_bayar] ?? $p->metode_bayar,
                    $p->status_bayar,
                    $p->status,
                    $p->total_harga,
                ]);
            }

            fclose($out);
        }, $namaFile, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Cetak laporan penjualan sebagai halaman HTML siap-print (PDF via
     * browser "Simpan sebagai PDF"). Tidak butuh ekstensi tambahan.
     */
    public function cetak(Request $request)
    {
        [$dari, $sampai] = $this->rentang($request);

        $ringkasan = $this->ringkasan($dari, $sampai);
        $harian = $this->penjualanHarian($dari, $sampai);
        $menuTerlaris = $this->menuTerlaris($dari, $sampai);
        $perWarung = $this->penjualanPerWarung($dari, $sampai);
        $perKantin = $this->penjualanPerKantin($dari, $sampai);
        $detail = $this->detailPesanan($dari, $sampai);

        return view('admin.laporan.cetak', compact(
            'dari',
            'sampai',
            'ringkasan',
            'harian',
            'menuTerlaris',
            'perWarung',
            'perKantin',
            'detail'
        ));
    }

    /* ------------------------------------------------------------------ */

    /**
     * @return array{0: string, 1: string}
     */
    private function rentang(Request $request): array
    {
        $dari = $request->filled('dari')
            ? Carbon::parse($request->dari)->toDateString()
            : now()->subDays(29)->toDateString();

        $sampai = $request->filled('sampai')
            ? Carbon::parse($request->sampai)->toDateString()
            : now()->toDateString();

        // Pastikan urutan benar.
        if ($dari > $sampai) {
            [$dari, $sampai] = [$sampai, $dari];
        }

        return [$dari, $sampai];
    }

    private function queryPesanan(string $dari, string $sampai)
    {
        return Pesanan::whereBetween(DB::raw('DATE(tanggal)'), [$dari, $sampai]);
    }

    private function ringkasan(string $dari, string $sampai): array
    {
        $q = $this->queryPesanan($dari, $sampai);

        $totalPesanan = (clone $q)->count();
        $pesananSelesai = (clone $q)->where('status', Pesanan::STATUS_SELESAI)->count();
        $pesananDibatalkan = (clone $q)->where('status', Pesanan::STATUS_DIBATALKAN)->count();

        $totalPendapatan = (float) (clone $q)
            ->where('status_bayar', Pesanan::BAYAR_LUNAS)
            ->where('status', '!=', Pesanan::STATUS_DIBATALKAN)
            ->sum('total_harga');

        $rataRata = $pesananSelesai > 0
            ? round($totalPendapatan / max($pesananSelesai, 1))
            : 0;

        return compact('totalPesanan', 'pesananSelesai', 'pesananDibatalkan', 'totalPendapatan', 'rataRata');
    }

    private function penjualanHarian(string $dari, string $sampai)
    {
        return $this->queryPesanan($dari, $sampai)
            ->selectRaw('DATE(tanggal) as tanggal, COUNT(*) as jumlah')
            ->selectRaw('SUM(CASE WHEN status_bayar = ? AND status != ? THEN total_harga ELSE 0 END) as pendapatan', [
                Pesanan::BAYAR_LUNAS,
                Pesanan::STATUS_DIBATALKAN,
            ])
            // WAJIB GROUP BY ekspresi mentah: `groupBy('tanggal')` akan
            // di-quote sebagai kolom `tanggal` (datetime penuh), sehingga
            // setiap jam menjadi grup terpisah.
            ->groupBy(DB::raw('DATE(tanggal)'))
            ->orderBy(DB::raw('DATE(tanggal)'))
            ->get()
            ->map(fn ($h) => (object) [
                'tanggal' => $h->tanggal,
                'jumlah' => (int) $h->jumlah,
                // Cast eksplisit ke float agar tidak ada string "0.00" yang
                // lolos dari `?: 1` dan memicu DivisionByZeroError di view.
                'pendapatan' => (float) $h->pendapatan,
            ]);
    }

    private function menuTerlaris(string $dari, string $sampai)
    {
        // Ambil baris mentah lalu agregasi di PHP. Pendekatan ini kebal
        // terhadap perbedaan perilaku GROUP BY antar DBMS
        // (MySQL only_full_group_by & resolusi alias di SQLite).
        $baris = PesananItem::query()
            ->join('pesanans', 'pesanans.id', '=', 'pesanan_items.pesanan_id')
            ->leftJoin('menus', 'menus.id', '=', 'pesanan_items.menu_id')
            ->whereBetween(DB::raw('DATE(pesanans.tanggal)'), [$dari, $sampai])
            ->where('pesanans.status', '!=', Pesanan::STATUS_DIBATALKAN)
            ->get([
                'pesanan_items.menu_id',
                'pesanan_items.nama_menu_snapshot',
                'pesanan_items.jumlah',
                'pesanan_items.subtotal',
                'menus.nama_menu as nama_menu_relasi',
                'menus.kategori as kategori_relasi',
            ]);

        return $baris
            ->groupBy(fn ($r) => $r->nama_menu_snapshot ?: ($r->nama_menu_relasi ?: '(menu dihapus)'))
            ->map(function ($items, $nama) {
                $pertama = $items->first();
                $kategori = $items->pluck('kategori_relasi')->filter()->first() ?: '-';

                return (object) [
                    'nama_menu' => $nama,
                    'kategori' => $kategori,
                    'terjual' => (int) $items->sum('jumlah'),
                    'pendapatan' => (float) $items->sum('subtotal'),
                ];
            })
            ->sortByDesc('terjual')
            ->take(20)
            ->values();
    }

    private function penjualanPerWarung(string $dari, string $sampai)
    {
        return $this->queryPesanan($dari, $sampai)
            ->join('warungs', 'warungs.id', '=', 'pesanans.warung_id')
            ->join('kantins', 'kantins.id', '=', 'warungs.kantin_id')
            ->where('pesanans.status', '!=', Pesanan::STATUS_DIBATALKAN)
            ->selectRaw('kantins.nama_kantin as kantin')
            ->selectRaw('warungs.nama_warung as warung')
            ->selectRaw('COUNT(*) as jumlah')
            ->selectRaw('SUM(CASE WHEN pesanans.status_bayar = ? THEN pesanans.total_harga ELSE 0 END) as pendapatan', [Pesanan::BAYAR_LUNAS])
            ->groupBy('kantins.nama_kantin', 'warungs.nama_warung')
            ->orderByDesc('pendapatan')
            ->get();
    }

    private function penjualanPerKantin(string $dari, string $sampai)
    {
        return $this->queryPesanan($dari, $sampai)
            ->join('warungs', 'warungs.id', '=', 'pesanans.warung_id')
            ->join('kantins', 'kantins.id', '=', 'warungs.kantin_id')
            ->where('pesanans.status', '!=', Pesanan::STATUS_DIBATALKAN)
            ->selectRaw('kantins.nama_kantin as kantin')
            ->selectRaw('COUNT(*) as jumlah')
            ->selectRaw('SUM(CASE WHEN pesanans.status_bayar = ? THEN pesanans.total_harga ELSE 0 END) as pendapatan', [Pesanan::BAYAR_LUNAS])
            ->groupBy('kantins.nama_kantin')
            ->orderByDesc('pendapatan')
            ->get();
    }

    /**
     * Daftar detail pesanan pada periode (dengan snapshot nama & NIM customer).
     */
    private function detailPesanan(string $dari, string $sampai)
    {
        return $this->queryPesanan($dari, $sampai)
            ->leftJoin('warungs', 'warungs.id', '=', 'pesanans.warung_id')
            ->orderByDesc('pesanans.tanggal')
            ->get([
                'pesanans.id',
                'pesanans.nomor_pesanan',
                'pesanans.tanggal',
                'pesanans.nama_customer_snapshot',
                'pesanans.nim_snapshot',
                'pesanans.metode_bayar',
                'pesanans.status_bayar',
                'pesanans.status',
                'pesanans.total_harga',
                'warungs.nama_warung',
            ]);
    }
}
