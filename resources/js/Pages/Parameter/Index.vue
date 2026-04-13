<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ parameters: Array });
</script>

<template>
    <Head title="Data Kualitas Air" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pemantauan Kualitas Air Harian</h2>
                <a href="/laporan/cetak" target="_blank" class="bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-900 font-medium">Cetak PDF</a>
                <!-- <Link href="/parameter/create" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 font-medium">+ Input Data Air</Link> -->
            </div>
        </template>
        <div class="py-12"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8"><div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 border-b text-gray-600 text-sm uppercase">
                        <th class="p-4">Tanggal</th>
                        <th class="p-4">Kolam</th>
                        <th class="p-4">Suhu (°C)</th>
                        <th class="p-4">pH Air</th>
                        <th class="p-4">Visual</th>
                        <th class="p-4">Petugas</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <tr v-for="param in parameters" :key="param.id" class="border-b">
                        <td class="p-4">{{ param.tanggal_cek }}</td>
                        <td class="p-4 font-bold">{{ param.kolam.nama_kolam }}</td>
                        <td class="p-4">{{ param.suhu }}</td>
                        <td class="p-4">{{ param.ph }}</td>
                        <td class="p-4">{{ param.kondisi_visual }}</td>
                        <td class="p-4 text-sm">{{ param.user.name }}</td>
                    </tr>
                    <tr v-if="parameters.length === 0">
                        <td colspan="6" class="p-4 text-center text-gray-500 italic">Belum ada data pengecekan air.</td>
                    </tr>
                </tbody>
            </table>
        </div></div></div>
    </AuthenticatedLayout>
</template>