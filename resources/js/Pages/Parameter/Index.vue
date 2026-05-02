<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({ 
    parameters: Object,
    kolams: Array,
    filters: Object
});

const filterForm = ref({
    kolam_id: props.filters?.kolam_id || '',
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
});

watch(filterForm, (value) => {
    router.get('/parameter', value, { 
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

// Fungsi Styling pH (Ideal Nila: 6.5 - 8.5)
const getPhStyle = (ph) => {
    if (ph >= 6.5 && ph <= 8.5) {
        return 'bg-emerald-50 text-emerald-700 border-emerald-200 shadow-sm';
    }
    return 'bg-rose-50 text-rose-700 border-rose-200 shadow-sm animate-pulse';
};

// Fungsi Styling Kondisi Visual
const getVisualConditionStyle = (kondisi) => {
    const k = kondisi?.toLowerCase() || '';
    if (k.includes('jernih')) return { class: 'bg-emerald-50 text-emerald-700 border-emerald-200', dot: 'bg-emerald-500' };
    if (k.includes('keruh')) return { class: 'bg-amber-50 text-amber-700 border-amber-200', dot: 'bg-amber-500' };
    if (k.includes('busa') || k.includes('berbusa')) return { class: 'bg-rose-50 text-rose-700 border-rose-200', dot: 'bg-rose-500' };
    return { class: 'bg-slate-50 text-slate-700 border-slate-200', dot: 'bg-slate-400' };
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
    <Head title="Data Kualitas Air" />
    
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:justify-between md:items-end gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Pemantauan Kualitas Air</h2>
                    <p class="text-sm text-slate-500 mt-1">Riwayat pengecekan suhu, pH, dan kondisi visual kolam harian.</p>
                </div>
                
                <div class="flex gap-3">
                    <a href="/laporan/cetak" target="_blank" class="flex items-center justify-center gap-2 bg-white border border-slate-200 text-slate-700 px-4 py-2.5 rounded-xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] hover:bg-slate-50 hover:text-slate-900 text-sm font-bold transition-all">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak Laporan
                    </a>
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
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-40">Tanggal</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Kolam</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Suhu Air</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">pH Air</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Kondisi Visual</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Petugas</th>
                                </tr>
                            </thead>
                            
                            <tbody class="text-sm">
                                <tr v-for="param in parameters.data" :key="param.id" class="border-b border-slate-50 hover:bg-slate-50/80 transition duration-200 group">
                                    
                                    <!-- Tanggal -->
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-2 text-slate-500 font-medium">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            {{ formatDate(param.tanggal_cek) }}
                                        </div>
                                    </td>
                                    
                                    <!-- Kolam -->
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="h-9 w-9 rounded-xl bg-cyan-50 flex items-center justify-center text-cyan-500 border border-cyan-100/50 shrink-0">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                                </svg>
                                            </div>
                                            <p class="font-bold text-slate-900 text-base">{{ param.kolam?.nama_kolam }}</p>
                                        </div>
                                    </td>

                                    <!-- Suhu -->
                                    <td class="px-6 py-5 text-center">
                                        <div class="inline-flex flex-col items-center">
                                            <span :class="(param.suhu >= 25 && param.suhu <= 32) ? 'bg-emerald-50 text-emerald-700 border-emerald-200 shadow-sm' : 'bg-rose-50 text-rose-700 border-rose-200 shadow-sm animate-pulse'" class="inline-flex items-center justify-center gap-0.5 px-3 py-1.5 rounded-lg border font-bold transition-colors">
                                                <span class="text-base">{{ param.suhu }}</span>
                                                <span class="text-xs opacity-70 font-semibold mt-0.5">°C</span>
                                            </span>
                                            <span v-if="param.suhu < 25" class="text-[9px] text-rose-500 font-bold uppercase mt-1">Terlalu Dingin</span>
                                            <span v-else-if="param.suhu > 32" class="text-[9px] text-rose-500 font-bold uppercase mt-1">Terlalu Panas</span>
                                        </div>
                                    </td>

                                    <!-- pH Air -->
                                    <td class="px-6 py-5 text-center">
                                        <div class="inline-flex flex-col items-center">
                                            <span :class="getPhStyle(param.ph)" class="inline-flex items-center justify-center min-w-[3.5rem] h-9 rounded-xl font-bold border">
                                                {{ param.ph }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Kondisi Visual -->
                                    <td class="px-6 py-5">
                                        <span :class="getVisualConditionStyle(param.kondisi_visual).class" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold capitalize border shadow-sm w-max">
                                            <span :class="getVisualConditionStyle(param.kondisi_visual).dot" class="w-2 h-2 rounded-full"></span>
                                            {{ param.kondisi_visual }}
                                        </span>
                                    </td>

                                    <!-- Petugas -->
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="rounded-full w-9 h-9 flex items-center justify-center font-bold text-xs uppercase shadow-sm border bg-white text-slate-700 border-slate-200">
                                                {{ getRoleInitial(param.user_id) }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-bold text-slate-900 text-sm">
                                                    {{ param.user ? param.user.name : 'Sistem' }}
                                                </span>
                                                <span class="text-[10px] font-bold uppercase tracking-wider" 
                                                      :class="getRoleName(param.user_id) === 'Pengelola Utama' ? 'text-indigo-500' : 'text-teal-500'">
                                                    {{ getRoleName(param.user_id) }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <!-- State Kosong -->
                                <tr v-if="parameters.data.length === 0">
                                    <td colspan="6" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="h-16 w-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 border border-slate-100">
                                                <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                                </svg>
                                            </div>
                                            <p class="text-slate-500 font-medium text-sm mb-2">Pencarian tidak menemukan riwayat parameter.</p>
                                            <button @click="resetFilter" v-if="filterForm.kolam_id || filterForm.start_date || filterForm.end_date" class="text-blue-600 font-bold hover:underline text-sm">Hapus Filter</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="bg-slate-50 border-t border-slate-100 p-4 flex flex-col md:flex-row items-center justify-between gap-4" v-if="parameters.links && parameters.links.length > 3">
                        <div class="text-sm text-slate-500 font-medium">
                            Menampilkan <span class="font-bold text-slate-800">{{ parameters.from }}</span> - <span class="font-bold text-slate-800">{{ parameters.to }}</span> dari total <span class="font-bold text-slate-800">{{ parameters.total }}</span> data.
                        </div>
                        <div class="flex flex-wrap gap-1 justify-center">
                            <template v-for="(link, index) in parameters.links" :key="index">
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