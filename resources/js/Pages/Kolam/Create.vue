<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const form = useForm({
    nama_kolam: '',
    lokasi: '',
    status_kolam: 'Persiapan', // Default: Persiapan (karena belum ditebar benih)
    bentuk_kolam: 'Bundar', 
    panjang_m: '', 
    lebar_m: '',
    kedalaman_m: '',
});

// Auto-sinkronisasi Lebar jika Bundar
watch([() => form.panjang_m, () => form.bentuk_kolam], () => {
    if (form.bentuk_kolam === 'Bundar') {
        form.lebar_m = form.panjang_m;
    }
});

const submit = () => {
    form.post(route('kolam.store'), { preserveScroll: true });
};
</script>

<template>
    <Head title="Tambah Kolam Baru" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div>
                    <h2 class="font-bold text-xl sm:text-2xl text-slate-800 dark:text-slate-100 leading-tight tracking-tight transition-colors duration-300">
                        Pendaftaran Kolam Baru
                    </h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors duration-300">Tambahkan data aset kolam baru ke dalam sistem.</p>
                </div>
            </div>
        </template>
        
        <div class="py-8 sm:py-12">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-slate-800 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] sm:rounded-3xl border border-slate-100 dark:border-slate-700 transition-colors duration-300">
            
                    <div class="p-5 sm:p-8">
                        <form @submit.prevent="submit" class="space-y-6 sm:space-y-8">
                            
                            <div v-if="Object.keys(form.errors).length > 0" class="p-4 bg-rose-50 dark:bg-rose-500/10 border-l-4 border-rose-500 text-rose-700 dark:text-rose-400 rounded-xl transition-colors duration-300">
                                <p class="font-bold text-sm">Gagal menyimpan data! Periksa isian berikut:</p>
                                <ul class="list-disc pl-5 mt-1 text-sm">
                                    <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
                                </ul>
                            </div>

                            <div>
                                <h3 class="text-lg font-bold border-b border-slate-200 dark:border-slate-700 pb-2 mb-4 text-slate-800 dark:text-slate-200 transition-colors duration-300">
                                    1. Identitas Kolam
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5 transition-colors duration-300">Nama/Kode Kolam</label>
                                        <input v-model="form.nama_kolam" type="text" class="w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-colors duration-300" placeholder="Misal: Kolam A1" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5 transition-colors duration-300">Lokasi / Blok</label>
                                        <input v-model="form.lokasi" type="text" class="w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-colors duration-300" placeholder="Misal: Blok Utara" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5 transition-colors duration-300">Status Kolam</label>
                                        <select v-model="form.status_kolam" class="w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-colors duration-300" required>
                                            <option value="Aktif">Aktif</option>
                                            <option value="Persiapan">Persiapan / Kuras</option>
                                            <option value="Istirahat">Istirahat / Kosong</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-lg font-bold border-b border-slate-200 dark:border-slate-700 pb-2 mb-4 text-slate-800 dark:text-slate-200 transition-colors duration-300">
                                    2. Dimensi Fisik
                                </h3>
                                
                                <div class="flex flex-col sm:flex-row gap-3 sm:gap-6 mb-5">
                                    <label class="inline-flex items-center cursor-pointer group">
                                        <input type="radio" v-model="form.bentuk_kolam" value="Bundar" class="w-5 h-5 text-blue-600 dark:text-blue-500 focus:ring-blue-500 dark:focus:ring-blue-400 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-600 transition-colors duration-300">
                                        <span class="ml-2.5 font-medium text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-slate-100 transition-colors duration-300">Kolam Bundar (Terpal/Bioflok)</span>
                                    </label>
                                    <label class="inline-flex items-center cursor-pointer group">
                                        <input type="radio" v-model="form.bentuk_kolam" value="Persegi" class="w-5 h-5 text-blue-600 dark:text-blue-500 focus:ring-blue-500 dark:focus:ring-blue-400 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-600 transition-colors duration-300">
                                        <span class="ml-2.5 font-medium text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-slate-100 transition-colors duration-300">Kolam Persegi (Tanah/Beton)</span>
                                    </label>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5 transition-colors duration-300">
                                            {{ form.bentuk_kolam === 'Bundar' ? 'Diameter (meter)' : 'Panjang (meter)' }}
                                        </label>
                                        <input v-model="form.panjang_m" type="number" step="0.1" class="w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-colors duration-300" placeholder="0.0" required>
                                    </div>
                                    
                                    <div v-show="form.bentuk_kolam === 'Persegi'">
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5 transition-colors duration-300">Lebar (meter)</label>
                                        <input v-model="form.lebar_m" type="number" step="0.1" class="w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-colors duration-300" placeholder="0.0">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5 transition-colors duration-300">Tinggi/Kedalaman (m)</label>
                                        <input v-model="form.kedalaman_m" type="number" step="0.1" class="w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-colors duration-300" placeholder="0.0" required>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col-reverse sm:flex-row justify-end items-stretch sm:items-center gap-3 sm:gap-4 mt-8 pt-6 border-t border-slate-200 dark:border-slate-700 transition-colors duration-300">
                                <Link href="/kolam" class="w-full sm:w-auto text-center text-slate-600 dark:text-slate-400 font-medium px-4 py-3 sm:py-2 hover:bg-slate-100 dark:hover:bg-slate-700 sm:hover:bg-transparent rounded-xl sm:rounded-none hover:text-slate-900 dark:hover:text-slate-100 transition-colors duration-300">
                                    Batal
                                </Link>
                                <button type="submit" :disabled="form.processing" class="w-full sm:w-auto text-center bg-blue-600 dark:bg-blue-500 text-white px-8 py-3.5 sm:py-3 rounded-xl font-black shadow-lg shadow-blue-200 dark:shadow-none hover:bg-blue-700 dark:hover:bg-blue-400 transition-all disabled:opacity-50">
                                    SIMPAN KOLAM
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>