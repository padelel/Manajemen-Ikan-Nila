<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

// Menerima data 'kolams' yang dikirim dari KolamController
defineProps({
    kolams: Array
});

// Fungsi konfirmasi hapus data
const hapusKolam = (id, nama) => {
    if (confirm(`Apakah Anda yakin ingin menghapus data ${nama}?`)) {
        router.delete(`/kolam/${id}`);
    }
};
</script>

<template>
    <Head title="Manajemen Kolam" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Data Kolam & Biomassa</h2>
                <Link href="/kolam/create" class="bg-blue-600 text-white px-4 py-2 rounded-md shadow hover:bg-blue-700 text-sm font-medium transition">
                    + Tambah Kolam
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-100 border-b text-gray-600 text-sm uppercase tracking-wider">
                                    <th class="p-4 font-semibold">Nama Kolam</th>
                                    <th class="p-4 font-semibold">Dimensi</th>
                                    <th class="p-4 font-semibold">Populasi (Ekor)</th>
                                    <th class="p-4 font-semibold">Berat Rata-rata</th>
                                    <th class="p-4 font-semibold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                <tr v-for="kolam in kolams" :key="kolam.id" class="border-b hover:bg-gray-50 transition">
                                    <td class="p-4 font-bold text-gray-900">{{ kolam.nama_kolam }}</td>
                                    <td class="p-4">{{ kolam.dimensi }}</td>
                                    <td class="p-4">
                                        <span class="bg-green-100 text-green-800 py-1 px-3 rounded-full text-xs font-semibold">
                                            {{ kolam.jumlah_ikan.toLocaleString('id-ID') }}
                                        </span>
                                    </td>
                                    <td class="p-4">{{ kolam.berat_rata_gram }} Gram</td>
                                    <td class="p-4 flex gap-3">
                                        <Link :href="'/kolam/' + kolam.id + '/edit'" class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                                            Edit
                                        </Link>

                                        <button @click="hapusKolam(kolam.id, kolam.nama_kolam)" class="text-red-500 hover:text-red-700 font-medium text-sm">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="kolams.length === 0">
                                    <td colspan="5" class="p-4 text-center text-gray-500 italic">Belum ada data kolam.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>