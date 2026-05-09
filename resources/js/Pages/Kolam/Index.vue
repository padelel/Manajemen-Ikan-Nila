<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

// Tambahkan prop 'logs' untuk menerima data riwayat dari controller
const props = defineProps({
    kolams: Array,
    logs: Array 
});

// Mengambil data user yang sedang login via Inertia Page Props
const page = usePage();
const isAdmin = computed(() => page.props.auth.user?.role === 'admin');

const hapusKolam = (id, nama) => {
    if (confirm(`Apakah Anda yakin ingin menghapus data ${nama}?`)) {
        router.delete(`/kolam/${id}`);
    }
};

// Helper format tanggal untuk log
const formatDate = (dateString) => {
    if (!dateString) return '-';
    const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};
</script>

<template>
    <Head title="Manajemen Kolam" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight transition-colors">Data Kolam</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors">Kelola data fisik kolam, dimensi, dan jumlah populasi ikan.</p>
                </div>
                <Link v-if="isAdmin" href="/kolam/create" class="inline-flex justify-center w-full sm:w-auto bg-slate-900 dark:bg-indigo-600 text-white px-5 py-2.5 rounded-xl shadow-lg shadow-slate-200/50 dark:shadow-none hover:bg-slate-800 dark:hover:bg-indigo-500 text-sm font-semibold transition-all">
                    + Tambah Kolam
                </Link>
            </div>
        </template>

        <div class="py-6 sm:py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 sm:space-y-12">
                
                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:rounded-3xl border border-slate-100 dark:border-slate-700 transition-colors duration-300">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50/50 dark:bg-slate-700/50 border-b border-slate-100 dark:border-slate-700 transition-colors">
                                <tr>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest w-48 whitespace-nowrap">Kolam</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest text-center whitespace-nowrap">Status</th> 
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest text-center whitespace-nowrap">Bentuk</th> 
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest whitespace-nowrap">Dimensi</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest text-center whitespace-nowrap">Populasi</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest text-center whitespace-nowrap">Berat Rata-rata</th>
                                    <th v-if="isAdmin" class="px-6 py-5 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest text-right whitespace-nowrap">Aksi</th>
                                </tr>
                            </thead>
                            
                            <tbody class="text-sm">
                                <tr v-for="kolam in kolams" :key="kolam.id" class="border-b border-slate-50 dark:border-slate-700/50 hover:bg-slate-50/80 dark:hover:bg-slate-700/50 transition duration-200 group">
                                    
                                    <td class="px-6 py-5 whitespace-nowrap">
                                        <p class="font-bold text-slate-900 dark:text-slate-100 text-base transition-colors">{{ kolam.nama_kolam }}</p>
                                    </td>
                                    
                                    <td class="px-6 py-5 text-center">
                                        <span :class="{
                                            'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20': kolam.status_kolam === 'Aktif',
                                            'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-500/20': kolam.status_kolam === 'Persiapan',
                                            'bg-slate-50 dark:bg-slate-700 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-600': kolam.status_kolam === 'Istirahat' || !kolam.status_kolam
                                        }" class="inline-block px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-widest border shadow-sm w-full max-w-[90px] text-center transition-colors">
                                            {{ kolam.status_kolam || 'Tidak Diketahui' }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-5 text-center">
                                        <span class="inline-block px-3 py-1.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-lg text-xs font-bold transition-colors">
                                            {{ kolam.bentuk_kolam }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-5 whitespace-nowrap">
                                        <span v-if="kolam.bentuk_kolam === 'Bundar'" class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-50/50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 rounded-lg text-xs font-semibold transition-colors">
                                            D: {{ kolam.panjang_m }}m <span class="text-slate-300 dark:text-slate-600">|</span> T: {{ kolam.kedalaman_m }}m
                                        </span>
                                        
                                        <span v-else class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-50/50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 rounded-lg text-xs font-semibold transition-colors">
                                            {{ kolam.panjang_m }}m &times; {{ kolam.lebar_m }}m <span class="text-slate-300 dark:text-slate-600">|</span> T: {{ kolam.kedalaman_m }}m
                                        </span>
                                    </td>

                                    <td class="px-6 py-5 text-center whitespace-nowrap">
                                        <span class="font-bold text-slate-900 dark:text-slate-100 text-base transition-colors">{{ kolam.jumlah_ikan.toLocaleString('id-ID') }}</span>
                                        <span class="text-xs text-slate-400 dark:text-slate-500 ml-1 transition-colors">Ekor</span>
                                    </td>

                                    <td class="px-6 py-5 text-center whitespace-nowrap">
                                        <span class="font-bold text-slate-900 dark:text-slate-100 text-base transition-colors">{{ kolam.berat_rata_gram }}</span>
                                        <span class="text-xs text-slate-400 dark:text-slate-500 ml-1 transition-colors">g</span>
                                    </td>

                                    <td v-if="isAdmin" class="px-6 py-5 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-3 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity duration-200">
                                            <Link :href="'/kolam/' + kolam.id + '/edit'" class="px-3 py-1.5 text-xs font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-500/10 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-500/20 transition">
                                                Edit
                                            </Link>

                                            <button @click="hapusKolam(kolam.id, kolam.nama_kolam)" class="px-3 py-1.5 text-xs font-bold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 rounded-lg hover:bg-red-100 dark:hover:bg-red-500/20 transition">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="kolams.length === 0">
                                    <td :colspan="isAdmin ? 7 : 6" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mb-4 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                            <p class="text-slate-500 dark:text-slate-400 font-medium text-sm transition-colors">Belum ada data kolam yang ditambahkan.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div v-if="isAdmin" class="space-y-4">
                    <div class="flex items-center gap-3 px-2">
                        <div class="p-2 bg-indigo-50 dark:bg-indigo-500/10 rounded-lg text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-500/20 shadow-sm transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 transition-colors">Log Aktivitas Master Kolam</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 transition-colors">Riwayat penambahan, pengubahan, dan penghapusan data kolam (Khusus Administrator).</p>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-[0_4px_20px_rgb(0,0,0,0.03)] dark:shadow-[0_4px_20px_rgb(0,0,0,0.2)] sm:rounded-3xl border border-slate-100 dark:border-slate-700 transition-colors duration-300">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead class="bg-slate-50/50 dark:bg-slate-700/50 border-b border-slate-100 dark:border-slate-700 transition-colors">
                                    <tr>
                                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest w-40 whitespace-nowrap">Waktu</th>
                                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest text-center w-28 whitespace-nowrap">Aksi</th>
                                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest whitespace-nowrap">Entitas Kolam</th>
                                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest whitespace-nowrap">Detail Perubahan</th>
                                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest whitespace-nowrap">Dieksekusi Oleh</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm">
                                    <tr v-for="log in logs" :key="log.id" class="border-b border-slate-50 dark:border-slate-700/50 hover:bg-slate-50/80 dark:hover:bg-slate-700/50 transition duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap text-xs font-medium text-slate-500 dark:text-slate-400 transition-colors">
                                            {{ formatDate(log.created_at) }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span :class="{
                                                'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20': log.aksi === 'Tambah',
                                                'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-500/20': log.aksi === 'Update',
                                                'bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-200 dark:border-rose-500/20': log.aksi === 'Hapus'
                                            }" class="inline-block px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-md border shadow-sm w-full text-center transition-colors">
                                                {{ log.aksi }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 font-bold text-slate-800 dark:text-slate-200 whitespace-nowrap transition-colors">{{ log.nama_kolam }}</td>
                                        <td class="px-6 py-4 text-xs text-slate-600 dark:text-slate-400 truncate max-w-[200px] sm:max-w-sm transition-colors" :title="log.keterangan">{{ log.keterangan }}</td>
                                        <td class="px-6 py-4 text-xs font-bold text-indigo-600 dark:text-indigo-400 whitespace-nowrap transition-colors">{{ log.user?.name || 'Administrator' }}</td>
                                    </tr>

                                    <tr v-if="!logs || logs.length === 0">
                                        <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400 italic transition-colors">
                                            Belum ada log aktivitas yang tercatat dalam sistem.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>