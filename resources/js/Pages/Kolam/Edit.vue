<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    kolam: Object
});

// Mengisi nilai awal form dari data kolam yang dikirim oleh Controller
const form = useForm({
    nama_kolam: props.kolam.nama_kolam,
    lokasi: props.kolam.lokasi || '',
    panjang_m: props.kolam.panjang_m,
    lebar_m: props.kolam.lebar_m,
    kedalaman_m: props.kolam.kedalaman_m,
    status_kolam: props.kolam.status_kolam,
});

const submit = () => {
    form.put(route('kolam.update', props.kolam.id));
};
</script>

<template>
    <Head title="Edit Kolam" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Perbarui Data Kolam: {{ kolam.nama_kolam }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="submit" class="space-y-4">
                        
                        <div>
                            <InputLabel for="nama_kolam" value="Nama / Kode Kolam" />
                            <TextInput id="nama_kolam" type="text" class="mt-1 block w-full" v-model="form.nama_kolam" required />
                            <InputError class="mt-2" :message="form.errors.nama_kolam" />
                        </div>

                        <div>
                            <InputLabel for="lokasi" value="Lokasi (Opsional)" />
                            <TextInput id="lokasi" type="text" class="mt-1 block w-full" v-model="form.lokasi" />
                            <InputError class="mt-2" :message="form.errors.lokasi" />
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <InputLabel for="panjang_m" value="Panjang (m)" />
                                <TextInput id="panjang_m" type="number" step="0.1" class="mt-1 block w-full" v-model="form.panjang_m" required />
                                <InputError class="mt-2" :message="form.errors.panjang_m" />
                            </div>
                            <div>
                                <InputLabel for="lebar_m" value="Lebar (m)" />
                                <TextInput id="lebar_m" type="number" step="0.1" class="mt-1 block w-full" v-model="form.lebar_m" required />
                                <InputError class="mt-2" :message="form.errors.lebar_m" />
                            </div>
                            <div>
                                <InputLabel for="kedalaman_m" value="Kedalaman (m)" />
                                <TextInput id="kedalaman_m" type="number" step="0.1" class="mt-1 block w-full" v-model="form.kedalaman_m" required />
                                <InputError class="mt-2" :message="form.errors.kedalaman_m" />
                            </div>
                        </div>

                        <div>
                            <InputLabel for="status_kolam" value="Status Operasional" />
                            <select id="status_kolam" v-model="form.status_kolam" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="aktif">Aktif</option>
                                <option value="tidak aktif">Tidak Aktif</option>
                                <option value="maintenance">Dalam Perbaikan (Maintenance)</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.status_kolam" />
                        </div>

                        <div class="flex items-center justify-end mt-6 border-t pt-4 space-x-3">
                            <Link :href="route('kolam.index')" class="text-gray-600 hover:text-gray-900 font-medium">Batal</Link>
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Simpan Perubahan
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>