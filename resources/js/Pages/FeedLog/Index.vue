<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ logs: Array });
</script>

<template>
    <Head title="Riwayat Pakan" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Riwayat Pemberian Pakan</h2>

                <div class="flex gap-3">
                    <a href="/laporan/cetak" target="_blank" class="bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-900 font-medium">Cetak PDF</a>
                    <Link href="/feedlog/create" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 font-medium">+ Beri Makan Ikan</Link>
                </div>
            </div>
        </template>
        <div class="py-12"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8"><div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 border-b text-gray-600 text-sm uppercase">
                        <th class="p-4">Tanggal</th>
                        <th class="p-4">Kolam</th>
                        <th class="p-4">Rule Pakar</th>
                        <th class="p-4">Saran Sistem</th>
                        <th class="p-4">Aktual Diberikan</th>
                        <th class="p-4">Jenis Pakan</th>
                        <th class="p-4">Petugas</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <tr v-for="log in logs" :key="log.id" class="border-b">
                        <td class="p-4">{{ log.tanggal_pakan }}</td>
                        <td class="p-4 font-bold">{{ log.kolam?.nama_kolam }}</td>
                        <td class="p-4"><span class="bg-yellow-100 text-yellow-800 py-1 px-2 rounded text-xs font-bold">{{ log.rule?.kode_rule }}</span></td>
                        <td class="p-4">{{ log.rekomendasi_sistem }} Kg</td>
                        <td class="p-4 font-bold text-green-600">{{ log.pakan_aktual }} Kg</td>
                        <td class="p-4 text-sm">{{ log.inventory?.nama_pakan }}</td>
                        <td class="p-4 text-sm">{{ log.user?.name }}</td>
                    </tr>
                    <tr v-if="logs.length === 0">
                        <td colspan="7" class="p-4 text-center text-gray-500 italic">Belum ada riwayat pemberian pakan.</td>
                    </tr>
                </tbody>
            </table>
        </div></div></div>
    </AuthenticatedLayout>
</template>