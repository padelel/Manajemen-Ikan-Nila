<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({ 
    logs: Array,
    kolams: Array,
    filters: Object
});

// State untuk Filter
const filterKolam = ref(props.filters.kolam_id || '');
const filterStartDate = ref(props.filters.start_date || '');
const filterEndDate = ref(props.filters.end_date || '');

// Watcher: Saat nilai filter berubah, otomatis panggil backend
watch([filterKolam, filterStartDate, filterEndDate], () => {
    router.get('/operasi-harian', { // Sesuaikan route url Anda, misal '/operasi-harian' atau '/feedlog'
        kolam_id: filterKolam.value,
        start_date: filterStartDate.value,
        end_date: filterEndDate.value
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
}, { deep: true });

// Fungsi Reset Filter
const resetFilter = () => {
    filterKolam.value = '';
    filterStartDate.value = '';
    filterEndDate.value = '';
};

// Fungsi Avatar Pelapor
const getRoleName = (userId) => {
    if (userId === 1) return 'Pengelola Utama';
    if (userId === 2) return 'Operator Lapangan';
    return 'Sistem Otomatis';
};

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
            <div class="flex justify-between items-end">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Riwayat Pakan Harian</h2>
                    <p class="text-sm text-slate-500 mt-1">Pantau jadwal, jenis, dan frekuensi pemberian pakan ikan.</p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="bg-white p-5 shadow-[0_4px_20px_rgb(0,0,0,0.03)] sm:rounded-2xl border border-slate-100 flex flex-col md:flex-row gap-4 items-end">
                    <div class="w-full md:w-1/4">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Filter Kolam</label>
                        <select v-model="filterKolam" class="w-full border-slate-200 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">Semua Kolam</option>
                            <option v-for="kolam in kolams" :key="kolam.id" :value="kolam.id">
                                {{ kolam.nama_kolam }}
                            </option>
                        </select>
                    </div>

                    <div class="w-full md:w-1/4">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Dari Tanggal</label>
                        <input type="date" v-model="filterStartDate" class="w-full border-slate-200 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>

                    <div class="w-full md:w-1/4">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Sampai Tanggal</label>
                        <input type="date" v-model="filterEndDate" class="w-full border-slate-200 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>

                    <div class="w-full md:w-auto">
                        <button @click="resetFilter" class="w-full md:w-auto bg-slate-100 text-slate-600 px-5 py-2.5 rounded-xl hover:bg-slate-200 font-bold text-sm transition-all">
                            Reset Filter
                        </button>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:rounded-3xl border border-slate-100">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-gray-100 border-b">
                                <tr>
                                    <th class="p-4">Tanggal</th>
                                    <th class="p-4">Kolam</th>
                                    <th class="p-4 text-center">Frekuensi</th> <th class="p-4 text-center">Total Pakan (Kg)</th>
                                    <th class="p-4">Detail Bahan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="log in logs" :key="log.id" class="border-b hover:bg-gray-50">
                                    <td class="p-4">{{ log.tanggal_pakan }}</td>
                                    <td class="p-4 font-bold">{{ log.kolam?.nama_kolam }}</td>
                                    <td class="p-4 text-center">{{ log.frekuensi }}x / Hari</td> <td class="p-4 text-center text-blue-600 font-bold">{{ log.pakan_aktual }} Kg</td>
                                    <td class="p-4 text-xs">
                                        <div v-for="detail in log.details" :key="detail.id">
                                            • {{ detail.inventory?.nama_pakan }} ({{ detail.jumlah_kg }} Kg)
                                        </div>
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