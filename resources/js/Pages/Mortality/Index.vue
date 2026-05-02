<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({ 
    logs: Object,
    kolams: Array,
    filters: Object
});

const filterForm = ref({
    kolam_id: props.filters?.kolam_id || '',
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
});

watch(filterForm, (value) => {
    router.get('/kematian', value, {
        preserveState: true, 
        replace: true 
    });
}, { deep: true });

const resetFilter = () => {
    filterForm.value.kolam_id = '';
    filterForm.value.start_date = '';
    filterForm.value.end_date = '';
};

const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};

const getRoleName = (user) => {
    if (user && user.role) return user.role === 'admin' ? 'Pengelola Utama' : 'Operator Lapangan';
    return 'Sistem Otomatis';
};

const getRoleInitial = (user) => {
    if (user && user.name) return user.name.charAt(0).toUpperCase();
    return 'S';
};
</script>

<template>
    <Head title="Data Kematian Ikan" />
    
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:justify-between md:items-end gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Riwayat Mortalitas</h2>
                    <p class="text-sm text-slate-500 mt-1">Catatan harian kematian ikan dan indikasi penyebabnya.</p>
                </div>
                
                <div class="flex flex-wrap gap-3">
                    <a href="/laporan/cetak" target="_blank" class="flex items-center gap-2 bg-white border border-slate-200 text-slate-700 px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-50 hover:text-slate-900 text-sm font-bold transition-all">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak Laporan
                    </a>
                    
                    <Link v-if="$page.props.auth.user.role === 'operator'" href="/kematian/create" class="bg-rose-600 text-white px-5 py-2.5 rounded-xl shadow-lg shadow-rose-500/30 hover:bg-rose-700 text-sm font-bold transition-all flex-shrink-0">
                        + Lapor Kematian
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Filter Section -->
                <div class="bg-white p-5 rounded-3xl shadow-[0_2px_15px_rgb(0,0,0,0.03)] border border-slate-100 flex flex-col md:flex-row gap-4 items-end">
                    <div class="w-full md:w-1/3">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Filter Kolam</label>
                        <select v-model="filterForm.kolam_id" class="w-full border-slate-200 rounded-xl text-sm focus:ring-rose-500 focus:border-rose-500 bg-slate-50/50">
                            <option value="">-- Semua Kolam --</option>
                            <option v-for="k in kolams" :key="k.id" :value="k.id">{{ k.nama_kolam }}</option>
                        </select>
                    </div>
                    <div class="w-full md:w-1/4">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Dari Tanggal</label>
                        <input type="date" v-model="filterForm.start_date" class="w-full border-slate-200 rounded-xl text-sm focus:ring-rose-500 focus:border-rose-500 bg-slate-50/50">
                    </div>
                    <div class="w-full md:w-1/4">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Sampai Tanggal</label>
                        <input type="date" v-model="filterForm.end_date" class="w-full border-slate-200 rounded-xl text-sm focus:ring-rose-500 focus:border-rose-500 bg-slate-50/50">
                    </div>
                    <div class="w-full md:w-auto">
                        <button @click="resetFilter" class="w-full md:w-auto px-5 py-2.5 bg-slate-100 text-slate-600 font-bold text-sm rounded-xl hover:bg-slate-200 transition-colors border border-slate-200">
                            Reset
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
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Jumlah Mati</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Catatan / Penyebab</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pelapor</th>
                                </tr>
                            </thead>
                            
                            <tbody class="text-sm">
                                <tr v-for="log in logs.data" :key="log.id" class="border-b border-slate-50 hover:bg-rose-50/20 transition duration-200 group">
                                    
                                    <!-- Tanggal -->
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-2 font-medium text-slate-500">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            {{ formatDate(log.tanggal || log.tanggal_kematian) }}
                                        </div>
                                    </td>
                                    
                                    <!-- Kolam -->
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-500 border border-slate-200/60">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-900 text-base">{{ log.kolam?.nama_kolam }}</p>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ log.kolam?.lokasi || '-' }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Jumlah Mati -->
                                    <td class="px-6 py-5 text-center">
                                        <div class="inline-flex flex-col items-center justify-center">
                                            <p class="font-black text-rose-600 text-xl tracking-tight">
                                                {{ log.jumlah_mati || log.jumlah }}
                                            </p>
                                            <span class="text-[10px] text-rose-500 font-bold uppercase tracking-widest bg-rose-50 px-2 py-0.5 rounded-md mt-1 border border-rose-100">
                                                Ekor
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Catatan -->
                                    <td class="px-6 py-5">
                                        <div class="bg-slate-50/50 p-3 rounded-xl border border-slate-100">
                                            <p class="text-slate-600 font-medium text-sm leading-relaxed">
                                                {{ log.catatan || 'Tidak ada catatan khusus.' }}
                                            </p>
                                        </div>
                                    </td>

                                    <!-- Pelapor -->
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="rounded-full w-9 h-9 flex items-center justify-center font-bold text-xs uppercase shadow-sm border bg-white text-slate-700 border-slate-200">
                                                {{ getRoleInitial(log.user) }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-bold text-slate-900 text-sm">
                                                    {{ log.user ? log.user.name : 'Sistem' }}
                                                </span>
                                                <span class="text-[10px] font-bold uppercase tracking-wider" :class="getRoleName(log.user) === 'Pengelola Utama' ? 'text-indigo-500' : 'text-teal-500'">
                                                    {{ getRoleName(log.user) }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <!-- State Kosong -->
                                <tr v-if="logs.data.length === 0">
                                    <td colspan="5" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="h-16 w-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 border border-slate-100">
                                                <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <p class="text-slate-700 font-bold text-base mb-1">Tidak Ada Data</p>
                                            <p class="text-slate-500 font-medium text-sm mb-3">Tidak ada catatan kematian ikan sesuai filter.</p>
                                            <button @click="resetFilter" v-if="filterForm.kolam_id || filterForm.start_date || filterForm.end_date" class="text-rose-600 font-bold hover:underline text-sm">
                                                Hapus Filter
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="bg-slate-50 border-t border-slate-100 p-4 flex items-center justify-between" v-if="logs.links && logs.links.length > 3">
                        <div class="text-sm text-slate-500 font-medium">
                            Menampilkan <span class="font-bold text-slate-800">{{ logs.from }}</span> - <span class="font-bold text-slate-800">{{ logs.to }}</span> dari total <span class="font-bold text-slate-800">{{ logs.total }}</span> data.
                        </div>
                        <div class="flex gap-1">
                            <template v-for="(link, index) in logs.links" :key="index">
                                <Link 
                                    v-if="link.url" 
                                    :href="link.url" 
                                    v-html="link.label"
                                    class="px-3.5 py-2 rounded-lg text-sm font-bold transition-all border"
                                    :class="link.active ? 'bg-rose-600 text-white border-rose-600 shadow-md shadow-rose-200' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-100'"
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