<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ logs: Array });
</script>

<template>
    <Head title="Data Kematian Ikan" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Riwayat Kematian (Mortalitas)</h2>
                <Link href="/kematian/create" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 font-medium">+ Lapor Kematian</Link>
            </div>
        </template>
        <div class="py-12"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8"><div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 border-b text-gray-600 text-sm uppercase">
                        <th class="p-4">Tanggal</th>
                        <th class="p-4">Kolam</th>
                        <th class="p-4">Jumlah Mati</th>
                        <th class="p-4">Catatan / Penyebab</th>
                        <th class="p-4">Pelapor</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <tr v-for="log in logs" :key="log.id" class="border-b hover:bg-red-50 transition">
                        <td class="p-4">{{ log.tanggal_kematian }}</td>
                        <td class="p-4 font-bold">{{ log.kolam?.nama_kolam }}</td>
                        <td class="p-4 font-bold text-red-600">{{ log.jumlah_mati }} Ekor</td>
                        <td class="p-4 text-sm">{{ log.catatan || '-' }}</td>
                        <td class="p-4 text-sm">{{ log.user?.name }}</td>
                    </tr>
                    <tr v-if="logs.length === 0">
                        <td colspan="5" class="p-4 text-center text-gray-500 italic">Tidak ada catatan kematian. Populasi aman.</td>
                    </tr>
                </tbody>
            </table>
        </div></div></div>
    </AuthenticatedLayout>
</template>