<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({ kolams: Array });

const form = useForm({
    kolam_id: '',
    tanggal_cek: new Date().toISOString().split('T')[0],
    suhu: '',
    ph: '',
    kondisi_visual: 'Jernih' // Nilai default
});

const submit = () => { form.post(route('parameter.store')); };
</script>

<template>
    <Head title="Input Parameter Air" />
    <AuthenticatedLayout>
        <template #header><h2 class="font-semibold text-xl text-gray-800 leading-tight">Input Pengecekan Air</h2></template>
        <div class="py-12"><div class="max-w-2xl mx-auto sm:px-6 lg:px-8"><div class="bg-white p-8 shadow-sm sm:rounded-lg">
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Pilih Kolam</label>
                        <select v-model="form.kolam_id" class="mt-1 block w-full border-gray-300 rounded-md" required>
                            <option value="" disabled>-- Pilih Kolam --</option>
                            <option v-for="kolam in kolams" :key="kolam.id" :value="kolam.id">{{ kolam.nama_kolam }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Cek</label>
                        <input v-model="form.tanggal_cek" type="date" class="mt-1 block w-full border-gray-300 rounded-md" required>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Suhu (°C)</label>
                        <input v-model="form.suhu" type="number" step="0.1" class="mt-1 block w-full border-gray-300 rounded-md" placeholder="Contoh: 28.5" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tingkat pH</label>
                        <input v-model="form.ph" type="number" step="0.1" class="mt-1 block w-full border-gray-300 rounded-md" placeholder="Contoh: 7.2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kondisi Visual</label>
                        <select v-model="form.kondisi_visual" class="mt-1 block w-full border-gray-300 rounded-md" required>
                            <option value="Jernih">Jernih</option>
                            <option value="Keruh">Keruh</option>
                            <option value="Berbusa">Berbusa</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end gap-4 mt-6">
                    <Link href="/parameter" class="text-gray-600 hover:text-gray-900 font-medium pt-2">Batal</Link>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700" :disabled="form.processing">Simpan Data</button>
                </div>
            </form>
        </div></div></div>
    </AuthenticatedLayout>
</template>