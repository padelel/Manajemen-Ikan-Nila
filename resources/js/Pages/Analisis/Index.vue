<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    statistik: Object,
    performa_siklus: Array,
});

const formatDate = (dateString) => {
    if (!dateString) return "-";
    const options = { year: "numeric", month: "long", day: "numeric" };
    return new Date(dateString).toLocaleDateString("id-ID", options);
};
</script>

<template>
    <Head title="Laporan & Analisis" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl text-slate-800 leading-tight">Laporan & Analisis Kolam</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Ringkasan Statistik Global -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-100 flex flex-col justify-center">
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider">Total Panen Global</div>
                        <div class="text-3xl font-black text-emerald-600 mt-2">{{ statistik.total_panen_kg?.toLocaleString('id-ID') || 0 }} <span class="text-lg text-slate-400">Kg</span></div>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-100 flex flex-col justify-center">
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider">Kematian Global</div>
                        <div class="text-3xl font-black text-red-600 mt-2">{{ statistik.total_kematian_ekor?.toLocaleString('id-ID') || 0 }} <span class="text-lg text-slate-400">Ekor</span></div>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-100 flex flex-col justify-center">
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider">Mitigasi Diselesaikan</div>
                        <div class="text-3xl font-black text-blue-600 mt-2">{{ statistik.tiket_selesai }} <span class="text-lg text-slate-400">Kasus</span></div>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-100 flex flex-col justify-center">
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider">Mitigasi Menggantung</div>
                        <div class="text-3xl font-black text-amber-500 mt-2">{{ statistik.tiket_aktif }} <span class="text-lg text-slate-400">Kasus</span></div>
                    </div>
                </div>

                <!-- Tabel Performa Per Siklus -->
                <div class="bg-white overflow-hidden shadow-sm rounded-3xl border border-slate-100">
                    <div class="p-6 border-b border-slate-100">
                        <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-slate-800">Laporan Performa Per Siklus Budidaya</h3>
                                <p class="text-sm text-slate-500 mt-1">Evaluasi hasil panen dan performa siklus, termasuk cycle yang sudah selesai panen.</p>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-slate-50 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-slate-600">
                                Total siklus: {{ performa_siklus.length }}
                            </span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left border-collapse">
                            <thead class="text-xs text-slate-400 uppercase bg-slate-50/50 tracking-widest border-b border-slate-100">
                                <tr>
                                    <th class="px-6 py-4">Siklus & Kolam</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th class="px-6 py-4 text-center">Mulai</th>
                                    <th class="px-6 py-4 text-center">Selesai</th>
                                    <th class="px-6 py-4 text-center">SR</th>
                                    <th class="px-6 py-4 text-right">Tebar Awal</th>
                                    <th class="px-6 py-4 text-right">Kematian</th>
                                    <th class="px-6 py-4 text-right">Total Panen</th>
                                    <th class="px-6 py-4 text-center">Tiket AI</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="siklus in performa_siklus" :key="siklus.id" class="border-b border-slate-50 hover:bg-slate-50/50 transition duration-200">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-800 text-base">{{ siklus.nama_kolam }}</div>
                                        <div class="text-[11px] text-slate-500 uppercase tracking-wider mt-1">Siklus mulai {{ formatDate(siklus.tanggal_mulai) }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span :class="siklus.status_siklus === 'berjalan' ? 'bg-blue-50 text-blue-600 border border-blue-100' : 'bg-emerald-50 text-emerald-700 border border-emerald-100'" class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest shadow-sm inline-flex items-center justify-center">
                                            {{ siklus.status_siklus === 'berjalan' ? 'Berjalan' : 'Selesai' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center text-slate-600">{{ formatDate(siklus.tanggal_mulai) }}</td>
                                    <td class="px-6 py-4 text-center text-slate-600">{{ siklus.status_siklus === 'selesai' ? formatDate(siklus.tanggal_selesai) : '—' }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span :class="{
                                                'text-emerald-500': siklus.survival_rate >= 80,
                                                'text-amber-500': siklus.survival_rate >= 50 && siklus.survival_rate < 80,
                                                'text-red-500': siklus.survival_rate < 50
                                            }" class="font-black text-sm">
                                            {{ siklus.survival_rate }}%
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-slate-600">{{ siklus.tebar_awal?.toLocaleString('id-ID') || 0 }}</td>
                                    <td class="px-6 py-4 text-right font-bold text-red-500">{{ siklus.total_kematian?.toLocaleString('id-ID') || 0 }}</td>
                                    <td class="px-6 py-4 text-right font-black text-slate-800">{{ siklus.total_panen_kg?.toLocaleString('id-ID') || 0 }} <span class="text-xs text-slate-400">Kg</span></td>
                                    <td class="px-6 py-4 text-center">
                                        <div v-if="siklus.total_tiket === 0" class="text-xs font-bold text-slate-400 bg-slate-50 border border-slate-100 px-3 py-1.5 rounded-lg inline-block">Tidak Ada</div>
                                        <div v-else class="text-xs font-bold text-slate-700">{{ siklus.tiket_selesai }} / {{ siklus.total_tiket }}</div>
                                    </td>
                                </tr>
                                <tr v-if="performa_siklus.length === 0">
                                    <td colspan="9" class="px-6 py-12 text-center text-slate-400 font-medium">Belum ada data siklus budidaya yang tercatat.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>