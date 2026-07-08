<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

defineProps({
    riwayat: Array,
});
</script>

<template>
    <Head title="Riwayat Kualitas Air" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Riwayat Pengecekan Kualitas Air</h2>
                <!-- Tombol ini hanya muncul untuk Operator -->
                <Link v-if="$page.props.auth.user.role === 'operator'" :href="route('parameter.create')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow">
                    + Catat Parameter Baru
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Notifikasi Flash Message (Akan muncul jika ada return -> with() dari Controller) -->
                <div v-if="$page.props.flash?.success" class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm" role="alert">
                    <p class="font-bold">Berhasil</p>
                    <p>{{ $page.props.flash.success }}</p>
                </div>
                <div v-if="$page.props.flash?.warning" class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm" role="alert">
                    <p class="font-bold">PERINGATAN DARURAT!</p>
                    <p>{{ $page.props.flash.warning }}</p>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto p-6">
                        <table class="w-full text-sm text-left text-gray-500 border">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b">
                                <tr>
                                    <th scope="col" class="px-6 py-4">Waktu Pengecekan</th>
                                    <th scope="col" class="px-6 py-4">Nama Kolam</th>
                                    <th scope="col" class="px-6 py-4">Data Parameter Air</th>
                                    <th scope="col" class="px-6 py-4">Hasil Diagnosa AI</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in riwayat" :key="item.id" class="bg-white border-b hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                        {{ item.tanggal_cek }}
                                    </td>
                                    <td class="px-6 py-4 font-bold text-indigo-600">
                                        {{ item.kolam?.nama_kolam || 'Kolam Dihapus' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <ul class="list-disc pl-4 space-y-1 text-gray-700">
                                            <li><span class="font-semibold">Suhu:</span> {{ item.suhu }} °C</li>
                                            <li><span class="font-semibold">pH:</span> {{ item.ph }}</li>
                                            <li><span class="font-semibold">DO:</span> {{ item.do_mgl }} mg/L</li>
                                            <li><span class="font-semibold">Amonia:</span> {{ item.amonia_mgl }} mg/L</li>
                                            <li><span class="font-semibold">Flok:</span> {{ item.flok_ml }} ml/L</li>
                                            <li><span class="font-semibold">Kecerahan:</span> {{ item.kecerahan_cm }} cm</li>
                                        </ul>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div v-if="item.inferensi_log">
                                            <!-- Badge Status (Hijau jika normal, Merah jika bahaya) -->
                                            <span 
                                                class="px-3 py-1 text-xs font-bold rounded-full border"
                                                :class="item.inferensi_log.kode_diagnosa === 'D-NORMAL' ? 'bg-green-100 text-green-800 border-green-300' : 'bg-red-100 text-red-800 border-red-300'"
                                            >
                                                {{ item.inferensi_log.kode_diagnosa }} - {{ item.inferensi_log.label_diagnosa }}
                                            </span>
                                            
                                            <!-- Menampilkan Peringatan dan Tiket Jika Air Tidak Normal -->
                                            <div v-if="item.inferensi_log.kode_diagnosa !== 'D-NORMAL'" class="mt-3 p-3 bg-gray-50 rounded-md border border-gray-200">
                                                <p class="text-xs text-gray-800">
                                                    <span class="font-bold text-red-600">Alert:</span> {{ item.inferensi_log.peringatan }}
                                                </p>
                                                <div class="mt-2 flex items-center text-xs text-indigo-600 font-bold">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                                    Tiket Mitigasi Diterbitkan!
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else>
                                            <span class="text-gray-400 italic">Data belum dianalisis</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="riwayat.length === 0">
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                        <p class="text-lg font-medium">Belum ada data pengecekan kualitas air.</p>
                                        <p class="text-sm mt-1">Operator lapangan belum melakukan input parameter harian.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>