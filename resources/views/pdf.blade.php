<!DOCTYPE html>
<html>
<head>
    <title>Laporan Pakan</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        h2 { text-align: center; }
    </style>
</head>
<body>
    <h2>Laporan Riwayat Pemberian Pakan<br>Sistem Cerdas Manajemen Tambak</h2>
    <p>Dicetak pada: {{ date('d M Y H:i') }}</p>
    
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Kolam</th>
                <th>Rule Sistem</th>
                <th>Rekomendasi</th>
                <th>Aktual Diberikan</th>
                <th>Petugas</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            <tr>
                <td>{{ $log->tanggal_pakan }}</td>
                <td>{{ $log->kolam->nama_kolam }}</td>
                <td>{{ $log->rule->kode_rule }}</td>
                <td>{{ $log->rekomendasi_sistem }} Kg</td>
                <td>{{ $log->pakan_aktual }} Kg</td>
                <td>{{ $log->user->name }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>