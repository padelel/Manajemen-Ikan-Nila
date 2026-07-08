<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Kolam;
use App\Models\Tiket;
use App\Models\HarvestLog;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'supervisor') {
            // Statistik untuk Supervisor (Melihat keseluruhan performa tambak)
            $stats = [
                'total_kolam_aktif' => Kolam::where('status_kolam', 'aktif')->count(),
                'tiket_aktif'       => Tiket::whereIn('status', ['open', 'in_progress'])->count(),
                'menunggu_verifikasi'=> Tiket::where('status', 'menunggu_verifikasi')->count(),
                'total_panen_kg'    => HarvestLog::sum('berat_total_kg'),
            ];
        } else {
            // Statistik untuk Operator (Fokus pada tugas lapangan)
            $stats = [
                'kolam_ditugaskan'  => $user->kolams()->count(),
                'tugas_mitigasi'    => Tiket::where('operator_id', $user->id)
                                            ->whereIn('status', ['open', 'in_progress'])
                                            ->count(),
            ];
        }

        return Inertia::render('Dashboard', [
            'role'  => $user->role,
            'stats' => $stats
        ]);
    }
}