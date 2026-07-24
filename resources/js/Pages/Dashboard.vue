<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
    grafikPerKolam: Array,
    ringkasan: Object,
    isOperator: Boolean,
    assignedKolamCount: Number,
    assignedKolamNote: String,
});

const airChartRefs = [];
const popChartRefs = [];

onMounted(() => {
    props.grafikPerKolam.forEach((item, index) => {
        // Kualitas Air chart
        if (airChartRefs[index] && item.kualitasAir) {
            new Chart(airChartRefs[index], {
                type: 'line',
                data: {
                    labels: item.kualitasAir.labels,
                    datasets: [
                    {
                        label: 'Suhu (°C)',
                        data: item.kualitasAir.suhu,
                        borderColor: '#f43f5e',
                        backgroundColor: '#f43f5e',
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 3,
                    },
                    {
                        label: 'pH',
                        data: item.kualitasAir.ph,
                        borderColor: '#10b981',
                        backgroundColor: '#10b981',
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 3,
                    },
                    {
                        label: 'DO (mg/L)',
                        data: item.kualitasAir.do_mgl,
                        borderColor: '#3b82f6',
                        backgroundColor: '#3b82f6',
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 3,
                    },
                    {
                        label: 'Amonia (mg/L)',
                        data: item.kualitasAir.amonia_mgl,
                        borderColor: '#f97316',
                        backgroundColor: '#f97316',
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 3,
                    },
                    {
                        label: 'Flok (ml)',
                        data: item.kualitasAir.flok_ml,
                        borderColor: '#a855f7',
                        backgroundColor: '#a855f7',
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 3,
                    },
                ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } },
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            grid: { color: 'rgba(148,163,184,0.2)' },
                            ticks: { font: { size: 10 } },
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 10 } },
                        },
                    },
                    interaction: { mode: 'index', intersect: false },
                },
            });
        }

        // Populasi chart
        if (popChartRefs[index] && item.populasi) {
            new Chart(popChartRefs[index], {
                type: 'line',
                data: {
                    labels: item.populasi.labels,
                    datasets: [
                        {
                            label: 'Populasi Ikan (Ekor)',
                            data: item.populasi.data,
                            borderColor: '#8b5cf6',
                            backgroundColor: 'rgba(139, 92, 246, 0.1)',
                            fill: true,
                            stepped: true,
                            borderWidth: 2,
                            pointRadius: 3,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } },
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            grid: { color: 'rgba(148,163,184,0.2)' },
                            ticks: {
                                font: { size: 10 },
                                callback: (v) => v.toLocaleString('id-ID'),
                            },
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 10 } },
                        },
                    },
                },
            });
        }
    });
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <h2 class="font-bold text-xl text-slate-800 leading-tight">Dashboard Utama</h2>
                <div class="flex flex-wrap gap-3">
                    <span class="inline-flex items-center gap-2 bg-white rounded-xl px-4 py-2 shadow-sm text-sm font-semibold text-slate-700">
                        🌊 Total Kolam: {{ ringkasan?.totalKolam ?? 0 }}
                    </span>
                    <span class="inline-flex items-center gap-2 bg-white rounded-xl px-4 py-2 shadow-sm text-sm font-semibold text-slate-700">
                        🐟 Siklus Aktif: {{ ringkasan?.siklusAktif ?? 0 }}
                    </span>
                    <span class="inline-flex items-center gap-2 bg-white rounded-xl px-4 py-2 shadow-sm text-sm font-semibold text-slate-700">
                        🚨 Tiket Aktif: {{ ringkasan?.tiketAktif ?? 0 }}
                    </span>
                </div>
            </div>
        </template>

        <div class="py-6 px-4 sm:px-6 lg:px-8">
            <div v-if="isOperator" class="mb-6 rounded-2xl border border-slate-200 bg-slate-50 p-6 text-slate-800 shadow-sm">
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="font-semibold text-base">Penugasan Kolam Operator</h3>
                        <p class="text-sm text-slate-600 mt-1">
                            {{ assignedKolamNote }}
                        </p>
                    </div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-white border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                        {{ assignedKolamCount }} kolam ditugaskan
                    </div>
                </div>
            </div>
            <div
                v-for="(item, index) in grafikPerKolam"
                :key="item.kolam.id"
                class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6"
            >
                <!-- Kolam Header -->
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <h3 class="text-base font-bold text-slateate-800">{{ item.kolam.nama_kolam }}</h3>
                        <span class="text-sm text-gray-400">{{ item.kolam.lokasi }}</span>
                    </div>
                    <div v-if="item.siklus" class="text-sm text-slate-500">
                        Populasi: <span class="font-semibold text-slate-700">{{ item.siklus.populasi_terkini?.toLocaleString('id-ID') }} ekor</span>
                        &nbsp;|&nbsp; Tebar: <span class="font-semibold text-slate-700">{{ item.siklus.jumlah_tebar_awal?.toLocaleString('id-ID') }} ekor</span>
                        &nbsp;|&nbsp; Sejak: <span class="font-semibold text-slate-700">{{ item.siklus.tanggal_mulai }}</span>
                    </div>
                    <div v-else class="text-sm text-gray-400 italic">Tidak ada siklus berjalan</div>
                </div>

                <!-- Chart Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <!-- Kualitas Air -->
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Kualitas Air (7 Hari Terakhir)</p>
                        <div class="relative h-56">
                            <canvas :ref="(el) => { if (el) airChartRefs[index] = el }" class="w-full" style="height: 220px;"></canvas>
                        </div>
                    </div>

                    <!-- Populasi -->
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Pergerakan Populasi (Sejak Penebaran)</p>
                        <div v-if="item.populasi" class="relative h-56">
                            <canvas :ref="(el) => { if (el) popChartRefs[index] = el }" class="w-full" style="height: 220px;"></canvas>
                        </div>
                        <div v-else class="h-56 flex items-center justify-center text-gray-400 text-sm bg-gray-50 rounded-xl">
                            Tidak ada siklus aktif
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="!grafikPerKolam || grafikPerKolam.length === 0" class="bg-amber-50 border border-amber-200 text-amber-700 p-6 rounded-2xl text-center font-medium">
                Belum ada data kolam yang tersedia.
            </div>
        </div>
    </AuthenticatedLayout>
</template>
