<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ parameters: Array });

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
    <Head title="Data Kualitas Air" />
    
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-end">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Pemantauan Kualitas Air</h2>
                    <p class="text-sm text-slate-500 mt-1">Riwayat pengecekan suhu, pH, dan kondisi visual kolam harian.</p>
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
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tanggal</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Kolam</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Suhu</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">pH Air</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Kondisi Visual</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Petugas</th>
                                </tr>
                            </thead>
                            
                            <tbody class="text-sm">
                                <tr v-for="param in parameters" :key="param.id" class="border-b border-slate-50 hover:bg-slate-50/80 transition duration-200 group">
                                    
                                    <td class="px-6 py-5">
                                        <p class="font-medium text-slate-500">{{ param.tanggal_cek }}</p>
                                    </td>
                                    
                                    <td class="px-6 py-5">
                                        <p class="font-bold text-slate-900 text-base">{{ param.kolam.nama_kolam }}</p>
                                    </td>

                                    <td class="px-6 py-5 text-center">
                                        <span 
                                            :class="(param.suhu >= 25 && param.suhu <= 32) ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200'"
                                            class="inline-flex items-center justify-center gap-0.5 px-3 py-1.5 rounded-lg border font-bold transition-colors"
                                        >
                                            <span class="text-base">{{ param.suhu }}</span>
                                            <span class="text-xs opacity-70 font-semibold mt-0.5">°C</span>
                                        </span>
                                    </td>

                                    <td class="px-6 py-5 text-center">
                                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-slate-100 text-slate-700 font-bold border border-slate-200">
                                            {{ param.ph }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-5">
                                        <span class="inline-block px-3 py-1.5 bg-slate-100 text-slate-600 rounded-lg text-xs font-semibold capitalize">
                                            {{ param.kondisi_visual }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="rounded-full w-9 h-9 flex items-center justify-center font-bold text-xs uppercase shadow-sm border"
                                                 :class="{
                                                     'bg-indigo-50 text-indigo-700 border-indigo-100': param.user_id === 1,
                                                     'bg-teal-50 text-teal-700 border-teal-100': param.user_id === 2,
                                                     'bg-slate-100 text-slate-600 border-slate-200': !param.user_id || param.user_id > 2
                                                 }">
                                                {{ getRoleInitial(param.user_id) }}
                                            </div>
                                            
                                            <div class="flex flex-col">
                                                <span class="text-xs font-bold uppercase tracking-wider" 
                                                      :class="param.user_id === 1 ? 'text-indigo-600' : (param.user_id === 2 ? 'text-teal-600' : 'text-slate-500')">
                                                    {{ getRoleName(param.user_id) }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                </tr>

                                <tr v-if="parameters.length === 0">
                                    <td colspan="6" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-slate-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.613.306a4 4 0 01-2.574.344l-2.484-.497a2 2 0 00-1.032.05L3 16.382V5.562l2.484-.828a2 2 0 011.032-.05l2.484.497a4 4 0 002.574-.344l.613-.306a6 6 0 013.86-.517l2.387.477a2 2 0 011.022.547V15.428z" />
                                            </svg>
                                            <p class="text-slate-500 font-medium text-sm">Belum ada data pengecekan air.</p>
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