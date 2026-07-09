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
            if (numberValue < 20.0) {
                return { label: 'Terlalu dingin', severity: 'danger' };
            }
            if (numberValue < 25.0) {
                return { label: 'Agak rendah', severity: 'warning' };
            }
            if (numberValue <= 30.0) {
                return { label: 'Normal', severity: 'normal' };
            }
            if (numberValue <= 33.0) {
                return { label: 'Agak tinggi', severity: 'warning' };
            }
            return { label: 'Terlalu panas', severity: 'danger' };
        case 'ph':
            if (numberValue < 6.0) {
                return { label: 'Terlalu rendah', severity: 'danger' };
            }
            if (numberValue < 6.5) {
                return { label: 'Agak rendah', severity: 'warning' };
            }
            if (numberValue <= 8.5) {
                return { label: 'Normal', severity: 'normal' };
            }
            if (numberValue <= 9.0) {
                return { label: 'Agak tinggi', severity: 'warning' };
            }
            return { label: 'Terlalu tinggi', severity: 'danger' };
        case 'do_mgl':
            if (numberValue < 3.0) {
                return { label: 'Sangat rendah', severity: 'danger' };
            }
            if (numberValue < 5.0) {
                return { label: 'Rendah', severity: 'warning' };
            }
            return { label: 'Normal', severity: 'normal' };
        case 'amonia_mgl':
            if (numberValue <= 0.0) {
                return { label: 'Normal', severity: 'normal' };
            }
            if (numberValue <= 0.05) {
                return { label: 'Sedikit tinggi', severity: 'warning' };
            }
            return { label: 'Terlalu tinggi', severity: 'danger' };
        case 'flok_ml':
            if (numberValue < 15.0) {
                return { label: 'Terlalu rendah', severity: 'danger' };
            }
            if (numberValue <= 30.0) {
                return { label: 'Normal', severity: 'normal' };
            }
            return { label: 'Terlalu tinggi', severity: 'warning' };
        case 'kecerahan_cm':
            if (numberValue < 30.0) {
                return { label: 'Terlalu keruh', severity: 'danger' };
            }
            if (numberValue <= 40.0) {
                return { label: 'Normal', severity: 'normal' };
            }
            return { label: 'Terlalu tinggi', severity: 'warning' };
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
                                            <li>
                                                <span class="font-semibold">Kecerahan:</span>
                                                {{ item.kecerahan_cm }} cm
                                                <span :class="statusBadgeClasses(getParameterStatus('kecerahan_cm', item.kecerahan_cm))">
                                                    {{ getParameterStatus('kecerahan_cm', item.kecerahan_cm).label }}
                                                </span>
                                            </li>
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