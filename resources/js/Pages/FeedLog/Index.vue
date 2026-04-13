<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ feedLogs: Array });

// Fungsi untuk menentukan Jabatan/Role berdasarkan user_id
const getRoleName = (userId) => {
    if (userId === 1) return 'Pengelola Utama';
    if (userId === 2) return 'Operator Lapangan';
    return 'Sistem Otomatis';
};

// Fungsi untuk menentukan Inisial Avatar
const getRoleInitial = (userId) => {
    if (userId === 1) return 'PU';
    if (userId === 2) return 'OL';
    return 'S';
};
</script>

<template>
    <Head title="Riwayat Pakan" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Riwayat Pemberian Pakan</h2>

                <div class="flex gap-3">
                    <a href="/laporan/cetak" target="_blank" class="bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-900 font-medium">Cetak PDF</a>
                    <!-- <Link href="/feedlog/create" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 font-medium">+ Beri Makan Ikan</Link> -->
                </div>
            </div>
        </template>
        
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100 border-b text-gray-600 text-sm uppercase">
                                <th class="p-4 w-1/6">Tanggal</th>
                                <th class="p-4 w-1/6">Kolam</th>
                                <th class="p-4 w-1/3">Komposisi Pakan</th>
                                <th class="p-4 w-1/6">Status & Dosis</th>
                                <th class="p-4 w-1/6">Petugas</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700">
                            <tr v-for="log in feedLogs" :key="log.id" class="border-b hover:bg-gray-50">
                                <td class="px-6 py-4">{{ log.tanggal_pakan }}</td>
                                <td class="px-6 py-4 font-bold text-gray-700">{{ log.kolam.nama_kolam }}</td>
                                
                                <td class="px-6 py-4">
                                    <ul class="list-disc list-inside text-sm text-gray-600">
                                        <li v-for="detail in log.details" :key="detail.id">
                                            {{ detail.inventory.nama_pakan }} 
                                            <span class="font-semibold text-blue-600">({{ detail.jumlah_kg }} Kg)</span>
                                        </li>
                                    </ul>
                                </td>
                                
                                <td class="px-6 py-4">
                                    <div class="text-sm">
                                        <span class="bg-gray-100 px-2 py-1 rounded text-xs text-gray-500">
                                            Rekomendasi AI: {{ log.rekomendasi_sistem }} Kg
                                        </span>
                                    </div>
                                    <div class="mt-2 text-sm font-bold" :class="log.pakan_aktual > log.rekomendasi_sistem ? 'text-yellow-600' : 'text-green-600'">
                                        Aktual: {{ log.pakan_aktual }} Kg
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="rounded-full w-9 h-9 flex items-center justify-center font-bold text-xs uppercase"
                                             :class="{
                                                 'bg-purple-100 text-purple-700': log.user_id === 1,
                                                 'bg-teal-100 text-teal-700': log.user_id === 2,
                                                 'bg-gray-100 text-gray-600': !log.user_id || log.user_id > 2
                                             }">
                                            {{ getRoleInitial(log.user_id) }}
                                        </div>
                                        
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold" 
                                                  :class="log.user_id === 1 ? 'text-purple-700' : (log.user_id === 2 ? 'text-teal-700' : 'text-gray-700')">
                                                {{ getRoleName(log.user_id) }}
                                            </span>
                                            <span v-if="log.user" class="text-xs text-gray-500 font-medium mt-0.5">
                                                {{ log.user.name }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            
                            <tr v-if="feedLogs.length === 0">
                                <td colspan="5" class="p-8 text-center text-gray-500 italic">
                                    Belum ada riwayat pemberian pakan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>