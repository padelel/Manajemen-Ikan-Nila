<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ dataSiklus: Array });

const formatDate = (dateString) => {
    if (!dateString) return "-";
    return new Date(dateString).toLocaleDateString("id-ID", { year: "numeric", month: "short", day: "numeric" });
};
</script>

<template>
    <Head title="Manajemen Panen" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl text-slate-800 leading-tight">Manajemen Panen Total</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="bg-white overflow-hidden shadow-sm rounded-3xl border border-slate-100">
                    <div class="p-6 border-b border-slate-100">
                        <h3 class="text-lg font-bold text-slate-800">Siklus Aktif</h3>
                        <p class="text-sm text-slate-500 mt-1">Pilih siklus yang sedang berjalan untuk melakukan pencatatan panen total dan menutup siklus. Riwayat siklus yang sudah selesai dapat dilihat di halaman <Link :href="route('analisis.index')" class="text-blue-600 hover:underline font-semibold">Laporan & Analisis</Link>.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left border-collapse">
                            <thead class="text-xs text-slate-400 uppercase bg-slate-50/50 tracking-widest border-b border-slate-100">
                                <tr>
                                    <th class="px-6 py-4">Kolam & Mulai</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th class="px-6 py-4 text-right">Tebar Awal</th>
                                    <th class="px-6 py-4 text-right">Hasil Panen</th>
                                    <th class="px-6 py-4 text-right">SR</th>
                                    <th class="px-6 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="siklus in dataSiklus" :key="siklus.id" class="border-b border-slate-50 hover:bg-slate-50/50 transition duration-200">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-800 text-base">{{ siklus.nama_kolam }}</div>
                                        <div class="text-[11px] text-slate-500 uppercase tracking-wider mt-1">{{ formatDate(siklus.tanggal_mulai) }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span :class="siklus.status_aktif === 'berjalan' ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-emerald-50 text-emerald-700 border-emerald-100'" class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest shadow-sm border inline-block">
                                            {{ siklus.status_aktif }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-slate-600">
                                        {{ siklus.tebar_awal.toLocaleString('id-ID') }} <span class="text-xs font-normal text-slate-400">Ekor</span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-slate-800">
                                        {{ siklus.status_aktif === 'selesai' ? siklus.jumlah_ikan_panen?.toLocaleString('id-ID') + ' ekor' : '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span v-if="siklus.survival_rate" 
                                            class="font-black text-lg"
                                            :class="siklus.survival_rate >= 80 ? 'text-emerald-600' : siklus.survival_rate >= 60 ? 'text-amber-500' : 'text-rose-600'">
                                            {{ siklus.survival_rate }}%
                                        </span>
                                        <span v-else class="text-slate-300">—</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <Link v-if="siklus.status_aktif === 'berjalan'" :href="route('panen.create', siklus.id)" class="inline-flex items-center justify-center whitespace-nowrap px-4 py-2 bg-emerald-500 text-white hover:bg-emerald-600 font-bold text-xs rounded-xl transition-colors shadow-sm shadow-emerald-500/30">
                                            Panen Total
                                        </Link>
                                        
                                        <Link v-else :href="route('analisis.show', siklus.kolam_id)" class="inline-flex items-center justify-center whitespace-nowrap px-4 py-2 bg-slate-100 text-slate-600 hover:bg-slate-200 font-bold text-xs rounded-xl transition-colors border border-slate-200">
                                            Lihat Analisis
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="dataSiklus.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-400 font-medium">Belum ada siklus yang tercatat.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>