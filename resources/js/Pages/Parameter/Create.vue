<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    kolams: Array
});

const form = useForm({
    kolam_id: '',
    tanggal_cek: new Date().toISOString().split('T')[0],
    suhu: '',
    ph: '',
    do_mgl: '',
    amonia_mgl: '',
    flok_ml: '',
    kecerahan_cm: ''
});

const submit = () => {
    form.post(route('parameter.store'));
};
</script>

<template>
    <Head title="Input Kualitas Air" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Catat Parameter Kualitas Air</h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="submit" class="space-y-4">
                        
                        <div>
                            <InputLabel for="kolam_id" value="Pilih Kolam" />
                            <select id="kolam_id" v-model="form.kolam_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="" disabled>-- Pilih Kolam Aktif --</option>
                                <option v-for="kolam in kolams" :key="kolam.id" :value="kolam.id">
                                    {{ kolam.nama_kolam }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.kolam_id" />
                        </div>

                        <div>
                            <InputLabel for="tanggal_cek" value="Tanggal Pengecekan" />
                            <TextInput id="tanggal_cek" type="date" class="mt-1 block w-full" v-model="form.tanggal_cek" required />
                            <InputError class="mt-2" :message="form.errors.tanggal_cek" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="suhu" value="Suhu (°C)" />
                                <TextInput id="suhu" type="number" step="0.1" class="mt-1 block w-full" v-model="form.suhu" placeholder="Misal: 28.5" required />
                                <InputError class="mt-2" :message="form.errors.suhu" />
                            </div>
                            <div>
                                <InputLabel for="ph" value="Tingkat pH" />
                                <TextInput id="ph" type="number" step="0.1" class="mt-1 block w-full" v-model="form.ph" placeholder="Misal: 7.2" required />
                                <InputError class="mt-2" :message="form.errors.ph" />
                            </div>
                            <div>
                                <InputLabel for="do_mgl" value="DO (mg/L)" />
                                <TextInput id="do_mgl" type="number" step="0.1" class="mt-1 block w-full" v-model="form.do_mgl" placeholder="Misal: 5.5" required />
                                <InputError class="mt-2" :message="form.errors.do_mgl" />
                            </div>
                            <div>
                                <InputLabel for="amonia_mgl" value="Amonia (mg/L)" />
                                <TextInput id="amonia_mgl" type="number" step="0.01" class="mt-1 block w-full" v-model="form.amonia_mgl" placeholder="Misal: 0.02" required />
                                <InputError class="mt-2" :message="form.errors.amonia_mgl" />
                            </div>
                            <div>
                                <InputLabel for="flok_ml" value="Volume Flok (ml/L)" />
                                <TextInput id="flok_ml" type="number" step="0.1" class="mt-1 block w-full" v-model="form.flok_ml" placeholder="Misal: 20" required />
                                <InputError class="mt-2" :message="form.errors.flok_ml" />
                            </div>
                            <div>
                                <InputLabel for="kecerahan_cm" value="Kecerahan (cm)" />
                                <TextInput id="kecerahan_cm" type="number" step="0.1" class="mt-1 block w-full" v-model="form.kecerahan_cm" placeholder="Misal: 35" required />
                                <InputError class="mt-2" :message="form.errors.kecerahan_cm" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6 border-t pt-4">
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Simpan & Analisis AI
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>