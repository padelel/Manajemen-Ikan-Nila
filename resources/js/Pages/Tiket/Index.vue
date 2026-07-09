<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import moment from 'moment';
import { ref, watch } from 'vue';

const props = defineProps({
    tikets: Array,
    filters: Object,
    kolams: Array,
});

const formFilter = ref({
    kolam_id: props.filters?.kolam_id || '',
});

const applyFilter = () => {
    router.get(route('tiket.index'), formFilter.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

watch(
    formFilter,
    () => {
        applyFilter();
    },
    { deep: true },
);

const resetFilter = () => {
    formFilter.value = {
        kolam_id: '',
    };
};

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

                <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-200 mb-6 transition-colors duration-300">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Filter Kolam</label>
                            <select
                                v-model="formFilter.kolam_id"
                                class="w-full border border-slate-200 rounded-2xl text-sm focus:ring-blue-500 focus:border-blue-500 bg-slate-50 text-slate-900 transition-colors duration-300 px-4 py-3"
                            >
                                <option value="">Semua Kolam</option>
                                <option
                                    v-for="kolam in kolams || []"
                                    :key="kolam.id"
                                    :value="kolam.id"
                                >
                                    {{ kolam.nama_kolam }}
                                </option>
                            </select>
                        </div>
                        <div class="md:w-1/3">
                            <button
                                @click="resetFilter"
                                type="button"
                                class="w-full px-5 py-3 bg-slate-900 text-white font-bold text-sm rounded-2xl hover:bg-slate-700 transition-colors border border-slate-800"
                            >
                                Reset Filter
                            </button>
                        </div>
                    </div>
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