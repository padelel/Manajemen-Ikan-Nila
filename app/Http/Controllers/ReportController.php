<?php

namespace App\Http\Controllers;

use App\Models\DailyParameter;
use App\Models\HarvestLog;
use App\Models\Kolam;
use App\Models\MortalityLog;
use App\Models\TebarLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $kolams = Kolam::all();

        $selectedKolamId = $request->kolam_id ?? ($kolams->first()->id ?? null);
        $selectedKolam = Kolam::find($selectedKolamId);

        $labels7Hari = [];
        for ($i = 6; $i >= 0; $i--) {
            $labels7Hari[] = Carbon::today()
                ->subDays($i)
                ->translatedFormat('d M');
        }

        $dataBeratAktual = [];
        $dataPopulasi = [];
        $eventsPerLabel = [];

        if ($selectedKolam) {
            // =========================================================
            // 1. DATA KURVA PERTUMBUHAN (SPESIFIK KOLAM)
            // =========================================================
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i)->format('Y-m-d');
                $avgBerat = DailyParameter::where('kolam_id', $selectedKolamId)
                    ->whereDate('tanggal_cek', $date)
                    ->avg('berat_sample');
                $dataBeratAktual[] = $avgBerat ? round($avgBerat, 2) : null;
            }

            // =========================================================
            // 2. DATA PERGERAKAN POPULASI (SPESIFIK KOLAM)
            // =========================================================
            $historicalPopulasi = [];
            $runningTotal = $selectedKolam->jumlah_ikan;

            for ($i = 0; $i <= 6; $i++) {
                if ($i == 0) {
                    $historicalPopulasi[] = $runningTotal;
                } else {
                    $besoknya = Carbon::today()
                        ->subDays($i - 1)
                        ->format('Y-m-d');

                    $tebar = TebarLog::where('kolam_id', $selectedKolamId)
                        ->whereDate('tanggal_tebar', $besoknya)
                        ->sum('jumlah_ikan');

                    $mati = MortalityLog::where('kolam_id', $selectedKolamId)
                        ->whereDate('tanggal_kematian', $besoknya)
                        ->sum('jumlah_mati');

                    $panen = HarvestLog::where('kolam_id', $selectedKolamId)
                        ->whereDate('tanggal_panen', $besoknya)
                        ->sum('jumlah_ikan');

                    $runningTotal = $runningTotal - $tebar + $mati + $panen;
                    $historicalPopulasi[] = $runningTotal;
                }
            }
            $dataPopulasi = array_reverse($historicalPopulasi);

            // =========================================================
            // 3. KUMPULKAN EVENTS (ANOTASI GRAFIK) — 7 HARI TERAKHIR
            // Meliputi panen (full/parsial), kematian, dan penebaran
            // yang terjadi dalam jendela 7 hari untuk kolam yang dipilih.
            // =========================================================
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $dateStr = $date->format('Y-m-d');
                $label = $date->translatedFormat('d M');
                $dayEvents = [];

                // Panen
                HarvestLog::where('kolam_id', $selectedKolamId)
                    ->whereDate('tanggal_panen', $dateStr)
                    ->get()
                    ->each(function ($p) use (&$dayEvents) {
                        $isFull = $p->jenis_panen === 'Full';
                        $jumlah = number_format($p->jumlah_ikan, 0, ',', '.');
                        $berat = number_format($p->berat_total_kg, 1, ',', '.');
                        $dayEvents[] = [
                            'type' => $isFull ? 'panen_full' : 'panen_parsial',
                            'jumlah' => $p->jumlah_ikan,
                            'text' => $isFull
                                ? "Panen Full: {$jumlah} ekor ({$berat} kg) — Siklus selesai"
                                : "Panen Parsial: {$jumlah} ekor ({$berat} kg)",
                        ];
                    });

                // Kematian
                MortalityLog::where('kolam_id', $selectedKolamId)
                    ->whereDate('tanggal_kematian', $dateStr)
                    ->get()
                    ->each(function ($m) use (&$dayEvents) {
                        $jumlah = number_format($m->jumlah_mati, 0, ',', '.');
                        $note = $m->catatan
                            ? ' — '.Str::limit($m->catatan, 40)
                            : '';
                        $dayEvents[] = [
                            'type' => 'kematian',
                            'jumlah' => $m->jumlah_mati,
                            'text' => "Kematian: {$jumlah} ekor{$note}",
                        ];
                    });

                // Penebaran
                TebarLog::where('kolam_id', $selectedKolamId)
                    ->whereDate('tanggal_tebar', $dateStr)
                    ->get()
                    ->each(function ($t) use (&$dayEvents) {
                        $jumlah = number_format($t->jumlah_ikan, 0, ',', '.');
                        $dayEvents[] = [
                            'type' => 'tebar',
                            'jumlah' => $t->jumlah_ikan,
                            'text' => "Penebaran: {$jumlah} ekor @ {$t->berat_rata_gram} g/ekor",
                        ];
                    });

                if (! empty($dayEvents)) {
                    $eventsPerLabel[$label] = $dayEvents;
                }
            }
        }

        return Inertia::render('Report/Index', [
            'kolams' => $kolams,
            'selectedKolamId' => (int) $selectedKolamId,
            'kolamInfo' => $selectedKolam,
            'events' => $eventsPerLabel,
            'chartBerat' => [
                'labels' => $labels7Hari,
                'datasets' => [
                    [
                        'label' => 'Berat Rata-rata',
                        'data' => $dataBeratAktual,
                        'borderColor' => '#4f46e5',
                        'backgroundColor' => 'rgba(79, 70, 229, 0.1)',
                        'fill' => true,
                        'tension' => 0.4,
                        'spanGaps' => false,
                    ],
                ],
            ],
            'chartPopulasi' => [
                'labels' => $labels7Hari,
                'datasets' => [
                    [
                        'label' => 'Populasi Kolam',
                        'data' => $dataPopulasi,
                        'borderColor' => '#f59e0b',
                        'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                        'fill' => true,
                    ],
                ],
            ],
        ]);
    }
}
