<?php

namespace App\Http\Controllers;

use App\Models\FeedLog;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function cetakPDF()
    {
        // Ambil data riwayat pakan beserta relasinya
        $logs = FeedLog::with(['kolam', 'rule', 'inventory', 'user'])->latest()->get();

        // Load tampilan dari file blade (yang akan kita buat setelah ini)
        $pdf = Pdf::loadView('laporan.pdf', ['logs' => $logs]);

        // Unduh otomatis dengan nama file khusus
        return $pdf->download('Laporan_Pakan_Skripsi_' . date('Y-m-d') . '.pdf');
    }
}