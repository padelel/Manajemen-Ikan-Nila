<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ logs: Array });

// Fungsi untuk menentukan Jabatan/Role berdasarkan user_id (Sama seperti halaman lain)
const getRoleName = (userId) => {
    if (userId === 1) return 'Pengelola Utama';
    if (userId === 2) return 'Operator Lapangan';
    return 'Sistem Otomatis';
};

// Fungsi untuk menentukan Inisial Avatar
const getRoleInitial = (userId) => {
    if (userId === 1) return 'PU';
    if (userId === 2) return 'OL';
    return 'S';
};
</script>

<template>
    <Head title="Data Kematian Ikan" />
    
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-end">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Riwayat Mortalitas</h2>
                    <p class="text-sm text-slate-500 mt-1">Catatan harian kematian ikan dan indikasi penyebabnya.</p>
                </div>
                
                <div class="flex gap-3">
                    <a href="/laporan/cetak" target="_blank" class="flex items-center gap-2 bg-white border border-slate-200 text-slate-700 px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-50 hover:text-slate-900 text-sm font-semibold transition-all">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak Laporan
                    </a>
                    
                    <Link href="/kematian/create" class="bg-rose-600 text-white px-5 py-2.5 rounded-xl shadow-lg shadow-rose-200/50 hover:bg-rose-700 text-sm font-semibold transition-all">
                        + Lapor Kematian
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
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-1/6">Tanggal</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-1/6">Kolam</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center w-1/6">Jumlah Mati</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-1/3">Catatan / Penyebab</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-1/6">Pelapor</th>
                                </tr>
                            </thead>
                            
                            <tbody class="text-sm">
                                <tr v-for="log in logs" :key="log.id" class="border-b border-slate-50 hover:bg-rose-50/30 transition duration-200 group">
                                    
                                    <td class="px-6 py-5">
                                        <p class="font-medium text-slate-500">{{ log.tanggal_kematian }}</p>
                                    </td>
                                    
                                    <td class="px-6 py-5">
                                        <p class="font-bold text-slate-900 text-base">{{ log.kolam?.nama_kolam }}</p>
                                    </td>

                                    <td class="px-6 py-5 text-center">
                                        <span class="inline-flex items-center justify-center gap-1 px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 border border-rose-100 font-bold">
                                            <span class="text-base">{{ log.jumlah_mati }}</span>
                                            <span class="text-xs font-medium opacity-70">Ekor</span>
                                        </span>
                                    </td>

                                    <td class="px-6 py-5">
                                        <p class="text-slate-600 font-medium leading-relaxed">{{ log.catatan || '-' }}</p>
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
                                            <svg class="w-12 h-12 text-emerald-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <p class="text-slate-700 font-bold text-base mb-1">Populasi Aman</p>
                                            <p class="text-slate-500 font-medium text-sm">Tidak ada catatan kematian ikan yang dilaporkan.</p>
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