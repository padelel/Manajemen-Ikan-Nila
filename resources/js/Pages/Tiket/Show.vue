<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    tiket: Object,
});

const page = usePage();
const userRole = page.props.auth.user.role;

// Form untuk Operator mengunggah bukti
const formOperator = useForm({
    bukti_penyelesaian: '',
});

// Form untuk Supervisor memverifikasi
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
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Tiket Mitigasi #{{ tiket.id }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Notifikasi Flash -->
                <div v-if="$page.props.flash?.success" class="bg-green-100 text-green-700 p-4 rounded shadow-sm">{{ $page.props.flash.success }}</div>
                <div v-if="$page.props.flash?.warning" class="bg-yellow-100 text-yellow-800 p-4 rounded shadow-sm">{{ $page.props.flash.warning }}</div>

                <!-- Informasi Detail Tiket -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <h3 class="text-sm text-gray-500 font-semibold uppercase">Nama Kolam</h3>
                            <p class="text-lg font-bold text-gray-900">{{ tiket.kolam.nama_kolam }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm text-gray-500 font-semibold uppercase">Status Saat Ini</h3>
                            <p class="text-lg font-bold text-indigo-600 uppercase">{{ tiket.status.replace('_', ' ') }}</p>
                        </div>
                        <div class="col-span-2 mt-4">
                            <h3 class="text-sm text-gray-500 font-semibold uppercase">Masalah Kualitas Air</h3>
                            <p class="text-md text-red-600 font-bold bg-red-50 p-3 border border-red-200 rounded mt-1">
                                {{ tiket.judul }}
                            </p>
                        </div>
                        <div class="col-span-2 mt-4">
                            <h3 class="text-sm text-gray-500 font-semibold uppercase">Instruksi Tindakan (Rekomendasi AI)</h3>
                            <p class="text-md text-gray-800 bg-gray-100 p-3 border border-gray-200 rounded mt-1 leading-relaxed">
                                {{ tiket.deskripsi_tindakan }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- AKSI OPERATOR: Form Unggah Bukti Penyelesaian -->
                <div v-if="userRole === 'operator' && (tiket.status === 'open' || tiket.status === 'in_progress')" class="bg-white shadow-sm sm:rounded-lg p-6 border-t-4 border-indigo-500">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Laporan Penyelesaian Tugas</h3>
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

                <!-- TAMPILAN BUKTI (Jika sudah dikerjakan) -->
                <div v-if="tiket.bukti_penyelesaian" class="bg-blue-50 shadow-sm sm:rounded-lg p-6 border border-blue-200">
                    <h3 class="text-sm text-gray-500 font-semibold uppercase">Laporan Operator</h3>
                    <p class="font-medium text-gray-900 mt-1">{{ tiket.bukti_penyelesaian }}</p>
                    <p class="text-xs text-gray-500 mt-2">Diselesaikan pada: {{ new Date(tiket.diselesaikan_at).toLocaleString() }}</p>
                </div>

                <!-- AKSI SUPERVISOR: Form Verifikasi -->
                <div v-if="userRole === 'supervisor' && tiket.status === 'menunggu_verifikasi'" class="bg-white shadow-sm sm:rounded-lg p-6 border-t-4 border-yellow-500">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Verifikasi Pekerjaan Operator</h3>
                    <form @submit.prevent class="space-y-4">
                        <div>
                            <InputLabel for="catatan_supervisor" value="Catatan Tambahan (Opsional)" />
                            <TextInput id="catatan_supervisor" type="text" class="mt-1 block w-full" v-model="formSupervisor.catatan_supervisor" placeholder="Tulis catatan jika ada yang kurang..." />
                        </div>
                        <div class="flex space-x-4 pt-4">
                            <!-- Tombol Tolak -->
                            <button @click="submitVerifikasi('tolak')" type="button" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow transition" :disabled="formSupervisor.processing">
                                ❌ Tolak & Ulangi Pekerjaan
                            </button>
                            <!-- Tombol Terima -->
                            <button @click="submitVerifikasi('terima')" type="button" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded shadow transition" :disabled="formSupervisor.processing">
                                ✅ Terima & Selesaikan Tiket
                            </button>
                        </div>
                    </form>
                </div>

                <!-- TAMPILAN VERIFIKASI SELESAI -->
                <div v-if="tiket.status === 'selesai'" class="bg-green-50 shadow-sm sm:rounded-lg p-6 border border-green-200">
                    <h3 class="text-sm text-gray-500 font-semibold uppercase">Catatan Verifikasi Supervisor</h3>
                    <p class="font-medium text-gray-900 mt-1">{{ tiket.catatan_supervisor || 'Tidak ada catatan. Pekerjaan disetujui.' }}</p>
                    <p class="text-xs text-gray-500 mt-2">Diverifikasi pada: {{ new Date(tiket.diverifikasi_at).toLocaleString() }}</p>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>