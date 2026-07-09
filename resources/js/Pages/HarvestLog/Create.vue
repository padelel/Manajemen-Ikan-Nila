<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    kolams: Array
});

const form = useForm({
    kolam_id: '',
    jenis_panen: 'Full',
    tanggal_panen: new Date().toISOString().split('T')[0],
    jumlah_ikan: '',
    berat_total_kg: '',
    catatan: ''
});

const normalizeInteger = (field) => {
    form[field] = String(form[field] ?? '').replace(/[^0-9]/g, '');
};

const normalizeDecimal = (field) => {
    let value = String(form[field] ?? '');
    value = value.replace(/[^0-9.]/g, '');
    const parts = value.split('.');
    if (parts.length > 2) {
        value = `${parts.shift()}.${parts.join('')}`;
    }
    form[field] = value;
};

const selectedKolam = computed(() => {
    const selectedId = Number(form.kolam_id);
    return props.kolams.find(k => k.id === selectedId);
});

// =====================================================================
// KECERDASAN UI (MEMANTAU KETIKAN ANGKA)
// =====================================================================
watch(() => form.kolam_id, () => {
    form.jumlah_ikan = selectedKolam.value
        ? selectedKolam.value.populasi_saat_ini ?? ''
        : '';
    form.berat_total_kg = '';
});

const submit = () => {
    form.post(route('panen.store'));
};
</script>

<template>
    <Head title="Catat Panen Baru" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight transition-colors duration-300">Catat Panen Baru</h2>
                    <p class="text-sm text-slate-500 mt-1 transition-colors duration-300">Masukkan data hasil panen full ke dalam sistem.</p>
                </div>
                <Link :href="route('panen.index')" class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-bold rounded-xl hover:bg-slate-200 transition-colors duration-300">
                    Kembali ke Riwayat
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-white p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:rounded-3xl border border-slate-100 transition-colors duration-300">
                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 transition-colors duration-300">Pilih Kolam Panen <span class="text-red-500">*</span></label>
                                <select v-model="form.kolam_id" required class="w-full bg-white border-slate-200 text-slate-900 rounded-xl focus:border-blue-500 focus:ring-blue-500 text-sm transition-colors duration-300">
                                    <option value="" disabled>-- Pilih Kolam Budidaya --</option>
                                    <option v-for="kolam in kolams" :key="kolam.id" :value="kolam.id">
                                        {{ kolam.nama_kolam }} - Populasi: {{ (kolam.populasi_saat_ini ?? 0).toLocaleString('id-ID') }} ekor
                                    </option>
                                </select>
                                <p v-if="form.errors.kolam_id" class="text-red-500 text-xs mt-1 transition-colors duration-300">{{ form.errors.kolam_id }}</p>

                                <div v-if="selectedKolam" class="mt-3 p-4 bg-blue-50 border border-blue-100 rounded-xl flex items-start gap-3 transition-colors duration-300">
                                    <svg class="w-5 h-5 text-blue-500 mt-0.5 transition-colors duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div>
                                        <p class="text-sm font-bold text-blue-900 transition-colors duration-300">Populasi Saat Ini</p>
                                        <p class="text-xs text-blue-700 mt-1 transition-colors duration-300">
                                            Estimasi populasi aktif: <b>{{ (selectedKolam.populasi_saat_ini ?? 0).toLocaleString('id-ID') }} ekor</b>. Masukkan berat total hasil panen secara manual.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 transition-colors duration-300">Jenis Panen <span class="text-red-500">*</span></label>
                                <div class="w-full rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm font-medium transition-colors duration-300">
                                    Panen Full (Total / Kosongkan Kolam)
                                </div>
                                <input type="hidden" v-model="form.jenis_panen" />
                                <p class="text-[10px] text-slate-400 mt-1 transition-colors duration-300">Operator hanya dapat mencatat panen full.</p>
                                <p v-if="form.errors.jenis_panen" class="text-red-500 text-xs mt-1 transition-colors duration-300">{{ form.errors.jenis_panen }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 transition-colors duration-300">Tanggal Panen <span class="text-red-500">*</span></label>
                                <input type="date" v-model="form.tanggal_panen" required class="w-full bg-white border-slate-200 text-slate-900 rounded-xl focus:border-blue-500 focus:ring-blue-500 text-sm transition-colors duration-300">
                                <p v-if="form.errors.tanggal_panen" class="text-red-500 text-xs mt-1 transition-colors duration-300">{{ form.errors.tanggal_panen }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 transition-colors duration-300">Jumlah Ikan (Ekor) <span class="text-red-500">*</span></label>
                                <input
                                    type="number"
                                    v-model="form.jumlah_ikan"
                                    required
                                    readonly
                                    min="1"
                                    step="1"
                                    inputmode="numeric"
                                    :max="selectedKolam ? (selectedKolam.populasi_saat_ini ?? '') : ''"
                                    @input="normalizeInteger('jumlah_ikan')"
                                    class="w-full bg-slate-100 border-slate-200 rounded-xl text-sm font-bold text-slate-700 transition-colors duration-300"
                                    placeholder="Jumlah akan diisi otomatis setelah pilih kolam"
                                >
                                <p class="text-[10px] text-slate-400 mt-1 transition-colors duration-300">Jumlah ikan disesuaikan dengan populasi aktif kolam saat ini.</p>
                                <p v-if="form.errors.jumlah_ikan" class="text-red-500 text-xs mt-1 transition-colors duration-300">{{ form.errors.jumlah_ikan }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 transition-colors duration-300">Berat Total (Kg) <span class="text-red-500">*</span></label>
                                <input
                                    type="number"
                                    v-model="form.berat_total_kg"
                                    required
                                    step="0.01"
                                    min="0.1"
                                    inputmode="decimal"
                                    @input="normalizeDecimal('berat_total_kg')"
                                    class="w-full bg-white border-slate-200 text-slate-900 rounded-xl focus:border-blue-500 focus:ring-blue-500 text-sm font-bold transition-colors duration-300"
                                    placeholder="Masukkan berat total aktual (kg)..."
                                >
                                <p v-if="form.errors.berat_total_kg" class="text-red-500 text-xs mt-1 transition-colors duration-300">{{ form.errors.berat_total_kg }}</p>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 transition-colors duration-300">Catatan Tambahan (Opsional)</label>
                                <textarea v-model="form.catatan" rows="3" class="w-full bg-white border-slate-200 text-slate-900 rounded-xl focus:border-blue-500 focus:ring-blue-500 text-sm transition-colors duration-300" placeholder="Contoh: Pembeli dari mitra pasar tradisional..."></textarea>
                                <p v-if="form.errors.catatan" class="text-red-500 text-xs mt-1 transition-colors duration-300">{{ form.errors.catatan }}</p>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-100 flex justify-end transition-colors duration-300">
                            <button type="submit" :disabled="form.processing" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 transition-all flex items-center gap-2 disabled:opacity-50">
                                <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Simpan Data Panen
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>