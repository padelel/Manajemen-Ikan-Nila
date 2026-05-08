<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    inventories: Array
});

const hapusInventory = (id, nama) => {
    if (confirm(`Yakin ingin menghapus pakan ${nama}?`)) {
        router.delete(`/inventory/${id}`);
    }
};
</script>

<template>
    <Head title="Gudang Pakan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:justify-between md:items-end gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight transition-colors duration-300">Gudang Pakan</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors duration-300">Pantau dan kelola ketersediaan stok pakan ikan Anda.</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <Link 
                        v-if="$page.props.auth.user.role === 'admin'" 
                        :href="route('inventory.history')" 
                        class="bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 px-5 py-2.5 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-600 text-sm font-bold transition-colors duration-300 flex-shrink-0"
                    >
                        Riwayat Stok
                    </Link>

                    <Link 
                        href="/inventory/create" 
                        class="bg-slate-900 dark:bg-indigo-600 text-white px-5 py-2.5 rounded-xl shadow-lg shadow-slate-200/50 dark:shadow-none hover:bg-slate-800 dark:hover:bg-indigo-500 text-sm font-semibold transition-all flex-shrink-0"
                    >
                        + Tambah Stok
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:rounded-3xl border border-slate-100 dark:border-slate-700 transition-colors duration-300">
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50/50 dark:bg-slate-700/50 border-b border-slate-100 dark:border-slate-700 transition-colors duration-300">
                                <tr>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest transition-colors">Nama Pakan</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest text-center transition-colors">Total Stok</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest transition-colors">Terakhir Update</th>
                                    <th v-if="$page.props.auth.user.role === 'admin'" class="px-6 py-5 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest text-right transition-colors">Aksi</th>
                                </tr>
                            </thead>
                            
                            <tbody class="text-sm">
                                <tr v-for="item in inventories" :key="item.id" class="border-b border-slate-50 dark:border-slate-700/50 hover:bg-slate-50/80 dark:hover:bg-slate-700/50 transition duration-200 group">
                                    
                                    <td class="px-6 py-5">
                                        <p class="font-bold text-slate-900 dark:text-slate-100 text-base transition-colors">{{ item.nama_pakan }}</p>
                                    </td>
                                    
                                    <td class="px-6 py-5 text-center">
                                        <span 
                                            :class="item.total_stok_kg < 50 
                                                ? 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 border-red-100 dark:border-red-500/20' 
                                                : 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-100 dark:border-emerald-500/20'" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg font-bold border transition-colors duration-300"
                                        >
                                            <span class="text-base">{{ Number(item.total_stok_kg).toFixed(2) }}</span>
                                            <span class="text-xs font-medium opacity-70">Kg</span>
                                        </span>
                                    </td>

                                    <td class="px-6 py-5 text-slate-500 dark:text-slate-400 font-medium transition-colors">
                                        {{ item.terakhir_update }}
                                    </td>

                                    <td v-if="$page.props.auth.user.role === 'admin'" class="px-6 py-5 text-right">
                                        <div class="flex items-center justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                            <Link :href="'/inventory/' + item.id + '/edit'" class="px-3 py-1.5 text-xs font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-500/10 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-500/20 transition">
                                                Edit
                                            </Link>
                                            <button @click="hapusInventory(item.id, item.nama_pakan)" class="px-3 py-1.5 text-xs font-bold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 rounded-lg hover:bg-red-100 dark:hover:bg-red-500/20 transition">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="inventories.length === 0">
                                    <td :colspan="$page.props.auth.user.role === 'admin' ? 4 : 3" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mb-4 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                            <p class="text-slate-500 dark:text-slate-400 font-medium text-sm transition-colors">Gudang kosong. Belum ada data pakan.</p>
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