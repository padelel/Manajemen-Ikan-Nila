<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ logs: Array });

const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};

// Mengambil nama role secara dinamis dari relasi user
const getRoleName = (user) => {
    if (user && user.role) return user.role === 'admin' ? 'Pengelola Utama' : 'Operator Lapangan';
    return 'Sistem Otomatis';
};

// Mengambil inisial nama secara dinamis
const getRoleInitial = (user) => {
    if (user && user.name) return user.name.charAt(0).toUpperCase();
    return 'S';
};
</script>

<template>
    <Head title="Riwayat Tebar Benih" />
    
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:justify-between md:items-end gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Riwayat Tebar Benih</h2>
                    <p class="text-sm text-slate-500 mt-1">Catatan penambahan populasi benih baru ke dalam kolam.</p>
                </div>
                
                <Link v-if="$page.props.auth.user.role === 'operator'" :href="route('tebar.create')" class="bg-indigo-600 text-white px-5 py-2.5 rounded-xl shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 text-sm font-bold transition-all flex-shrink-0">
                    + Tebar Benih Baru
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="bg-white overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:rounded-3xl border border-slate-100">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50/50 border-b border-slate-100">
                                <tr>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-40">Tanggal Tebar</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tujuan Kolam</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Jumlah Benih</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Detail Benih</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Dicatat Oleh</th>
                                </tr>
                            </thead>
                            
                            <tbody class="text-sm">
                                <tr v-for="log in logs" :key="log.id" class="border-b border-slate-50 hover:bg-indigo-50/20 transition duration-200">
                                    
                                    <!-- Tanggal -->
                                    <td class="px-6 py-5 font-medium text-slate-500">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            {{ formatDate(log.tanggal_tebar) }}
                                        </div>
                                    </td>
                                    
                                    <!-- Kolam -->
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-500 border border-indigo-100/50">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-900 text-base">{{ log.kolam?.nama_kolam }}</p>
                                                <p class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest mt-0.5">Penambahan Baru</p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Jumlah Benih -->
                                    <td class="px-6 py-5 text-center">
                                        <div class="inline-flex flex-col items-center justify-center">
                                            <p class="font-black text-indigo-600 text-xl tracking-tight">
                                                +{{ log.jumlah_ikan.toLocaleString('id-ID') }}
                                            </p>
                                            <span class="text-[10px] text-indigo-400 font-bold uppercase tracking-widest bg-indigo-50 px-2 py-0.5 rounded-md mt-1 border border-indigo-100/50">
                                                Ekor
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Sumber & Ukuran (Detail) -->
                                    <td class="px-6 py-5">
                                        <div class="flex flex-col gap-2">
                                            <div class="inline-flex items-center gap-1.5">
                                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Sumber:</span>
                                                <span class="text-sm font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200/60">
                                                    {{ log.sumber_benih || 'Sumber Lokal' }}
                                                </span>
                                            </div>
                                            <div class="inline-flex items-center gap-1.5">
                                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Rata-rata:</span>
                                                <span class="text-sm font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100/50">
                                                    {{ log.berat_rata_gram }} gr / ekor
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Pelapor -->
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="rounded-full w-9 h-9 flex items-center justify-center font-bold text-xs uppercase border shadow-sm bg-white text-slate-700 border-slate-200">
                                                {{ getRoleInitial(log.user) }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-bold text-slate-900 text-sm">
                                                    {{ log.user ? log.user.name : 'Sistem' }}
                                                </span>
                                                <span class="text-[10px] font-bold uppercase tracking-wider" 
                                                      :class="getRoleName(log.user) === 'Pengelola Utama' ? 'text-indigo-500' : 'text-teal-500'">
                                                    {{ getRoleName(log.user) }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <!-- State Kosong -->
                                <tr v-if="logs.length === 0">
                                    <td colspan="5" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="h-16 w-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 border border-slate-100">
                                                <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                                </svg>
                                            </div>
                                            <p class="text-slate-500 font-medium text-sm">Belum ada riwayat tebar benih yang tercatat.</p>
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