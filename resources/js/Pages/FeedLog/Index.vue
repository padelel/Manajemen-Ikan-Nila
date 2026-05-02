<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({ 
    feedLogs: Object, 
    kolams: Array,
    filters: Object 
});

const filterForm = ref({
    kolam_id: props.filters?.kolam_id || '',
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
});

watch(filterForm, (value) => {
    router.get('/feedlog', value, { 
        preserveState: true, 
        preserveScroll: true,
        replace: true 
    });
}, { deep: true });

const resetFilter = () => {
    filterForm.value.kolam_id = '';
    filterForm.value.start_date = '';
    filterForm.value.end_date = '';
};

// Fungsi Format Tanggal
const formatDate = (dateString) => {
    if (!dateString) return '-';
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};

// Fungsi Evaluasi Status Dosis Pakan
const getStatusDosis = (aktual, rekomendasi) => {
    const margin = 0.1; // Toleransi perbedaan 0.1 Kg
    const selisih = aktual - rekomendasi;

    if (Math.abs(selisih) <= margin) {
        return { label: 'Sesuai Target', class: 'bg-emerald-50 text-emerald-700 border-emerald-200' };
    } else if (selisih > margin) {
        return { label: 'Berlebih (Over)', class: 'bg-amber-50 text-amber-700 border-amber-200' };
    } else {
        return { label: 'Kurang (Under)', class: 'bg-rose-50 text-rose-700 border-rose-200' };
    }
};

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
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Riwayat Pemberian Pakan</h2>
                    <p class="text-sm text-slate-500 mt-1">Pantau dosis aktual, komposisi pakan, dan tingkat kepatuhan terhadap sistem.</p>
                </div>
            </div>
        </template>
        
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Filter Section -->
                <div class="bg-white p-5 rounded-3xl shadow-[0_2px_15px_rgb(0,0,0,0.03)] border border-slate-100 flex flex-col md:flex-row gap-4 items-end">
                    <div class="w-full md:w-1/3">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Filter Kolam</label>
                        <select v-model="filterForm.kolam_id" class="w-full border-slate-200 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 bg-slate-50/50">
                            <option value="">Semua Kolam</option>
                            <option v-for="k in kolams" :key="k.id" :value="k.id">{{ k.nama_kolam }}</option>
                        </select>
                    </div>
                    <div class="w-full md:w-1/4">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Dari Tanggal</label>
                        <input type="date" v-model="filterForm.start_date" class="w-full border-slate-200 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 bg-slate-50/50">
                    </div>
                    <div class="w-full md:w-1/4">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Sampai Tanggal</label>
                        <input type="date" v-model="filterForm.end_date" class="w-full border-slate-200 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 bg-slate-50/50">
                    </div>
                    <div class="w-full md:w-1/4">
                        <button @click="resetFilter" class="w-full px-5 py-2.5 bg-slate-100 text-slate-600 font-bold text-sm rounded-xl hover:bg-slate-200 transition-colors border border-slate-200">
                            Reset Filter
                        </button>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="bg-white overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:rounded-3xl border border-slate-100">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50/50 border-b border-slate-100">
                                <tr>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-36">Tanggal</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Kolam</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Komposisi Pakan</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Evaluasi Dosis</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Petugas</th>
                                </tr>
                            </thead>
                            
                            <tbody class="text-sm">
                                <tr v-for="log in feedLogs.data" :key="log.id" class="border-b border-slate-50 hover:bg-slate-50/80 transition duration-200">
                                    
                                    <!-- Tanggal -->
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-2 text-slate-500 font-medium">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            {{ formatDate(log.tanggal_pakan) }}
                                        </div>
                                    </td>
                                    
                                    <!-- Kolam -->
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="h-9 w-9 rounded-xl bg-blue-50 flex items-center justify-center text-blue-500 border border-blue-100/50 shrink-0">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-900 text-base leading-tight">{{ log.kolam.nama_kolam }}</p>
                                                <p class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest mt-0.5">{{ log.frekuensi }}x Pemberian</p>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Komposisi Pakan -->
                                    <td class="px-6 py-5">
                                        <div class="bg-slate-50/80 border border-slate-100 rounded-xl p-3 inline-block min-w-[200px]">
                                            <div class="flex flex-col gap-1.5">
                                                <div v-for="detail in log.details" :key="detail.id" class="flex justify-between items-center gap-4 text-xs">
                                                    <div class="flex items-center gap-2 truncate">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300 shrink-0"></span>
                                                        <span class="font-semibold text-slate-600 truncate">{{ detail.inventory?.nama_pakan }}</span>
                                                    </div>
                                                    <span class="font-black text-slate-800 whitespace-nowrap">{{ detail.jumlah_kg }} <span class="text-[10px] text-slate-400 font-bold uppercase">Kg</span></span>
                                                </div>
                                                <div v-if="!log.details || log.details.length === 0" class="text-xs text-slate-400 italic">
                                                    Rincian tidak tersedia
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Evaluasi Dosis -->
                                    <td class="px-6 py-5">
                                        <div class="flex flex-col gap-2.5">
                                            <span :class="getStatusDosis(log.pakan_aktual, log.rekomendasi_sistem).class" class="inline-flex items-center justify-center px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-widest w-max border shadow-sm">
                                                {{ getStatusDosis(log.pakan_aktual, log.rekomendasi_sistem).label }}
                                            </span>
                                            <div class="flex items-center gap-3 text-xs">
                                                <div class="flex flex-col">
                                                    <span class="text-[10px] text-slate-400 font-bold uppercase">Aktual</span>
                                                    <span class="font-black text-slate-900">{{ log.pakan_aktual }} <span class="text-[10px] text-slate-500">Kg</span></span>
                                                </div>
                                                <span class="text-slate-300">/</span>
                                                <div class="flex flex-col">
                                                    <span class="text-[10px] text-slate-400 font-bold uppercase">Target AI</span>
                                                    <span class="font-black text-slate-500">{{ log.rekomendasi_sistem }} <span class="text-[10px] text-slate-400">Kg</span></span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Petugas -->
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="rounded-full w-9 h-9 flex items-center justify-center font-bold text-xs uppercase shadow-sm border bg-white text-slate-700 border-slate-200">
                                                {{ getRoleInitial(log.user_id) }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-bold text-slate-900 text-sm">
                                                    {{ log.user ? log.user.name : 'Sistem' }}
                                                </span>
                                                <span class="text-[10px] font-bold uppercase tracking-wider" 
                                                      :class="getRoleName(log.user_id) === 'Pengelola Utama' ? 'text-indigo-500' : 'text-teal-500'">
                                                    {{ getRoleName(log.user_id) }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                
                                <!-- State Kosong -->
                                <tr v-if="feedLogs.data.length === 0">
                                    <td colspan="5" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="h-16 w-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 border border-slate-100">
                                                <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                                </svg>
                                            </div>
                                            <p class="text-slate-500 font-medium text-sm mb-2">Pencarian tidak menemukan hasil pakan.</p>
                                            <button @click="resetFilter" v-if="filterForm.kolam_id || filterForm.start_date || filterForm.end_date" class="text-blue-600 font-bold hover:underline text-sm">Hapus Filter</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="bg-slate-50 border-t border-slate-100 p-4 flex flex-col md:flex-row items-center justify-between gap-4" v-if="feedLogs.links && feedLogs.links.length > 3">
                        <div class="text-sm text-slate-500 font-medium">
                            Menampilkan <span class="font-bold text-slate-800">{{ feedLogs.from }}</span> - <span class="font-bold text-slate-800">{{ feedLogs.to }}</span> dari total <span class="font-bold text-slate-800">{{ feedLogs.total }}</span> data.
                        </div>
                        <div class="flex flex-wrap gap-1 justify-center">
                            <template v-for="(link, index) in feedLogs.links" :key="index">
                                <Link 
                                    v-if="link.url" 
                                    :href="link.url" 
                                    v-html="link.label"
                                    class="px-3.5 py-2 rounded-lg text-sm font-semibold transition-all border"
                                    :class="link.active ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-200' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-100'"
                                />
                                <span 
                                    v-else 
                                    v-html="link.label" 
                                    class="px-3.5 py-2 rounded-lg text-sm font-semibold text-slate-400 bg-slate-50 border border-slate-100 cursor-not-allowed">
                                </span>
                            </template>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>