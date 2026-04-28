<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    kolams: Array
});

const form = useForm({
    kolam_id: '',
    jenis_panen: 'Parsial',
    tanggal_panen: new Date().toISOString().split('T')[0],
    jumlah_ikan: '',
    berat_total_kg: '',
    catatan: ''
});

const selectedKolam = computed(() => {
    return props.kolams.find(k => k.id === form.kolam_id);
});

// =====================================================================
// KECERDASAN UI (MEMANTAU KETIKAN ANGKA & AUTO-CALCULATE BERAT)
// =====================================================================
watch(() => form.jumlah_ikan, (newJumlah) => {
    if (selectedKolam.value) {
        const populasiMaksimal = selectedKolam.value.jumlah_ikan;
        const beratRataGram = selectedKolam.value.berat_rata_gram || 0; // Ambil berat rata-rata kolam

        // 1. Logika pengubah dropdown jenis panen (Parsial <-> Full)
        if (newJumlah >= populasiMaksimal && form.jenis_panen !== 'Full') {
            form.jenis_panen = 'Full';
        } else if (newJumlah < populasiMaksimal && newJumlah > 0 && form.jenis_panen !== 'Parsial') {
            form.jenis_panen = 'Parsial';
        }

        // 2. LOGIKA AUTO-CALCULATE BERAT TOTAL (KG)
        // Jika jumlah ikan valid dan kolam memiliki data berat rata-rata
        if (newJumlah > 0 && beratRataGram > 0) {
            // Rumus: (Jumlah Ikan * Berat dalam Gram) / 1000 untuk dapat Kg
            form.berat_total_kg = ((newJumlah * beratRataGram) / 1000).toFixed(2);
        } else if (!newJumlah) {
            // Kosongkan berat jika jumlah ikan dihapus
            form.berat_total_kg = '';
        }
    }
});

// Jika kolam diganti, reset inputan
watch(() => form.kolam_id, () => {
    form.jumlah_ikan = '';
    form.berat_total_kg = '';
});

// =====================================================================
// KECERDASAN UI (SAAT DROPDOWN DIKLIK MANUAL)
// =====================================================================
const onJenisPanenChange = () => {
    if (selectedKolam.value) {
        const populasiMaksimal = selectedKolam.value.jumlah_ikan;
        
        if (form.jenis_panen === 'Full') {
            // Mengubah ini otomatis akan memicu fungsi watch di atas (sehingga berat juga ikut terhitung)
            form.jumlah_ikan = populasiMaksimal;
        } else if (form.jenis_panen === 'Parsial' && form.jumlah_ikan >= populasiMaksimal) {
            form.jenis_panen = 'Full';
        }
    }
};

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
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Catat Panen Baru</h2>
                    <p class="text-sm text-slate-500 mt-1">Masukkan data hasil panen (parsial atau full) ke dalam sistem.</p>
                </div>
                <Link :href="route('panen.index')" class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-bold rounded-xl hover:bg-slate-200 transition">
                    Kembali ke Riwayat
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-white p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:rounded-3xl border border-slate-100">
                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Pilih Kolam Panen <span class="text-red-500">*</span></label>
                                <select v-model="form.kolam_id" required class="w-full border-slate-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 text-sm">
                                    <option value="" disabled>-- Pilih Kolam Budidaya --</option>
                                    <option v-for="kolam in kolams" :key="kolam.id" :value="kolam.id" :disabled="kolam.jumlah_ikan === 0">
                                        {{ kolam.nama_kolam }} - Sisa: {{ kolam.jumlah_ikan.toLocaleString('id-ID') }} Ekor (± {{ kolam.berat_rata_gram }} gr/ekor)
                                    </option>
                                </select>
                                <p v-if="form.errors.kolam_id" class="text-red-500 text-xs mt-1">{{ form.errors.kolam_id }}</p>

                                <div v-if="selectedKolam" class="mt-3 p-4 bg-blue-50 border border-blue-100 rounded-xl flex items-start gap-3 transition-all">
                                    <svg class="w-5 h-5 text-blue-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div>
                                        <p class="text-sm font-bold text-blue-900">Estimasi Berat Otomatis</p>
                                        <p class="text-xs text-blue-700 mt-1">
                                            Sistem akan otomatis menghitung <b>Berat Total</b> berdasarkan ukuran sampel terakhir (<b>{{ selectedKolam.berat_rata_gram }} gram/ekor</b>). Anda tetap bisa mengubah angkanya secara manual jika timbangan aktual berbeda.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Jenis Panen <span class="text-red-500">*</span></label>
                                <select v-model="form.jenis_panen" @change="onJenisPanenChange" required class="w-full border-slate-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 text-sm font-medium" :class="{'bg-emerald-50 text-emerald-700 border-emerald-300': form.jenis_panen === 'Full'}">
                                    <option value="Parsial" :disabled="selectedKolam && form.jumlah_ikan >= selectedKolam.jumlah_ikan">
                                        Panen Parsial (Sebagian / Sortir)
                                    </option>
                                    <option value="Full">Panen Full (Total / Kosongkan Kolam)</option>
                                </select>
                                <p class="text-[10px] text-slate-400 mt-1">*Bisa berubah otomatis sesuai angka yang diketik.</p>
                                <p v-if="form.errors.jenis_panen" class="text-red-500 text-xs mt-1">{{ form.errors.jenis_panen }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Tanggal Panen <span class="text-red-500">*</span></label>
                                <input type="date" v-model="form.tanggal_panen" required class="w-full border-slate-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <p v-if="form.errors.tanggal_panen" class="text-red-500 text-xs mt-1">{{ form.errors.tanggal_panen }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Jumlah Ikan (Ekor) <span class="text-red-500">*</span></label>
                                <input type="number" v-model="form.jumlah_ikan" required min="1" :max="selectedKolam ? selectedKolam.jumlah_ikan : ''" class="w-full border-slate-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 text-sm font-bold text-blue-700" placeholder="Ketik jumlah ekor...">
                                <p v-if="form.errors.jumlah_ikan" class="text-red-500 text-xs mt-1">{{ form.errors.jumlah_ikan }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Berat Total (Kg) <span class="text-red-500">*</span></label>
                                <input type="number" v-model="form.berat_total_kg" required step="0.01" min="0.1" class="w-full border-slate-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 text-sm font-bold" placeholder="Terhitung otomatis..." :class="{'bg-slate-50': form.berat_total_kg !== ''}">
                                <p v-if="form.errors.berat_total_kg" class="text-red-500 text-xs mt-1">{{ form.errors.berat_total_kg }}</p>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Catatan Tambahan (Opsional)</label>
                                <textarea v-model="form.catatan" rows="3" class="w-full border-slate-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="Contoh: Pembeli dari mitra pasar tradisional..."></textarea>
                                <p v-if="form.errors.catatan" class="text-red-500 text-xs mt-1">{{ form.errors.catatan }}</p>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-100 flex justify-end">
                            <button type="submit" :disabled="form.processing" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 transition flex items-center gap-2 disabled:opacity-50">
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