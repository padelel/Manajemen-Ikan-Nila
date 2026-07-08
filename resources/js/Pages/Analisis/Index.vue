<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    statistik: Object,
    performa_kolam: Array,
});
</script>

<template>
    <Head title="Laporan & Analisis" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Laporan & Analisis Tambak</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Ringkasan Statistik -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                        <div class="text-sm text-gray-500 font-semibold uppercase">Total Panen</div>
                        <div class="text-3xl font-bold text-gray-900 mt-2">{{ statistik.total_panen_kg }} <span class="text-lg text-gray-600">Kg</span></div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
                        <div class="text-sm text-gray-500 font-semibold uppercase">Total Kematian</div>
                        <div class="text-3xl font-bold text-gray-900 mt-2">{{ statistik.total_kematian_ekor }} <span class="text-lg text-gray-600">Ekor</span></div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                        <div class="text-sm text-gray-500 font-semibold uppercase">Mitigasi Selesai</div>
                        <div class="text-3xl font-bold text-gray-900 mt-2">{{ statistik.tiket_selesai }} <span class="text-lg text-gray-600">Kasus</span></div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
                        <div class="text-sm text-gray-500 font-semibold uppercase">Mitigasi Aktif</div>
                        <div class="text-3xl font-bold text-gray-900 mt-2">{{ statistik.tiket_aktif }} <span class="text-lg text-gray-600">Kasus</span></div>
                    </div>
                </div>

                <!-- Tabel Performa Kolam -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Performa & Riwayat Masalah per Kolam</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500 border">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b">
                                    <tr>
                                        <th class="px-6 py-4">Nama Kolam</th>
                                        <th class="px-6 py-4 text-center">Total Kematian (Ekor)</th>
                                        <th class="px-6 py-4 text-center">Masalah Air (Deteksi AI)</th>
                                        <th class="px-6 py-4 text-right">Total Panen (Kg)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="kolam in performa_kolam" :key="kolam.id" class="border-b hover:bg-gray-50">
                                        <td class="px-6 py-4 font-bold text-gray-900">{{ kolam.nama_kolam }}</td>
                                        <td class="px-6 py-4 text-center font-medium text-red-600">{{ kolam.total_kematian }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 font-bold rounded-full text-xs">
                                                {{ kolam.jumlah_masalah_air }} Kasus
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right font-bold text-green-600">{{ kolam.total_panen_kg }} Kg</td>
                                    </tr>
                                    <tr v-if="performa_kolam.length === 0">
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada data kolam.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>