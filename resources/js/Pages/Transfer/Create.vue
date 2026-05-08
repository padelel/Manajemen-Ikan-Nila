<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    kolams: Array
});

const form = useForm({
    kolam_asal_id: '',
    kolam_tujuan_id: '',
    jumlah_ikan: '',
    tanggal_transfer: new Date().toISOString().split('T')[0],
    catatan: ''
});

// Filter agar kolam tujuan tidak sama dengan kolam asal
const availableTujuan = computed(() => {
    return props.kolams.filter(k => k.id !== form.kolam_asal_id);
});

const selectedAsal = computed(() => {
    return props.kolams.find(k => k.id === form.kolam_asal_id);
});

const submit = () => {
    form.post(route('transfer.store'));
};
</script>

<template>
    <Head title="Pindahkan Ikan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight transition-colors duration-300">Transfer Ikan</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors duration-300">Pindahkan populasi ikan antar kolam dengan aman.</p>
                </div>
                <Link :href="route('transfer.index')" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-bold rounded-xl hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors duration-300">
                    Batal
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-slate-800 p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] sm:rounded-3xl border border-slate-100 dark:border-slate-700 transition-colors duration-300">
                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 transition-colors duration-300">Kolam Sumber (Asal) <span class="text-red-500 dark:text-red-400">*</span></label>
                                <select v-model="form.kolam_asal_id" required class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-600 text-slate-900 dark:text-slate-100 rounded-xl focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400 text-sm transition-colors duration-300">
                                    <option value="" disabled>-- Pilih Kolam Asal --</option>
                                    <option v-for="kolam in kolams" :key="kolam.id" :value="kolam.id" :disabled="kolam.jumlah_ikan === 0">
                                        {{ kolam.nama_kolam }} ({{ kolam.jumlah_ikan }} ekor)
                                    </option>
                                </select>
                                <p v-if="form.errors.kolam_asal_id" class="text-red-500 dark:text-red-400 text-xs mt-1 transition-colors duration-300">{{ form.errors.kolam_asal_id }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 transition-colors duration-300">Kolam Target (Tujuan) <span class="text-red-500 dark:text-red-400">*</span></label>
                                <select v-model="form.kolam_tujuan_id" required :disabled="!form.kolam_asal_id" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-600 text-slate-900 dark:text-slate-100 rounded-xl focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400 text-sm disabled:bg-slate-50 dark:disabled:bg-slate-800/50 disabled:text-slate-400 dark:disabled:text-slate-500 disabled:cursor-not-allowed transition-colors duration-300">
                                    <option value="" disabled>-- Pilih Kolam Tujuan --</option>
                                    <option v-for="kolam in availableTujuan" :key="kolam.id" :value="kolam.id">
                                        {{ kolam.nama_kolam }} ({{ kolam.jumlah_ikan }} ekor)
                                    </option>
                                </select>
                                <p v-if="form.errors.kolam_tujuan_id" class="text-red-500 dark:text-red-400 text-xs mt-1 transition-colors duration-300">{{ form.errors.kolam_tujuan_id }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 transition-colors duration-300">Tanggal Transfer <span class="text-red-500 dark:text-red-400">*</span></label>
                                <input type="date" v-model="form.tanggal_transfer" required class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-600 text-slate-900 dark:text-slate-100 rounded-xl focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400 text-sm transition-colors duration-300">
                                <p v-if="form.errors.tanggal_transfer" class="text-red-500 dark:text-red-400 text-xs mt-1 transition-colors duration-300">{{ form.errors.tanggal_transfer }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 transition-colors duration-300">Jumlah Ikan (Ekor) <span class="text-red-500 dark:text-red-400">*</span></label>
                                <input type="number" v-model="form.jumlah_ikan" required :max="selectedAsal?.jumlah_ikan" min="1" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-600 text-blue-600 dark:text-blue-400 rounded-xl focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400 text-sm font-bold transition-colors duration-300" placeholder="Contoh: 100">
                                <p v-if="selectedAsal" class="text-[10px] text-slate-400 dark:text-slate-500 mt-1 transition-colors duration-300">Maksimal pemindahan: {{ selectedAsal.jumlah_ikan }} ekor</p>
                                <p v-if="form.errors.jumlah_ikan" class="text-red-500 dark:text-red-400 text-xs mt-1 transition-colors duration-300">{{ form.errors.jumlah_ikan }}</p>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 transition-colors duration-300">Keterangan / Alasan (Opsional)</label>
                                <textarea v-model="form.catatan" rows="3" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-600 text-slate-900 dark:text-slate-100 rounded-xl focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400 text-sm transition-colors duration-300" placeholder="Contoh: Sortir ukuran atau pecah kepadatan kolam..."></textarea>
                                <p v-if="form.errors.catatan" class="text-red-500 dark:text-red-400 text-xs mt-1 transition-colors duration-300">{{ form.errors.catatan }}</p>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-100 dark:border-slate-700 flex justify-end transition-colors duration-300">
                            <button type="submit" :disabled="form.processing" class="px-8 py-3 bg-blue-600 dark:bg-blue-500 hover:bg-blue-700 dark:hover:bg-blue-400 text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 dark:shadow-none transition-all flex items-center gap-2 disabled:opacity-50">
                                <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span v-if="!form.processing">Proses Transfer</span>
                                <span v-else>Memproses...</span>
                            </button>
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>