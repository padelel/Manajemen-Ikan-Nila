<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

// PERBAIKAN 1: feedLogs diubah jadi Object (karena data pagination formatnya object)
const props = defineProps({ 
    feedLogs: Object, 
    kolams: Array,
    filters: Object 
});

// State untuk Form Filter (Nilai default diambil dari URL jika ada)
const filterForm = ref({
    kolam_id: props.filters?.kolam_id || '',
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
});

// Watcher: Jika filter diubah, otomatis refresh data tabel tanpa loading ulang
watch(filterForm, (value) => {
    router.get('/feedlog', value, { 
        preserveState: true, 
        replace: true 
    });
}, { deep: true });

// Fungsi Reset Filter
const resetFilter = () => {
    filterForm.value.kolam_id = '';
    filterForm.value.start_date = '';
    filterForm.value.end_date = '';
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
                    <p class="text-sm text-slate-500 mt-1">Catatan harian komposisi, dosis aktual, dan rekomendasi sistem.</p>
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
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Frekuensi</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Komposisi Pakan</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Status & Dosis</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Petugas</th>
                                </tr>
                            </thead>
                            
                            <tbody class="text-sm">
                                <tr v-for="log in feedLogs.data" :key="log.id" class="border-b border-slate-50 hover:bg-slate-50/80 transition duration-200">
                                    <td class="px-6 py-5"><p class="font-medium text-slate-500">{{ log.tanggal_pakan }}</p></td>
                                    <td class="px-6 py-5"><p class="font-bold text-slate-900 text-base">{{ log.kolam.nama_kolam }}</p></td>
                                    
                                    <td class="px-6 py-5 text-center font-bold text-indigo-600 bg-indigo-50/50">
                                        {{ log.frekuensi }}x / Hari
                                    </td>
                                    
                                    <td class="px-6 py-5">
                                        <div class="flex flex-col gap-2">
                                            <div v-for="detail in log.details" :key="detail.id" class="flex items-center gap-2">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                                                <span class="font-medium text-slate-700">{{ detail.inventory?.nama_pakan }}</span>
                                                <span class="text-slate-300">—</span>
                                                <span class="font-bold text-slate-900">{{ detail.jumlah_kg }} <span class="text-[10px] text-slate-400 font-normal">Kg</span></span>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-5">
                                        <div class="flex flex-col gap-2.5">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold w-max border border-slate-200">
                                                Target AI: {{ log.rekomendasi_sistem }} Kg
                                            </span>
                                            <div class="text-sm font-bold flex items-center gap-1.5" :class="log.pakan_aktual > log.rekomendasi_sistem ? 'text-amber-600' : 'text-emerald-600'">
                                                Aktual: {{ log.pakan_aktual }} Kg
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="rounded-full w-9 h-9 flex items-center justify-center font-bold text-xs uppercase shadow-sm border"
                                                 :class="{
                                                     'bg-indigo-50 text-indigo-700 border-indigo-100': log.user_id === 1,
                                                     'bg-teal-50 text-teal-700 border-teal-100': log.user_id === 2,
                                                     'bg-slate-100 text-slate-600 border-slate-200': !log.user_id || log.user_id > 2
                                                 }">
                                                {{ getRoleInitial(log.user_id) }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-xs font-bold uppercase tracking-wider" 
                                                      :class="log.user_id === 1 ? 'text-indigo-600' : (log.user_id === 2 ? 'text-teal-600' : 'text-slate-500')">
                                                    {{ getRoleName(log.user_id) }}
                                                </span>
                                                <span v-if="log.user" class="text-sm text-slate-700 font-medium mt-0.5">
                                                    {{ log.user.name }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                
                                <tr v-if="feedLogs.data.length === 0">
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
                    
                    <div class="bg-slate-50 border-t border-slate-100 p-4 flex items-center justify-between" v-if="feedLogs.links && feedLogs.links.length > 3">
                        <div class="text-sm text-slate-500 font-medium">
                            Menampilkan <span class="font-bold text-slate-800">{{ feedLogs.from }}</span> - <span class="font-bold text-slate-800">{{ feedLogs.to }}</span> dari total <span class="font-bold text-slate-800">{{ feedLogs.total }}</span> data.
                        </div>
                        <div class="flex gap-1">
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