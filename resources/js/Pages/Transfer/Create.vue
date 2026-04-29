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
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Transfer Ikan</h2>
                    <p class="text-sm text-slate-500 mt-1">Pindahkan populasi ikan antar kolam dengan aman.</p>
                </div>
                <Link :href="route('transfer.index')" class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-bold rounded-xl hover:bg-slate-200 transition">
                    Batal
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:rounded-3xl border border-slate-100">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Kolam Sumber (Asal) <span class="text-red-500">*</span></label>
                                <select v-model="form.kolam_asal_id" required class="w-full border-slate-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 text-sm">
                                    <option value="" disabled>-- Pilih Kolam Asal --</option>
                                    <option v-for="kolam in kolams" :key="kolam.id" :value="kolam.id" :disabled="kolam.jumlah_ikan === 0">
                                        {{ kolam.nama_kolam }} ({{ kolam.jumlah_ikan }} ekor)
                                    </option>
                                </select>
                                <p v-if="form.errors.kolam_asal_id" class="text-red-500 text-xs mt-1">{{ form.errors.kolam_asal_id }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Kolam Target (Tujuan) <span class="text-red-500">*</span></label>
                                <select v-model="form.kolam_tujuan_id" required :disabled="!form.kolam_asal_id" class="w-full border-slate-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 text-sm disabled:bg-slate-50">
                                    <option value="" disabled>-- Pilih Kolam Tujuan --</option>
                                    <option v-for="kolam in availableTujuan" :key="kolam.id" :value="kolam.id">
                                        {{ kolam.nama_kolam }} ({{ kolam.jumlah_ikan }} ekor)
                                    </option>
                                </select>
                                <p v-if="form.errors.kolam_tujuan_id" class="text-red-500 text-xs mt-1">{{ form.errors.kolam_tujuan_id }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Tanggal Transfer <span class="text-red-500">*</span></label>
                                <input type="date" v-model="form.tanggal_transfer" required class="w-full border-slate-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 text-sm">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Jumlah Ikan (Ekor) <span class="text-red-500">*</span></label>
                                <input type="number" v-model="form.jumlah_ikan" required :max="selectedAsal?.jumlah_ikan" min="1" class="w-full border-slate-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 text-sm font-bold text-blue-600" placeholder="Contoh: 100">
                                <p v-if="selectedAsal" class="text-[10px] text-slate-400 mt-1">Maksimal pemindahan: {{ selectedAsal.jumlah_ikan }} ekor</p>
                                <p v-if="form.errors.jumlah_ikan" class="text-red-500 text-xs mt-1">{{ form.errors.jumlah_ikan }}</p>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Keterangan / Alasan (Opsional)</label>
                                <textarea v-model="form.catatan" rows="3" class="w-full border-slate-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="Contoh: Sortir ukuran atau pecah kepadatan kolam..."></textarea>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-100 flex justify-end">
                            <button type="submit" :disabled="form.processing" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 transition flex items-center gap-2">
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