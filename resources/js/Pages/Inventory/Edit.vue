<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ inventory: Object });

const form = useForm({
    nama_pakan: props.inventory.nama_pakan,
    total_stok_kg: props.inventory.total_stok_kg,
    terakhir_update: props.inventory.terakhir_update
});

const submit = () => {
    form.put(`/inventory/${props.inventory.id}`);
};
</script>

<template>
    <Head title="Edit Pakan" />
    <AuthenticatedLayout>
        <template #header><h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Stok Pakan</h2></template>
        <div class="py-12"><div class="max-w-2xl mx-auto sm:px-6 lg:px-8"><div class="bg-white p-8 shadow-sm sm:rounded-lg">
            <form @submit.prevent="submit" class="space-y-6">
                <div><label class="block text-sm font-medium text-gray-700">Nama/Merek Pakan</label>
                <input v-model="form.nama_pakan" type="text" class="mt-1 block w-full border-gray-300 rounded-md" required></div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700">Total Stok (Kg)</label>
                    <input v-model="form.total_stok_kg" type="number" step="0.1" class="mt-1 block w-full border-gray-300 rounded-md" required></div>
                    
                    <div><label class="block text-sm font-medium text-gray-700">Tanggal Update</label>
                    <input v-model="form.terakhir_update" type="date" class="mt-1 block w-full border-gray-300 rounded-md" required></div>
                </div>
                
                <div class="flex justify-end gap-4 mt-6">
                    <Link href="/inventory" class="text-gray-600 hover:text-gray-900 font-medium pt-2">Batal</Link>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700" :disabled="form.processing">Update</button>
                </div>
            </form>
        </div></div></div>
    </AuthenticatedLayout>
</template>