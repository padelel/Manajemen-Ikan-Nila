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
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight transition-colors duration-300">Riwayat Panen</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors duration-300">Catatan histori hasil panen ikan (parsial & full) beserta total berat.</p>
                </div>
                <Link v-if="$page.props.auth.user.role === 'operator'" :href="route('panen.create')" class="px-5 py-2.5 bg-blue-600 dark:bg-blue-500 text-white text-sm font-bold rounded-xl hover:bg-blue-700 dark:hover:bg-blue-400 transition-all shadow-lg shadow-blue-500/30 dark:shadow-none flex-shrink-0">
                    + Catat Panen Baru
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl shadow-[0_2px_15px_rgb(0,0,0,0.03)] dark:shadow-[0_2px_15px_rgb(0,0,0,0.2)] border border-slate-100 dark:border-slate-700 flex flex-col md:flex-row gap-4 items-end transition-colors duration-300">
                    <div class="w-full md:w-1/4">
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 transition-colors duration-300">Jenis Panen</label>
                        <select v-model="formFilter.jenis_panen" class="w-full border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500 dark:focus:border-blue-400 bg-slate-50/50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 transition-colors duration-300">
                            <option value="Semua">Semua Jenis</option>
                            <option value="Parsial">Parsial (Sortir)</option>
                            <option value="Full">Full (Total)</option>
                        </select>
                    </div>
                    
                    <div class="w-full md:w-1/4">
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 transition-colors duration-300">Dari Tanggal</label>
                        <input type="date" v-model="formFilter.tanggal_mulai" class="w-full border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500 dark:focus:border-blue-400 bg-slate-50/50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 transition-colors duration-300">
                    </div>
                    
                    <div class="w-full md:w-1/4">
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 transition-colors duration-300">Sampai Tanggal</label>
                        <input type="date" v-model="formFilter.tanggal_akhir" class="w-full border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500 dark:focus:border-blue-400 bg-slate-50/50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 transition-colors duration-300">
                    </div>
                    
                    <div class="w-full md:w-1/4">
                        <button @click="resetFilter" class="w-full px-5 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-sm rounded-xl hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors border border-slate-200 dark:border-slate-600">
                            Reset Filter
                        </button>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:rounded-3xl border border-slate-100 dark:border-slate-700 transition-colors duration-300">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50/50 dark:bg-slate-700/50 border-b border-slate-100 dark:border-slate-700 transition-colors duration-300">
                                <tr>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest w-40 transition-colors">Tanggal Panen</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest transition-colors">Kolam</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest text-center transition-colors">Jenis Panen</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest text-center transition-colors">Jumlah Ikan</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest text-center transition-colors">Berat Total</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest transition-colors">Pencatat</th>
                                </tr>
                            </thead>
                            
                            <tbody class="text-sm">
                                <tr v-for="log in logs" :key="log.id" class="border-b border-slate-50 dark:border-slate-700/50 hover:bg-emerald-50/20 dark:hover:bg-emerald-500/5 transition duration-200 group">
                                    
                                    <td class="px-6 py-5 font-medium text-slate-500 dark:text-slate-400 transition-colors">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            {{ formatDate(log.tanggal_panen) }}
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center text-blue-500 dark:text-blue-400 border border-blue-100/50 dark:border-blue-500/20 transition-colors">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-900 dark:text-slate-100 text-base transition-colors">{{ log.kolam.nama_kolam }}</p>
                                                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-0.5 transition-colors">{{ log.kolam.lokasi }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-5 text-center">
                                        <span :class="{
                                            'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20': log.jenis_panen === 'Full',
                                            'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-500/20': log.jenis_panen === 'Parsial'
                                        }" class="px-3 py-1.5 rounded-lg text-xs font-black uppercase tracking-widest border shadow-sm inline-block min-w-[80px] transition-colors duration-300">
                                            {{ log.jenis_panen }}
                                        </span>
                                    </td>
                                    
                                    <td class="px-6 py-5 text-center">
                                        <div class="inline-flex flex-col items-center justify-center">
                                            <p class="font-bold text-slate-700 dark:text-slate-200 text-lg transition-colors">
                                                {{ log.jumlah_ikan.toLocaleString('id-ID') }}
                                            </p>
                                            <span class="text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-widest bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-md mt-1 border border-slate-200/50 dark:border-slate-600 transition-colors">
                                                Ekor
                                            </span>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-5 text-center">
                                        <div class="inline-flex flex-col items-center justify-center">
                                            <p class="font-black text-emerald-600 dark:text-emerald-400 text-xl tracking-tight transition-colors">
                                                {{ log.berat_total_kg.toLocaleString('id-ID') }}
                                            </p>
                                            <span class="text-[10px] text-emerald-500 dark:text-emerald-400 font-bold uppercase tracking-widest bg-emerald-50 dark:bg-emerald-500/10 px-2 py-0.5 rounded-md mt-1 border border-emerald-100 dark:border-emerald-500/20 transition-colors">
                                                Kg
                                            </span>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="rounded-full w-9 h-9 flex items-center justify-center font-bold text-xs uppercase border shadow-sm bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-600 transition-colors">
                                                {{ getRoleInitial(log.user) }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-bold text-slate-900 dark:text-slate-100 text-sm transition-colors">
                                                    {{ log.user ? log.user.name : 'Sistem' }}
                                                </span>
                                                <span class="text-[10px] font-bold uppercase tracking-wider transition-colors" 
                                                      :class="getRoleName(log.user) === 'Pengelola Utama' ? 'text-indigo-500 dark:text-indigo-400' : 'text-teal-500 dark:text-teal-400'">
                                                    {{ getRoleName(log.user) }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                </tr>
                                
                                <tr v-if="logs.length === 0">
                                    <td colspan="6" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="h-16 w-16 bg-slate-50 dark:bg-slate-700 rounded-full flex items-center justify-center mb-4 border border-slate-100 dark:border-slate-600 transition-colors duration-300">
                                                <svg class="w-8 h-8 text-slate-300 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                                </svg>
                                            </div>
                                            <p class="text-slate-500 dark:text-slate-400 font-medium text-sm mb-2 transition-colors">Pencarian tidak menemukan hasil.</p>
                                            <button @click="resetFilter" v-if="formFilter.jenis_panen !== 'Semua' || formFilter.tanggal_mulai || formFilter.tanggal_akhir" class="text-blue-600 dark:text-blue-400 font-bold hover:underline text-sm transition-colors">
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