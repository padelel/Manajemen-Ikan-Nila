<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    logs: Array
});

const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};

// Fungsi Penamaan Role
const getRoleName = (userId) => {
    if (userId === 1) return 'Pengelola Utama';
    if (userId === 2) return 'Operator Lapangan';
    return 'Sistem Otomatis';
};

// Fungsi Inisial Avatar
const getRoleInitial = (userId) => {
    if (userId === 1) return 'PU';
    if (userId === 2) return 'OL';
    return 'S';
};
</script>

<template>
    <Head title="Riwayat Stok Gudang" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:justify-between md:items-end gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight transition-colors duration-300">Riwayat Pergerakan Stok Pakan</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors duration-300">Pantau arus masuk (restock) dan keluar (penggunaan) pakan.</p>
                </div>
                <Link :href="route('inventory.index')" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-bold rounded-xl hover:bg-slate-200 dark:hover:bg-slate-600 transition flex-shrink-0 transition-colors duration-300">
                    Kembali ke Master Gudang
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-emerald-50/50 dark:bg-emerald-500/10 p-6 rounded-2xl border border-emerald-100 dark:border-emerald-500/20 flex items-center gap-4 transition-colors duration-300">
                        <div class="h-12 w-12 rounded-full bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400 transition-colors duration-300">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-widest transition-colors duration-300">Restock Pakan (Masuk)</p>
                            <p class="text-sm text-emerald-800 dark:text-emerald-300 mt-1 font-medium transition-colors duration-300">Penambahan stok dari supplier baru.</p>
                        </div>
                    </div>
                    
                    <div class="bg-amber-50/50 dark:bg-amber-500/10 p-6 rounded-2xl border border-amber-100 dark:border-amber-500/20 flex items-center gap-4 transition-colors duration-300">
                        <div class="h-12 w-12 rounded-full bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center text-amber-600 dark:text-amber-400 transition-colors duration-300">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-widest transition-colors duration-300">Penggunaan (Keluar)</p>
                            <p class="text-sm text-amber-800 dark:text-amber-300 mt-1 font-medium transition-colors duration-300">Pakan yang diberikan ke kolam budidaya.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:rounded-3xl border border-slate-100 dark:border-slate-700 transition-colors duration-300">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50/50 dark:bg-slate-700/50 border-b border-slate-100 dark:border-slate-700 transition-colors duration-300">
                                <tr>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest transition-colors">Tanggal & Waktu</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest transition-colors">Tipe Arus</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest transition-colors">Item Pakan</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest transition-colors">Jumlah (Kg)</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest transition-colors">Keterangan</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest transition-colors">Operator</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <tr v-for="log in logs" :key="log.id" class="border-b border-slate-50 dark:border-slate-700/50 hover:bg-slate-50/80 dark:hover:bg-slate-700/50 transition duration-200">
                                    
                                    <td class="px-6 py-5 font-medium text-slate-500 dark:text-slate-400 whitespace-nowrap transition-colors">
                                        {{ formatDate(log.created_at) }}
                                    </td>
                                    
                                    <td class="px-6 py-5">
                                        <span v-if="log.tipe === 'Masuk'" class="px-3 py-1 bg-emerald-100 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 text-[10px] font-black uppercase tracking-wider rounded-lg border border-emerald-200 dark:border-emerald-500/20 transition-colors">
                                            Masuk
                                        </span>
                                        <span v-else class="px-3 py-1 bg-amber-100 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 text-[10px] font-black uppercase tracking-wider rounded-lg border border-amber-200 dark:border-amber-500/20 transition-colors">
                                            Keluar
                                        </span>
                                    </td>
                                    
                                    <td class="px-6 py-5">
                                        <p class="font-bold text-slate-900 dark:text-slate-100 transition-colors">{{ log.inventory.nama_pakan }}</p>
                                    </td>
                                    
                                    <td class="px-6 py-5">
                                        <p class="font-black text-lg transition-colors" :class="log.tipe === 'Masuk' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'">
                                            <span v-if="log.tipe === 'Masuk'">+</span>
                                            <span v-else>-</span>
                                            {{ log.jumlah }} <span class="text-xs font-bold uppercase text-slate-400 dark:text-slate-500 transition-colors">Kg</span>
                                        </p>
                                    </td>

                                    <td class="px-6 py-5 max-w-xs">
                                        <p class="text-xs text-slate-600 dark:text-slate-400 truncate transition-colors" :title="log.keterangan">{{ log.keterangan || '-' }}</p>
                                    </td>
                                    
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="rounded-full w-9 h-9 flex items-center justify-center font-bold text-xs uppercase shadow-sm border bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-600 transition-colors">
                                                {{ getRoleInitial(log.user_id) }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-bold text-slate-900 dark:text-slate-100 text-sm transition-colors">
                                                    {{ log.user ? log.user.name : 'Sistem' }}
                                                </span>
                                                <span class="text-[10px] font-bold uppercase tracking-wider transition-colors" 
                                                      :class="getRoleName(log.user_id) === 'Pengelola Utama' ? 'text-indigo-500 dark:text-indigo-400' : 'text-teal-500 dark:text-teal-400'">
                                                    {{ getRoleName(log.user_id) }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                </tr>

                                <tr v-if="logs.length === 0">
                                    <td colspan="6" class="px-6 py-16 text-center text-slate-500 dark:text-slate-400 transition-colors">Belum ada riwayat pergerakan stok pakan.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>