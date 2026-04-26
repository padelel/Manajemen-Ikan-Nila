<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3'; // Tambahkan Link di sini
import { watch, computed } from 'vue';

const props = defineProps({ 
    kolams: Array 
});

const form = useForm({
    kolam_id: '',
    jenis_panen: 'Parsial',
    tanggal_panen: new Date().toISOString().slice(0, 10),
    jumlah_ikan: '',
    berat_total_kg: '',
    catatan: ''
});

const selectedKolam = computed(() => {
    return props.kolams.find(k => k.id === form.kolam_id);
});

watch([() => form.kolam_id, () => form.jenis_panen], ([newKolamId, newJenisPanen]) => {
    if (newJenisPanen === 'Full' && selectedKolam.value) {
        form.jumlah_ikan = selectedKolam.value.jumlah_ikan;
    }
});

watch(() => form.jumlah_ikan, (newJumlah) => {
    if (selectedKolam.value && newJumlah > 0) {
        const kalkulasi = (newJumlah * selectedKolam.value.berat_rata_gram) / 1000;
        form.berat_total_kg = kalkulasi.toFixed(2);
    } else {
        form.berat_total_kg = '';
    }
});

const submit = () => {
    form.post('/panen');
};
</script>

<template>
    <Head title="Catat Panen" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Catat Hasil Panen</h2>
        </template>

        <div class="py-8">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:rounded-3xl border border-slate-100">
                    
                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Pilih Kolam</label>
                                <select v-model="form.kolam_id" class="w-full border-slate-200 rounded-xl shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                                    <option value="" disabled>-- Pilih Kolam --</option>
                                    <option v-for="kolam in kolams" :key="kolam.id" :value="kolam.id">
                                        {{ kolam.nama_kolam }} (Tersedia: {{ kolam.jumlah_ikan }} ekor)
                                    </option>
                                </select>
                                <p v-if="form.errors.kolam_id" class="text-red-500 text-xs mt-1">{{ form.errors.kolam_id }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Jenis Panen</label>
                                <select v-model="form.jenis_panen" class="w-full border-slate-200 rounded-xl shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                                    <option value="Parsial">Parsial (Sebagian)</option>
                                    <option value="Full">Full (Seluruh Isi Kolam)</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Tanggal Panen</label>
                                <input type="date" v-model="form.tanggal_panen" class="w-full border-slate-200 rounded-xl shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Jumlah Panen (Ekor)</label>
                                <input 
                                    type="number" 
                                    v-model="form.jumlah_ikan" 
                                    :readonly="form.jenis_panen === 'Full'"
                                    :class="{'bg-slate-50 text-slate-500 cursor-not-allowed': form.jenis_panen === 'Full'}"
                                    class="w-full border-slate-200 rounded-xl shadow-sm focus:border-emerald-500 focus:ring-emerald-500" 
                                    required 
                                    min="1"
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Estimasi Berat (Kg)</label>
                                <div class="relative">
                                    <input 
                                        type="number" 
                                        step="0.01" 
                                        v-model="form.berat_total_kg" 
                                        class="w-full border-slate-200 rounded-xl shadow-sm focus:border-emerald-500 focus:ring-emerald-500 pr-10" 
                                        required
                                    >
                                    <span class="absolute right-3 top-2.5 text-slate-400 text-sm font-bold">Kg</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Catatan Tambahan (Opsional)</label>
                            <textarea v-model="form.catatan" rows="3" placeholder="Misal: Kualitas ikan sangat baik..." class="w-full border-slate-200 rounded-xl shadow-sm focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                        </div>

                        <div class="flex justify-end items-center gap-4 pt-4 border-t border-slate-100">
                            <Link 
                                :href="route('panen.index')" 
                                class="text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors"
                            >
                                Batal
                            </Link>

                            <button 
                                type="submit" 
                                :disabled="form.processing" 
                                class="bg-emerald-600 text-white px-8 py-3 rounded-xl shadow-lg shadow-emerald-200/50 hover:bg-emerald-700 text-sm font-bold transition-all disabled:opacity-50"
                            >
                                Simpan Data Panen
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>