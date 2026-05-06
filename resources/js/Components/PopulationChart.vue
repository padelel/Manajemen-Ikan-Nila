<script setup>
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler);

const props = defineProps({
    chartPopulasi: {
        type: Object,
        required: true
    }
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#0f172a',
            titleFont: { size: 13 },
            bodyFont: { size: 14, weight: 'bold' },
            padding: 12,
            cornerRadius: 8,
            callbacks: {
                label: function(context) {
                    return `${context.dataset.label}: ${context.parsed.y.toLocaleString('id-ID')} Ekor`;
                }
            }
        }
    },
    scales: {
        y: {
            beginAtZero: false, 
            // Gunakan RGBA dengan opacity agar grid menyatu dengan warna background terang maupun gelap
            grid: { color: 'rgba(148, 163, 184, 0.2)' },
            ticks: { 
                color: '#94a3b8', 
                font: { size: 11 },
                callback: function(value) {
                    return value.toLocaleString('id-ID') + ' Ekor';
                }
            }
        },
        x: {
            grid: { display: false },
            ticks: { color: '#94a3b8', font: { size: 11 } }
        }
    },
    elements: {
        line: { tension: 0.3 }, 
        point: { radius: 3, hoverRadius: 6 }
    },
    interaction: { mode: 'index', intersect: false }
};
</script>

<template>
    <div class="bg-white dark:bg-slate-800 p-8 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_10px_40px_rgb(0,0,0,0.2)] z-0 relative transition-colors duration-300">
        <div class="flex flex-col md:flex-row md:justify-between md:items-start mb-6 gap-4">
            <div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 transition-colors">Pergerakan Populasi Ikan</h3>
                <span class="inline-block mt-1 px-3 py-1 bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 text-[10px] font-bold rounded-lg uppercase tracking-wider border border-amber-100 dark:border-amber-500/20 transition-colors">Tren Penebaran & Kematian</span>
            </div>
            
            <div class="flex flex-wrap gap-x-4 gap-y-2 justify-end max-w-lg">
                <div v-for="(dataset, idx) in chartPopulasi?.datasets" :key="idx" class="flex items-center gap-2 bg-slate-50 dark:bg-slate-700/50 px-3 py-1.5 rounded-lg border border-slate-100 dark:border-slate-600 transition-colors">
                    <span class="w-2.5 h-2.5 rounded-full shadow-sm" :style="{ backgroundColor: dataset.borderColor }"></span>
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 transition-colors">{{ dataset.label }}</span>
                </div>
            </div>
        </div>

        <div class="relative h-80 w-full">
            <Line v-if="chartPopulasi && chartPopulasi.datasets" :data="chartPopulasi" :options="chartOptions" />
            <div v-else class="absolute inset-0 flex items-center justify-center text-slate-400 dark:text-slate-500 text-sm font-medium transition-colors">
                Memuat data populasi...
            </div>
        </div>
    </div>
</template>