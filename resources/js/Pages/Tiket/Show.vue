<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import moment from 'moment';

const props = defineProps({
    tiket: Object,
});

const page = usePage();
const userRole = page.props.auth.user.role;

const log = props.tiket.inferensi_log;

const paramLabels = {
    suhu: { label: 'Suhu', unit: '°C' },
    ph: { label: 'pH', unit: '' },
    do_mgl: { label: 'DO', unit: 'mg/L' },
    amonia_mgl: { label: 'Amonia', unit: 'mg/L' },
    flok_ml: { label: 'Flok', unit: 'ml/L' },
    kecerahan_cm: { label: 'Kecerahan', unit: 'cm' },
};

const faktaLabelMap = {
    F19: 'Suhu Optimal (25–30°C)', F20: 'Suhu Tidak Ideal', F21: 'Suhu Kritis',
    F22: 'pH Optimal (6.5–8.5)', F23: 'pH Tidak Ideal', F24: 'pH Kritis',
    F25: 'DO Cukup (≥5 mg/L)', F26: 'DO Rendah (3–4.99 mg/L)', F27: 'DO Kritis (<3 mg/L)',
    F28: 'Amonia Aman (<0.01 mg/L)', F29: 'Amonia Waspada (0.01–0.05 mg/L)', F30: 'Amonia Berbahaya (>0.05 mg/L)',
    F31: 'Flok Optimal (15–30 ml/L)', F32: 'Flok Terlalu Rendah (<15 ml/L)', F33: 'Flok Terlalu Tinggi (>30 ml/L)',
    F34: 'Kecerahan Optimal (30–40 cm)', F35: 'Kecerahan Rendah/Pekat (<30 cm)', F36: 'Kecerahan Tinggi/Bening (>40 cm)',
};

const statusBadge = (status) => {
    switch (status) {
        case 'open': return 'bg-red-100 text-red-800 border-red-300';
        case 'in_progress': return 'bg-yellow-100 text-yellow-800 border-yellow-300';
        case 'menunggu_verifikasi': return 'bg-blue-100 text-blue-800 border-blue-300';
        case 'selesai': return 'bg-green-100 text-green-800 border-green-300';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const statusLabel = (status) => {
    switch (status) {
        case 'open': return 'Terbuka';
        case 'in_progress': return 'Sedang Dikerjakan';
        case 'menunggu_verifikasi': return 'Menunggu Verifikasi';
        case 'selesai': return 'Selesai';
        default: return status;
    }
};

const formatDate = (date) => date ? moment(date).format('DD MMM YYYY HH:mm') : '-';
const formatDateShort = (date) => date ? moment(date).format('DD MMM YYYY') : '-';

const formOperator = useForm({
    bukti_penyelesaian: '',
});

const formSupervisor = useForm({
    keputusan: '',
    catatan_supervisor: '',
});

const submitOperator = () => {
    formOperator.post(route('tiket.selesaikan', props.tiket.id));
};

const submitVerifikasi = (keputusan) => {
    formSupervisor.keputusan = keputusan;
    formSupervisor.post(route('tiket.verifikasi', props.tiket.id));
};
</script>

<template>
    <Head title="Detail Tiket" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('tiket.index')" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <h2 class="font-bold text-xl text-slate-800 leading-tight">
                    Detail Tiket Mitigasi #{{ tiket.id }}
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Flash Messages -->
                <div v-if="$page.props.flash?.success" class="bg-green-100 text-green-700 p-4 rounded-2xl shadow-sm border border-green-200">{{ $page.props.flash.success }}</div>
                <div v-if="$page.props.flash?.warning" class="bg-yellow-100 text-yellow-800 p-4 rounded-2xl shadow-sm border border-yellow-200">{{ $page.props.flash.warning }}</div>

                <!-- HEADER: Status & Identitas -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Kolam</p>
                            <p class="text-lg font-black text-slate-800 mt-1">{{ tiket.kolam.nama_kolam }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Status</p>
                            <span class="mt-1 inline-block px-3 py-1 text-xs font-bold rounded-full border"
                                  :class="statusBadge(tiket.status)">
                                {{ statusLabel(tiket.status) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Ditugaskan Ke</p>
                            <p class="text-base font-bold text-slate-800 mt-1">{{ tiket.operator?.name || '-' }}</p>
                        </div>
                        <div v-if="tiket.supervisor">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Diverifikasi Oleh</p>
                            <p class="text-base font-bold text-slate-800 mt-1">{{ tiket.supervisor.name }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Tanggal Dibuat</p>
                            <p class="text-base font-bold text-slate-800 mt-1">{{ formatDate(tiket.created_at) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Deadline</p>
                            <p class="text-base font-bold text-slate-800 mt-1"
                               :class="{'text-red-600': tiket.status !== 'selesai' && moment().isAfter(tiket.deadline)}">
                                {{ formatDate(tiket.deadline) }}
                            </p>
                        </div>
                        <div v-if="tiket.diselesaikan_at">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Diselesaikan Pada</p>
                            <p class="text-base font-bold text-emerald-600 mt-1">{{ formatDate(tiket.diselesaikan_at) }}</p>
                        </div>
                        <div v-if="tiket.diverifikasi_at">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Diverifikasi Pada</p>
                            <p class="text-base font-bold text-emerald-600 mt-1">{{ formatDate(tiket.diverifikasi_at) }}</p>
                        </div>
                    </div>
                </div>

                <!-- DIAGNOSA: Detail Diagnosa & Mitigasi -->
                <div v-if="log" class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Hasil Diagnosa AI
                    </h3>

                    <div class="space-y-4">
                        <div v-for="(kd, idx) in (Array.isArray(log.kode_diagnosa) ? log.kode_diagnosa : [log.kode_diagnosa])" :key="idx"
                             class="rounded-2xl border p-4"
                             :class="kd === 'D-NORMAL' ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'">

                            <div class="flex items-center gap-2 mb-2">
                                <span class="px-3 py-1 text-xs font-bold rounded-full border"
                                      :class="kd === 'D-NORMAL' ? 'bg-green-100 text-green-800 border-green-300' : 'bg-red-100 text-red-800 border-red-300'">
                                    {{ kd }}
                                </span>
                                <span class="text-sm font-bold text-slate-800">
                                    {{ Array.isArray(log.label_diagnosa) ? log.label_diagnosa[idx] : log.label_diagnosa }}
                                </span>
                                <span v-if="Array.isArray(log.kode_kesimpulan)" class="px-2 py-0.5 text-xs font-bold rounded-full bg-blue-100 text-blue-700 border border-blue-200 ml-auto">
                                    {{ log.kode_kesimpulan[idx] }}
                                </span>
                            </div>

                            <div v-if="Array.isArray(log.peringatan)" class="mt-2">
                                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Peringatan</p>
                                <p class="text-sm text-slate-700 font-medium">{{ log.peringatan[idx] }}</p>
                            </div>

                            <div v-if="Array.isArray(log.tindakan_mitigasi)" class="mt-2">
                                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Tindakan Mitigasi</p>
                                <p class="text-sm text-slate-700">{{ log.tindakan_mitigasi[idx] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PARAMETER AIR: Nilai Input -->
                <div v-if="log?.fakta_input" class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Data Parameter Air Saat Pengecekan
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                        <div v-for="(info, key) in paramLabels" :key="key"
                             class="bg-slate-50 rounded-xl p-3 border border-slate-100 text-center">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ info.label }}</p>
                            <p class="text-lg font-black text-slate-800 mt-1">
                                {{ log.fakta_input[key] ?? '-' }}
                                <span class="text-xs text-slate-500">{{ info.unit }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- FAKTA LINGUISTIK -->
                <div v-if="log?.fakta_baru?.length" class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        Fakta Linguistik Terdeteksi
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        <span v-for="f in log.fakta_baru" :key="f"
                              class="inline-flex items-center gap-1 px-3 py-1 bg-indigo-50 text-indigo-700 font-bold rounded-full text-xs border border-indigo-200">
                            <span class="bg-indigo-200 text-indigo-800 px-1.5 py-0.5 rounded text-[10px]">{{ f }}</span>
                            {{ faktaLabelMap[f] || 'Fakta tidak dikenal' }}
                        </span>
                    </div>
                </div>

                <!-- INSTRUKSI TINDAKAN -->
                <div v-if="tiket.deskripsi_tindakan" class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                        Instruksi Tindakan
                    </h3>
                    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
                        <p class="text-sm text-slate-800 whitespace-pre-line leading-relaxed">{{ tiket.deskripsi_tindakan }}</p>
                    </div>
                </div>

                <!-- OPERATOR: Form Unggah Bukti -->
                <div v-if="userRole === 'operator' && (tiket.status === 'open' || tiket.status === 'in_progress')" class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 border-t-4 border-t-indigo-500">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">Laporan Penyelesaian Tugas</h3>
                    <form @submit.prevent="submitOperator" class="space-y-4">
                        <div>
                            <InputLabel for="bukti_penyelesaian" value="Deskripsi / Tautan Bukti Pekerjaan" />
                            <TextInput id="bukti_penyelesaian" type="text" class="mt-1 block w-full" v-model="formOperator.bukti_penyelesaian" placeholder="Contoh: Sudah mengganti air 30% dan menambah aerasi. Link foto: ...." required />
                            <InputError class="mt-2" :message="formOperator.errors.bukti_penyelesaian" />
                        </div>
                        <div class="flex justify-end">
                            <PrimaryButton :class="{ 'opacity-25': formOperator.processing }" :disabled="formOperator.processing">
                                Kirim Laporan ke Supervisor
                            </PrimaryButton>
                        </div>
                    </form>
                </div>

                <!-- BUKTI YANG SUDAH DIKERJAKAN -->
                <div v-if="tiket.bukti_penyelesaian" class="bg-blue-50 rounded-3xl shadow-sm border border-blue-200 p-6">
                    <h3 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-2">Laporan Operator</h3>
                    <p class="font-medium text-slate-800">{{ tiket.bukti_penyelesaian }}</p>
                    <p class="text-xs text-slate-500 mt-2">Diselesaikan pada: {{ formatDate(tiket.diselesaikan_at) }}</p>
                </div>

                <!-- SUPERVISOR: Form Verifikasi -->
                <div v-if="userRole === 'supervisor' && tiket.status === 'menunggu_verifikasi'" class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 border-t-4 border-t-yellow-500">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">Verifikasi Pekerjaan Operator</h3>
                    <form @submit.prevent class="space-y-4">
                        <div>
                            <InputLabel for="catatan_supervisor" value="Catatan Tambahan (Opsional)" />
                            <TextInput id="catatan_supervisor" type="text" class="mt-1 block w-full" v-model="formSupervisor.catatan_supervisor" placeholder="Tulis catatan jika ada yang kurang..." />
                        </div>
                        <div class="flex gap-4 pt-4">
                            <button @click="submitVerifikasi('tolak')" type="button" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 px-4 rounded-xl shadow-sm transition" :disabled="formSupervisor.processing">
                                Tolak & Ulangi Pekerjaan
                            </button>
                            <button @click="submitVerifikasi('terima')" type="button" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-4 rounded-xl shadow-sm transition" :disabled="formSupervisor.processing">
                                Terima & Selesaikan Tiket
                            </button>
                        </div>
                    </form>
                </div>

                <!-- VERIFIKASI SELESAI -->
                <div v-if="tiket.status === 'selesai'" class="bg-emerald-50 rounded-3xl shadow-sm border border-emerald-200 p-6">
                    <h3 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-2">Catatan Verifikasi Supervisor</h3>
                    <p class="font-medium text-slate-800">{{ tiket.catatan_supervisor || 'Tidak ada catatan. Pekerjaan disetujui.' }}</p>
                    <p class="text-xs text-slate-500 mt-2">Diverifikasi pada: {{ formatDate(tiket.diverifikasi_at) }}</p>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
