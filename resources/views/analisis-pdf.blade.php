<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Evaluasi Panen: {{ $kolam->nama_kolam }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1e293b; }
        h2 { text-align: center; font-size: 16px; margin-bottom: 4px; }
        .subtitle { text-align: center; font-size: 11px; color: #64748b; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th { background-color: #2563eb; color: white; padding: 6px 8px; text-align: left; font-size: 9px; text-transform: uppercase; }
        td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; }
        tr:nth-child(even) td { background-color: #f8fafc; }
        .section-title { font-size: 13px; font-weight: bold; color: #2563eb; margin: 20px 0 8px; border-bottom: 2px solid #2563eb; padding-bottom: 4px; }
        .info-grid { width: 100%; margin-bottom: 16px; }
        .info-grid td { border: none; padding: 3px 8px; vertical-align: top; }
        .info-grid td:first-child { font-weight: bold; width: 140px; color: #64748b; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 9px; font-weight: bold; }
        .badge-aktif { background: #dbeafe; color: #1d4ed8; }
        .badge-selesai { background: #dcfce7; color: #16a34a; }
        .page-break { page-break-before: always; }
        .footer { text-align: center; font-size: 8px; color: #94a3b8; margin-top: 24px; border-top: 1px solid #e2e8f0; padding-top: 8px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .text-green { color: #16a34a; }
        .text-red { color: #dc2626; }
        .text-slate { color: #64748b; }
    </style>
</head>
<body>
    <h2>Evaluasi Panen: {{ $kolam->nama_kolam }}</h2>
    <p class="subtitle">Laporan analisis siklus budidaya ikan nila</p>

    <table class="info-grid">
        <tr><td>Lokasi Kolam</td><td>: {{ $kolam->lokasi ?? '-' }}</td></tr>
        <tr><td>Total Siklus</td><td>: {{ count($dataSiklus) }}</td></tr>
        <tr><td>Tanggal Cetak</td><td>: {{ date('d M Y H:i') }}</td></tr>
    </table>

    @foreach($dataSiklus as $index => $siklus)
        @if($index > 0)
            <div class="page-break"></div>
        @endif

        <div class="section-title">{{ $siklus['nama_siklus'] }}</div>

        <table>
            <tr>
                <td class="font-bold text-slate" style="width:130px">Periode Siklus</td>
                <td>{{ \Carbon\Carbon::parse($siklus['tanggal_mulai'])->format('d M Y') }} s.d. {{ $siklus['tanggal_panen'] ? \Carbon\Carbon::parse($siklus['tanggal_panen'])->format('d M Y') : 'Belum Panen' }}</td>
            </tr>
            <tr>
                <td class="font-bold text-slate">Status</td>
                <td><span class="badge {{ $siklus['status'] === 'berjalan' ? 'badge-aktif' : 'badge-selesai' }}">{{ ucfirst($siklus['status']) }}</span></td>
            </tr>
            <tr>
                <td class="font-bold text-slate">Durasi</td>
                <td>{{ $siklus['durasi_hari'] }} Hari</td>
            </tr>
            <tr>
                <td class="font-bold text-slate">Sumber Benih</td>
                <td>{{ $siklus['sumber_benih'] }}</td>
            </tr>
        </table>

        <div class="section-title">Hasil Produksi</div>
        <table>
            <thead>
                <tr>
                    <th>Metrik</th>
                    <th class="text-right">Nilai</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Tebar Awal</td>
                    <td class="text-right font-bold">{{ number_format($siklus['tebar_awal'], 0, ',', '.') }} Ekor</td>
                </tr>
                <tr>
                    <td>Total Kematian</td>
                    <td class="text-right font-bold text-red">{{ number_format($siklus['total_kematian'], 0, ',', '.') }} Ekor</td>
                </tr>
                <tr>
                    <td>Total Panen</td>
                    <td class="text-right font-bold text-green">{{ number_format($siklus['jumlah_ikan_panen'], 0, ',', '.') }} Ekor</td>
                </tr>
                <tr>
                    <td>Survival Rate (SR)</td>
                    <td class="text-right font-bold {{ $siklus['sr'] >= 80 ? 'text-green' : 'text-red' }}">{{ $siklus['sr'] }}%</td>
                </tr>
            </tbody>
        </table>

        <div class="section-title">Mitigasi AI</div>
        <table>
            <thead>
                <tr>
                    <th>Total Tiket</th>
                    <th class="text-right">Selesai</th>
                    <th class="text-right">Progress</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $siklus['mitigasi']['total'] }}</td>
                    <td class="text-right">{{ $siklus['mitigasi']['selesai'] }}</td>
                    <td class="text-right">{{ $siklus['mitigasi']['total'] > 0 ? round(($siklus['mitigasi']['selesai'] / $siklus['mitigasi']['total']) * 100) : 0 }}%</td>
                </tr>
            </tbody>
        </table>

        @if(count($siklus['riwayat_kematian']['labels']) > 0)
            <div class="section-title">Riwayat Kematian</div>
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th class="text-right">Jumlah Mati (Ekor)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($siklus['riwayat_kematian']['labels'] as $i => $label)
                        <tr>
                            <td>{{ $label }}</td>
                            <td class="text-right">{{ number_format($siklus['riwayat_kematian']['data'][$i], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if(count($siklus['grafik_air']['labels']) > 0)
            <div class="section-title">Parameter Kualitas Air</div>
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th class="text-right">Suhu (°C)</th>
                        <th class="text-right">pH</th>
                        <th class="text-right">DO (mg/L)</th>
                        <th class="text-right">Kecerahan (cm)</th>
                        <th class="text-right">Flok (ml)</th>
                        <th class="text-right">Amonia (mg/L)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($siklus['grafik_air']['labels'] as $i => $label)
                        <tr>
                            <td>{{ $label }}</td>
                            <td class="text-right">{{ $siklus['grafik_air']['suhu'][$i] ?? '-' }}</td>
                            <td class="text-right">{{ $siklus['grafik_air']['ph'][$i] ?? '-' }}</td>
                            <td class="text-right">{{ $siklus['grafik_air']['do'][$i] ?? '-' }}</td>
                            <td class="text-right">{{ $siklus['grafik_air']['kecerahan'][$i] ?? '-' }}</td>
                            <td class="text-right">{{ $siklus['grafik_air']['flok'][$i] ?? '-' }}</td>
                            <td class="text-right">{{ $siklus['grafik_air']['amonia'][$i] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endforeach

    <div class="footer">
        Dicetak pada {{ date('d M Y H:i') }} — Sistem Manajemen Budidaya Ikan Nila
    </div>
</body>
</html>
