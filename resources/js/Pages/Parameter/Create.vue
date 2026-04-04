<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({ kolams: Array });

const form = useForm({
    kolam_id: '',
    tanggal_cek: new Date().toISOString().split('T')[0],
    suhu: '',
    ph: '',
    kondisi_visual: 'Jernih', // Nilai default
    berat_sample: '' // Tambahan untuk grafik dan update biomassa
});

const submit = () => { 
    form.post(route('parameter.store')); 
};
</script>

<template>
    <Head title="Input Parameter Air & Sample" />
    
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Input Pengecekan Air & Sample</h2>
        </template>
        
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white p-8 shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Pilih Kolam</label>
                                <select v-model="form.kolam_id" class="mt-1 block w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                                    <option value="" disabled>-- Pilih Kolam --</option>
                                    <option v-for="kolam in kolams" :key="kolam.id" :value="kolam.id">
                                        {{ kolam.nama_kolam }}
                                    </option>
                                </select>
                                <div v-if="form.errors.kolam_id" class="text-red-500 text-xs mt-1">{{ form.errors.kolam_id }}</div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tanggal Cek</label>
                                <input v-model="form.tanggal_cek" type="date" class="mt-1 block w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                                <div v-if="form.errors.tanggal_cek" class="text-red-500 text-xs mt-1">{{ form.errors.tanggal_cek }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Suhu (°C)</label>
                                <input v-model="form.suhu" type="number" step="0.1" class="mt-1 block w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: 28.5" required>
                                <div v-if="form.errors.suhu" class="text-red-500 text-xs mt-1">{{ form.errors.suhu }}</div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tingkat pH</label>
                                <input v-model="form.ph" type="number" step="0.1" class="mt-1 block w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: 7.2" required>
                                <div v-if="form.errors.ph" class="text-red-500 text-xs mt-1">{{ form.errors.ph }}</div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Kondisi Visual</label>
                                <select v-model="form.kondisi_visual" class="mt-1 block w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                                    <option value="Jernih">Jernih</option>
                                    <option value="Keruh">Keruh</option>
                                    <option value="Berbusa">Berbusa</option>
                                </select>
                                <div v-if="form.errors.kondisi_visual" class="text-red-500 text-xs mt-1">{{ form.errors.kondisi_visual }}</div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Berat Sample Ikan (gram)</label>
                            <input v-model="form.berat_sample" type="number" step="0.1" class="mt-1 block w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: 150" required>
                            <p class="text-xs text-gray-500 mt-1">Data ini otomatis memperbarui rata-rata berat di tabel Kolam dan grafik Dashboard.</p>
                            <div v-if="form.errors.berat_sample" class="text-red-500 text-xs mt-1">{{ form.errors.berat_sample }}</div>
                        </div>
                        
                        <div class="flex justify-end gap-4 mt-6">
                            <Link href="/parameter" class="text-gray-600 hover:text-gray-900 font-medium pt-2">Batal</Link>
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50" :disabled="form.processing">
                                <span v-if="form.processing">Menyimpan...</span>
                                <span v-else>Simpan Data</span>
                            </button>
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>