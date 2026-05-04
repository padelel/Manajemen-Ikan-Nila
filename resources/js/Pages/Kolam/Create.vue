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
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pendaftaran Kolam Baru</h2>
        </template>
        
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg">
            
                    <div class="p-8">
                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <div v-if="Object.keys(form.errors).length > 0" class="p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-md">
                                <p class="font-bold text-sm">Gagal menyimpan data! Periksa isian berikut:</p>
                                <ul class="list-disc pl-5 mt-1 text-sm">
                                    <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
                                </ul>
                            </div>

                            <h3 class="text-lg font-bold border-b pb-2 text-gray-700">1. Identitas Kolam</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nama/Kode Kolam</label>
                                    <input v-model="form.nama_kolam" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" placeholder="Misal: Kolam A1" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Lokasi / Blok</label>
                                    <input v-model="form.lokasi" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" placeholder="Misal: Blok Utara" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Status Kolam</label>
                                    <select v-model="form.status_kolam" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                                        <option value="Aktif">Aktif</option>
                                        <option value="Persiapan">Persiapan / Kuras</option>
                                        <option value="Istirahat">Istirahat / Kosong</option>
                                    </select>
                                </div>
                            </div>

                            <h3 class="text-lg font-bold border-b pb-2 text-gray-700 mt-8">2. Dimensi Fisik</h3>
                            
                            <div class="flex gap-4 mb-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" v-model="form.bentuk_kolam" value="Bundar" class="text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 font-medium text-gray-700">Kolam Bundar (Terpal/Bioflok)</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" v-model="form.bentuk_kolam" value="Persegi" class="text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 font-medium text-gray-700">Kolam Persegi (Tanah/Beton)</span>
                                </label>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">
                                        {{ form.bentuk_kolam === 'Bundar' ? 'Diameter (meter)' : 'Panjang (meter)' }}
                                    </label>
                                    <input v-model="form.panjang_m" type="number" step="0.1" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" placeholder="0.0" required>
                                </div>
                                
                                <div v-show="form.bentuk_kolam === 'Persegi'">
                                    <label class="block text-sm font-medium text-gray-700">Lebar (meter)</label>
                                    <input v-model="form.lebar_m" type="number" step="0.1" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" placeholder="0.0">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tinggi/Kedalaman (meter)</label>
                                    <input v-model="form.kedalaman_m" type="number" step="0.1" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" placeholder="0.0" required>
                                </div>
                            </div>

                            <div class="flex justify-end gap-4 mt-8 pt-4 border-t border-gray-200">
                                <Link href="/kolam" class="text-gray-600 font-medium px-4 py-2 hover:text-gray-900">Batal</Link>
                                <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-black shadow-lg hover:bg-blue-700 transition" :disabled="form.processing">
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