<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    logs: Array
});

const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};

const getRoleName = (userId) => {
    if (userId === 1) return 'Pengelola Utama';
    if (userId === 2) return 'Operator Lapangan';
    return 'Sistem';
};

const getRoleInitial = (userId) => {
    if (userId === 1) return 'PU';
    if (userId === 2) return 'OL';
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
                    <p class="text-sm text-slate-500 mt-1">Catatan pemindahan populasi antar kolam budidaya.</p>
                </div>
                <Link :href="route('transfer.create')" class="px-5 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-500/30 flex-shrink-0">
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
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tanggal</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Dari Kolam</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Ke Kolam</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Jumlah</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pencatat</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <tr v-for="log in logs" :key="log.id" class="border-b border-slate-50 hover:bg-slate-50/80 transition duration-200">
                                    <td class="px-6 py-5 font-medium text-slate-500">
                                        {{ formatDate(log.tanggal_transfer) }}
                                    </td>
                                    <td class="px-6 py-5">
                                        <p class="font-bold text-slate-900">{{ log.kolam_asal.nama_kolam }}</p>
                                        <p class="text-[10px] text-red-500 font-bold uppercase">Keluar</p>
                                    </td>
                                    <td class="px-6 py-5">
                                        <p class="font-bold text-slate-900">{{ log.kolam_tujuan.nama_kolam }}</p>
                                        <p class="text-[10px] text-emerald-500 font-bold uppercase">Masuk</p>
                                    </td>
                                    <td class="px-6 py-5">
                                        <p class="font-black text-slate-900 text-base">
                                            {{ log.jumlah_ikan.toLocaleString('id-ID') }} <span class="text-[10px] text-slate-400 font-bold uppercase">Ekor</span>
                                        </p>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="rounded-full w-9 h-9 flex items-center justify-center font-bold text-xs uppercase border"
                                                 :class="log.user_id === 1 ? 'bg-indigo-50 text-indigo-700 border-indigo-100' : 'bg-teal-50 text-teal-700 border-teal-100'">
                                                {{ getRoleInitial(log.user_id) }}
                                            </div>
                                            <div class="flex flex-col">
                                                <!-- <span class="text-[10px] font-black uppercase tracking-widest" :class="log.user_id === 1 ? 'text-indigo-600' : 'text-teal-600'">
                                                    {{ getRoleName(log.user_id) }}
                                                </span> -->
                                                <span class="text-xs font-bold uppercase tracking-wider" 
                                                      :class="log.user_id === 1 ? 'text-indigo-600' : (log.user_id === 2 ? 'text-teal-600' : 'text-slate-500')">
                                                    {{ getRoleName(log.user_id) }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="logs.length === 0">
                                    <td colspan="5" class="px-6 py-16 text-center text-slate-500">Belum ada riwayat pemindahan ikan.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>