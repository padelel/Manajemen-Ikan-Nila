<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref, watch, nextTick } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
    kolam: Object,
    dataSiklus: Array
});

const activeIndex = ref(0);
const airChartRef = ref(null);
const deathChartRef = ref(null);
let airChartInstance = null;
let deathChartInstance = null;

const renderCharts = () => {
    const currentData = props.dataSiklus[activeIndex.value];
    if (!currentData) return;

    // Hancurkan grafik sebelumnya agar tidak bertumpuk
    if (airChartInstance) airChartInstance.destroy();
    if (deathChartInstance) deathChartInstance.destroy();

    // Render Grafik Riwayat Kematian (Bar Chart)
    if (deathChartRef.value && currentData.riwayat_kematian.labels.length > 0) {
        deathChartInstance = new Chart(deathChartRef.value, {
            type: 'bar',
            data: {
                labels: currentData.riwayat_kematian.labels,
                datasets: [{
                    label: 'Kematian Ikan (Ekor)',
                    data: currentData.riwayat_kematian.data,
                    backgroundColor: '#ef4444',
                    borderRadius: 4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });
    }

    // Render Grafik Parameter Kualitas Air (Line Chart)
    if (airChartRef.value && currentData.grafik_air.labels.length > 0) {
        airChartInstance = new Chart(airChartRef.value, {
            type: 'line',
            data: {
                labels: currentData.grafik_air.labels,
                datasets: [
                    { label: 'Suhu (°C)', data: currentData.grafik_air.suhu, borderColor: '#f43f5e', tension: 0.3 },
                    { label: 'pH', data: currentData.grafik_air.ph, borderColor: '#10b981', tension: 0.3 },
                    { label: 'DO (mg/L)', data: currentData.grafik_air.do, borderColor: '#3b82f6', tension: 0.3 },
                    { label: 'Flok (ml)', data: currentData.grafik_air.flok, borderColor: '#a855f7', tension: 0.3 },
                    { label: 'Amonia (mg/L)', data: currentData.grafik_air.amonia, borderColor: '#f97316', tension: 0.3 },
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }
};

watch(activeIndex, async () => {
    await nextTick();
    renderCharts();
});

onMounted(() => {
    renderCharts();
});

const formatDate = (dateString) => {
    if (!dateString) return "-";
    return new Date(dateString).toLocaleDateString("id-ID", { year: "numeric", month: "long", day: "numeric" });
};
</script>

<template>
    <Head :title="`Siklus - ${kolam.nama_kolam}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('analisis.index')" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <h2 class="font-bold text-xl text-slate-800 leading-tight">
                    Evaluasi Panen: {{ kolam.nama_kolam }}
                </h2>
                <div class="ml-auto flex gap-2">
                    <a :href="route('analisis.export.pdf', kolam.id)" target="_blank"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded-xl transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        PDF
                    </a>
                    <a :href="route('analisis.export.excel', kolam.id)" target="_blank"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Excel
                    </a>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="dataSiklus.length === 0" class="bg-amber-50 text-amber-700 p-8 text-center rounded-3xl font-medium border border-amber-200">
                    Belum ada riwayat siklus yang tercatat pada kolam ini.
                </div>

                <div v-else class="space-y-6">
                    <!-- Navigasi Tab Siklus -->
                    <div class="flex gap-2 overflow-x-auto pb-2">
                        <button 
                            v-for="(siklus, index) in dataSiklus" 
                            :key="siklus.id"
                            @click="activeIndex = index"
                            :class="activeIndex === index ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30' : 'bg-white text-slate-500 hover:bg-slate-50 border border-slate-200'"
                            class="px-6 py-3 rounded-2xl font-bold text-sm whitespace-nowrap transition-all"
                        >
                            {{ siklus.nama_siklus }} 
                            <span v-if="siklus.status === 'berjalan'" class="ml-2 text-[10px] bg-white/20 px-2 py-0.5 rounded-full uppercase">Aktif</span>
                        </button>
                    </div>

                    <!-- Area Konten Siklus Aktif -->
                    <div class="space-y-6">
                        
                        <!-- Rincian Identitas & Hasil Panen -->
                        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Periode Siklus</p>
                                <p class="text-base font-bold text-slate-800 mt-1">{{ formatDate(dataSiklus[activeIndex].tanggal_mulai) }}</p>
                                <p class="text-sm text-slate-500">s.d. {{ dataSiklus[activeIndex].tanggal_panen ? formatDate(dataSiklus[activeIndex].tanggal_panen) : 'Belum Panen' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Durasi Siklus</p>
                                <p class="text-xl font-black text-slate-800 mt-1">{{ dataSiklus[activeIndex].durasi_hari }} <span class="text-sm font-medium text-slate-500">Hari</span></p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Sumber Benih</p>
                                <p class="text-base font-bold text-slate-800 mt-1">{{ dataSiklus[activeIndex].sumber_benih }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Survival Rate (SR)</p>
                                <p :class="dataSiklus[activeIndex].sr >= 80 ? 'text-emerald-500' : 'text-red-500'" class="text-2xl font-black mt-1">
                                    {{ dataSiklus[activeIndex].sr }}%
                                </p>
                            </div>

                            <div class="col-span-1 md:col-span-2 lg:col-span-4 border-t border-slate-100 pt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 text-center">
                                    <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Tebar Awal</p>
                                    <p class="text-2xl font-black text-slate-800">{{ dataSiklus[activeIndex].tebar_awal.toLocaleString('id-ID') }} <span class="text-sm text-slate-500">Ekor</span></p>
                                </div>
                                <div class="bg-red-50 rounded-2xl p-4 border border-red-100 text-center">
                                    <p class="text-xs font-bold text-red-500 uppercase tracking-widest mb-1">Total Kematian</p>
                                    <p class="text-2xl font-black text-red-600">{{ dataSiklus[activeIndex].total_kematian.toLocaleString('id-ID') }} <span class="text-sm text-red-400">Ekor</span></p>
                                </div>
                                <div class="bg-emerald-50 rounded-2xl p-4 border border-emerald-100 text-center">
                                    <p class="text-xs font-bold text-emerald-600 uppercase tracking-widest mb-1">Total Panen</p>
                                    <p class="text-2xl font-black text-emerald-700">{{ dataSiklus[activeIndex].jumlah_ikan_panen.toLocaleString('id-ID') }} <span class="text-sm text-emerald-500">Ekor</span></p>
                                </div>
                            </div>
                        </div>

                        <!-- Riwayat Kematian & Tiket Mitigasi -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            
                            <!-- Grafik Kematian -->
                            <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                                <h3 class="text-lg font-bold text-slate-800 mb-4">Riwayat Kematian</h3>
                                <div v-if="dataSiklus[activeIndex].riwayat_kematian.labels.length > 0" class="h-60 w-full relative">
                                    <canvas ref="deathChartRef"></canvas>
                                </div>
                                <div v-else class="h-60 flex items-center justify-center text-slate-400 font-medium bg-slate-50 rounded-2xl border border-slate-100">
                                    Aman (Tidak ada catatan kematian pada siklus ini)
                                </div>
                            </div>

                            <!-- Ringkasan Tiket -->
                            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 flex flex-col justify-center">
                                <h3 class="text-lg font-bold text-slate-800 mb-2">Ringkasan Mitigasi AI</h3>
                                <p class="text-sm text-slate-500 mb-6">Tingkat respons terhadap peringatan sistem selama siklus berlangsung.</p>
                                
                                <div class="text-center mb-4">
                                    <p class="text-5xl font-black text-slate-800">{{ dataSiklus[activeIndex].mitigasi.selesai }} <span class="text-xl text-slate-400 font-bold">/ {{ dataSiklus[activeIndex].mitigasi.total }}</span></p>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-2">Tiket Selesai</p>
                                </div>

                                <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden shadow-inner">
                                    <div class="bg-blue-500 h-3 rounded-full transition-all duration-500" :style="`width: ${dataSiklus[activeIndex].mitigasi.total > 0 ? (dataSiklus[activeIndex].mitigasi.selesai / dataSiklus[activeIndex].mitigasi.total * 100) : 0}%`"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Grafik Parameter Air -->
                        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                            <h3 class="text-lg font-bold text-slate-800 mb-6">Grafik Parameter Kualitas Air</h3>
                            <div v-if="dataSiklus[activeIndex].grafik_air.labels.length > 0" class="h-80 relative w-full">
                                <canvas ref="airChartRef"></canvas>
                            </div>
                            <div v-else class="h-80 flex items-center justify-center text-slate-400 font-medium bg-slate-50 rounded-2xl border border-slate-100">
                                Belum ada data parameter kualitas air yang tercatat pada siklus ini.
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>