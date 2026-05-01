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
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Pakan Masuk (Restock)</h2>
                    <p class="text-sm text-slate-500 mt-1">Tambahkan stok pakan yang sudah ada atau daftarkan merek baru.</p>
                </div>
                <Link :href="route('inventory.index')" class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-bold rounded-xl hover:bg-slate-200 transition">
                    Batal & Kembali
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:rounded-3xl border border-slate-100">
                    
                    <!-- TOGGLE MODE INPUT -->
                    <div class="flex p-1 bg-slate-100 rounded-xl mb-8">
                        <button type="button" @click="form.mode = 'restock'" :class="form.mode === 'restock' ? 'bg-white shadow-sm text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-700 font-medium'" class="flex-1 py-2.5 rounded-lg text-sm transition-all duration-200">
                            Tambah Stok Tersedia
                        </button>
                        <button type="button" @click="form.mode = 'baru'" :class="form.mode === 'baru' ? 'bg-white shadow-sm text-emerald-600 font-bold' : 'text-slate-500 hover:text-slate-700 font-medium'" class="flex-1 py-2.5 rounded-lg text-sm transition-all duration-200">
                            Daftarkan Merek Baru
                        </button>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- JIKA MODE RESTOCK -->
                            <div v-if="form.mode === 'restock'" class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Pilih Pakan Yang Direstock <span class="text-red-500">*</span></label>
                                <select v-model="form.inventory_id" :required="form.mode === 'restock'" class="w-full border-slate-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 text-sm bg-slate-50/50">
                                    <option value="" disabled>-- Pilih Merek Pakan --</option>
                                    <option v-for="item in inventories" :key="item.id" :value="item.id">
                                        {{ item.nama_pakan }} (Sisa Stok: {{ item.total_stok_kg }} Kg)
                                    </option>
                                </select>
                                <p v-if="form.errors.inventory_id" class="text-red-500 text-xs mt-1">{{ form.errors.inventory_id }}</p>
                            </div>

                            <!-- JIKA MODE BARU -->
                            <div v-if="form.mode === 'baru'" class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nama / Merek Pakan Baru <span class="text-red-500">*</span></label>
                                <input v-model="form.nama_pakan" type="text" :required="form.mode === 'baru'" class="w-full border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500 text-sm" placeholder="Contoh: PF-1000 Mutiara">
                                <p v-if="form.errors.nama_pakan" class="text-red-500 text-xs mt-1">{{ form.errors.nama_pakan }}</p>
                            </div>

                            <!-- JUMLAH STOK MASUK -->
                            <div class="md:col-span-2 mt-2">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Jumlah Yang Ditambahkan (Kg) <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input v-model="form.jumlah_kg" type="number" step="0.1" min="0.1" required class="w-full border-slate-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 text-lg font-black text-center py-4" :class="form.mode === 'baru' ? 'text-emerald-600' : 'text-blue-600'" placeholder="0.0">
                                    <span class="absolute right-6 top-1/2 -translate-y-1/2 font-bold text-slate-400">Kg</span>
                                </div>
                                <p v-if="form.errors.jumlah_kg" class="text-red-500 text-xs mt-1">{{ form.errors.jumlah_kg }}</p>
                                
                                <!-- Simulasi Penjumlahan (Khusus Restock) -->
                                <p v-if="form.mode === 'restock' && selectedInventory && form.jumlah_kg" class="text-xs font-medium text-slate-500 mt-2 text-center bg-blue-50 py-2 rounded-lg border border-blue-100">
                                    Total stok setelah ditambah: <span class="font-black text-blue-700">{{ (parseFloat(selectedInventory.total_stok_kg) + parseFloat(form.jumlah_kg)).toFixed(1) }} Kg</span>
                                </p>
                            </div>

                            <!-- KETERANGAN / CATATAN -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Catatan / Keterangan Masuk (Opsional)</label>
                                <textarea v-model="form.keterangan" rows="2" class="w-full border-slate-200 rounded-xl focus:border-slate-500 focus:ring-slate-500 text-sm" placeholder="Contoh: Pembelian dari Supplier A (Nota #1234)..."></textarea>
                                <p v-if="form.errors.keterangan" class="text-red-500 text-xs mt-1">{{ form.errors.keterangan }}</p>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-100 flex justify-end">
                            <button type="submit" :disabled="form.processing" :class="form.mode === 'baru' ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-500/30' : 'bg-blue-600 hover:bg-blue-700 shadow-blue-500/30'" class="px-8 py-3 text-white font-bold rounded-xl shadow-lg transition flex items-center gap-2">
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