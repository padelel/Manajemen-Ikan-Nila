<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    inventories: Array
});

const form = useForm({
    mode: 'restock', // Default ke mode penambahan stok
    inventory_id: '',
    nama_pakan: '',
    jumlah_kg: '',
    keterangan: ''
});

// Mencari data stok saat ini (jika mode restock)
const selectedInventory = computed(() => {
    if (!form.inventory_id || !props.inventories) return null;
    return props.inventories.find(i => i.id === form.inventory_id);
});

const submit = () => {
    form.post(route('inventory.store'));
};
</script>

<template>
    <Head title="Input Pakan Masuk" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight transition-colors duration-300">Pakan Masuk (Restock)</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors duration-300">Tambahkan stok pakan yang sudah ada atau daftarkan merek baru.</p>
                </div>
                <Link :href="route('inventory.index')" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-bold rounded-xl hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors duration-300">
                    Batal & Kembali
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-slate-800 p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] sm:rounded-3xl border border-slate-100 dark:border-slate-700 transition-colors duration-300">
                    
                    <div class="flex p-1 bg-slate-100 dark:bg-slate-700/50 rounded-xl mb-8 transition-colors duration-300">
                        <button 
                            type="button" 
                            @click="form.mode = 'restock'" 
                            :class="form.mode === 'restock' ? 'bg-white dark:bg-slate-800 shadow-sm text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 font-medium'" 
                            class="flex-1 py-2.5 rounded-lg text-sm transition-all duration-200"
                        >
                            Tambah Stok Tersedia
                        </button>
                        <button 
                            type="button" 
                            @click="form.mode = 'baru'" 
                            :class="form.mode === 'baru' ? 'bg-white dark:bg-slate-800 shadow-sm text-emerald-600 dark:text-emerald-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 font-medium'" 
                            class="flex-1 py-2.5 rounded-lg text-sm transition-all duration-200"
                        >
                            Daftarkan Merek Baru
                        </button>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <div v-if="form.mode === 'restock'" class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 transition-colors duration-300">Pilih Pakan Yang Direstock <span class="text-red-500 dark:text-red-400">*</span></label>
                                <select v-model="form.inventory_id" :required="form.mode === 'restock'" class="w-full border-slate-200 dark:border-slate-600 rounded-xl focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400 text-sm bg-slate-50/50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 transition-colors duration-300">
                                    <option value="" disabled>-- Pilih Merek Pakan --</option>
                                    <option v-for="item in inventories" :key="item.id" :value="item.id">
                                        {{ item.nama_pakan }} (Sisa Stok: {{ item.total_stok_kg }} Kg)
                                    </option>
                                </select>
                                <p v-if="form.errors.inventory_id" class="text-red-500 dark:text-red-400 text-xs mt-1 transition-colors duration-300">{{ form.errors.inventory_id }}</p>
                            </div>

                            <div v-if="form.mode === 'baru'" class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 transition-colors duration-300">Nama / Merek Pakan Baru <span class="text-red-500 dark:text-red-400">*</span></label>
                                <input v-model="form.nama_pakan" type="text" :required="form.mode === 'baru'" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-600 rounded-xl focus:border-emerald-500 dark:focus:border-emerald-400 focus:ring-emerald-500 dark:focus:ring-emerald-400 text-slate-900 dark:text-slate-100 text-sm transition-colors duration-300" placeholder="Contoh: PF-1000 Mutiara">
                                <p v-if="form.errors.nama_pakan" class="text-red-500 dark:text-red-400 text-xs mt-1 transition-colors duration-300">{{ form.errors.nama_pakan }}</p>
                            </div>

                            <div class="md:col-span-2 mt-2">
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 transition-colors duration-300">Jumlah Yang Ditambahkan (Kg) <span class="text-red-500 dark:text-red-400">*</span></label>
                                <div class="relative">
                                    <input 
                                        v-model="form.jumlah_kg" 
                                        type="number" 
                                        step="0.1" 
                                        min="0.1" 
                                        required 
                                        class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-600 rounded-xl text-lg font-black text-center py-4 transition-colors duration-300" 
                                        :class="[
                                            form.mode === 'baru' ? 'text-emerald-600 dark:text-emerald-400 focus:border-emerald-500 focus:ring-emerald-500 dark:focus:border-emerald-400 dark:focus:ring-emerald-400' : 'text-blue-600 dark:text-blue-400 focus:border-blue-500 focus:ring-blue-500 dark:focus:border-blue-400 dark:focus:ring-blue-400'
                                        ]" 
                                        placeholder="0.0"
                                    >
                                    <span class="absolute right-6 top-1/2 -translate-y-1/2 font-bold text-slate-400 dark:text-slate-500 transition-colors duration-300">Kg</span>
                                </div>
                                <p v-if="form.errors.jumlah_kg" class="text-red-500 dark:text-red-400 text-xs mt-1 transition-colors duration-300">{{ form.errors.jumlah_kg }}</p>
                                
                                <p v-if="form.mode === 'restock' && selectedInventory && form.jumlah_kg" class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-2 text-center bg-blue-50 dark:bg-blue-500/10 py-2 rounded-lg border border-blue-100 dark:border-blue-500/20 transition-colors duration-300">
                                    Total stok setelah ditambah: <span class="font-black text-blue-700 dark:text-blue-400 transition-colors duration-300">{{ (parseFloat(selectedInventory.total_stok_kg) + parseFloat(form.jumlah_kg)).toFixed(1) }} Kg</span>
                                </p>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 transition-colors duration-300">Catatan / Keterangan Masuk (Opsional)</label>
                                <textarea v-model="form.keterangan" rows="2" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-600 rounded-xl focus:border-slate-500 dark:focus:border-slate-400 focus:ring-slate-500 dark:focus:ring-slate-400 text-slate-900 dark:text-slate-100 text-sm transition-colors duration-300" placeholder="Contoh: Pembelian dari Supplier A (Nota #1234)..."></textarea>
                                <p v-if="form.errors.keterangan" class="text-red-500 dark:text-red-400 text-xs mt-1 transition-colors duration-300">{{ form.errors.keterangan }}</p>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-100 dark:border-slate-700 flex justify-end transition-colors duration-300">
                            <button 
                                type="submit" 
                                :disabled="form.processing" 
                                :class="form.mode === 'baru' 
                                    ? 'bg-emerald-600 dark:bg-emerald-500 hover:bg-emerald-700 dark:hover:bg-emerald-400 shadow-emerald-500/30 dark:shadow-none' 
                                    : 'bg-blue-600 dark:bg-blue-500 hover:bg-blue-700 dark:hover:bg-blue-400 shadow-blue-500/30 dark:shadow-none'" 
                                class="px-8 py-3 text-white font-bold rounded-xl shadow-lg transition-all flex items-center gap-2 disabled:opacity-50"
                            >
                                <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span v-if="!form.processing">Simpan Stok Masuk</span>
                                <span v-else>Menyimpan...</span>
                            </button>
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>