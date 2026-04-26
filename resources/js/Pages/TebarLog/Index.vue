<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ logs: Array });

const getRoleName = (userId) => {
    if (userId === 1) return 'Pengelola Utama';
    if (userId === 2) return 'Operator Lapangan';
    return 'Sistem Otomatis';
};

const getRoleInitial = (userId) => {
    if (userId === 1) return 'PU';
    if (userId === 2) return 'OL';
    return 'S';
};
</script>

<template>
    <Head title="Riwayat Tebar Benih" />
    
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-end">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Tebar Benih</h2>
                    <p class="text-sm text-slate-500 mt-1">Catatan pemasukan benih baru ke dalam kolam.</p>
                </div>
                
                <Link href="/tebar/create" class="bg-indigo-600 text-white px-5 py-2.5 rounded-xl shadow-lg shadow-indigo-200/50 hover:bg-indigo-700 text-sm font-semibold transition-all">
                    + Tebar Benih Baru
                </Link>
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
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Kolam</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Jumlah Benih</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Sumber & Ukuran</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pelapor</th>
                                </tr>
                            </thead>
                            
                            <tbody class="text-sm">
                                <tr v-for="log in logs" :key="log.id" class="border-b border-slate-50 hover:bg-indigo-50/30 transition duration-200">
                                    <td class="px-6 py-5 font-medium text-slate-500">{{ log.tanggal_tebar }}</td>
                                    
                                    <td class="px-6 py-5">
                                        <p class="font-bold text-slate-900 text-base">{{ log.kolam?.nama_kolam }}</p>
                                    </td>

                                    <td class="px-6 py-5 text-center">
                                        <span class="inline-flex items-center justify-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100 font-bold">
                                            <span class="text-base">{{ log.jumlah_ikan.toLocaleString('id-ID') }}</span>
                                            <span class="text-xs font-medium opacity-70">Ekor</span>
                                        </span>
                                    </td>

                                    <td class="px-6 py-5">
                                        <p class="text-slate-700 font-bold">{{ log.sumber_benih || 'Sumber Lokal' }}</p>
                                        <p class="text-xs text-slate-400 font-medium uppercase mt-0.5">Rata-rata: {{ log.berat_rata_gram }} gram</p>
                                    </td>

                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="rounded-full w-9 h-9 flex items-center justify-center font-bold text-xs uppercase border"
                                                 :class="log.user_id === 1 ? 'bg-indigo-50 text-indigo-700 border-indigo-100' : 'bg-teal-50 text-teal-700 border-teal-100'">
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
                                                <path d="M12 4.5v15m7.5-7.5h-15" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <p class="text-slate-500 font-medium text-sm">Belum ada riwayat tebar benih.</p>
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