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
            <div class="flex justify-between items-end">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Data Kolam</h2>
                    <p class="text-sm text-slate-500 mt-1">Kelola data fisik kolam, dimensi, dan jumlah populasi ikan.</p>
                </div>
                <Link v-if="isAdmin" href="/kolam/create" class="bg-slate-900 text-white px-5 py-2.5 rounded-xl shadow-lg shadow-slate-200/50 hover:bg-slate-800 text-sm font-semibold transition-all">
                    + Tambah Kolam
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-12">
                
                <!-- TABEL UTAMA KOLAM -->
                <div class="bg-white overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:rounded-3xl border border-slate-100">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50/50 border-b border-slate-100">
                                <tr>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Kolam</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Status</th> 
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Bentuk</th> 
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Dimensi</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Populasi</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Berat Rata-rata</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Aksi</th>
                                </tr>
                            </thead>
                            
                            <tbody class="text-sm">
                                <tr v-for="kolam in kolams" :key="kolam.id" class="border-b border-slate-50 hover:bg-slate-50/80 transition duration-200 group">
                                    
                                    <td class="px-6 py-5">
                                        <p class="font-bold text-slate-900 text-base">{{ kolam.nama_kolam }}</p>
                                    </td>
                                    
                                    <td class="px-6 py-5 text-center">
                                        <span :class="{
                                            'bg-emerald-50 text-emerald-700 border-emerald-200': kolam.status_kolam === 'Aktif',
                                            'bg-amber-50 text-amber-700 border-amber-200': kolam.status_kolam === 'Persiapan',
                                            'bg-slate-50 text-slate-600 border-slate-200': kolam.status_kolam === 'Istirahat' || !kolam.status_kolam
                                        }" class="inline-block px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-widest border shadow-sm w-full text-center max-w-[90px]">
                                            {{ kolam.status_kolam || 'Tidak Diketahui' }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-5 text-center">
                                        <span class="inline-block px-3 py-1.5 bg-slate-100 text-slate-600 rounded-lg text-xs font-bold">
                                            {{ kolam.bentuk_kolam }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-5">
                                        <span v-if="kolam.bentuk_kolam === 'Bundar'" class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-50/50 text-blue-700 rounded-lg text-xs font-semibold">
                                            <span class="text-blue-400"></span> {{ kolam.panjang_m }}m <span class="text-slate-300">|</span> Kedalaman: {{ kolam.kedalaman_m }}m
                                        </span>
                                        
                                        <span v-else class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-50/50 text-emerald-700 rounded-lg text-xs font-semibold">
                                            <span class="text-emerald-400"></span>{{ kolam.panjang_m }}m &times; {{ kolam.lebar_m }}m <span class="text-slate-300">|</span> Kedalaman: {{ kolam.kedalaman_m }}m
                                        </span>
                                    </td>

                                    <td class="px-6 py-5 text-center">
                                        <span class="font-bold text-slate-900 text-base">{{ kolam.jumlah_ikan.toLocaleString('id-ID') }}</span>
                                        <span class="text-xs text-slate-400 ml-1">Ekor</span>
                                    </td>

                                    <td class="px-6 py-5">
                                        <span class="font-bold text-slate-900 text-base">{{ kolam.berat_rata_gram }}</span>
                                        <span class="text-xs text-slate-400 ml-1">g</span>
                                    </td>

                                    <td class="px-6 py-5 text-right">
                                        <div class="flex items-center justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                            <Link :href="'/kolam/' + kolam.id + '/edit'" class="px-3 py-1.5 text-xs font-bold text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                                                Edit
                                            </Link>

                                            <button v-if="isAdmin" @click="hapusKolam(kolam.id, kolam.nama_kolam)" class="px-3 py-1.5 text-xs font-bold text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="kolams.length === 0">
                                    <td colspan="7" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-slate-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                            <p class="text-slate-500 font-medium text-sm">Belum ada data kolam yang ditambahkan.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- BAGIAN HISTORY LOG (HANYA MUNCUL JIKA ADMIN) -->
                <div v-if="isAdmin" class="space-y-4">
                    <div class="flex items-center gap-3 px-2">
                        <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600 border border-indigo-100 shadow-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Log Aktivitas Master Kolam</h3>
                            <p class="text-xs text-slate-500">Riwayat penambahan, pengubahan, dan penghapusan data kolam (Khusus Administrator).</p>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-[0_4px_20px_rgb(0,0,0,0.03)] sm:rounded-3xl border border-slate-100">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead class="bg-slate-50/50 border-b border-slate-100">
                                    <tr>
                                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-40">Waktu</th>
                                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-24">Aksi</th>
                                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Entitas Kolam</th>
                                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Detail Perubahan</th>
                                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Dieksekusi Oleh</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm">
                                    <tr v-for="log in logs" :key="log.id" class="border-b border-slate-50 hover:bg-slate-50/80 transition duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap text-xs font-medium text-slate-500">
                                            {{ formatDate(log.created_at) }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span :class="{
                                                'bg-emerald-50 text-emerald-600 border-emerald-200': log.aksi === 'Tambah',
                                                'bg-blue-50 text-blue-600 border-blue-200': log.aksi === 'Update',
                                                'bg-rose-50 text-rose-600 border-rose-200': log.aksi === 'Hapus'
                                            }" class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-md border shadow-sm w-full block text-center">
                                                {{ log.aksi }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 font-bold text-slate-800">{{ log.nama_kolam }}</td>
                                        <td class="px-6 py-4 text-xs text-slate-600 truncate max-w-sm" :title="log.keterangan">{{ log.keterangan }}</td>
                                        <td class="px-6 py-4 text-xs font-bold text-indigo-600">{{ log.user?.name || 'Administrator' }}</td>
                                    </tr>
                                    <tr v-if="!logs || logs.length === 0">
                                        <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500 italic">
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