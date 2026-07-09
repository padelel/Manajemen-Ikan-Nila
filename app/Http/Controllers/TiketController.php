<?php

namespace App\Http\Controllers;

use App\Models\Tiket;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class TiketController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Jika Operator, hanya lihat tiket berdasarkan penugasan kolamnya.
        if ($user->role === 'operator') {
            $assignedKolamIds = $user->kolams()->pluck('kolams.id');
            $tikets = Tiket::with(['kolam', 'inferensiLog'])
                ->whereIn('kolam_id', $assignedKolamIds)
                ->latest()
                ->get();
        } else {
            $tikets = Tiket::with(['kolam', 'inferensiLog', 'operator'])->latest()->get();
        }

        return Inertia::render('Tiket/Index', [
            'tikets' => $tikets
        ]);
    }

    public function show(Tiket $tiket)
    {
        $tiket->load(['kolam', 'inferensiLog', 'operator', 'supervisor']);
        return Inertia::render('Tiket/Show', [
            'tiket' => $tiket
        ]);
    }

    // Metode untuk OPERATOR: Menyelesaikan tugas dan unggah bukti
    public function selesaikan(Request $request, Tiket $tiket)
    {
        $request->validate([
            'bukti_penyelesaian' => 'required|string', // Sesuai tipe text di database (bisa berupa path file atau base64 foto)
        ]);

        $tiket->update([
            'status' => 'menunggu_verifikasi',
            'bukti_penyelesaian' => $request->bukti_penyelesaian,
            'diselesaikan_at' => Carbon::now(),
        ]);

        return redirect()->back()->with('success', 'Tugas mitigasi berhasil diselesaikan. Menunggu verifikasi Supervisor.');
    }

    // Metode untuk SUPERVISOR: Memverifikasi bukti dari Operator
    public function verifikasi(Request $request, Tiket $tiket)
    {
        $request->validate([
            'keputusan' => 'required|in:terima,tolak',
            'catatan_supervisor' => 'nullable|string'
        ]);

        if ($request->keputusan === 'terima') {
            $tiket->update([
                'status' => 'selesai',
                'supervisor_id' => auth()->id(),
                'diverifikasi_at' => Carbon::now(),
                'catatan_supervisor' => $request->catatan_supervisor
            ]);
            return redirect()->back()->with('success', 'Tiket berhasil diverifikasi dan ditutup.');
        } else {
            // Jika ditolak, status kembali in_progress agar operator mengulang
            $tiket->update([
                'status' => 'in_progress',
                'supervisor_id' => auth()->id(),
                'catatan_supervisor' => $request->catatan_supervisor,
                'diselesaikan_at' => null // Reset waktu selesai
            ]);
            return redirect()->back()->with('warning', 'Bukti ditolak. Tiket dikembalikan ke Operator.');
        }
    }
}