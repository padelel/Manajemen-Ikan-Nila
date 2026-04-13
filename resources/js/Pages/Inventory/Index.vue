<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    inventories: Array
});

const hapusInventory = (id, nama) => {
    if (confirm(`Yakin ingin menghapus pakan ${nama}?`)) {
        router.delete(`/inventory/${id}`);
    }
};
</script>

<template>
    <Head title="Gudang Pakan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manajemen Gudang Pakan</h2>
                <Link href="/inventory/create" class="bg-blue-600 text-white px-4 py-2 rounded-md shadow hover:bg-blue-700 text-sm font-medium transition">
                    + Tambah Stok
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-100 border-b text-gray-600 text-sm uppercase tracking-wider">
                                    <th class="p-4 font-semibold">Nama Pakan</th>
                                    <th class="p-4 font-semibold">Total Stok (Kg)</th>
                                    <th class="p-4 font-semibold">Terakhir Update</th>
                                    <th class="p-4 font-semibold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                <tr v-for="item in inventories" :key="item.id" class="border-b hover:bg-gray-50 transition">
                                    <td class="p-4 font-bold text-gray-900">{{ item.nama_pakan }}</td>
                                    <td class="p-4">
                                        <span :class="item.total_stok_kg < 50 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'" class="py-1 px-3 rounded-full text-xs font-semibold">
                                            {{ Number(item.total_stok_kg).toFixed(2) }} Kg
                                        </span>
                                    </td>
                                    <td class="p-4">{{ item.terakhir_update }}</td>
                                    <td class="p-4 flex gap-3">
                                        <Link :href="'/inventory/' + item.id + '/edit'" class="text-blue-500 hover:text-blue-700 font-medium text-sm">Edit</Link>
                                        <button @click="hapusInventory(item.id, item.nama_pakan)" class="text-red-500 hover:text-red-700 font-medium text-sm">Hapus</button>
                                    </td>
                                </tr>
                                <tr v-if="inventories.length === 0">
                                    <td colspan="4" class="p-4 text-center text-gray-500 italic">Gudang kosong. Belum ada data pakan.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>