<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({ kolams: Array });

const form = useForm({
    kolam_id: '',
    tanggal_kematian: new Date().toISOString().split('T')[0],
    jumlah_mati: '',
    catatan: ''
});

const submit = () => { form.post(route('kematian.store')); };
</script>

<template>
    <Head title="Lapor Kematian" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight transition-colors duration-300">Form Laporan Kematian Ikan</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors duration-300">Catat insiden mortalitas untuk mengurangi stok populasi secara otomatis.</p>
                </div>
                <Link href="/kematian" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-bold rounded-xl hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors duration-300">
                    Kembali
                </Link>
            </div>
        </template>
        
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-slate-800 p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] sm:rounded-3xl border-t-4 border-t-rose-500 border border-slate-100 dark:border-slate-700 transition-colors duration-300">
                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 transition-colors duration-300">Pilih Kolam</label>
                                <select v-model="form.kolam_id" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-600 text-slate-900 dark:text-slate-100 rounded-xl focus:border-rose-500 focus:ring-rose-500 text-sm transition-colors duration-300" required>
                                    <option value="" disabled>-- Pilih Kolam --</option>
                                    <option v-for="kolam in kolams" :key="kolam.id" :value="kolam.id">
                                        {{ kolam.nama_kolam }} (Populasi: {{ kolam.jumlah_ikan }})
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 transition-colors duration-300">Tanggal Ditemukan</label>
                                <input v-model="form.tanggal_kematian" type="date" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-600 text-slate-900 dark:text-slate-100 rounded-xl focus:border-rose-500 focus:ring-rose-500 text-sm transition-colors duration-300" required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 transition-colors duration-300">Jumlah Ikan Mati (Ekor)</label>
                            <input v-model="form.jumlah_mati" type="number" min="1" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-600 text-slate-900 dark:text-slate-100 rounded-xl focus:border-rose-500 focus:ring-rose-500 text-sm transition-colors duration-300" placeholder="Contoh: 5" required>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 transition-colors duration-300">Catatan Kondisi / Dugaan Penyebab</label>
                            <textarea v-model="form.catatan" rows="3" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-600 text-slate-900 dark:text-slate-100 rounded-xl focus:border-rose-500 focus:ring-rose-500 text-sm transition-colors duration-300" placeholder="Contoh: Ditemukan mengambang di pojok kolam, insang pucat..."></textarea>
                        </div>
                        
                        <div class="pt-6 border-t border-slate-100 dark:border-slate-700 flex justify-end items-center gap-4 transition-colors duration-300">
                            <Link href="/kematian" class="text-slate-600 dark:text-slate-400 font-medium px-4 py-2 hover:text-slate-900 dark:hover:text-slate-100 transition-colors duration-300">
                                Batal
                            </Link>
                            <button type="submit" class="bg-rose-600 dark:bg-rose-500 text-white px-8 py-3 rounded-xl shadow-lg shadow-rose-500/30 dark:shadow-none hover:bg-rose-700 dark:hover:bg-rose-400 font-bold transition-all disabled:opacity-50" :disabled="form.processing">
                                Simpan & Kurangi Populasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>