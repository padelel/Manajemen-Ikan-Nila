<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ inventory: Object });

const form = useForm({
    nama_pakan: props.inventory.nama_pakan,
    total_stok_kg: props.inventory.total_stok_kg,
    terakhir_update: props.inventory.terakhir_update
});

const submit = () => {
    form.put(`/inventory/${props.inventory.id}`);
};
</script>

<template>
    <Head title="Edit Pakan" />
    
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight transition-colors duration-300">Edit Stok Pakan</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors duration-300">Perbarui informasi merek atau koreksi jumlah stok pakan.</p>
                </div>
                <Link href="/inventory" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-bold rounded-xl hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors duration-300">
                    Kembali
                </Link>
            </div>
        </template>
        
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-slate-800 p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] sm:rounded-3xl border border-slate-100 dark:border-slate-700 transition-colors duration-300">
                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 transition-colors duration-300">Nama / Merek Pakan</label>
                            <input v-model="form.nama_pakan" type="text" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-600 text-slate-900 dark:text-slate-100 rounded-xl focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400 text-sm transition-colors duration-300" required>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 transition-colors duration-300">Total Stok (Kg)</label>
                                <div class="relative">
                                    <input v-model="form.total_stok_kg" type="number" step="0.1" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-600 text-slate-900 dark:text-slate-100 rounded-xl focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400 text-sm transition-colors duration-300" required>
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 font-bold text-xs transition-colors duration-300">Kg</span>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 transition-colors duration-300">Tanggal Update</label>
                                <input v-model="form.terakhir_update" type="date" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-600 text-slate-900 dark:text-slate-100 rounded-xl focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400 text-sm transition-colors duration-300" required>
                            </div>
                        </div>
                        
                        <div class="pt-6 border-t border-slate-100 dark:border-slate-700 flex justify-end items-center gap-4 transition-colors duration-300">
                            <Link href="/inventory" class="text-slate-600 dark:text-slate-400 font-medium px-4 py-2 hover:text-slate-900 dark:hover:text-slate-100 transition-colors duration-300">
                                Batal
                            </Link>
                            <button type="submit" class="bg-blue-600 dark:bg-blue-500 text-white px-8 py-3 rounded-xl shadow-lg shadow-blue-500/30 dark:shadow-none hover:bg-blue-700 dark:hover:bg-blue-400 font-bold transition-all disabled:opacity-50" :disabled="form.processing">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>