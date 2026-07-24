<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    parameter: Object,
});

const log = props.parameter.inferensi_log;

const paramLabels = {
    suhu: { label: 'Suhu', unit: '°C' },
    ph: { label: 'pH', unit: '' },
    do_mgl: { label: 'DO', unit: 'mg/L' },
    amonia_mgl: { label: 'Amonia', unit: 'mg/L' },
    flok_ml: { label: 'Flok', unit: 'ml/L' },
};

const getParameterStatus = (key, value) => {
    const numberValue = Number(value);
    if (Number.isNaN(numberValue)) return { label: 'Tidak tersedia', severity: 'neutral' };
    switch (key) {
        case 'suhu':
            if (numberValue < 27.0) return { label: 'Rendah', severity: 'warning' };
            if (numberValue <= 32.0) return { label: 'Normal', severity: 'normal' };
            return { label: 'Tinggi', severity: 'warning' };
        case 'ph':
            if (numberValue < 5.5) return { label: 'Rendah', severity: 'danger' };
            if (numberValue <= 8.5) return { label: 'Normal', severity: 'normal' };
            return { label: 'Tinggi', severity: 'danger' };
        case 'do_mgl':
            if (numberValue < 5.0) return { label: 'Rendah', severity: 'warning' };
            return { label: 'Normal', severity: 'normal' };
        case 'amonia_mgl':
            if (numberValue < 0.01) return { label: 'Normal', severity: 'normal' };
            return { label: 'Tinggi', severity: 'warning' };
        case 'flok_ml':
            if (numberValue < 15.0) return { label: 'Rendah', severity: 'warning' };
            if (numberValue <= 40.0) return { label: 'Normal', severity: 'normal' };
            return { label: 'Tinggi', severity: 'warning' };
        default:
            return { label: 'Tidak diketahui', severity: 'neutral' };
    }
};

const statusColor = (severity) => {
    switch (severity) {
        case 'normal': return 'bg-green-100 text-green-800 border-green-300';
        case 'warning': return 'bg-yellow-100 text-yellow-800 border-yellow-300';
        case 'danger': return 'bg-red-100 text-red-800 border-red-300';
        default: return 'bg-gray-100 text-gray-700 border-gray-200';
    }
};

const severityBorder = (severity) => {
    switch (severity) {
        case 'normal': return 'border-green-400';
        case 'warning': return 'border-yellow-400';
        case 'danger': return 'border-red-400';
        default: return 'border-gray-300';
    }
};

const faktaLabelMap = {
    F01: 'Suhu Rendah (<27°C)', F02: 'Suhu Normal (27–32°C)', F03: 'Suhu Tinggi (>32°C)',
    F04: 'pH Rendah (<5.5)', F05: 'pH Normal (5.5–8.5)', F06: 'pH Tinggi (>8.5)',
    F07: 'DO Rendah (<5 mg/L)', F08: 'DO Normal (≥5 mg/L)',
    F09: 'Amonia Normal (<0.01 mg/L)', F10: 'Amonia Tinggi (≥0.01 mg/L)',
    F11: 'Flok Rendah (<15 ml/L)', F12: 'Flok Normal (15–40 ml/L)', F13: 'Flok Tinggi (>40 ml/L)',
};

const ruleLabelMap = {
    'D01': 'Gangguan Metabolisme & Imunitas Menurun (Stres Suhu Rendah)',
    'D02': 'Stres Panas & Peningkatan Kebutuhan Oksigen (Stres Suhu Tinggi)',
    'D03': 'Iritasi Kulit & Produksi Lendir Berlebih (Stres Air Asam)',
    'D04': 'Kerusakan Jaringan Insang (Stres Air Basa)',
    'D05': 'Hipoksia — Sesak Napas Akut',
    'D06': 'Keracunan Amonia Akut (Toksisitas NH3 Diperparah pH Basa)',
    'D07': 'Keracunan Amonia Kronis',
    'D08': 'Pertumbuhan Terhambat Akibat Sistem Bioflok Belum Matang',
    'D09': 'Penurunan Oksigen Akibat Respirasi Bakteri Flok Berlebih',
    'D10': 'Motile Aeromonas Septicemia (MAS) — Infeksi Bakteri Aeromonas',
    'D11': 'Stres Metabolik Akut & Risiko Kematian Mendadak',
    'D12': 'Saprolegniasis — Infeksi Jamur pada Kulit/Sirip',
    'DN': 'Kondisi Kolam Optimal — Risiko Penyakit Rendah',
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });
};
</script>

<template>
    <Head :title="`Diagnosa - ${parameter.kolam?.nama_kolam || 'Kolam'}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('parameter.index')" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <h2 class="font-bold text-xl text-slate-800 leading-tight">
                    Diagnosa Kualitas Air: {{ parameter.kolam?.nama_kolam || 'Kolam' }}
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Info Pengecekan -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Tanggal Pengecekan</p>
                        <p class="text-base font-bold text-slate-800 mt-1">{{ formatDate(parameter.tanggal_cek) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Kolam</p>
                        <p class="text-base font-bold text-indigo-600 mt-1">{{ parameter.kolam?.nama_kolam || '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Diinput Oleh</p>
                        <p class="text-base font-bold text-slate-800 mt-1">{{ parameter.user?.name || '-' }}</p>
                    </div>
                </div>

                <!-- Parameter Air -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">Data Parameter Air</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="(info, key) in paramLabels" :key="key"
                             class="rounded-2xl border-2 p-4 transition-colors"
                             :class="severityBorder(getParameterStatus(key, parameter[key]).severity)">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ info.label }}</p>
                            <p class="text-2xl font-black text-slate-800 mt-1">
                                {{ parameter[key] }}
                                <span class="text-sm font-medium text-slate-500">{{ info.unit }}</span>
                            </p>
                            <span class="mt-2 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold border"
                                  :class="statusColor(getParameterStatus(key, parameter[key]).severity)">
                                {{ getParameterStatus(key, parameter[key]).label }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Hasil Diagnosa AI -->
                <div v-if="log" class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">Hasil Diagnosa AI (Forward Chaining)</h3>

                    <div class="flex flex-wrap items-center gap-3 mb-4">
                        <template v-if="Array.isArray(log.kode_diagnosa)">
                            <span v-for="(kd, idx) in log.kode_diagnosa" :key="idx"
                                  class="px-4 py-2 text-sm font-bold rounded-full border"
                                  :class="kd === 'DN' ? 'bg-green-100 text-green-800 border-green-300' : 'bg-red-100 text-red-800 border-red-300'">
                                {{ kd }} — {{ log.label_diagnosa?.[idx] || 'Unknown' }}
                            </span>
                        </template>
                        <template v-else>
                            <span class="px-4 py-2 text-sm font-bold rounded-full border"
                                  :class="log.kode_diagnosa === 'DN' ? 'bg-green-100 text-green-800 border-green-300' : 'bg-red-100 text-red-800 border-red-300'">
                                {{ log.kode_diagnosa }} — {{ log.label_diagnosa }}
                            </span>
                        </template>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200">
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Peringatan</p>
                            <div v-if="Array.isArray(log.peringatan)">
                                <p v-for="(p, idx) in log.peringatan" :key="idx"
                                   class="text-sm text-slate-800 font-medium leading-relaxed"
                                   :class="{'mt-2': idx > 0}">
                                    {{ idx + 1 }}. {{ p }}
                                </p>
                            </div>
                            <p v-else class="text-sm text-slate-800 font-medium leading-relaxed">{{ log.peringatan }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200">
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Tindakan Mitigasi</p>
                            <div v-if="Array.isArray(log.tindakan_mitigasi)">
                                <p v-for="(t, idx) in log.tindakan_mitigasi" :key="idx"
                                   class="text-sm text-slate-800 font-medium leading-relaxed"
                                   :class="{'mt-2': idx > 0}">
                                    {{ idx + 1 }}. {{ t }}
                                </p>
                            </div>
                            <p v-else class="text-sm text-slate-800 font-medium leading-relaxed">{{ log.tindakan_mitigasi }}</p>
                        </div>
                    </div>

                    <!-- Detail Inferensi -->
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Fakta Baru (Linguistik) -->
                        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200">
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">Fakta Linguistik (Fuzzifikasi)</p>
                            <div v-if="log.fakta_baru?.length" class="space-y-1.5">
                                <div v-for="f in log.fakta_baru" :key="f"
                                     class="flex items-center gap-2 text-sm">
                                    <span class="px-2 py-0.5 bg-indigo-100 text-indigo-700 font-bold rounded text-xs border border-indigo-200">{{ f }}</span>
                                    <span class="text-slate-600">{{ faktaLabelMap[f] || 'Fakta tidak dikenal' }}</span>
                                </div>
                            </div>
                            <p v-else class="text-sm text-slate-400 italic">Tidak ada fakta linguistik yang terpicu.</p>
                        </div>

                        <!-- Rule Terpicu -->
                        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200">
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">Rule Terpicu</p>
                            <div v-if="log.rule_terpicu?.length" class="space-y-1.5">
                                <div v-for="r in log.rule_terpicu" :key="r"
                                     class="flex items-center gap-2 text-sm">
                                    <span class="px-2 py-0.5 bg-amber-100 text-amber-700 font-bold rounded text-xs border border-amber-200">{{ r }}</span>
                                    <span class="text-slate-600">{{ ruleLabelMap[r] || 'Rule tidak dikenal' }}</span>
                                </div>
                            </div>
                            <p v-else class="text-sm text-slate-400 italic">Tidak ada rule yang terpicu (kondisi normal).</p>
                        </div>
                    </div>

                    <!-- Nilai Input Mentah -->
                    <div class="mt-6 bg-slate-50 rounded-2xl p-4 border border-slate-200">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">Nilai Input (Fakta Dasar)</p>
                        <div v-if="log.fakta_input" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                            <div v-for="(val, key) in log.fakta_input" :key="key"
                                 class="bg-white rounded-xl p-3 border border-slate-100 text-center">
                                <p class="text-[10px] font-bold text-slate-400 uppercase">{{ key }}</p>
                                <p class="text-lg font-black text-slate-800">{{ val }}</p>
                            </div>
                        </div>
                        <p v-else class="text-sm text-slate-400 italic">Data input tidak tersedia.</p>
                    </div>

                    <!-- Tiket -->
                    <div v-if="log.tiket" class="mt-6 bg-red-50 rounded-2xl p-4 border border-red-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <svg class="w-6 h-6 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <div>
                                    <p class="text-sm font-bold text-red-800">Tiket Mitigasi Diterbitkan</p>
                                    <p class="text-xs text-red-600 mt-0.5">{{ log.tiket.judul }}</p>
                                </div>
                            </div>
                            <Link :href="route('tiket.show', log.tiket.id)"
                                  class="inline-flex items-center gap-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl transition-colors shadow-sm">
                                Buka Tiket
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Tidak Ada Diagnosa -->
                <div v-else class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                        <p class="text-lg font-bold text-slate-500">Data Belum Dianalisis</p>
                        <p class="text-sm text-slate-400 mt-1">Data parameter ini belum melalui proses inferensi forward chaining.</p>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
