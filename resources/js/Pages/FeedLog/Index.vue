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
                            <thead class="bg-slate-50/50 border-b border-slate-100">
                                <tr>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tanggal</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Kolam</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Frekuensi</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Total Pakan</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Detail Pakan</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pelapor</th>
                                </tr>
                            </thead>
                            
                            <tbody class="text-sm">
                                <tr v-for="log in logs" :key="log.id" class="border-b border-slate-50 hover:bg-blue-50/30 transition duration-200">
                                    
                                    <td class="px-6 py-5 font-medium text-slate-500">{{ log.tanggal_pakan }}</td>
                                    
                                    <td class="px-6 py-5">
                                        <p class="font-bold text-slate-900 text-base">{{ log.kolam?.nama_kolam }}</p>
                                    </td>

                                    <td class="px-6 py-5 text-center">
                                        <span class="inline-block bg-indigo-50 text-indigo-700 px-3 py-1 rounded-lg text-xs font-bold border border-indigo-100">
                                            {{ log.frekuensi || '-' }} Kali / Hari
                                        </span>
                                    </td>

                                    <td class="px-6 py-5 text-center">
                                        <span class="inline-flex items-center justify-center gap-1 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 border border-blue-100 font-bold">
                                            <span class="text-base">{{ log.pakan_aktual }}</span>
                                            <span class="text-xs font-medium opacity-70">Kg</span>
                                        </span>
                                    </td>

                                    <td class="px-6 py-5">
                                        <ul class="list-disc pl-4 text-xs text-slate-600 space-y-1">
                                            <li v-for="detail in log.feed_log_details" :key="detail.id">
                                                <span class="font-bold">{{ detail.inventory?.nama_pakan }}</span> 
                                                ({{ detail.jumlah_kg }} Kg)
                                            </li>
                                        </ul>
                                    </td>

                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="rounded-full w-9 h-9 flex items-center justify-center font-bold text-xs uppercase border"
                                                 :class="log.user_id === 1 ? 'bg-indigo-50 text-indigo-700 border-indigo-100' : 'bg-teal-50 text-teal-700 border-teal-100'">
                                                {{ getRoleInitial(log.user_id) }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-xs font-bold uppercase text-slate-400 tracking-wider">{{ getRoleName(log.user_id) }}</span>
                                                <span class="text-sm text-slate-700 font-medium">{{ log.user?.name }}</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="logs.length === 0">
                                    <td colspan="6" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-slate-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                            <p class="text-slate-700 font-bold text-base mb-1">Data Tidak Ditemukan</p>
                                            <p class="text-slate-500 font-medium text-sm">Tidak ada riwayat pakan yang sesuai dengan filter yang dipilih.</p>
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