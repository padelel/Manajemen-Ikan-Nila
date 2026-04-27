<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

// PERBAIKAN 1: parameters diubah menjadi Object, dan tambah kolams & filters
const props = defineProps({ 
    parameters: Object,
    kolams: Array,
    filters: Object
});

// State untuk Filter
const filterForm = ref({
    kolam_id: props.filters?.kolam_id || '',
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
});

// Watcher untuk Filter Otomatis
watch(filterForm, (value) => {
    router.get('/parameter', value, { // Pastikan URL ini sesuai dengan route Anda (misal: /parameter atau /kualitas-air)
        preserveState: true, 
        replace: true 
    });
}, { deep: true });

// Fungsi Reset
const resetFilter = () => {
    filterForm.value.kolam_id = '';
    filterForm.value.start_date = '';
    filterForm.value.end_date = '';
};

// Fungsi Role
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
            <div class="flex justify-between items-end">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Pemantauan Kualitas Air</h2>
                    <p class="text-sm text-slate-500 mt-1">Riwayat pengecekan suhu, pH, dan kondisi visual kolam harian.</p>
                </div>
                
                <div class="flex gap-3">
                    <a href="/laporan/cetak" target="_blank" class="flex items-center gap-2 bg-white border border-slate-200 text-slate-700 px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-50 hover:text-slate-900 text-sm font-semibold transition-all">
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
                
                <div class="bg-white p-5 rounded-2xl shadow-[0_2px_15px_rgb(0,0,0,0.03)] border border-slate-100 flex flex-col md:flex-row gap-4 items-end">
                    <div class="w-full md:w-1/3">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Filter Kolam</label>
                        <select v-model="filterForm.kolam_id" class="w-full border-slate-200 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Semua Kolam --</option>
                            <option v-for="k in kolams" :key="k.id" :value="k.id">{{ k.nama_kolam }}</option>
                        </select>
                    </div>
                    <div class="w-full md:w-1/4">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Dari Tanggal</label>
                        <input type="date" v-model="filterForm.start_date" class="w-full border-slate-200 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="w-full md:w-1/4">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Sampai Tanggal</label>
                        <input type="date" v-model="filterForm.end_date" class="w-full border-slate-200 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <button @click="resetFilter" class="px-5 py-2.5 bg-slate-100 text-slate-600 font-bold text-sm rounded-xl hover:bg-slate-200 transition-colors">
                            Reset
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
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Suhu</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">pH Air</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Kondisi Visual</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Petugas</th>
                                </tr>
                            </thead>
                            
                            <tbody class="text-sm">
                                <tr v-for="param in parameters.data" :key="param.id" class="border-b border-slate-50 hover:bg-slate-50/80 transition duration-200 group">
                                    <td class="px-6 py-5"><p class="font-medium text-slate-500">{{ param.tanggal_cek }}</p></td>
                                    <td class="px-6 py-5"><p class="font-bold text-slate-900 text-base">{{ param.kolam?.nama_kolam }}</p></td>

                                    <td class="px-6 py-5 text-center">
                                        <span :class="(param.suhu >= 25 && param.suhu <= 32) ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200'" class="inline-flex items-center justify-center gap-0.5 px-3 py-1.5 rounded-lg border font-bold transition-colors">
                                            <span class="text-base">{{ param.suhu }}</span>
                                            <span class="text-xs opacity-70 font-semibold mt-0.5">°C</span>
                                        </span>
                                    </td>

                                    <td class="px-6 py-5 text-center">
                                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-slate-100 text-slate-700 font-bold border border-slate-200">
                                            {{ param.ph }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-5">
                                        <span class="inline-block px-3 py-1.5 bg-slate-100 text-slate-600 rounded-lg text-xs font-semibold capitalize">
                                            {{ param.kondisi_visual }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="rounded-full w-9 h-9 flex items-center justify-center font-bold text-xs uppercase shadow-sm border"
                                                 :class="{
                                                     'bg-indigo-50 text-indigo-700 border-indigo-100': param.user_id === 1,
                                                     'bg-teal-50 text-teal-700 border-teal-100': param.user_id === 2,
                                                     'bg-slate-100 text-slate-600 border-slate-200': !param.user_id || param.user_id > 2
                                                 }">
                                                {{ getRoleInitial(param.user_id) }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-xs font-bold uppercase tracking-wider" :class="param.user_id === 1 ? 'text-indigo-600' : (param.user_id === 2 ? 'text-teal-600' : 'text-slate-500')">
                                                    {{ getRoleName(param.user_id) }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="parameters.data.length === 0">
                                    <td colspan="6" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <p class="text-slate-500 font-medium text-sm mb-2">Pencarian tidak menemukan hasil.</p>
                                            <button @click="resetFilter" v-if="filterForm.kolam_id || filterForm.start_date" class="text-blue-600 font-bold hover:underline text-sm">Hapus Filter</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="bg-slate-50 border-t border-slate-100 p-4 flex items-center justify-between" v-if="parameters.links && parameters.links.length > 3">
                        <div class="text-sm text-slate-500 font-medium">
                            Menampilkan <span class="font-bold text-slate-800">{{ parameters.from }}</span> - <span class="font-bold text-slate-800">{{ parameters.to }}</span> dari total <span class="font-bold text-slate-800">{{ parameters.total }}</span> data.
                        </div>
                        <div class="flex gap-1">
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