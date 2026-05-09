<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler } from 'chart.js';
import GrowthChart from '@/Components/GrowthChart.vue';
import PopulationChart from '@/Components/PopulationChart.vue';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler);

const props = defineProps({
    ringkasan: Object,
    kolam_list: Array, 
    inventory: Object, 
    inventory_list: Array, 
    chartPakan: Object,
    chartBerat: Object,
    chartPopulasi: Object
});

// Konfigurasi khusus untuk Tren Konsumsi Pakan
const chartPakanOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#0f172a',
            padding: 12,
            titleFont: { size: 13 },
            bodyFont: { size: 14, weight: 'bold' },
            cornerRadius: 8,
            callbacks: {
                label: function(context) {
                    let label = context.dataset.label || '';
                    if (label) label += ': ';
                    if (context.parsed.y !== null) label += context.parsed.y + ' Kg';
                    return label;
                }
            }
        }
    },
    scales: {
        y: { 
            beginAtZero: true,
            // Menggunakan warna transparan agar cocok untuk Light dan Dark mode
            grid: { color: 'rgba(148, 163, 184, 0.2)' }, 
            ticks: { color: '#94a3b8', font: { size: 11 } }
        },
        x: { 
            grid: { display: false },
            ticks: { color: '#94a3b8', font: { size: 11 } }
        }
    },
    elements: {
        line: { tension: 0.4 },
        point: { radius: 2, hoverRadius: 6 }
    },
    interaction: { mode: 'index', intersect: false }
};

const chartPakanConfig = {
    labels: props.chartPakan.labels,
    datasets: props.chartPakan.datasets
};
</script>

<template>
    <Head title="Ringkasan Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight transition-colors">Ringkasan Tambak</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors">Pantau performa kolam, stok pakan, dan analisis cerdas sistem.</p>
            </div>
        </template>

        <div class="py-6 sm:py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 sm:space-y-8">
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                    <div class="bg-white dark:bg-slate-800 p-5 sm:p-6 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-[0_4px_20px_rgb(0,0,0,0.03)] hover:-translate-y-1 transition-all duration-300 cursor-default">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-50 dark:bg-blue-500/10 text-blue-500 dark:text-blue-400 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                            </span>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Kolam Aktif</p>
                        </div>
                        <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-slate-100 transition-colors">{{ ringkasan.totalKolam }} <span class="text-xs sm:text-sm font-semibold text-slate-400 dark:text-slate-500">Unit</span></p>
                    </div>
                    
                    <div class="group relative z-30 hover:z-50 bg-white dark:bg-slate-800 p-5 sm:p-6 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-[0_4px_20px_rgb(0,0,0,0.03)] hover:-translate-y-1 transition-all duration-300 cursor-default">
                        <div class="flex items-center gap-3 mb-3 relative z-10">
                            <span class="flex items-center justify-center w-8 h-8 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-500 dark:text-emerald-400 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76" /></svg>
                            </span>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Total Populasi</p>
                        </div>
                        <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-slate-100 relative z-10 transition-colors">{{ ringkasan.totalIkan.toLocaleString('id-ID') }} <span class="text-xs sm:text-sm font-semibold text-slate-400 dark:text-slate-500">Ekor</span></p>
                        
                        <div class="absolute -left-2 right-2 sm:left-0 sm:right-auto top-full mt-3 w-auto sm:min-w-[250px] bg-slate-900 dark:bg-slate-700 p-4 sm:p-5 rounded-2xl shadow-[0_20px_40px_rgb(0,0,0,0.2)] dark:shadow-none opacity-0 invisible group-hover:opacity-100 group-hover:visible translate-y-3 group-hover:translate-y-0 transition-all duration-300 z-50 border border-slate-800 dark:border-slate-600">
                            <div class="absolute -top-2 left-10 sm:left-8 w-4 h-4 bg-slate-900 dark:bg-slate-700 rotate-45 border-t border-l border-slate-800 dark:border-slate-600"></div>
                            <div class="relative z-10">
                                <p class="text-[10px] uppercase tracking-widest text-slate-400 dark:text-slate-300 font-bold mb-3 border-b border-slate-800 dark:border-slate-600 pb-2">Rincian Populasi</p>
                                <div class="space-y-2.5 max-h-[150px] overflow-y-auto custom-scrollbar">
                                    <div v-for="kolam in kolam_list" :key="kolam.id" class="flex justify-between items-center text-sm">
                                        <span class="font-medium text-slate-300 dark:text-slate-200 truncate pr-2">{{ kolam.nama }}</span>
                                        <span class="font-bold text-white whitespace-nowrap">{{ kolam.populasi.toLocaleString('id-ID') }} <span class="text-[10px] text-slate-500 dark:text-slate-400 font-normal">Ekor</span></span>
                                    </div>
                                    <div v-if="kolam_list.length === 0" class="text-xs text-slate-500 italic text-center py-2">Belum ada data kolam</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 p-5 sm:p-6 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-[0_4px_20px_rgb(0,0,0,0.03)] hover:-translate-y-1 transition-all duration-300 cursor-default">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="flex items-center justify-center w-8 h-8 rounded-full bg-purple-50 dark:bg-purple-500/10 text-purple-500 dark:text-purple-400 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" /></svg>
                            </span>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Est. Biomassa</p>
                        </div>
                        <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-slate-100 transition-colors">{{ ringkasan.totalBiomassaKg.toLocaleString('id-ID') }} <span class="text-xs sm:text-sm font-semibold text-slate-400 dark:text-slate-500">Kg</span></p>
                    </div>

                    <div class="group relative z-20 hover:z-50 bg-white dark:bg-slate-800 p-5 sm:p-6 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-[0_4px_20px_rgb(0,0,0,0.03)] hover:-translate-y-1 transition-all duration-300 cursor-default">
                        <div class="flex items-center gap-3 mb-3 relative z-10">
                            <span class="flex items-center justify-center w-8 h-8 rounded-full bg-amber-50 dark:bg-amber-500/10 text-amber-500 dark:text-amber-400 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                            </span>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Total Stok Gudang</p>
                        </div>
                        <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-slate-100 relative z-10 transition-colors">{{ inventory.stokPakan }} <span class="text-xs sm:text-sm font-semibold text-slate-400 dark:text-slate-500">Kg</span></p>
                        
                        <div class="absolute -left-2 right-2 sm:left-auto sm:-right-2 top-full mt-3 w-auto sm:min-w-[250px] bg-slate-900 dark:bg-slate-700 p-4 sm:p-5 rounded-2xl shadow-[0_20px_40px_rgb(0,0,0,0.2)] dark:shadow-none opacity-0 invisible group-hover:opacity-100 group-hover:visible translate-y-3 group-hover:translate-y-0 transition-all duration-300 z-50 border border-slate-800 dark:border-slate-600">
                            <div class="absolute -top-2 left-10 sm:left-auto sm:right-10 w-4 h-4 bg-slate-900 dark:bg-slate-700 rotate-45 border-t border-l border-slate-800 dark:border-slate-600"></div>
                            <div class="relative z-10">
                                <p class="text-[10px] uppercase tracking-widest text-slate-400 dark:text-slate-300 font-bold mb-3 border-b border-slate-800 dark:border-slate-600 pb-2">Rincian Pakan</p>
                                <div class="space-y-2.5 max-h-[150px] overflow-y-auto custom-scrollbar">
                                    <div v-for="item in inventory_list" :key="item.id" class="flex justify-between items-center text-sm">
                                        <span class="font-medium text-slate-300 dark:text-slate-200 truncate pr-2">{{ item.nama }}</span>
                                        <span class="font-bold text-white whitespace-nowrap">{{ item.stok }} <span class="text-[10px] text-slate-500 dark:text-slate-400 font-normal">Kg</span></span>
                                    </div>
                                    <div v-if="inventory_list.length === 0" class="text-xs text-slate-500 italic text-center py-2">Belum ada data</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
                    
                    <div class="lg:col-span-2 bg-white dark:bg-slate-800 p-5 sm:p-8 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.04)] flex flex-col relative z-0 transition-colors duration-300">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start mb-6 gap-4">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 transition-colors">Tren Konsumsi Pakan</h3>
                                <span class="inline-block mt-1 px-3 py-1 bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-300 text-[10px] font-bold rounded-lg uppercase tracking-wider border border-slate-200 dark:border-slate-600 transition-colors">7 Hari Terakhir</span>
                            </div>
                            <div class="flex flex-wrap gap-3 sm:justify-end">
                                <div v-for="(dataset, idx) in chartPakanConfig.datasets" :key="idx" class="flex items-center gap-2 bg-slate-50 dark:bg-slate-700/50 px-3 py-1.5 rounded-lg border border-slate-100 dark:border-slate-600 transition-colors">
                                    <span class="w-2.5 h-2.5 rounded-full shadow-sm shrink-0" :style="{ backgroundColor: dataset.borderColor }"></span>
                                    <span class="text-[11px] sm:text-xs font-bold text-slate-700 dark:text-slate-300 transition-colors">{{ dataset.label }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow relative h-64 sm:h-72 w-full">
                            <Line :data="chartPakanConfig" :options="chartPakanOptions" />
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 p-5 sm:p-8 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_10px_40px_rgb(0,0,0,0.2)] flex flex-col relative overflow-hidden z-0 transition-colors duration-300">
                        <div class="absolute -top-10 -right-10 w-40 h-40 bg-blue-50 dark:bg-white opacity-50 dark:opacity-5 rounded-full blur-2xl transition-colors duration-300"></div>
                        
                        <div class="relative z-10 flex-1 flex flex-col">
                            <h3 class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2 transition-colors duration-300">
                                <svg class="w-4 h-4 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                Analisis Sisa Stok
                            </h3>
                            
                            <div class="space-y-4 flex-1 overflow-y-auto pr-2 custom-scrollbar max-h-[350px]">
                                <div v-for="item in inventory_list" :key="item.id" class="p-4 sm:p-5 rounded-2xl border border-slate-100 dark:border-slate-700/50 bg-slate-50 dark:bg-slate-700/30 transition-all hover:bg-slate-100 dark:hover:bg-slate-700/50">
                                    <div class="flex justify-between items-start mb-3 gap-2">
                                        <div class="max-w-[65%]">
                                            <p class="text-slate-800 dark:text-slate-100 font-bold text-sm sm:text-base truncate transition-colors duration-300">{{ item.nama }}</p>
                                            <p class="text-slate-500 dark:text-slate-400 text-[10px] uppercase font-semibold mt-0.5 transition-colors duration-300">Laju: {{ item.rata_rata }} Kg/Hari</p>
                                        </div>
                                        
                                        <span :class="{
                                            'bg-emerald-50 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20': item.status === 'Aman',
                                            'bg-amber-50 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-500/20': item.status === 'Waspada',
                                            'bg-rose-50 dark:bg-red-500/20 text-rose-600 dark:text-red-400 border-rose-200 dark:border-red-500/30 animate-pulse': item.status === 'Kritis' || item.status === 'Habis'
                                        }" class="px-2 py-1 rounded-md text-[9px] sm:text-[10px] shrink-0 font-bold uppercase border transition-colors duration-300">
                                            {{ item.status }}
                                        </span>
                                    </div>
                                    
                                    <div class="flex items-baseline gap-2 mt-2">
                                        <template v-if="item.rata_rata > 0">
                                            <span class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white transition-colors duration-300">{{ item.estimasi }}</span>
                                            <span class="text-[10px] sm:text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider transition-colors duration-300">Hari lagi</span>
                                        </template>
                                        <p v-else class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400 italic mt-2 transition-colors duration-300">Belum cukup data historis.</p>
                                    </div>
                                </div>
                                
                                <div v-if="inventory_list.length === 0" class="text-center py-6 text-slate-500 dark:text-slate-400 text-sm italic transition-colors duration-300">Belum ada data pakan di gudang.</div>
                            </div>
                            
                            <div class="mt-6 sm:mt-8 pt-5 sm:pt-6 border-t border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row gap-3 transition-colors duration-300">
                                <Link href="/operasi-harian" class="flex-1 text-center bg-blue-600 dark:bg-blue-500 text-white px-4 py-3 rounded-xl shadow-lg shadow-blue-200 dark:shadow-none hover:bg-blue-700 dark:hover:bg-blue-400 font-bold transition-all text-sm">Beri Pakan</Link>
                                <Link href="/inventory" class="flex-1 text-center bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-white hover:bg-slate-200 dark:hover:bg-slate-600 px-4 py-3 rounded-xl font-bold transition-all text-sm backdrop-blur-sm">Gudang</Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Scrollbar dioptimalkan untuk Light & Dark Mode */
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; }
.custom-scrollbar:hover::-webkit-scrollbar-thumb { background: #94a3b8; }
.dark .custom-scrollbar:hover::-webkit-scrollbar-thumb { background: #64748b; }
</style>