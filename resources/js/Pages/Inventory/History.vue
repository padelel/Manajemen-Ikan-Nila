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

// Mengambil inisial nama operator
const getInitial = (name) => {
    return name ? name.charAt(0).toUpperCase() : '?';
};
</script>

<template>
    <Head title="Riwayat Stok Gudang" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:justify-between md:items-end gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Riwayat Pergerakan Stok Pakan</h2>
                    <p class="text-sm text-slate-500 mt-1">Pantau arus masuk (restock) dan keluar (penggunaan) pakan.</p>
                </div>
                <Link :href="route('inventory.index')" class="px-5 py-2.5 bg-slate-100 text-slate-700 text-sm font-bold rounded-xl hover:bg-slate-200 transition flex-shrink-0">
                    Kembali ke Master Gudang
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Ringkasan Cepat -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-emerald-50/50 p-6 rounded-2xl border border-emerald-100 flex items-center gap-4">
                        <div class="h-12 w-12 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-emerald-600 uppercase tracking-widest">Restock Pakan (Masuk)</p>
                            <p class="text-sm text-emerald-800 mt-1 font-medium">Penambahan stok dari supplier baru.</p>
                        </div>
                    </div>
                    
                    <div class="bg-amber-50/50 p-6 rounded-2xl border border-amber-100 flex items-center gap-4">
                        <div class="h-12 w-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-600">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-amber-600 uppercase tracking-widest">Penggunaan (Keluar)</p>
                            <p class="text-sm text-amber-800 mt-1 font-medium">Pakan yang diberikan ke kolam budidaya.</p>
                        </div>
                    </div>
                </div>

                <!-- Tabel Riwayat -->
                <div class="bg-white overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:rounded-3xl border border-slate-100">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50/50 border-b border-slate-100">
                                <tr>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tanggal & Waktu</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tipe Arus</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Item Pakan</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Jumlah (Kg)</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Keterangan</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Operator</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <tr v-for="log in logs" :key="log.id" class="border-b border-slate-50 hover:bg-slate-50/80 transition duration-200">
                                    
                                    <td class="px-6 py-5 font-medium text-slate-500 whitespace-nowrap">
                                        {{ formatDate(log.created_at) }}
                                    </td>
                                    
                                    <!-- Label Tipe: Masuk (Restock) atau Keluar (Penggunaan) -->
                                    <td class="px-6 py-5">
                                        <span v-if="log.tipe === 'Masuk'" class="px-3 py-1 bg-emerald-100 text-emerald-700 text-[10px] font-black uppercase tracking-wider rounded-lg border border-emerald-200">
                                            Masuk
                                        </span>
                                        <span v-else class="px-3 py-1 bg-amber-100 text-amber-700 text-[10px] font-black uppercase tracking-wider rounded-lg border border-amber-200">
                                            Keluar
                                        </span>
                                    </td>
                                    
                                    <td class="px-6 py-5">
                                        <p class="font-bold text-slate-900">{{ log.inventory.nama_pakan }}</p>
                                    </td>
                                    
                                    <td class="px-6 py-5">
                                        <p class="font-black text-lg" :class="log.tipe === 'Masuk' ? 'text-emerald-600' : 'text-amber-600'">
                                            <span v-if="log.tipe === 'Masuk'">+</span>
                                            <span v-else>-</span>
                                            {{ log.jumlah }} <span class="text-xs font-bold uppercase text-slate-400">Kg</span>
                                        </p>
                                    </td>

                                    <td class="px-6 py-5 max-w-xs">
                                        <p class="text-xs text-slate-600 truncate" :title="log.keterangan">{{ log.keterangan || '-' }}</p>
                                    </td>
                                    
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="rounded-full w-8 h-8 flex items-center justify-center bg-slate-100 text-slate-600 border border-slate-200 font-bold text-xs uppercase shadow-sm">
                                                {{ getInitial(log.user?.name) }}
                                            </div>
                                            <span class="text-xs font-bold text-slate-800">{{ log.user?.name || 'Sistem' }}</span>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="logs.length === 0">
                                    <td colspan="6" class="px-6 py-16 text-center text-slate-500">Belum ada riwayat pergerakan stok pakan.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>