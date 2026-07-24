<?php

namespace App\Http\Controllers;

use App\Models\HarvestLog;
use App\Models\Kolam;
use App\Models\MortalityLog;
use App\Models\SiklusBudidaya;
use App\Models\Tiket;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    public function index()
    {
        $kolams = Kolam::withCount(['siklusBudidayas as total_siklus'])->get()->map(function ($kolam) {
            return [
                'id' => $kolam->id,
                'nama_kolam' => $kolam->nama_kolam,
                'total_siklus' => (int) $kolam->total_siklus,
            ];
        });

        return Inertia::render('Analisis/Index', [
            'kolams' => $kolams,
        ]);
    }

    public function show($id)
    {
        $kolam = Kolam::findOrFail($id);

        return Inertia::render('Analisis/Show', [
            'kolam' => $kolam,
            'dataSiklus' => array_reverse($this->buildDataSiklus($kolam)),
        ]);
    }

    public function exportPdf($id)
    {
        $kolam = Kolam::findOrFail($id);
        $dataSiklus = array_reverse($this->buildDataSiklus($kolam));

        $pdf = Pdf::loadView('analisis-pdf', [
            'kolam' => $kolam,
            'dataSiklus' => $dataSiklus,
        ]);

        $filename = 'Evaluasi-Panen-'.str_replace(' ', '-', $kolam->nama_kolam).'.pdf';

        return $pdf->download($filename);
    }

    public function exportExcel($id)
    {
        $kolam = Kolam::findOrFail($id);
        $dataSiklus = array_reverse($this->buildDataSiklus($kolam));

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Evaluasi Panen');

        $row = 1;

        // Title
        $sheet->setCellValue('A'.$row, 'Evaluasi Panen: '.$kolam->nama_kolam);
        $sheet->mergeCells('A'.$row.':F'.$row);
        $sheet->getStyle('A'.$row)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row += 2;

        // Info
        $sheet->setCellValue('A'.$row, 'Lokasi: '.($kolam->lokasi ?? '-'));
        $row++;
        $sheet->setCellValue('A'.$row, 'Total Siklus: '.count($dataSiklus));
        $sheet->getStyle('A'.$row)->getFont()->setItalic(true);
        $row += 2;

        foreach ($dataSiklus as $siklus) {
            // Siklus header
            $styleHeader = [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
            ];
            $sheet->setCellValue('A'.$row, $siklus['nama_siklus'].' - '.ucfirst($siklus['status']));
            $sheet->mergeCells('A'.$row.':F'.$row);
            $sheet->getStyle('A'.$row)->applyFromArray($styleHeader);
            $row++;

            // Info siklus
            $infoData = [
                ['Periode', Carbon::parse($siklus['tanggal_mulai'])->format('d M Y').' s.d. '.($siklus['tanggal_panen'] ? Carbon::parse($siklus['tanggal_panen'])->format('d M Y') : 'Belum Panen')],
                ['Durasi', $siklus['durasi_hari'].' Hari'],
                ['Sumber Benih', $siklus['sumber_benih']],
            ];
            foreach ($infoData as $info) {
                $sheet->setCellValue('A'.$row, $info[0]);
                $sheet->setCellValue('B'.$row, $info[1]);
                $sheet->mergeCells('B'.$row.':G'.$row);
                $sheet->getStyle('A'.$row)->getFont()->setBold(true);
                $row++;
            }
            $row++;

            // Hasil Produksi table
            $tableHeaders = ['Metrik', 'Nilai'];
            $sheet->setCellValue('A'.$row, $tableHeaders[0]);
            $sheet->setCellValue('B'.$row, $tableHeaders[1]);
            $sheet->mergeCells('B'.$row.':G'.$row);
            $sheet->getStyle('A'.$row.':B'.$row)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '475569']],
            ]);
            $row++;

            $produksiData = [
                ['Tebar Awal', number_format($siklus['tebar_awal'], 0, ',', '.').' Ekor'],
                ['Total Kematian', number_format($siklus['total_kematian'], 0, ',', '.').' Ekor'],
                ['Total Panen', number_format($siklus['jumlah_ikan_panen'], 0, ',', '.').' Ekor'],
                ['Survival Rate (SR)', $siklus['sr'].'%'],
            ];
            foreach ($produksiData as $prod) {
                $sheet->setCellValue('A'.$row, $prod[0]);
                $sheet->setCellValue('B'.$row, $prod[1]);
                $sheet->mergeCells('B'.$row.':G'.$row);
                $sheet->getStyle('A'.$row)->getFont()->setBold(true);
                $row++;
            }
            $row++;

            // Mitigasi
            $sheet->setCellValue('A'.$row, 'Total Tiket');
            $sheet->setCellValue('B'.$row, $siklus['mitigasi']['total']);
            $row++;
            $sheet->setCellValue('A'.$row, 'Tiket Selesai');
            $sheet->setCellValue('B'.$row, $siklus['mitigasi']['selesai']);
            $sheet->getStyle('A'.$row - 1 .':A'.$row)->getFont()->setBold(true);
            $row++;
            $row++;

            // Riwayat Kematian
            if (count($siklus['riwayat_kematian']['labels']) > 0) {
                $sheet->setCellValue('A'.$row, 'Riwayat Kematian');
                $sheet->mergeCells('A'.$row.':F'.$row);
                $sheet->getStyle('A'.$row)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DC2626']],
                ]);
                $row++;
                $sheet->setCellValue('A'.$row, 'Tanggal');
                $sheet->setCellValue('B'.$row, 'Jumlah Mati (Ekor)');
                $sheet->mergeCells('B'.$row.':G'.$row);
                $sheet->getStyle('A'.$row.':B'.$row)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEE2E2']],
                ]);
                $row++;
                foreach ($siklus['riwayat_kematian']['labels'] as $i => $label) {
                    $sheet->setCellValue('A'.$row, $label);
                    $sheet->setCellValue('B'.$row, $siklus['riwayat_kematian']['data'][$i]);
                    $sheet->mergeCells('B'.$row.':G'.$row);
                    $row++;
                }
                $row++;
            }

            // Parameter Air
            if (count($siklus['grafik_air']['labels']) > 0) {
                $sheet->setCellValue('A'.$row, 'Parameter Kualitas Air');
                $sheet->mergeCells('A'.$row.':F'.$row);
                $sheet->getStyle('A'.$row)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0EA5E9']],
                ]);
                $row++;

                $airHeaders = ['Tanggal', 'Suhu (°C)', 'pH', 'DO (mg/L)', 'Flok (ml)', 'Amonia (mg/L)'];
                $col = 'A';
                foreach ($airHeaders as $header) {
                    $sheet->setCellValue($col.$row, $header);
                    $col++;
                }
                $sheet->getStyle('A'.$row.':F'.$row)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0F2FE']],
                ]);
                $row++;

                foreach ($siklus['grafik_air']['labels'] as $i => $label) {
                    $sheet->setCellValue('A'.$row, $label);
                    $sheet->setCellValue('B'.$row, $siklus['grafik_air']['suhu'][$i] ?? '-');
                    $sheet->setCellValue('C'.$row, $siklus['grafik_air']['ph'][$i] ?? '-');
                    $sheet->setCellValue('D'.$row, $siklus['grafik_air']['do'][$i] ?? '-');
                    $sheet->setCellValue('E'.$row, $siklus['grafik_air']['flok'][$i] ?? '-');
                    $sheet->setCellValue('F'.$row, $siklus['grafik_air']['amonia'][$i] ?? '-');
                    $row++;
                }
                $row++;
            }

            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Evaluasi-Panen-'.str_replace(' ', '-', $kolam->nama_kolam).'.xlsx';
        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function buildDataSiklus($kolam)
    {
        $siklusBudidaya = SiklusBudidaya::where('kolam_id', $kolam->id)
            ->orderBy('tanggal_mulai', 'asc')
            ->get();

        $dataSiklus = [];
        $counter = 1;

        foreach ($siklusBudidaya as $siklus) {
            $waktuMulai = $siklus->tanggal_mulai;
            $waktuSelesai = $siklus->status_aktif === 'selesai'
                ? ($siklus->tanggal_selesai ?? $siklus->updated_at)
                : Carbon::now();

            $panen = HarvestLog::where('siklus_budidaya_id', $siklus->id)->first();

            $tanggalPanen = $panen?->tanggal_panen;
            $jumlahIkanPanen = $panen?->jumlah_ikan_panen ?? 0;
            $sr = $panen?->survival_rate ?? 0;

            if ($tanggalPanen) {
                $durasi = Carbon::parse($waktuMulai)->diffInDays(Carbon::parse($tanggalPanen));
            } else {
                $durasi = Carbon::parse($waktuMulai)->diffInDays($waktuSelesai);
            }

            $mortality = MortalityLog::where('siklus_budidaya_id', $siklus->id)
                ->select(DB::raw('DATE(tanggal_kematian) as tanggal'), DB::raw('SUM(jumlah_mati) as total_mati'))
                ->groupBy('tanggal')
                ->orderBy('tanggal', 'asc')
                ->get();

            $totalMati = $mortality->sum('total_mati');

            $tikets = Tiket::where('kolam_id', $kolam->id)
                ->whereBetween('created_at', [$waktuMulai, $waktuSelesai])
                ->get();

            $parameterAir = \App\Models\ParameterHarian::where('kolam_id', $kolam->id)
                ->whereBetween('tanggal_cek', [$waktuMulai, $waktuSelesai])
                ->orderBy('tanggal_cek', 'asc')
                ->get();

            $dataSiklus[] = [
                'id' => $siklus->id,
                'nama_siklus' => 'Siklus '.$counter,
                'status' => $siklus->status_aktif,
                'tanggal_mulai' => $waktuMulai,
                'tanggal_panen' => $tanggalPanen,
                'durasi_hari' => (int) $durasi,
                'sumber_benih' => $siklus->catatan ?? 'Tidak dicatat',
                'tebar_awal' => $siklus->jumlah_tebar_awal,
                'jumlah_ikan_panen' => (int) $jumlahIkanPanen,
                'total_kematian' => (int) $totalMati,
                'sr' => $siklus->jumlah_tebar_awal > 0
                    ? round((($siklus->jumlah_tebar_awal - $totalMati) / $siklus->jumlah_tebar_awal) * 100, 2)
                    : 0,
                'sr_panen' => (float) $sr,
                'riwayat_kematian' => [
                    'labels' => $mortality->pluck('tanggal')->map(fn ($d) => Carbon::parse($d)->format('d M Y'))->toArray(),
                    'data' => $mortality->pluck('total_mati')->toArray(),
                ],
                'mitigasi' => [
                    'total' => $tikets->count(),
                    'selesai' => $tikets->where('status', 'selesai')->count(),
                ],
                'grafik_air' => [
                    'labels' => $parameterAir->pluck('tanggal_cek')->map(fn ($d) => Carbon::parse($d)->format('d M'))->toArray(),
                    'suhu' => $parameterAir->pluck('suhu')->toArray(),
                    'ph' => $parameterAir->pluck('ph')->toArray(),
                    'do' => $parameterAir->pluck('do_mgl')->toArray(),

                    'flok' => $parameterAir->pluck('flok_ml')->toArray(),
                    'amonia' => $parameterAir->pluck('amonia_mgl')->toArray(),
                ],
            ];
            $counter++;
        }

        return $dataSiklus;
    }
}
