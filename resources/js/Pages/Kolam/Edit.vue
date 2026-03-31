<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

// Menerima data kolam yang dikirim dari Controller
const props = defineProps({
    kolam: Object
});

// Memasukkan data lama ke dalam form secara otomatis
const form = useForm({
    nama_kolam: props.kolam.nama_kolam,
    dimensi: props.kolam.dimensi,
    jumlah_ikan: props.kolam.jumlah_ikan,
    berat_rata_gram: props.kolam.berat_rata_gram,
    deskripsi: props.kolam.deskripsi
});

// Mengirim data menggunakan method PUT
const submit = () => {
    form.put(`/kolam/${props.kolam.id}`);
};
</script>

<template>
    <Head title="Edit Kolam" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Data Kolam</h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8">
                    
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama Kolam</label>
                            <input v-model="form.nama_kolam" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Dimensi</label>
                            <input v-model="form.dimensi" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Populasi (Ekor)</label>
                                <input v-model="form.jumlah_ikan" type="number" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Berat Rata-rata (Gram)</label>
                                <input v-model="form.berat_rata_gram" type="number" step="0.01" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Deskripsi / Keterangan</label>
                            <textarea v-model="form.deskripsi" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-4 mt-6">
                            <Link href="/kolam" class="text-gray-600 hover:text-gray-900 font-medium">Batal</Link>
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md shadow hover:bg-blue-700 disabled:opacity-50" :disabled="form.processing">
                                Update Data
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>