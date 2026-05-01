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

// Fungsi Penamaan Role
const getRoleName = (userId) => {
    if (userId === 1) return 'Pengelola Utama';
    if (userId === 2) return 'Operator Lapangan';
    return 'Sistem Otomatis';
};

// Fungsi Inisial Avatar
const getRoleInitial = (userId) => {
    if (userId === 1) return 'PU';
    if (userId === 2) return 'OL';
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
                    <p class="text-sm text-slate-500 mt-1">Catatan histori panen ikan (parsial & full) beserta total berat.</p>
                </div>
                <Link v-if="$page.props.auth.user.role === 'operator'" :href="route('panen.create')" class="px-5 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-500/30 flex-shrink-0">
                    + Catat Panen Baru
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <div class="bg-white p-5 rounded-2xl shadow-[0_2px_15px_rgb(0,0,0,0.03)] border border-slate-100 flex flex-col md:flex-row gap-4 items-end">
                    <div class="w-full md:w-1/4">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Jenis Panen</label>
                        <select v-model="formFilter.jenis_panen" class="w-full border-slate-200 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="Semua">Semua Jenis</option>
                            <option value="Parsial">Parsial (Sortir)</option>
                            <option value="Full">Full (Total)</option>
                        </select>
                    </div>
                    
                    <div class="w-full md:w-1/4">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Dari Tanggal</label>
                        <input type="date" v-model="formFilter.tanggal_mulai" class="w-full border-slate-200 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div class="w-full md:w-1/4">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Sampai Tanggal</label>
                        <input type="date" v-model="formFilter.tanggal_akhir" class="w-full border-slate-200 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div class="w-full md:w-1/4">
                        <button @click="resetFilter" class="w-full px-5 py-2.5 bg-slate-100 text-slate-600 font-bold text-sm rounded-xl hover:bg-slate-200 transition-colors">
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
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Jenis Panen</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Jumlah Ikan</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Berat Total</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pencatat</th>
                                </tr>
                            </thead>
                            
                            <tbody class="text-sm">
                                <tr v-for="log in logs" :key="log.id" class="border-b border-slate-50 hover:bg-slate-50/80 transition duration-200">
                                    
                                    <td class="px-6 py-5">
                                        <p class="font-medium text-slate-500">{{ formatDate(log.tanggal_panen) }}</p>
                                    </td>
                                    
                                    <td class="px-6 py-5">
                                        <p class="font-bold text-slate-900 text-base">{{ log.kolam.nama_kolam }}</p>
                                        <p class="text-xs text-slate-400 mt-0.5">{{ log.kolam.lokasi }}</p>
                                    </td>
                                    
                                    <td class="px-6 py-5 text-center">
                                        <span :class="{
                                            'bg-emerald-50 text-emerald-600 border-emerald-100': log.jenis_panen === 'Full',
                                            'bg-amber-50 text-amber-600 border-amber-100': log.jenis_panen === 'Parsial'
                                        }" class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider border shadow-sm">
                                            {{ log.jenis_panen }}
                                        </span>
                                    </td>
                                    
                                    <td class="px-6 py-5">
                                        <p class="font-bold text-slate-700 text-sm">
                                            {{ log.jumlah_ikan.toLocaleString('id-ID') }} <span class="text-[10px] text-slate-400 font-normal uppercase">Ekor</span>
                                        </p>
                                    </td>
                                    
                                    <td class="px-6 py-5">
                                        <p class="font-black text-slate-900 text-base">
                                            {{ log.berat_total_kg.toLocaleString('id-ID') }} <span class="text-[10px] text-slate-400 font-bold uppercase">Kg</span>
                                        </p>
                                    </td>
                                    
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="rounded-full w-8 h-8 flex items-center justify-center bg-slate-100 text-slate-600 border border-slate-200 font-bold text-xs uppercase shadow-sm">
                                                {{ getRoleInitial(log.user_id) }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-xs font-bold uppercase tracking-wider" 
                                                      :class="log.user_id === 1 ? 'text-indigo-600' : (log.user_id === 2 ? 'text-teal-600' : 'text-slate-500')">
                                                    {{ getRoleName(log.user_id) }}
                                                </span>
                                                <!-- <span v-if="log.user" class="text-sm text-slate-800 font-bold mt-0.5">
                                                    {{ log.user.name }}
                                                </span> -->
                                            </div>
                                        </div>
                                    </td>

                                </tr>
                                
                                <tr v-if="logs.length === 0">
                                    <td colspan="6" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center">
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