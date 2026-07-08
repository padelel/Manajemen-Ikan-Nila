<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import moment from 'moment';

defineProps({
    tikets: Array,
});

// Fungsi untuk format tanggal
const formatTime = (date) => moment(date).format('DD MMM YYYY HH:mm');

// Fungsi untuk menentukan warna badge status
const getStatusBadge = (status) => {
    switch(status) {
        case 'open': return 'bg-red-100 text-red-800 border-red-300';
        case 'in_progress': return 'bg-yellow-100 text-yellow-800 border-yellow-300';
        case 'menunggu_verifikasi': return 'bg-blue-100 text-blue-800 border-blue-300';
        case 'selesai': return 'bg-green-100 text-green-800 border-green-300';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const getStatusText = (status) => {
    return status.replace('_', ' ').toUpperCase();
};
</script>

<template>
    <Head title="Daftar Tiket Mitigasi" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manajemen Tiket Mitigasi Darurat</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="$page.props.flash?.success" class="mb-4 bg-green-100 text-green-700 p-4 rounded shadow-sm">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.flash?.warning" class="mb-4 bg-yellow-100 text-yellow-800 p-4 rounded shadow-sm">
                    {{ $page.props.flash.warning }}
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto p-6">
                        <table class="w-full text-sm text-left text-gray-500 border">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b">
                                <tr>
                                    <th class="px-6 py-4">Waktu Dibuat</th>
                                    <th class="px-6 py-4">Kolam</th>
                                    <th class="px-6 py-4">Masalah (Diagnosa AI)</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th class="px-6 py-4 text-center">Deadline</th>
                                    <th class="px-6 py-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="tiket in tikets" :key="tiket.id" class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ formatTime(tiket.created_at) }}</td>
                                    <td class="px-6 py-4 font-bold">{{ tiket.kolam?.nama_kolam }}</td>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ tiket.judul }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span :class="getStatusBadge(tiket.status)" class="px-2 py-1 text-xs font-bold rounded-full border">
                                            {{ getStatusText(tiket.status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center text-red-600 font-semibold">{{ formatTime(tiket.deadline) }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <Link :href="route('tiket.show', tiket.id)" class="text-white bg-indigo-600 hover:bg-indigo-700 px-3 py-1.5 rounded shadow-sm text-xs font-bold transition">
                                            Buka Detail
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="tikets.length === 0">
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">Tidak ada tiket mitigasi aktif.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>