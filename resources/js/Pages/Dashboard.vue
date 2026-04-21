<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler } from 'chart.js';

// Mendaftarkan elemen Chart.js
ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler);

// Menerima data dari Controller
const props = defineProps({
    ringkasan: Object,
    inventory: Object,
    sma: Object,
    chartPakan: Object,
    chartBerat: Object
});

// Konfigurasi Visual Umum
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#0f172a',
            padding: 12,
            titleFont: { size: 13, family: 'Inter, sans-serif' },
            bodyFont: { size: 14, font: 'Inter, sans-serif', weight: 'bold' },
            displayColors: false,
            cornerRadius: 8,
        }
    },
    scales: {
        y: { 
            beginAtZero: true,
            border: { display: false },
            grid: { color: '#f1f5f9' },
            ticks: { color: '#94a3b8', font: { size: 11 } }
        },
        x: { 
            grid: { display: false },
            border: { display: false },
            ticks: { color: '#94a3b8', font: { size: 11 } }
        }
    },
    elements: {
        line: { tension: 0.4 },
        point: { radius: 0, hitRadius: 10, hoverRadius: 6 } // Sembunyikan titik kecuali di-hover
    },
    interaction: { mode: 'index', intersect: false }
};

// 1. Grafik Tren Pakan (Biru - Indigo)
const chartPakanConfig = {
    labels: props.chartPakan.labels,
    datasets: [{
        label: 'Total Pakan (Kg)',
        borderColor: '#6366f1', // Indigo-500
        backgroundColor: 'rgba(99, 102, 241, 0.05)', 
        borderWidth: 3,
        pointBackgroundColor: '#4f46e5',
        fill: true,
        data: props.chartPakan.data,
    }]
};

// 2. Grafik Pertumbuhan Berat Ikan (Hijau - Emerald)
const chartBeratConfig = {
    labels: props.chartBerat.labels,
    datasets: [{
        label: 'Berat Rata-rata (gram)',
        borderColor: '#10b981', // Emerald-500
        backgroundColor: 'rgba(16, 185, 129, 0.05)',
        borderWidth: 3,
        pointBackgroundColor: '#059669',
        fill: true,
        data: props.chartBerat.berat,
    }]
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col">
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Ringkasan Tambak</h2>
                <p class="text-sm text-slate-500 mt-1">Pantau performa kolam, stok pakan, dan analisis cerdas sistem.</p>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-[0_4px_20px_rgb(0,0,0,0.03)] hover:-translate-y-1 transition-transform duration-300">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-50 text-blue-500">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                            </span>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Kolam Aktif</p>
                        </div>
                        <p class="text-3xl font-black text-slate-900">{{ ringkasan.totalKolam }} <span class="text-sm font-semibold text-slate-400">Unit</span></p>
                    </div>
                    
                    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-[0_4px_20px_rgb(0,0,0,0.03)] hover:-translate-y-1 transition-transform duration-300">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="flex items-center justify-center w-8 h-8 rounded-full bg-emerald-50 text-emerald-500">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76" /></svg>
                            </span>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Populasi</p>
                        </div>
                        <p class="text-3xl font-black text-slate-900">{{ ringkasan.totalIkan.toLocaleString('id-ID') }} <span class="text-sm font-semibold text-slate-400">Ekor</span></p>
                    </div>

                    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-[0_4px_20px_rgb(0,0,0,0.03)] hover:-translate-y-1 transition-transform duration-300">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="flex items-center justify-center w-8 h-8 rounded-full bg-purple-50 text-purple-500">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" /></svg>
                            </span>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Est. Biomassa</p>
                        </div>
                        <p class="text-3xl font-black text-slate-900">{{ ringkasan.totalBiomassaKg.toLocaleString('id-ID') }} <span class="text-sm font-semibold text-slate-400">Kg</span></p>
                    </div>

                    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-[0_4px_20px_rgb(0,0,0,0.03)] hover:-translate-y-1 transition-transform duration-300">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="flex items-center justify-center w-8 h-8 rounded-full bg-amber-50 text-amber-500">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                            </span>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest overflow-hidden text-ellipsis whitespace-nowrap">Stok ({{ inventory.namaPakan }})</p>
                        </div>
                        <p class="text-3xl font-black text-slate-900">{{ inventory.stokPakan }} <span class="text-sm font-semibold text-slate-400">Kg</span></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <div class="lg:col-span-2 bg-white p-8 rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] flex flex-col">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-bold text-slate-900">Tren Konsumsi Pakan</h3>
                            <span class="px-3 py-1 bg-slate-100 text-slate-500 text-xs font-bold rounded-lg">7 Hari Terakhir</span>
                        </div>
                        <div class="flex-grow relative h-72 w-full">
                            <Line :data="chartPakanConfig" :options="chartOptions" />
                        </div>
                    </div>

                    <div class="bg-slate-900 text-white p-8 rounded-3xl shadow-[0_10px_40px_rgb(0,0,0,0.2)] flex flex-col justify-between relative overflow-hidden">
                        <div class="absolute -top-10 -right-10 w-40 h-40 bg-white opacity-5 rounded-full blur-2xl"></div>
                        
                        <div class="relative z-10">
                            <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                                <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                Prediksi Cerdas
                            </h3>
                            
                            <div class="mb-6">
                                <p class="text-slate-400 text-sm mb-1">Laju Konsumsi Pakan</p>
                                <p class="text-2xl font-light"><span class="font-bold text-white">{{ sma.rata_rata_harian }}</span> Kg/Hari</p>
                            </div>
                            
                            <div class="p-5 rounded-2xl border"
                                :class="{
                                    'bg-emerald-500/10 border-emerald-500/20 text-emerald-100': sma.status === 'Aman',
                                    'bg-amber-500/10 border-amber-500/20 text-amber-100': sma.status === 'Waspada',
                                    'bg-red-500/10 border-red-500/30 text-red-100 animate-pulse': sma.status === 'Kritis' || sma.status === 'Habis'
                                }">
                                <p class="text-[10px] font-bold uppercase tracking-widest opacity-70 mb-1">Estimasi Stok Habis</p>
                                <p v-if="sma.rata_rata_harian > 0" class="text-5xl font-black mb-1">
                                    {{ sma.estimasi_hari }} <span class="text-xl font-medium opacity-60">Hari</span>
                                </p>
                                <p v-else class="text-lg font-medium mb-1">Menunggu Data...</p>
                                
                                <div class="mt-2 text-xs font-medium opacity-80 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full" :class="sma.status === 'Aman' ? 'bg-emerald-400' : (sma.status === 'Waspada' ? 'bg-amber-400' : 'bg-red-400')"></span>
                                    Status: {{ sma.status }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex gap-3 relative z-10">
                            <Link href="/operasi-harian" class="flex-1 text-center bg-blue-500 text-white px-4 py-3 rounded-xl shadow-lg shadow-blue-500/30 hover:bg-blue-400 font-bold transition-all text-sm">
                                Beri Pakan
                            </Link>
                            <Link href="/inventory" class="flex-1 text-center bg-white/10 text-white hover:bg-white/20 px-4 py-3 rounded-xl font-bold transition-all text-sm backdrop-blur-sm">
                                Restock
                            </Link>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-slate-900">Kurva Pertumbuhan Ikan</h3>
                        <span class="px-3 py-1 bg-emerald-50 text-emerald-600 text-xs font-bold rounded-lg border border-emerald-100">Berdasarkan Sampling</span>
                    </div>
                    <div class="relative h-80 w-full">
                        <Line :data="chartBeratConfig" :options="chartOptions" />
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>