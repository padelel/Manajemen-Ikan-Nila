<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";

const props = defineProps({
    logs: Array,
    filters: Object,
});

const formFilter = ref({
    jenis_panen: props.filters?.jenis_panen || "Semua",
    tanggal_mulai: props.filters?.tanggal_mulai || "",
    tanggal_akhir: props.filters?.tanggal_akhir || "",
});

const applyFilter = () => {
    // DIKEMBALIKAN KE: panen.index
    router.get(route("panen.index"), formFilter.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

watch(
    formFilter,
    () => {
        applyFilter();
    },
    { deep: true },
);

const resetFilter = () => {
    formFilter.value = {
        jenis_panen: "Semua",
        tanggal_mulai: "",
        tanggal_akhir: "",
    };
};

// Pengecekan null agar aman
const formatDate = (dateString) => {
    if (!dateString) return "-";
    const options = { year: "numeric", month: "long", day: "numeric" };
    return new Date(dateString).toLocaleDateString("id-ID", options);
};

// Mengambil nama role secara dinamis dari relasi user
const getRoleName = (user) => {
    if (user && user.role)
        return user.role === "admin" ? "Pengelola Utama" : "Operator Lapangan";
    return "Sistem Otomatis";
};

// Mengambil inisial nama secara dinamis
const getRoleInitial = (user) => {
    if (user && user.name) return user.name.charAt(0).toUpperCase();
    return "S";
};
</script>

<template>
    <Head title="Riwayat Panen" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:justify-between md:items-end gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight transition-colors duration-300">
                        Riwayat Panen
                    </h2>
                    <p class="text-sm text-slate-500 mt-1 transition-colors duration-300">
                        Catatan histori hasil panen ikan (parsial & full) beserta total berat.
                    </p>
                </div>
                <!-- DIKEMBALIKAN KE: panen.create -->
                <Link
                    v-if="$page.props.auth.user.role === 'operator'"
                    :href="route('panen.create')"
                    class="px-5 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/30 flex-shrink-0"
                >
                    + Catat Panen Baru
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Filter Section -->
                <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-200 flex flex-col md:flex-row gap-4 items-end transition-colors duration-300">
                    <div class="w-full md:w-1/4">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 transition-colors duration-300">Jenis Panen</label>
                        <select v-model="formFilter.jenis_panen" class="w-full border border-slate-200 rounded-2xl text-sm focus:ring-blue-500 focus:border-blue-500 bg-slate-50 text-slate-900 transition-colors duration-300 px-4 py-3">
                            <option value="Semua">Semua Jenis</option>
                            <option value="Parsial">Parsial (Sortir)</option>
                            <option value="Full">Full (Total)</option>
                        </select>
                    </div>

                    <div class="w-full md:w-1/4">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 transition-colors duration-300">Dari Tanggal</label>
                        <input type="date" v-model="formFilter.tanggal_mulai" class="w-full border border-slate-200 rounded-2xl text-sm focus:ring-blue-500 focus:border-blue-500 bg-slate-50 text-slate-900 transition-colors duration-300 px-4 py-3" />
                    </div>

                    <div class="w-full md:w-1/4">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 transition-colors duration-300">Sampai Tanggal</label>
                        <input type="date" v-model="formFilter.tanggal_akhir" class="w-full border border-slate-200 rounded-2xl text-sm focus:ring-blue-500 focus:border-blue-500 bg-slate-50 text-slate-900 transition-colors duration-300 px-4 py-3" />
                    </div>

                    <div class="w-full md:w-1/4">
                        <button @click="resetFilter" class="w-full px-5 py-3 bg-slate-900 text-white font-bold text-sm rounded-2xl hover:bg-slate-700 transition-colors border border-slate-800">
                            Reset Filter
                        </button>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl border border-slate-200 transition-colors duration-300">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50 border-b border-slate-200 transition-colors duration-300">
                                <tr>
                                    <th class="px-6 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-widest w-40">Tanggal Panen</th>
                                    <th class="px-6 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-widest">Kolam</th>
                                    <th class="px-6 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-widest text-center">Jenis Panen</th>
                                    <th class="px-6 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-widest text-center">Jumlah Ikan</th>
                                    <th class="px-6 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-widest text-center">Berat Total</th>
                                    <th class="px-6 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-widest">Pencatat</th>
                                    <th class="px-6 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-widest text-right">Aksi</th>
                                </tr>
                            </thead>

                            <tbody class="text-sm">
                                <tr v-for="log in logs" :key="log.id" class="border-b border-slate-200 hover:bg-slate-50 transition duration-200 group">
                                    
                                    <td class="px-6 py-4 font-medium text-slate-700">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            {{ formatDate(log.tanggal_panen) }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-700 border border-slate-200 transition-colors">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-900 text-base transition-colors">
                                                    {{ log.kolam?.nama_kolam || 'Kolam Dihapus' }}
                                                </p>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5 transition-colors">
                                                    {{ log.kolam?.lokasi || '-' }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <span
                                            :class="{
                                                'bg-emerald-50 text-emerald-700 border-emerald-200': log.jenis_panen === 'total',
                                                'bg-amber-50 text-amber-700 border-amber-200': log.jenis_panen === 'parsial',
                                            }"
                                            class="px-3 py-1.5 rounded-full text-xs font-black uppercase tracking-widest border inline-block min-w-[90px] transition-colors duration-300"
                                        >
                                            {{ log.jenis_panen === 'total' ? 'Full' : 'Parsial' }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <div class="inline-flex flex-col items-center justify-center">
                                            <p class="font-bold text-slate-700 text-lg">
                                                {{ log.jumlah_ikan_panen?.toLocaleString("id-ID") || "0" }}
                                            </p>
                                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest bg-slate-100 px-2 py-0.5 rounded-md mt-1 border border-slate-200/50 transition-colors">
                                                Ekor
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <div class="inline-flex flex-col items-center justify-center">
                                            <p class="font-black text-emerald-600 text-xl tracking-tight">
                                                {{ log.berat_total_kg?.toLocaleString("id-ID") || "0" }}
                                            </p>
                                            <span class="text-[10px] text-emerald-500 font-bold uppercase tracking-widest bg-emerald-50 px-2 py-0.5 rounded-md mt-1 border border-emerald-100 transition-colors">
                                                Kg
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="rounded-full w-9 h-9 flex items-center justify-center font-bold text-xs uppercase border bg-slate-100 text-slate-700 border-slate-200 transition-colors">
                                                {{ getRoleInitial(log.user) }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-bold text-slate-900 text-sm transition-colors">
                                                    {{ log.user ? log.user.name : "Sistem" }}
                                                </span>
                                                <span
                                                    class="text-[10px] font-bold uppercase tracking-wider transition-colors"
                                                    :class="getRoleName(log.user) === 'Pengelola Utama' ? 'text-indigo-500' : 'text-teal-500'"
                                                >
                                                    {{ getRoleName(log.user) }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <Link
                                            :href="route('panen.show', log.id)"
                                            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold rounded-full transition-colors border"
                                            :class="
                                                log.jenis_panen === 'total'
                                                    ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border-emerald-200'
                                                    : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border-slate-200'
                                            "
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                            </svg>
                                            {{ log.jenis_panen === 'total' ? 'Detail Siklus' : 'Detail' }}
                                        </Link>
                                    </td>
                                </tr>

                                <tr v-if="logs.length === 0">
                                    <td colspan="7" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="h-16 w-16 bg-slate-100 rounded-full flex items-center justify-center mb-4 border border-slate-200 transition-colors duration-300">
                                                <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                                </svg>
                                            </div>
                                            <p class="text-slate-500 font-medium text-sm mb-2 transition-colors">
                                                Pencarian tidak menemukan hasil.
                                            </p>
                                            <button
                                                @click="resetFilter"
                                                v-if="formFilter.jenis_panen !== 'Semua' || formFilter.tanggal_mulai || formFilter.tanggal_akhir"
                                                class="text-blue-600 font-bold hover:underline text-sm transition-colors"
                                            >
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