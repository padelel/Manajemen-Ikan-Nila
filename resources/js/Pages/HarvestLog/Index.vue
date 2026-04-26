<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ logs: Array });

const getRoleInitial = (userId) => {
    if (userId === 1) return 'PU';
    if (userId === 2) return 'OL';
    return 'S';
};

const getRoleName = (userId) => {
    if (userId === 1) return 'Pengelola Utama';
    if (userId === 2) return 'Operator Lapangan';
    return 'Sistem Otomatis';
};
</script>

<template>
    <Head title="Data Panen" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-end">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Riwayat Panen</h2>
                    <p class="text-sm text-slate-500 mt-1">Data panen parsial dan panen full dari seluruh kolam.</p>
                </div>
                <div class="flex gap-3">
                    <Link href="/panen/create" class="bg-emerald-600 text-white px-5 py-2.5 rounded-xl shadow-lg shadow-emerald-200/50 hover:bg-emerald-700 text-sm font-semibold transition-all">
                        + Catat Panen Baru
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:rounded-3xl border border-slate-100">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50/50 border-b border-slate-100">
                                <tr>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tanggal</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Kolam & Jenis</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Populasi Dipanen</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Berat Total</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pelapor</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <tr v-for="log in logs" :key="log.id" class="border-b border-slate-50 hover:bg-emerald-50/30 transition duration-200">
                                    <td class="px-6 py-5 font-medium text-slate-500">{{ log.tanggal_panen }}</td>
                                    
                                    <td class="px-6 py-5">
                                        <p class="font-bold text-slate-900 text-base">{{ log.kolam?.nama_kolam }}</p>
                                        <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border"
                                            :class="log.jenis_panen === 'Full' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-blue-50 text-blue-700 border-blue-200'">
                                            Panen {{ log.jenis_panen }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-5 text-center">
                                        <span class="font-bold text-slate-900 text-base">{{ log.jumlah_ikan.toLocaleString('id-ID') }}</span>
                                        <span class="text-xs text-slate-400 ml-1">Ekor</span>
                                    </td>

                                    <td class="px-6 py-5 text-center">
                                        <span class="inline-flex items-center justify-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100 font-bold">
                                            <span class="text-base">{{ log.berat_total_kg }}</span>
                                            <span class="text-xs font-medium opacity-70">Kg</span>
                                        </span>
                                    </td>

                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="rounded-full w-9 h-9 flex items-center justify-center font-bold text-xs uppercase shadow-sm border"
                                                 :class="{
                                                     'bg-indigo-50 text-indigo-700 border-indigo-100': log.user_id === 1,
                                                     'bg-teal-50 text-teal-700 border-teal-100': log.user_id === 2,
                                                     'bg-slate-100 text-slate-600 border-slate-200': !log.user_id || log.user_id > 2
                                                 }">
                                                {{ getRoleInitial(log.user_id) }}
                                            </div>
                                            
                                            <div class="flex flex-col">
                                                <span class="text-xs font-bold uppercase tracking-wider" 
                                                      :class="log.user_id === 1 ? 'text-indigo-600' : (log.user_id === 2 ? 'text-teal-600' : 'text-slate-500')">
                                                    {{ getRoleName(log.user_id) }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="logs.length === 0">
                                    <td colspan="5" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-slate-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                                            </svg>
                                            <p class="text-slate-500 font-medium text-sm">Belum ada riwayat panen ikan.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>