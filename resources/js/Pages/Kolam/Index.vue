<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({ kolams: Array });

const form = useForm({});

const hapusKolam = (id) => {
    if (confirm('Yakin ingin menghapus kolam ini? Semua log parameter dan tiket terkait juga akan terhapus.')) {
        form.delete(route('kolam.destroy', id));
    }
};
</script>

<template>
    <Head title="Master Kolam" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800">Manajemen Data Kolam</h2>
                <Link :href="route('kolam.create')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow">
                    + Tambah Kolam
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="$page.props.flash?.success" class="mb-4 bg-green-100 text-green-700 p-4 rounded shadow-sm">
                    {{ $page.props.flash.success }}
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b">
                            <tr>
                                <th class="px-6 py-4">Nama Kolam</th>
                                <th class="px-6 py-4">Dimensi (P x L x T)</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="kolam in kolams" :key="kolam.id" class="border-b">
                                <td class="px-6 py-4 font-bold text-gray-900">
                                    {{ kolam.nama_kolam }}
                                    <div class="text-xs font-normal text-gray-500">{{ kolam.lokasi || 'Lokasi tidak diatur' }}</div>
                                </td>
                                <td class="px-6 py-4">{{ kolam.panjang_m }}m x {{ kolam.lebar_m }}m x {{ kolam.kedalaman_m }}m</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-1 text-xs font-bold rounded-full uppercase"
                                          :class="kolam.status_kolam === 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                        {{ kolam.status_kolam }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center space-x-2">
                                    <Link :href="route('kolam.edit', kolam.id)" class="text-blue-600 hover:underline font-bold">Edit</Link>
                                    <button @click="hapusKolam(kolam.id)" class="text-red-600 hover:underline font-bold">Hapus</button>
                                </td>
                            </tr>
                            <tr v-if="kolams.length === 0">
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada data kolam.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>