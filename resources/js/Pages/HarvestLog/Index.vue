<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    logs: Array,
    filters: Object
});

const formFilter = ref({
    jenis_panen: props.filters?.jenis_panen || 'Semua',
    tanggal_mulai: props.filters?.tanggal_mulai || '',
    tanggal_akhir: props.filters?.tanggal_akhir || '',
});

const applyFilter = () => {
    router.get(route('panen.index'), formFilter.value, {
        preserveState: true, 
        preserveScroll: true,
        replace: true
    });
};

watch(formFilter, () => {
    applyFilter();
}, { deep: true });

const resetFilter = () => {
    formFilter.value = {
        jenis_panen: 'Semua',
        tanggal_mulai: '',
        tanggal_akhir: ''
    };
};

const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};

// Mengambil nama role secara dinamis dari relasi user
const getRoleName = (user) => {
    if (user && user.role) return user.role === 'admin' ? 'Pengelola Utama' : 'Operator Lapangan';
    return 'Sistem Otomatis';
};

// Mengambil inisial nama secara dinamis
const getRoleInitial = (user) => {
    if (user && user.name) return user.name.charAt(0).toUpperCase();
    return 'S';
};
</script>

<template>
    <Head title="Riwayat Panen" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:justify-between md:items-end gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Riwayat Panen</h2>
                    <p class="text-sm text-slate-500 mt-1">Catatan histori hasil panen ikan (parsial & full) beserta total berat.</p>
                </div>
                <Link v-if="$page.props.auth.user.role === 'operator'" :href="route('panen.create')" class="px-5 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-500/30 flex-shrink-0">
                    + Catat Panen Baru
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Filter Section -->
                <div class="bg-white p-5 rounded-3xl shadow-[0_2px_15px_rgb(0,0,0,0.03)] border border-slate-100 flex flex-col md:flex-row gap-4 items-end">
                    <div class="w-full md:w-1/4">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Jenis Panen</label>
                        <select v-model="formFilter.jenis_panen" class="w-full border-slate-200 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 bg-slate-50/50">
                            <option value="Semua">Semua Jenis</option>
                            <option value="Parsial">Parsial (Sortir)</option>
                            <option value="Full">Full (Total)</option>
                        </select>
                    </div>
                    
                    <div class="w-full md:w-1/4">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Dari Tanggal</label>
                        <input type="date" v-model="formFilter.tanggal_mulai" class="w-full border-slate-200 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 bg-slate-50/50">
                    </div>
                    
                    <div class="w-full md:w-1/4">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Sampai Tanggal</label>
                        <input type="date" v-model="formFilter.tanggal_akhir" class="w-full border-slate-200 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 bg-slate-50/50">
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
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-40">Tanggal Panen</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Kolam</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Jenis Panen</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Jumlah Ikan</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Berat Total</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pencatat</th>
                                </tr>
                            </thead>
                            
                            <tbody class="text-sm">
                                <tr v-for="log in logs" :key="log.id" class="border-b border-slate-50 hover:bg-emerald-50/20 transition duration-200">
                                    
                                    <!-- Tanggal -->
                                    <td class="px-6 py-5 font-medium text-slate-500">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            {{ formatDate(log.tanggal_panen) }}
                                        </div>
                                    </td>
                                    
                                    <!-- Kolam -->
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-500 border border-blue-100/50">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-900 text-base">{{ log.kolam.nama_kolam }}</p>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ log.kolam.lokasi }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Jenis Panen -->
                                    <td class="px-6 py-5 text-center">
                                        <span :class="{
                                            'bg-emerald-50 text-emerald-700 border-emerald-200': log.jenis_panen === 'Full',
                                            'bg-amber-50 text-amber-700 border-amber-200': log.jenis_panen === 'Parsial'
                                        }" class="px-3 py-1.5 rounded-lg text-xs font-black uppercase tracking-widest border shadow-sm inline-block min-w-[80px]">
                                            {{ log.jenis_panen }}
                                        </span>
                                    </td>
                                    
                                    <!-- Jumlah Ikan -->
                                    <td class="px-6 py-5 text-center">
                                        <div class="inline-flex flex-col items-center justify-center">
                                            <p class="font-bold text-slate-700 text-lg">
                                                {{ log.jumlah_ikan.toLocaleString('id-ID') }}
                                            </p>
                                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest bg-slate-100 px-2 py-0.5 rounded-md mt-1 border border-slate-200/50">
                                                Ekor
                                            </span>
                                        </div>
                                    </td>
                                    
                                    <!-- Berat Total -->
                                    <td class="px-6 py-5 text-center">
                                        <div class="inline-flex flex-col items-center justify-center">
                                            <p class="font-black text-emerald-600 text-xl tracking-tight">
                                                {{ log.berat_total_kg.toLocaleString('id-ID') }}
                                            </p>
                                            <span class="text-[10px] text-emerald-500 font-bold uppercase tracking-widest bg-emerald-50 px-2 py-0.5 rounded-md mt-1 border border-emerald-100">
                                                Kg
                                            </span>
                                        </div>
                                    </td>
                                    
                                    <!-- Pencatat -->
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="rounded-full w-9 h-9 flex items-center justify-center font-bold text-xs uppercase border shadow-sm bg-white text-slate-700 border-slate-200">
                                                {{ getRoleInitial(log.user) }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-bold text-slate-900 text-sm">
                                                    {{ log.user ? log.user.name : 'Sistem' }}
                                                </span>
                                                <span class="text-[10px] font-bold uppercase tracking-wider" 
                                                      :class="getRoleName(log.user) === 'Pengelola Utama' ? 'text-indigo-500' : 'text-teal-500'">
                                                    {{ getRoleName(log.user) }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                </tr>
                                
                                <!-- State Kosong -->
                                <tr v-if="logs.length === 0">
                                    <td colspan="6" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="h-16 w-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 border border-slate-100">
                                                <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                                </svg>
                                            </div>
                                            <p class="text-slate-500 font-medium text-sm mb-2">Pencarian tidak menemukan hasil.</p>
                                            <button @click="resetFilter" v-if="formFilter.jenis_panen !== 'Semua' || formFilter.tanggal_mulai || formFilter.tanggal_akhir" class="text-blue-600 font-bold hover:underline text-sm">
                                                Hapus Filter
                                            </button>
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