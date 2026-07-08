<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    role: String,
    stats: Object,
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard {{ role === 'supervisor' ? 'Supervisor' : 'Operator' }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- TAMPILAN SUPERVISOR -->
                <div v-if="role === 'supervisor'" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-b-4 border-blue-500">
                        <div class="text-gray-500 text-sm">Total Kolam Aktif</div>
                        <div class="text-3xl font-bold">{{ stats.total_kolam_aktif }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-b-4 border-red-500">
                        <div class="text-gray-500 text-sm">Tiket Darurat Aktif</div>
                        <div class="text-3xl font-bold">{{ stats.tiket_aktif }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-b-4 border-yellow-500">
                        <div class="text-gray-500 text-sm">Menunggu Verifikasi</div>
                        <div class="text-3xl font-bold">{{ stats.menunggu_verifikasi }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-b-4 border-green-500">
                        <div class="text-gray-500 text-sm">Total Panen (Kg)</div>
                        <div class="text-3xl font-bold">{{ stats.total_panen_kg }}</div>
                    </div>
                </div>

                <!-- TAMPILAN OPERATOR -->
                <div v-if="role === 'operator'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-b-4 border-indigo-500">
                        <div class="text-gray-500 text-sm">Kolam Dalam Penugasan</div>
                        <div class="text-3xl font-bold">{{ stats.kolam_ditugaskan }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-b-4 border-red-500">
                        <div class="text-gray-500 text-sm">Tugas Mitigasi (Belum Selesai)</div>
                        <div class="text-3xl font-bold">{{ stats.tugas_mitigasi }}</div>
                        <div class="mt-4">
                            <Link :href="route('tiket.index')" class="text-sm text-red-600 font-bold hover:underline">
                                Lihat Tugas →
                            </Link>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>