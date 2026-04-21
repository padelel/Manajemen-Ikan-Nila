<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ feedLogs: Array });

// Fungsi untuk menentukan Jabatan/Role berdasarkan user_id
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
    <Head title="Riwayat Pakan" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-end">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Riwayat Pemberian Pakan</h2>
                    <p class="text-sm text-slate-500 mt-1">Catatan harian komposisi, dosis aktual, dan rekomendasi sistem.</p>
                </div>

                <div class="flex gap-3">
                    <a href="/laporan/cetak" target="_blank" class="flex items-center gap-2 bg-white border border-slate-200 text-slate-700 px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-50 hover:text-slate-900 text-sm font-semibold transition-all">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak Laporan
                    </a>
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
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-1/3">Komposisi Pakan</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-1/6">Status & Dosis</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-1/6">Petugas</th>
                                </tr>
                            </thead>
                            
                            <tbody class="text-sm">
                                <tr v-for="log in feedLogs" :key="log.id" class="border-b border-slate-50 hover:bg-slate-50/80 transition duration-200">
                                    
                                    <td class="px-6 py-5">
                                        <p class="font-medium text-slate-500">{{ log.tanggal_pakan }}</p>
                                    </td>
                                    
                                    <td class="px-6 py-5">
                                        <p class="font-bold text-slate-900 text-base">{{ log.kolam.nama_kolam }}</p>
                                    </td>
                                    
                                    <td class="px-6 py-5">
                                        <div class="flex flex-col gap-2">
                                            <div v-for="detail in log.details" :key="detail.id" class="flex items-center gap-2">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                                                <span class="font-medium text-slate-700">{{ detail.inventory.nama_pakan }}</span>
                                                <span class="text-slate-300">—</span>
                                                <span class="font-bold text-slate-900">{{ detail.jumlah_kg }} <span class="text-[10px] text-slate-400 font-normal">Kg</span></span>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-5">
                                        <div class="flex flex-col gap-2.5">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold w-max border border-slate-200">
                                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>
                                                Target AI: {{ log.rekomendasi_sistem }} Kg
                                            </span>
                                            
                                            <div class="text-sm font-bold flex items-center gap-1.5" :class="log.pakan_aktual > log.rekomendasi_sistem ? 'text-amber-600' : 'text-emerald-600'">
                                                Aktual: {{ log.pakan_aktual }} Kg
                                                
                                                <svg v-if="log.pakan_aktual > log.rekomendasi_sistem" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                        </div>
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
                                                <span v-if="log.user" class="text-sm text-slate-700 font-medium mt-0.5">
                                                    {{ log.user.name }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                
                                <tr v-if="feedLogs.length === 0">
                                    <td colspan="5" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-slate-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <p class="text-slate-500 font-medium text-sm">Belum ada riwayat pemberian pakan.</p>
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