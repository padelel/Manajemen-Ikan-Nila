<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    logs: Array
});

const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};

// Disarankan menggunakan relasi log.user.role dari database, 
// namun ini dipertahankan sebagai fallback jika relasi tidak di-load
const getRoleName = (user) => {
    if (user && user.role) return user.role === 'admin' ? 'Pengelola Utama' : 'Operator Lapangan';
    if (user && user.id === 1) return 'Pengelola Utama';
    if (user && user.id === 2) return 'Operator Lapangan';
    return 'Sistem';
};

const getRoleInitial = (user) => {
    if (user && user.name) return user.name.charAt(0).toUpperCase();
    return 'S';
};
</script>

<template>
    <Head title="Riwayat Transfer" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:justify-between md:items-end gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Riwayat Transfer Ikan</h2>
                    <p class="text-sm text-slate-500 mt-1">Pantau alur pemindahan populasi antar kolam budidaya.</p>
                </div>
                <Link v-if="$page.props.auth.user.role === 'operator'" :href="route('transfer.create')" class="px-5 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-500/30 flex-shrink-0">
                    + Pindahkan Ikan
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="bg-white overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:rounded-3xl border border-slate-100">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50/50 border-b border-slate-100">
                                <tr>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-48">Waktu Transfer</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Alur Pemindahan</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Jumlah Pindah</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pencatat</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <tr v-for="log in logs" :key="log.id" class="border-b border-slate-50 hover:bg-slate-50/80 transition duration-200">
                                    
                                    <!-- Kolom Tanggal -->
                                    <td class="px-6 py-5 font-medium text-slate-500">
                                        {{ formatDate(log.tanggal_transfer) }}
                                    </td>
                                    
                                    <!-- Kolom Visual Alur Pemindahan -->
                                    <td class="px-6 py-5">
                                        <div class="flex items-center justify-center gap-4 bg-slate-50/50 py-3 px-4 rounded-xl border border-slate-100/50">
                                            
                                            <!-- Kolom Asal (Kiri) -->
                                            <div class="flex flex-col items-end w-1/3">
                                                <span class="text-[10px] text-red-500 font-bold uppercase tracking-wider mb-1">Sumber</span>
                                                <p class="font-black text-slate-900 truncate">{{ log.kolam_asal.nama_kolam }}</p>
                                            </div>

                                            <!-- Panah Arah (Tengah) -->
                                            <div class="flex flex-col items-center justify-center px-4">
                                                <div class="h-8 w-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-500 border border-blue-100 shadow-sm relative z-10">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                    </svg>
                                                </div>
                                            </div>

                                            <!-- Kolom Tujuan (Kanan) -->
                                            <div class="flex flex-col items-start w-1/3">
                                                <span class="text-[10px] text-emerald-500 font-bold uppercase tracking-wider mb-1">Tujuan</span>
                                                <p class="font-black text-slate-900 truncate">{{ log.kolam_tujuan.nama_kolam }}</p>
                                            </div>
                                            
                                        </div>
                                    </td>
                                    
                                    <!-- Kolom Jumlah -->
                                    <td class="px-6 py-5 text-center">
                                        <div class="inline-flex flex-col items-center justify-center">
                                            <p class="font-black text-blue-600 text-lg">
                                                {{ log.jumlah_ikan.toLocaleString('id-ID') }}
                                            </p>
                                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest bg-slate-100 px-2 py-0.5 rounded-md mt-1">Ekor</span>
                                        </div>
                                    </td>
                                    
                                    <!-- Kolom Pencatat -->
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
                                <tr v-if="logs.length === 0">
                                    <td colspan="4" class="px-6 py-16 text-center text-slate-500 italic">Belum ada riwayat pemindahan ikan antar kolam.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>