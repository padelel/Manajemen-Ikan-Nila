<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    kolam: Object,
});

const form = useForm({
    nama_kolam: props.kolam.nama_kolam,
    lokasi: props.kolam.lokasi,
    status_kolam: props.kolam.status_kolam || 'Persiapan',
    bentuk_kolam: props.kolam.bentuk_kolam,
    panjang_m: props.kolam.panjang_m,
    lebar_m: props.kolam.lebar_m,
    kedalaman_m: props.kolam.kedalaman_m,
});

// Auto-sinkronisasi Lebar jika Bundar
watch([() => form.panjang_m, () => form.bentuk_kolam], () => {
    if (form.bentuk_kolam === 'Bundar') {
        form.lebar_m = form.panjang_m;
    }
});

const submit = () => {
    form.put(route('kolam.update', props.kolam.id), { 
        preserveScroll: true 
    });
};
</script>

<template>
    <Head title="Update Kolam" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-100 leading-tight transition-colors duration-300">
                Update Data Kolam
            </h2>
        </template>
        
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <!-- Main Card -->
                <div class="bg-white dark:bg-slate-800 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] sm:rounded-3xl border border-slate-100 dark:border-slate-700 transition-colors duration-300">
            
                    <div class="p-8">
                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <!-- Alert Error -->
                            <div v-if="Object.keys(form.errors).length > 0" class="p-4 bg-rose-50 dark:bg-rose-500/10 border-l-4 border-rose-500 text-rose-700 dark:text-rose-400 rounded-xl transition-colors duration-300">
                                <p class="font-bold text-sm">Gagal menyimpan data! Periksa isian berikut:</p>
                                <ul class="list-disc pl-5 mt-1 text-sm">
                                    <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
                                </ul>
                            </div>

                            <!-- Section 1 -->
                            <h3 class="text-lg font-bold border-b border-slate-200 dark:border-slate-700 pb-2 text-slate-800 dark:text-slate-200 transition-colors duration-300">
                                1. Identitas Kolam
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1 transition-colors duration-300">Nama/Kode Kolam</label>
                                    <input v-model="form.nama_kolam" type="text" class="w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-colors duration-300" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1 transition-colors duration-300">Lokasi / Blok</label>
                                    <input v-model="form.lokasi" type="text" class="w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-colors duration-300" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1 transition-colors duration-300">Status Kolam</label>
                                    <select v-model="form.status_kolam" class="w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-colors duration-300" required>
                                        <option value="Aktif">Aktif</option>
                                        <option value="Persiapan">Persiapan / Kuras</option>
                                        <option value="Istirahat">Istirahat / Kosong</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Section 2 -->
                            <h3 class="text-lg font-bold border-b border-slate-200 dark:border-slate-700 pb-2 text-slate-800 dark:text-slate-200 mt-8 transition-colors duration-300">
                                2. Dimensi Fisik
                            </h3>
                            
                            <div class="flex gap-6 mb-4">
                                <label class="inline-flex items-center cursor-pointer group">
                                    <input type="radio" v-model="form.bentuk_kolam" value="Bundar" class="text-blue-600 dark:text-blue-500 focus:ring-blue-500 dark:focus:ring-blue-400 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-600 transition-colors duration-300">
                                    <span class="ml-2 font-medium text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-slate-100 transition-colors duration-300">Kolam Bundar (Terpal/Bioflok)</span>
                                </label>
                                <label class="inline-flex items-center cursor-pointer group">
                                    <input type="radio" v-model="form.bentuk_kolam" value="Persegi" class="text-blue-600 dark:text-blue-500 focus:ring-blue-500 dark:focus:ring-blue-400 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-600 transition-colors duration-300">
                                    <span class="ml-2 font-medium text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-slate-100 transition-colors duration-300">Kolam Persegi (Tanah/Beton)</span>
                                </label>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1 transition-colors duration-300">
                                        {{ form.bentuk_kolam === 'Bundar' ? 'Diameter (meter)' : 'Panjang (meter)' }}
                                    </label>
                                    <input v-model="form.panjang_m" type="number" step="0.1" class="w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-colors duration-300" required>
                                </div>
                                
                                <div v-show="form.bentuk_kolam === 'Persegi'">
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1 transition-colors duration-300">Lebar (meter)</label>
                                    <input v-model="form.lebar_m" type="number" step="0.1" class="w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-colors duration-300">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1 transition-colors duration-300">Tinggi/Kedalaman (m)</label>
                                    <input v-model="form.kedalaman_m" type="number" step="0.1" class="w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-colors duration-300" required>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="flex justify-end items-center gap-4 mt-8 pt-6 border-t border-slate-200 dark:border-slate-700 transition-colors duration-300">
                                <Link href="/kolam" class="text-slate-600 dark:text-slate-400 font-medium px-4 py-2 hover:text-slate-900 dark:hover:text-slate-100 transition-colors duration-300">
                                    Batal
                                </Link>
                                <button type="submit" :disabled="form.processing" class="bg-blue-600 dark:bg-blue-500 text-white px-8 py-3 rounded-xl font-black shadow-lg shadow-blue-200 dark:shadow-none hover:bg-blue-700 dark:hover:bg-blue-400 transition-all disabled:opacity-50">
                                    UPDATE DATA
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>