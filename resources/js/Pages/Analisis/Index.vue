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
                        <h3 class="text-lg font-bold text-slate-800 mb-6">Laporan Performa Per Siklus Budidaya</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left border-collapse">
                                <thead class="text-xs text-slate-400 uppercase bg-slate-50/50 tracking-widest border-b border-slate-100">
                                    <tr>
                                        <th class="px-6 py-4">Siklus & Kolam</th>
                                        <th class="px-6 py-4 text-center">Status</th>
                                        <th class="px-6 py-4 text-right">Tebar Awal</th>
                                        <th class="px-6 py-4 text-right">Kematian</th>
                                        <th class="px-6 py-4 text-center">Survival Rate (SR)</th>
                                        <th class="px-6 py-4 text-center">Tindakan Mitigasi AI</th>
                                        <th class="px-6 py-4 text-right">Total Panen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="siklus in performa_siklus" :key="siklus.id" class="border-b border-slate-50 hover:bg-slate-50/50 transition duration-200">
                                        
                                        <!-- Kolam & Tanggal -->
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-slate-800 text-base">{{ siklus.nama_kolam }}</div>
                                            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">Mulai: {{ formatDate(siklus.tanggal_mulai) }}</div>
                                        </td>
                                        
                                        <!-- Status -->
                                        <td class="px-6 py-4 text-center">
                                            <span :class="siklus.status_siklus === 'berjalan' ? 'bg-blue-50 text-blue-600 border border-blue-100' : 'bg-slate-100 text-slate-600 border border-slate-200'" class="px-3 py-1.5 rounded-lg text-xs font-black uppercase tracking-widest shadow-sm">
                                                {{ siklus.status_siklus }}
                                            </span>
                                        </td>
                                        
                                        <!-- Tebar Awal -->
                                        <td class="px-6 py-4 text-right font-bold text-slate-600">
                                            {{ siklus.tebar_awal?.toLocaleString('id-ID') || 0 }}
                                        </td>
                                        
                                        <!-- Kematian -->
                                        <td class="px-6 py-4 text-right font-bold text-red-500">
                                            {{ siklus.total_kematian?.toLocaleString('id-ID') || 0 }}
                                        </td>
                                        
                                        <!-- Survival Rate (SR) -->
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex flex-col items-center">
                                                <span class="font-black text-xl" 
                                                    :class="{
                                                        'text-emerald-500': siklus.survival_rate >= 80,
                                                        'text-amber-500': siklus.survival_rate >= 50 && siklus.survival_rate < 80,
                                                        'text-red-500': siklus.survival_rate < 50
                                                    }">
                                                    {{ siklus.survival_rate }}%
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Resolusi Mitigasi AI -->
                                        <td class="px-6 py-4 text-center">
                                            <div v-if="siklus.total_tiket === 0" class="text-xs font-bold text-slate-400 bg-slate-50 border border-slate-100 px-3 py-1.5 rounded-lg inline-block">
                                                Tidak Ada Kasus
                                            </div>
                                            <div v-else class="flex flex-col items-center gap-1.5">
                                                <span class="font-bold text-slate-700 text-xs">
                                                    {{ siklus.tiket_selesai }} / {{ siklus.total_tiket }} Selesai
                                                </span>
                                                <!-- Progress Bar Mini -->
                                                <div class="w-24 h-2 bg-slate-100 rounded-full overflow-hidden shadow-inner">
                                                    <div class="h-full bg-blue-500 rounded-full transition-all duration-500" :style="`width: ${(siklus.tiket_selesai / siklus.total_tiket) * 100}%`"></div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Total Panen -->
                                        <td class="px-6 py-4 text-right">
                                            <span class="font-black text-emerald-600 text-xl">
                                                {{ siklus.total_panen_kg?.toLocaleString('id-ID') || 0 }}
                                            </span>
                                            <span class="text-xs font-bold text-slate-400 ml-1">Kg</span>
                                        </td>
                                        
                                    </tr>
                                    <tr v-if="performa_siklus.length === 0">
                                        <td colspan="7" class="px-6 py-12 text-center text-slate-400 font-medium">Belum ada data siklus budidaya yang tercatat.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>