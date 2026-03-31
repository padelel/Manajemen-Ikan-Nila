<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({ kolams: Array });

const form = useForm({
    kolam_id: '',
    tanggal_kematian: new Date().toISOString().split('T')[0],
    jumlah_mati: '',
    catatan: ''
});

const submit = () => { form.post(route('kematian.store')); };
</script>

<template>
    <Head title="Lapor Kematian" />
    <AuthenticatedLayout>
        <template #header><h2 class="font-semibold text-xl text-gray-800 leading-tight">Form Laporan Kematian Ikan</h2></template>
        <div class="py-12"><div class="max-w-2xl mx-auto sm:px-6 lg:px-8"><div class="bg-white p-8 shadow-sm sm:rounded-lg border-t-4 border-red-500">
            <form @submit.prevent="submit" class="space-y-6">
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Pilih Kolam</label>
                        <select v-model="form.kolam_id" class="mt-1 block w-full border-gray-300 rounded-md" required>
                            <option value="" disabled>-- Pilih Kolam --</option>
                            <option v-for="kolam in kolams" :key="kolam.id" :value="kolam.id">{{ kolam.nama_kolam }} (Populasi: {{ kolam.jumlah_ikan }})</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Ditemukan</label>
                        <input v-model="form.tanggal_kematian" type="date" class="mt-1 block w-full border-gray-300 rounded-md" required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Jumlah Ikan Mati (Ekor)</label>
                    <input v-model="form.jumlah_mati" type="number" min="1" class="mt-1 block w-full border-gray-300 rounded-md focus:border-red-500 focus:ring-red-500" placeholder="Contoh: 5" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Catatan Kondisi / Dugaan Penyebab</label>
                    <textarea v-model="form.catatan" rows="3" class="mt-1 block w-full border-gray-300 rounded-md" placeholder="Contoh: Ditemukan mengambang di pojok kolam, insang pucat..."></textarea>
                </div>
                
                <div class="flex items-center justify-end gap-4 mt-6">
                    <Link href="/kematian" class="text-gray-600 hover:text-gray-900 font-medium pt-2">Batal</Link>
                    <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-md hover:bg-red-700 font-bold" :disabled="form.processing">Simpan & Kurangi Populasi</button>
                </div>
            </form>
        </div></div></div>
    </AuthenticatedLayout>
</template>