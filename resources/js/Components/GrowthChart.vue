<script setup>
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler } from 'chart.js';

// INI BAGIAN YANG SEBELUMNYA TERLEWAT:
ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler);

const props = defineProps({
    chartBerat: {
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
                    return `${context.dataset.label}: ${context.parsed.y} Gram`;
                }
            }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: { color: '#f1f5f9' },
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
</script>

<template>
    <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] z-0 relative">
        <div class="flex flex-col md:flex-row md:justify-between md:items-start mb-6 gap-4">
            <div>
                <h3 class="text-lg font-bold text-slate-900">Kurva Pertumbuhan Ikan</h3>
                <span class="inline-block mt-1 px-3 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-bold rounded-lg uppercase tracking-wider border border-emerald-100">Berdasarkan Siklus Berjalan</span>
            </div>
            
            <div class="flex flex-wrap gap-x-4 gap-y-2 justify-end max-w-lg">
                <div v-for="(dataset, idx) in chartBerat?.datasets" :key="idx" class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                    <span class="w-2.5 h-2.5 rounded-full shadow-sm" :style="{ backgroundColor: dataset.borderColor }"></span>
                    <span class="text-xs font-bold text-slate-700">{{ dataset.label }}</span>
                </div>
            </div>
        </div>

        <div class="relative h-80 w-full">
            <Line v-if="chartBerat && chartBerat.datasets" :data="chartBerat" :options="chartOptions" />
            <div v-else class="absolute inset-0 flex items-center justify-center text-slate-400 text-sm font-medium">
                Memuat data pertumbuhan...
            </div>
        </div>
    </div>
</template>