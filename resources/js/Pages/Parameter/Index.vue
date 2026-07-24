<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    riwayat: Array,
    kolams: Array,
    filters: Object,
});

const filterForm = ref({
    kolam_id: props.filters?.kolam_id || '',
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
});

watch(filterForm, (value) => {
    router.get(route('parameter.index'), value, {
        preserveState: true,
        replace: true,
    });
}, { deep: true });

const resetFilter = () => {
    filterForm.value.kolam_id = '';
    filterForm.value.start_date = '';
    filterForm.value.end_date = '';
};

const getParameterStatus = (key, value) => {
    const numberValue = Number(value);

    if (Number.isNaN(numberValue)) {
        return { label: 'Tidak tersedia', severity: 'neutral' };
    }

    switch (key) {
        case 'suhu':
            if (numberValue < 27.0) {
                return { label: 'Rendah', severity: 'warning' };
            }
            if (numberValue <= 32.0) {
                return { label: 'Normal', severity: 'normal' };
            }
            return { label: 'Tinggi', severity: 'warning' };
        case 'ph':
            if (numberValue < 5.5) {
                return { label: 'Rendah', severity: 'danger' };
            }
            if (numberValue <= 8.5) {
                return { label: 'Normal', severity: 'normal' };
            }
            return { label: 'Tinggi', severity: 'danger' };
        case 'do_mgl':
            if (numberValue < 5.0) {
                return { label: 'Rendah', severity: 'warning' };
            }
            return { label: 'Normal', severity: 'normal' };
        case 'amonia_mgl':
            if (numberValue < 0.01) {
                return { label: 'Normal', severity: 'normal' };
            }
            return { label: 'Tinggi', severity: 'warning' };
        case 'flok_ml':
            if (numberValue < 15.0) {
                return { label: 'Rendah', severity: 'warning' };
            }
            if (numberValue <= 40.0) {
                return { label: 'Normal', severity: 'normal' };
            }
            return { label: 'Tinggi', severity: 'warning' };
        default:
            return { label: 'Tidak diketahui', severity: 'neutral' };
    }
};

const statusBadgeClasses = (status) => {
    const base = 'ml-2 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold';

    switch (status.severity) {
        case 'normal':
            return `${base} bg-green-100 text-green-800 border border-green-200`;
        case 'warning':
            return `${base} bg-yellow-100 text-yellow-800 border border-yellow-200`;
        case 'danger':
            return `${base} bg-red-100 text-red-800 border border-red-200`;
        default:
            return `${base} bg-gray-100 text-gray-700 border border-gray-200`;
    }
};
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
                
                <!-- Filter Kolam dan Tanggal -->
                <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100 mb-6">
                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Filter Kolam</label>
                            <select v-model="filterForm.kolam_id" class="w-full border border-slate-200 rounded-2xl text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50 text-slate-900 px-4 py-3">
                                <option value="">-- Semua Kolam --</option>
                                <option v-for="k in kolams" :key="k.id" :value="k.id">{{ k.nama_kolam }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Dari Tanggal</label>
                            <input type="date" v-model="filterForm.start_date" class="w-full border border-slate-200 rounded-2xl text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50 text-slate-900 px-4 py-3" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Sampai Tanggal</label>
                            <input type="date" v-model="filterForm.end_date" class="w-full border border-slate-200 rounded-2xl text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50 text-slate-900 px-4 py-3" />
                        </div>
                        <div class="flex items-end">
                            <button type="button" @click="resetFilter" class="w-full px-5 py-3 bg-slate-100 text-slate-600 font-bold text-sm rounded-2xl hover:bg-slate-200 transition-colors border border-slate-200">
                                Reset
                            </button>
                        </div>
                    </div>
                </div>

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
                                    <th v-if="$page.props.auth.user.role === 'supervisor'" scope="col" class="px-6 py-4 text-center">Detail</th>
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
                                            <li>
                                                <span class="font-semibold">Suhu:</span>
                                                {{ item.suhu }} °C
                                                <span :class="statusBadgeClasses(getParameterStatus('suhu', item.suhu))">
                                                    {{ getParameterStatus('suhu', item.suhu).label }}
                                                </span>
                                            </li>
                                            <li>
                                                <span class="font-semibold">pH:</span>
                                                {{ item.ph }}
                                                <span :class="statusBadgeClasses(getParameterStatus('ph', item.ph))">
                                                    {{ getParameterStatus('ph', item.ph).label }}
                                                </span>
                                            </li>
                                            <li>
                                                <span class="font-semibold">DO:</span>
                                                {{ item.do_mgl }} mg/L
                                                <span :class="statusBadgeClasses(getParameterStatus('do_mgl', item.do_mgl))">
                                                    {{ getParameterStatus('do_mgl', item.do_mgl).label }}
                                                </span>
                                            </li>
                                            <li>
                                                <span class="font-semibold">Amonia:</span>
                                                {{ item.amonia_mgl }} mg/L
                                                <span :class="statusBadgeClasses(getParameterStatus('amonia_mgl', item.amonia_mgl))">
                                                    {{ getParameterStatus('amonia_mgl', item.amonia_mgl).label }}
                                                </span>
                                            </li>
                                            <li>
                                                <span class="font-semibold">Flok:</span>
                                                {{ item.flok_ml }} ml/L
                                                <span :class="statusBadgeClasses(getParameterStatus('flok_ml', item.flok_ml))">
                                                    {{ getParameterStatus('flok_ml', item.flok_ml).label }}
                                                </span>
                                            </li>
                                        </ul>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div v-if="item.inferensi_log">
                                            <div class="flex flex-wrap gap-1">
                                                <template v-if="Array.isArray(item.inferensi_log.kode_diagnosa)">
                                                    <span v-for="(kd, idx) in item.inferensi_log.kode_diagnosa" :key="idx"
                                                        class="inline-block px-3 py-1 text-xs font-bold rounded-full border"
                                                        :class="kd === 'DN' ? 'bg-green-100 text-green-800 border-green-300' : 'bg-red-100 text-red-800 border-red-300'"
                                                    >
                                                        {{ kd }} - {{ item.inferensi_log.label_diagnosa?.[idx] || '' }}
                                                    </span>
                                                </template>
                                                <span v-else
                                                    class="inline-block px-3 py-1 text-xs font-bold rounded-full border"
                                                    :class="item.inferensi_log.kode_diagnosa === 'DN' ? 'bg-green-100 text-green-800 border-green-300' : 'bg-red-100 text-red-800 border-red-300'"
                                                >
                                                    {{ item.inferensi_log.kode_diagnosa }} - {{ item.inferensi_log.label_diagnosa }}
                                                </span>
                                            </div>
                                            
                                            <template v-if="Array.isArray(item.inferensi_log.kode_diagnosa)">
                                                <div v-if="!item.inferensi_log.kode_diagnosa.includes('DN')" class="mt-3 p-3 bg-gray-50 rounded-md border border-gray-200">
                                                    <div v-for="(p, idx) in (Array.isArray(item.inferensi_log.peringatan) ? item.inferensi_log.peringatan : [item.inferensi_log.peringatan])" :key="idx" class="text-xs text-gray-800">
                                                        <span class="font-bold text-red-600">Alert {{ idx + 1 }}:</span> {{ p }}
                                                    </div>
                                                    <div class="mt-2 flex items-center text-xs text-indigo-600 font-bold">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                                        Tiket Mitigasi Diterbitkan!
                                                    </div>
                                                </div>
                                            </template>
                                            <template v-else>
                                                <div v-if="item.inferensi_log.kode_diagnosa !== 'DN'" class="mt-3 p-3 bg-gray-50 rounded-md border border-gray-200">
                                                    <p class="text-xs text-gray-800">
                                                        <span class="font-bold text-red-600">Alert:</span> {{ item.inferensi_log.peringatan }}
                                                    </p>
                                                    <div class="mt-2 flex items-center text-xs text-indigo-600 font-bold">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                                        Tiket Mitigasi Diterbitkan!
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                        <div v-else>
                                            <span class="text-gray-400 italic">Data belum dianalisis</span>
                                        </div>
                                    </td>
                                    <td v-if="$page.props.auth.user.role === 'supervisor'" class="px-6 py-4 text-center">
                                        <Link :href="route('parameter.show', item.id)" class="text-white bg-indigo-600 hover:bg-indigo-700 px-3 py-1.5 rounded shadow-sm text-xs font-bold transition inline-flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            Detail
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="riwayat.length === 0">
                                    <td :colspan="$page.props.auth.user.role === 'supervisor' ? 5 : 4" class="px-6 py-8 text-center text-gray-500">
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