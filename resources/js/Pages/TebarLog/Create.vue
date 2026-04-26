<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

defineProps({ kolams: Array });

const form = useForm({
    kolam_id: '',
    tanggal_tebar: new Date().toISOString().slice(0, 10),
    jumlah_ikan: '',
    berat_rata_gram: '',
    sumber_benih: '',
    catatan: ''
});

const submit = () => {
    form.post('/tebar');
};
</script>

<template>
    <Head title="Catat Tebar Benih" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Catat Tebar Benih</h2>
        </template>

        <div class="py-8">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:rounded-3xl border border-slate-100">
                    
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Target Kolam</label>
                                <select v-model="form.kolam_id" class="w-full border-slate-200 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="" disabled>-- Pilih Kolam --</option>
                                    <option v-for="kolam in kolams" :key="kolam.id" :value="kolam.id">
                                        {{ kolam.nama_kolam }} (Saat ini: {{ kolam.jumlah_ikan }} ekor)
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Tanggal Tebar</label>
                                <input type="date" v-model="form.tanggal_tebar" class="w-full border-slate-200 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Jumlah Benih (Ekor)</label>
                                <input type="number" v-model="form.jumlah_ikan" placeholder="Cth: 2000" class="w-full border-slate-200 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required min="1">
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Berat Rata-rata (Gram)</label>
                                <div class="relative">
                                    <input type="number" step="0.01" v-model="form.berat_rata_gram" placeholder="Cth: 15.5" class="w-full border-slate-200 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <span class="absolute right-3 top-2.5 text-slate-400 text-sm font-bold">gr</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Sumber Benih / Supplier</label>
                            <input type="text" v-model="form.sumber_benih" placeholder="Nama PT atau Lokasi Pembibitan" class="w-full border-slate-200 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Catatan (Opsional)</label>
                            <textarea v-model="form.catatan" rows="3" placeholder="Informasi tambahan terkait kondisi benih..." class="w-full border-slate-200 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>

                        <div class="flex justify-end items-center gap-4 pt-4 border-t border-slate-100">
                            <Link :href="route('tebar.index')" class="text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors">
                                Batal
                            </Link>

                            <button type="submit" :disabled="form.processing" class="bg-indigo-600 text-white px-8 py-3 rounded-xl shadow-lg shadow-indigo-200/50 hover:bg-indigo-700 text-sm font-bold transition-all disabled:opacity-50">
                                Simpan Data Tebar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>