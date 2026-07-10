<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { computed, onMounted, ref } from "vue";
import Chart from "chart.js/auto";

const props = defineProps({
 harvest: Object,
 siklus: Object,
 tebarLog: Object,
 statistik: Object,
 parameter_chart: Object,
 riwayat_panen: Array,
 mortality_logs: Array,
});

const parameterChartRef = ref(null);
const chartInstance = ref(null);

const hasParameterChart = computed(
 () =>
 props.parameter_chart &&
 Array.isArray(props.parameter_chart.labels) &&
 props.parameter_chart.labels.length > 0,
);

onMounted(() => {
 if (parameterChartRef.value && hasParameterChart.value) {
 chartInstance.value = new Chart(parameterChartRef.value, {
 type: "line",
 data: props.parameter_chart,
 options: {
 responsive: true,
 maintainAspectRatio: false,
 plugins: {
 legend: { position: "bottom", labels: { boxWidth: 12, font: { size: 10 } } },
 },
 scales: {
 y: {
 beginAtZero: false,
 grid: { color: "rgba(148,163,184,0.2)" },
 ticks: { font: { size: 10 } },
 },
 x: {
 grid: { display: false },
 ticks: { font: { size: 10 } },
 },
 },
 interaction: { mode: "index", intersect: false },
 },
 });
 }
});

const formatDate = (dateString) => {
 if (!dateString) return "—";
 return new Date(dateString).toLocaleDateString("id-ID", {
 year: "numeric",
 month: "long",
 day: "numeric",
 });
};

const formatNumber = (n) => {
 if (n === null || n === undefined) return "—";
 return Number(n).toLocaleString("id-ID");
};

const isFull = computed(() => props.harvest.jenis_panen === 'total');
const displayHarvestType = computed(() =>
 props.harvest.jenis_panen === 'total' ? 'Full' : 'Parsial',
);

const survivalRateColor = computed(() => {
 const v = props.statistik?.survival_rate_persen;
 if (v == null)
 return { text: "text-slate-400 text-slate-500", label: "" };
 if (v >= 90)
 return {
 text: "text-emerald-600",
 label: "Sangat Baik",
 };
 if (v >= 80)
 return { text: "text-blue-600", label: "Baik" };
 if (v >= 70)
 return { text: "text-amber-600", label: "Cukup" };
 return { text: "text-red-600", label: "Kurang Baik" };
});


const survivalBarWidth = computed(() => {
 const v = props.statistik?.survival_rate_persen;
 if (v == null) return 0;
 return Math.min(100, v);
});

const mortalityBarWidth = computed(() => {
 const total = props.statistik?.jumlah_tebar_awal;
 const mati = props.statistik?.total_mati;
 if (!total || mati == null) return 0;
 return Math.min(100, (mati / total) * 100);
});
</script>

<template>
 <Head title="Detail Panen" />

 <AuthenticatedLayout>
 <template #header>
 <div
 class="flex flex-col md:flex-row md:justify-between md:items-end gap-4"
 >
 <div>
 <h2
 class="text-2xl font-bold text-slate-900 tracking-tight transition-colors duration-300"
 >
 Detail Panen
 </h2>
 <p
 class="text-sm text-slate-500 mt-1 transition-colors duration-300"
 >
 Ringkasan lengkap hasil panen dan kinerja siklus
 budidaya.
 </p>
 </div>
 <Link
 :href="route('panen.index')"
 class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 text-slate-700 text-slate-300 text-sm font-bold rounded-xl hover:bg-slate-200 hover:bg-slate-600 transition-colors duration-300 flex-shrink-0"
 >
 <svg
 class="w-4 h-4"
 fill="none"
 viewBox="0 0 24 24"
 stroke="currentColor"
 stroke-width="2.5"
 >
 <path
 stroke-linecap="round"
 stroke-linejoin="round"
 d="M15 19l-7-7 7-7"
 />
 </svg>
 Kembali ke Riwayat
 </Link>
 </div>
 </template>

 <div class="py-8">
 <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
 <!-- Hero Card -->
 <div
 class="bg-white rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 transition-colors duration-300"
 >
 <div
 class="flex flex-col md:flex-row md:items-center gap-6"
 >
 <!-- Left: Icon + Identity -->
 <div class="flex items-start gap-5 flex-1 min-w-0">
 <div
 :class="
 isFull
 ? 'bg-emerald-50 border-emerald-200 border-emerald-500/20 text-emerald-600'
 : 'bg-amber-50 border-amber-200 border-amber-500/20 text-amber-600'
 "
 class="h-16 w-16 rounded-2xl flex items-center justify-center border flex-shrink-0 transition-colors duration-300"
 >
 <svg
 class="w-8 h-8"
 fill="none"
 viewBox="0 0 24 24"
 stroke="currentColor"
 stroke-width="1.8"
 >
 <path
 stroke-linecap="round"
 stroke-linejoin="round"
 d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"
 />
 </svg>
 </div>
 <div class="min-w-0">
 <span
 :class="
 isFull
 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 border-emerald-500/20'
 : 'bg-amber-50 text-amber-700 text-amber-400 border border-amber-200 border-amber-500/20'
 "
 class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest inline-block mb-2 transition-colors duration-300"
 >
 {{
 displayHarvestType.value === 'Full'
 ? "Panen Full"
 : "Panen Parsial"
 }}
 </span>
 <h3
 class="text-xl font-bold text-slate-900 transition-colors duration-300"
 >
 {{ harvest.kolam.nama_kolam }}
 </h3>
 <p
 class="text-sm text-slate-500 mt-0.5 transition-colors duration-300"
 >
 {{ harvest.kolam.lokasi }}
 </p>
 </div>
 </div>

 <!-- Right: Meta -->
 <div
 class="flex flex-col gap-2 md:items-end flex-shrink-0"
 >
 <div class="flex items-center gap-2">
 <svg
 class="w-4 h-4 text-slate-400 text-slate-500"
 fill="none"
 viewBox="0 0 24 24"
 stroke="currentColor"
 stroke-width="2"
 >
 <path
 stroke-linecap="round"
 stroke-linejoin="round"
 d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
 />
 </svg>
 <span
 class="text-sm font-bold text-slate-700 transition-colors duration-300"
 >{{
 formatDate(harvest.tanggal_panen)
 }}</span
 >
 </div>
 <p
 class="text-xs text-slate-500 transition-colors duration-300"
 >
 Dicatat oleh
 <span
 class="font-bold text-slate-700 transition-colors duration-300"
 >{{ harvest.user?.name ?? "—" }}</span
 >
 </p>
 <p
 class="text-xs text-slate-500 transition-colors duration-300"
 >
 Durasi Siklus:
 <span
 class="font-bold text-slate-700 transition-colors duration-300"
 >{{
 statistik?.durasi_hari != null
 ? statistik.durasi_hari + " hari"
 : "—"
 }}</span
 >
 </p>
 </div>
 </div>
 </div>

 <!-- KPI Cards — only for Full harvest -->
 <div
 v-if="isFull"
 class="grid grid-cols-2 lg:grid-cols-4 gap-4"
 >
 <!-- Survival Rate -->
 <div
 class="bg-white p-5 rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-colors duration-300"
 >
 <div class="flex items-start justify-between mb-1">
 <p
 class="text-xs font-black text-slate-900 uppercase tracking-wider transition-colors duration-300"
 >
 Survival Rate
 </p>
 <span
 v-if="statistik?.survival_rate_persen != null"
 :class="survivalRateColor.text"
 class="text-[10px] font-black uppercase tracking-widest text-right transition-colors duration-300"
 >{{ survivalRateColor.label }}</span
 >
 </div>
 <p
 :class="survivalRateColor.text"
 class="text-3xl font-black mt-3 tabular-nums transition-colors duration-300"
 >
 {{
 statistik?.survival_rate_persen != null
 ? Number(
 statistik.survival_rate_persen,
 ).toLocaleString("id-ID") + "%"
 : "—"
 }}
 </p>
 <p
 class="text-[10px] text-slate-500 mt-2 font-medium transition-colors duration-300"
 >
 Tingkat kelangsungan hidup
 </p>
 </div>


 <!-- Total Hasil Panen -->
 <div
 class="bg-white p-5 rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-colors duration-300"
 >
 <p
 class="text-xs font-black text-slate-900 uppercase tracking-wider mb-1 transition-colors duration-300"
 >
 Total Hasil Panen
 </p>
 <p
 class="text-3xl font-black text-slate-700 mt-3 tabular-nums transition-colors duration-300"
 >
 {{
 statistik?.total_biomassa_panen_kg != null
 ? Number(
 statistik.total_biomassa_panen_kg,
 ).toLocaleString("id-ID") + " kg"
 : "—"
 }}
 </p>
 <p
 class="text-[10px] text-slate-500 mt-2 font-medium transition-colors duration-300"
 >
 dari
 {{ formatNumber(statistik?.total_ekor_panen) }} ekor
 </p>
 </div>
 </div>

 <!-- Two-column grid -->
 <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
 <!-- Left: Ringkasan Siklus Budidaya -->
 <div
 class="bg-white rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden transition-colors duration-300"
 >
 <div
 class="px-6 py-5 border-b border-slate-100 transition-colors duration-300"
 >
 <h3
 class="text-xs font-black text-slate-900 uppercase tracking-wider transition-colors duration-300"
 >
 Ringkasan Siklus Budidaya
 </h3>
 </div>
 <div
 class="divide-y divide-slate-50 divide-slate-700/50"
 >
 <div
 class="flex items-center justify-between px-6 py-4"
 >
 <span
 class="text-sm text-slate-500 transition-colors duration-300"
 >Tanggal Mulai Siklus</span
 >
 <span
 class="text-sm font-bold text-slate-900 transition-colors duration-300"
 >{{
 formatDate(siklus?.tanggal_mulai)
 }}</span
 >
 </div>
 <div
 class="flex items-center justify-between px-6 py-4"
 >
 <span
 class="text-sm text-slate-500 transition-colors duration-300"
 >Tanggal Panen (Selesai)</span
 >
 <span
 class="text-sm font-bold text-slate-900 transition-colors duration-300"
 >{{
 formatDate(harvest.tanggal_panen)
 }}</span
 >
 </div>
 <div
 class="flex items-center justify-between px-6 py-4"
 >
 <span
 class="text-sm text-slate-500 transition-colors duration-300"
 >Durasi Siklus</span
 >
 <span
 class="text-sm font-bold text-slate-900 transition-colors duration-300"
 >
 {{
 statistik?.durasi_hari != null
 ? statistik.durasi_hari + " hari"
 : "—"
 }}
 </span>
 </div>
 <div
 class="flex items-center justify-between px-6 py-4"
 >
 <span
 class="text-sm text-slate-500 transition-colors duration-300"
 >Sumber Benih</span
 >
 <span
 class="text-sm font-bold text-slate-900 transition-colors duration-300"
 >{{ tebarLog?.sumber_benih ?? "—" }}</span
 >
 </div>
 </div>
 </div>

 <!-- Right: Perbandingan Populasi -->
 <div
 class="bg-white rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden transition-colors duration-300"
 >
 <div
 class="px-6 py-5 border-b border-slate-100 transition-colors duration-300"
 >
 <h3
 class="text-xs font-black text-slate-900 uppercase tracking-wider transition-colors duration-300"
 >
 Perbandingan Populasi
 </h3>
 </div>
 <div class="p-6 space-y-5">
 <!-- Tebar Awal -->
 <div>
 <div class="flex items-center gap-3 mb-2">
 <div
 class="h-7 w-7 rounded-lg bg-blue-50 border border-blue-100 border-blue-500/20 flex items-center justify-center text-blue-600 font-black text-sm flex-shrink-0 transition-colors duration-300"
 >
 +
 </div>
 <div class="flex-1 min-w-0">
 <div
 class="flex items-baseline justify-between gap-2"
 >
 <span
 class="text-xs font-bold text-slate-500 uppercase tracking-wider transition-colors duration-300"
 >Tebar Awal</span
 >
 <span
 class="text-sm font-black text-slate-900 tabular-nums transition-colors duration-300"
 >{{
 formatNumber(
 statistik?.jumlah_tebar_awal,
 )
 }}
 ekor</span
 >
 </div>
 </div>
 </div>
 <div
 class="h-1.5 bg-slate-100 rounded-full overflow-hidden transition-colors duration-300"
 >
 <div
 class="h-full bg-blue-500 bg-blue-400 rounded-full"
 style="width: 100%"
 ></div>
 </div>
 </div>

 <!-- Total Dipanen -->
 <div>
 <div class="flex items-center gap-3 mb-2">
 <div
 class="h-7 w-7 rounded-lg bg-emerald-50 border border-emerald-100 border-emerald-500/20 flex items-center justify-center text-emerald-600 font-black text-sm flex-shrink-0 transition-colors duration-300"
 >
 ✓
 </div>
 <div class="flex-1 min-w-0">
 <div
 class="flex items-baseline justify-between gap-2"
 >
 <span
 class="text-xs font-bold text-slate-500 uppercase tracking-wider transition-colors duration-300"
 >Total Dipanen</span
 >
 <span
 class="text-sm font-black text-slate-900 tabular-nums transition-colors duration-300"
 >{{
 formatNumber(
 statistik?.total_ekor_panen,
 )
 }}
 ekor</span
 >
 </div>
 <p
 class="text-[10px] text-slate-400 text-slate-500 mt-0.5 transition-colors duration-300"
 >
 {{
 formatNumber(
 statistik?.total_biomassa_panen_kg,
 )
 }}
 kg
 </p>
 </div>
 </div>
 <div
 class="h-1.5 bg-slate-100 rounded-full overflow-hidden transition-colors duration-300"
 >
 <div
 class="h-full bg-emerald-500 bg-emerald-400 rounded-full transition-all duration-700"
 :style="{
 width: survivalBarWidth + '%',
 }"
 ></div>
 </div>
 </div>

 <!-- Total Kematian -->
 <div>
 <div class="flex items-center gap-3 mb-2">
 <div
 class="h-7 w-7 rounded-lg bg-red-50 border border-red-100 border-red-500/20 flex items-center justify-center text-red-600 font-black text-sm flex-shrink-0 transition-colors duration-300"
 >
 ✕
 </div>
 <div class="flex-1 min-w-0">
 <div
 class="flex items-baseline justify-between gap-2"
 >
 <span
 class="text-xs font-bold text-slate-500 uppercase tracking-wider transition-colors duration-300"
 >Total Kematian</span
 >
 <span
 class="text-sm font-black text-slate-900 tabular-nums transition-colors duration-300"
 >{{
 formatNumber(
 statistik?.total_mati,
 )
 }}
 ekor</span
 >
 </div>
 </div>
 </div>
 <div
 class="h-1.5 bg-slate-100 rounded-full overflow-hidden transition-colors duration-300"
 >
 <div
 class="h-full bg-red-500 bg-red-400 rounded-full transition-all duration-700"
 :style="{
 width: mortalityBarWidth + '%',
 }"
 ></div>
 </div>
 </div>

 <!-- Divider + Survival Rate summary -->
 <div
 class="pt-4 border-t border-slate-100 transition-colors duration-300"
 >
 <div class="flex items-center justify-between">
 <div>
 <p
 class="text-[10px] font-bold text-slate-400 text-slate-500 uppercase tracking-wider transition-colors duration-300"
 >
 Total Tebar Awal
 </p>
 <p
 class="text-sm font-black text-slate-900 mt-0.5 tabular-nums transition-colors duration-300"
 >
 {{
 statistik?.jumlah_tebar_awal != null
 ? Number(
 statistik.jumlah_tebar_awal,
 ).toLocaleString("id-ID") + " ekor"
 : "—"
 }}
 </p>
 </div>
 <div class="text-right">
 <p
 class="text-[10px] font-bold text-slate-400 text-slate-500 uppercase tracking-wider transition-colors duration-300"
 >
 Survival Rate
 </p>
 <p
 class="text-sm font-black mt-0.5 tabular-nums text-emerald-600 transition-colors duration-300"
 >
 {{
 statistik?.survival_rate_persen != null
 ? Number(
 statistik.survival_rate_persen,
 ).toLocaleString("id-ID") + "%"
 : "—"
 }}
 </p>
 </div>
 </div>
 </div>
 </div>
 </div>
 </div>

 <!-- Riwayat Panen dalam Siklus Ini -->
 <div
 class="bg-white overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-3xl border border-slate-100 transition-colors duration-300"
 >
 <div
 class="px-6 py-5 border-b border-slate-100 transition-colors duration-300"
 >
 <h3
 class="text-xs font-black text-slate-900 uppercase tracking-wider transition-colors duration-300"
 >
 Riwayat Panen dalam Siklus Ini
 </h3>
 </div>
 <div class="overflow-x-auto">
 <table class="w-full text-left border-collapse">
 <thead
 class="bg-slate-50/50 border-b border-slate-100 transition-colors duration-300"
 >
 <tr>
 <th
 class="px-6 py-4 text-[10px] font-bold text-slate-400 text-slate-300 uppercase tracking-widest transition-colors"
 >
 Tanggal
 </th>
 <th
 class="px-6 py-4 text-[10px] font-bold text-slate-400 text-slate-300 uppercase tracking-widest transition-colors"
 >
 Jenis
 </th>
 <th
 class="px-6 py-4 text-[10px] font-bold text-slate-400 text-slate-300 uppercase tracking-widest text-right transition-colors"
 >
 Jumlah (Ekor)
 </th>
 <th
 class="px-6 py-4 text-[10px] font-bold text-slate-400 text-slate-300 uppercase tracking-widest text-right transition-colors"
 >
 Berat Total (Kg)
 </th>
 <th
 class="px-6 py-4 text-[10px] font-bold text-slate-400 text-slate-300 uppercase tracking-widest text-right transition-colors"
 >
 Berat Rata-rata (g/ekor)
 </th>
 <th
 class="px-6 py-4 text-[10px] font-bold text-slate-400 text-slate-300 uppercase tracking-widest transition-colors"
 >
 Catatan
 </th>
 <th
 class="px-6 py-4 text-[10px] font-bold text-slate-400 text-slate-300 uppercase tracking-widest transition-colors"
 >
 Pencatat
 </th>
 </tr>
 </thead>
 <tbody class="text-sm">
 <tr
 v-for="p in riwayat_panen"
 :key="p.id"
 class="border-b border-slate-50 border-slate-200 transition duration-200"
 :class="
 p.id === harvest.id
 ? 'bg-emerald-50/60 bg-emerald-500/5'
 : 'hover:bg-slate-50'
 "
 >
 <td
 class="px-6 py-4 font-medium text-slate-500 whitespace-nowrap transition-colors"
 >
 <div class="flex items-center gap-2">
 {{ formatDate(p.tanggal_panen) }}
 <span
 v-if="p.id === harvest.id"
 class="px-1.5 py-0.5 bg-emerald-100 bg-emerald-500/20 text-emerald-700 text-[9px] font-black uppercase tracking-widest rounded-md border border-emerald-200 border-emerald-500/30 transition-colors duration-300"
 >ini</span
 >
 </div>
 </td>
 <td class="px-6 py-4">
 <span
 :class="
 p.jenis_panen === 'total'
 ? 'bg-emerald-50 text-emerald-700 border-emerald-200 border-emerald-500/20'
 : 'bg-amber-50 text-amber-700 text-amber-400 border-amber-200 border-amber-500/20'
 "
 class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border inline-block transition-colors duration-300"
 >{{ p.jenis_panen === 'total' ? 'Full' : 'Parsial' }}</span>
 </td>
 <td
 class="px-6 py-4 text-right font-bold text-slate-700 tabular-nums transition-colors"
 >
 {{ formatNumber(p.jumlah_ikan_panen) }}
 </td>
 <td
 class="px-6 py-4 text-right font-bold text-emerald-600 tabular-nums transition-colors"
 >
 {{ formatNumber(p.berat_total_kg) }}
 </td>
 <td
 class="px-6 py-4 text-right font-medium text-slate-600 text-slate-300 tabular-nums transition-colors"
 >
 {{
 p.jumlah_ikan_panen > 0 &&
 p.berat_total_kg
 ? Number(
 (p.berat_total_kg /
 p.jumlah_ikan_panen) *
 1000,
 ).toLocaleString("id-ID", {
 maximumFractionDigits: 0,
 })
 : "—"
 }}
 </td>
 <td
 class="px-6 py-4 text-slate-500 max-w-[180px] truncate transition-colors"
 >
 {{ p.catatan || "—" }}
 </td>
 <td
 class="px-6 py-4 font-medium text-slate-700 whitespace-nowrap transition-colors"
 >
 {{ p.user?.name ?? "—" }}
 </td>
 </tr>
 <tr
 v-if="
 !riwayat_panen ||
 riwayat_panen.length === 0
 "
 >
 <td
 colspan="7"
 class="px-6 py-12 text-center text-sm text-slate-400 text-slate-500 transition-colors duration-300"
 >
 Tidak ada data riwayat panen.
 </td>
 </tr>
 </tbody>
 </table>
 </div>
 </div>

 <!-- Riwayat Kematian dalam Siklus Ini -->
 <div
 class="bg-white overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-3xl border border-slate-100 transition-colors duration-300"
 >
 <div
 class="px-6 py-5 border-b border-slate-100 transition-colors duration-300"
 >
 <div class="flex items-center justify-between">
 <h3
 class="text-xs font-black text-slate-900 uppercase tracking-wider transition-colors duration-300"
 >
 Riwayat Kematian dalam Siklus Ini
 </h3>
 <span
 class="text-xs text-slate-500 transition-colors duration-300"
 >
 Total mati:
 <span
 class="font-bold text-red-600 transition-colors duration-300"
 >{{
 formatNumber(statistik?.total_mati)
 }}
 ekor</span
 >
 </span>
 </div>
 </div>
 <div class="overflow-x-auto">
 <table class="w-full text-left border-collapse">
 <thead
 class="bg-slate-50/50 border-b border-slate-100 transition-colors duration-300"
 >
 <tr>
 <th
 class="px-6 py-4 text-[10px] font-bold text-slate-400 text-slate-300 uppercase tracking-widest transition-colors"
 >
 Tanggal
 </th>
 <th
 class="px-6 py-4 text-[10px] font-bold text-slate-400 text-slate-300 uppercase tracking-widest text-right transition-colors"
 >
 Jumlah Mati
 </th>
 <th
 class="px-6 py-4 text-[10px] font-bold text-slate-400 text-slate-300 uppercase tracking-widest transition-colors"
 >
 Catatan / Penyebab
 </th>
 <th
 class="px-6 py-4 text-[10px] font-bold text-slate-400 text-slate-300 uppercase tracking-widest transition-colors"
 >
 Dilaporkan Oleh
 </th>
 </tr>
 </thead>
 <tbody class="text-sm">
 <tr
 v-for="log in mortality_logs"
 :key="log.id"
 class="border-b border-slate-50 border-slate-200 hover:bg-slate-50 transition duration-200"
 >
 <td
 class="px-6 py-4 font-medium text-slate-500 whitespace-nowrap transition-colors"
 >
 {{ formatDate(log.tanggal_kematian) }}
 </td>
 <td
 class="px-6 py-4 text-right font-bold text-red-600 tabular-nums transition-colors"
 >
 {{ formatNumber(log.jumlah_mati) }}
 </td>
 <td
 class="px-6 py-4 text-slate-500 transition-colors"
 >
 {{ log.catatan || "—" }}
 </td>
 <td
 class="px-6 py-4 font-medium text-slate-700 whitespace-nowrap transition-colors"
 >
 {{ log.user?.name ?? "—" }}
 </td>
 </tr>
 <tr
 v-if="
 !mortality_logs ||
 mortality_logs.length === 0
 "
 >
 <td
 colspan="4"
 class="px-6 py-12 text-center text-sm text-slate-400 text-slate-500 transition-colors duration-300"
 >
 Tidak ada data kematian tercatat dalam
 siklus ini.
 </td>
 </tr>
 </tbody>
 </table>
 </div>
 </div>

 <!-- Tiket Mitigasi Summary -->
 <div
 class="bg-white rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 transition-colors duration-300"
 >
 <div class="flex items-center justify-between mb-4">
 <h3
 class="text-xs font-black text-slate-900 uppercase tracking-wider transition-colors duration-300"
 >
 Ringkasan Tiket Mitigasi
 </h3>
 <span
 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider"
 >
 {{ statistik?.tiket_selesai || 0 }}/{{ statistik?.total_tiket || 0 }} selesai
 </span>
 </div>
 <div class="grid grid-cols-2 gap-4">
 <div class="rounded-3xl bg-slate-50 p-4 transition-colors duration-300">
 <p class="text-xs font-black text-slate-500 uppercase tracking-wider mb-2">
 Total Tiket
 </p>
 <p class="text-3xl font-black text-slate-900">
 {{ formatNumber(statistik?.total_tiket) }}
 </p>
 </div>
 <div class="rounded-3xl bg-slate-50 p-4 transition-colors duration-300">
 <p class="text-xs font-black text-slate-500 uppercase tracking-wider mb-2">
 Tiket Selesai
 </p>
 <p class="text-3xl font-black text-slate-900">
 {{ formatNumber(statistik?.tiket_selesai) }}
 </p>
 </div>
 </div>
 </div>

 <!-- Grafik Parameter Air -->
 <div
 class="bg-white rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 transition-colors duration-300"
 >
 <div class="flex items-center justify-between mb-4">
 <h3
 class="text-xs font-black text-slate-900 uppercase tracking-wider transition-colors duration-300"
 >
 Grafik Parameter Air
 </h3>
 <p class="text-xs text-slate-500">
 Dari awal siklus sampai panen
 </p>
 </div>
 <div class="h-[340px]">
 <canvas ref="parameterChartRef" class="w-full h-full"></canvas>
 </div>
 <p
 v-if="!hasParameterChart"
 class="text-sm text-slate-500 mt-4"
 >
 Tidak ada data parameter harian untuk siklus ini.
 </p>
 </div>

 <!-- Catatan Panen — only if catatan is not empty -->
 <div
 v-if="harvest.catatan"
 class="bg-white rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 transition-colors duration-300"
 >
 <h3
 class="text-xs font-black text-slate-900 uppercase tracking-wider mb-4 transition-colors duration-300"
 >
 Catatan Panen
 </h3>
 <p
 class="text-sm text-slate-600 text-slate-300 leading-relaxed transition-colors duration-300"
 >
 {{ harvest.catatan }}
 </p>
 </div>
 </div>
 </div>
 </AuthenticatedLayout>
</template>
