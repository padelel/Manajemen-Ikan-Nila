<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({ kolams: Array });

const form = useForm({
    kolam_id: '',
    tanggal_tebar: new Date().toISOString().slice(0, 10),
    jumlah_ikan: '',
    berat_rata_gram: '',
    sumber_benih: '',
    catatan: ''
});

// Variabel untuk kepadatan tebar
const sistem_budidaya = ref(80);

// Mendapatkan data kolam yang sedang dipilih di dropdown
const selectedKolam = computed(() => {
    if (!form.kolam_id) return null;
    return props.kolams.find(k => k.id === form.kolam_id);
});

// Kalkulator Volume Air Real-Time berdasarkan kolam yang dipilih
const kalkulasi = computed(() => {
    if (!selectedKolam.value) return { volume: '0.00', rekomendasi: 0 };

    let p = parseFloat(selectedKolam.value.panjang_m) || 0;
    let l = parseFloat(selectedKolam.value.lebar_m) || 0;
    let t = parseFloat(selectedKolam.value.kedalaman_m) || 0;
    
    let volume = 0;
    if (selectedKolam.value.bentuk_kolam === 'Bundar') {
        let r = p / 2; // r = Jari-jari (setengah diameter)
        volume = 3.14159 * Math.pow(r, 2) * t;
    } else {
        volume = p * l * t;
    }
    
    let rekomendasi = Math.floor(volume * sistem_budidaya.value);
    return { volume: volume.toFixed(2), rekomendasi: rekomendasi > 0 ? rekomendasi : 0 };
});

// Fungsi untuk memasukkan angka rekomendasi ke dalam input jumlah_ikan
const pakaiRekomendasi = () => {
    if (kalkulasi.value.rekomendasi > 0) {
        form.jumlah_ikan = kalkulasi.value.rekomendasi;
    }
};

const submit = () => {
    form.post('/tebar');
};
</script>

<template>
    <Head title="Catat Tebar Benih" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight transition-colors duration-300">Catat Tebar Benih</h2>
                <Link :href="route('tebar.index')" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-bold rounded-xl hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors duration-300">
                    Kembali
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-slate-800 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] sm:rounded-3xl border border-slate-100 dark:border-slate-700 flex flex-col md:flex-row overflow-hidden transition-colors duration-300">
                    
                    <div class="w-full md:w-2/3 p-8 border-b md:border-b-0 md:border-r border-slate-100 dark:border-slate-700 transition-colors duration-300">
                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <h3 class="text-lg font-bold border-b border-slate-100 dark:border-slate-700 pb-2 text-slate-800 dark:text-slate-200 transition-colors duration-300">1. Target Wadah & Waktu</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 transition-colors duration-300">Target Kolam</label>
                                    <select v-model="form.kolam_id" class="w-full bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-600 text-slate-900 dark:text-slate-100 rounded-xl shadow-sm focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-colors duration-300" required>
                                        <option value="" disabled>-- Pilih Kolam --</option>
                                        <option v-for="kolam in kolams" :key="kolam.id" :value="kolam.id">
                                            {{ kolam.nama_kolam }} (Saat ini: {{ kolam.jumlah_ikan }} ekor)
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 transition-colors duration-300">Tanggal Tebar</label>
                                    <input type="date" v-model="form.tanggal_tebar" class="w-full bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-600 text-slate-900 dark:text-slate-100 rounded-xl shadow-sm focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-colors duration-300" required>
                                </div>
                            </div>

                            <h3 class="text-lg font-bold border-b border-slate-100 dark:border-slate-700 pb-2 text-slate-800 dark:text-slate-200 mt-8 transition-colors duration-300">2. Detail Benih</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 transition-colors duration-300">Jumlah Benih (Ekor)</label>
                                    <input type="number" v-model="form.jumlah_ikan" placeholder="Cth: 2000" class="w-full bg-white dark:bg-slate-900 border-indigo-300 dark:border-indigo-500/50 ring-1 ring-indigo-500/20 dark:ring-indigo-500/40 rounded-xl shadow-sm focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 font-bold text-indigo-700 dark:text-indigo-400 transition-colors duration-300" required min="1">
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 transition-colors duration-300">Berat Rata-rata (Gram)</label>
                                    <div class="relative">
                                        <input type="number" step="0.01" v-model="form.berat_rata_gram" placeholder="Cth: 15.5" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-600 text-slate-900 dark:text-slate-100 rounded-xl shadow-sm focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-colors duration-300" required>
                                        <span class="absolute right-3 top-2.5 text-slate-400 dark:text-slate-500 text-sm font-bold transition-colors duration-300">gr</span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 transition-colors duration-300">Sumber Benih / Supplier</label>
                                <input type="text" v-model="form.sumber_benih" placeholder="Nama PT atau Lokasi Pembibitan" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-600 text-slate-900 dark:text-slate-100 rounded-xl shadow-sm focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-colors duration-300">
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 transition-colors duration-300">Catatan (Opsional)</label>
                                <textarea v-model="form.catatan" rows="3" placeholder="Informasi tambahan terkait kondisi benih..." class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-600 text-slate-900 dark:text-slate-100 rounded-xl shadow-sm focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-colors duration-300"></textarea>
                            </div>

                            <div class="flex justify-end items-center gap-4 pt-4 border-t border-slate-100 dark:border-slate-700 transition-colors duration-300">
                                <Link :href="route('tebar.index')" class="text-sm font-bold text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition-colors duration-300">
                                    Batal
                                </Link>

                                <button type="submit" :disabled="form.processing" class="bg-indigo-600 dark:bg-indigo-500 text-white px-8 py-3 rounded-xl shadow-lg shadow-indigo-200/50 dark:shadow-none hover:bg-indigo-700 dark:hover:bg-indigo-400 text-sm font-bold transition-all disabled:opacity-50">
                                    Simpan Data Tebar
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="w-full md:w-1/3 bg-indigo-50/50 dark:bg-slate-800/80 p-8 transition-colors duration-300">
                        <div class="sticky top-8">
                            <h4 class="font-black text-indigo-900 dark:text-indigo-300 text-lg flex items-center gap-2 mb-6 transition-colors duration-300">
                                💡 Kalkulator Kapasitas
                            </h4>
                            
                            <div v-if="!selectedKolam" class="bg-white dark:bg-slate-700/50 p-6 rounded-2xl border border-indigo-100 dark:border-slate-600 text-center transition-colors duration-300">
                                <svg class="w-12 h-12 text-indigo-200 dark:text-slate-500 mx-auto mb-3 transition-colors duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672L13.684 16.6m0 0l-2.51 2.225.569-9.47 5.227 7.917-3.286-.672zm-7.518-.267A8.25 8.25 0 1120.25 10.5M8.288 14.212A5.25 5.25 0 1117.25 10.5" />
                                </svg>
                                <p class="text-sm text-indigo-800 dark:text-slate-400 font-medium leading-relaxed transition-colors duration-300">
                                    Pilih kolam terlebih dahulu di form untuk melihat estimasi daya tampung maksimal.
                                </p>
                            </div>

                            <div v-else class="space-y-5 animate-in fade-in zoom-in duration-300">
                                
                                <div class="bg-white dark:bg-slate-700/50 p-4 rounded-2xl border border-indigo-100 dark:border-slate-600 shadow-sm transition-colors duration-300">
                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-widest mb-1 transition-colors duration-300">Dimensi Terdeteksi</p>
                                    <p class="text-sm font-bold text-slate-800 dark:text-slate-200 transition-colors duration-300">
                                        <span v-if="selectedKolam.bentuk_kolam === 'Bundar'">
                                            Diameter {{ selectedKolam.panjang_m }}m &times; Tinggi {{ selectedKolam.kedalaman_m }}m
                                        </span>
                                        <span v-else>
                                            {{ selectedKolam.panjang_m }}m &times; {{ selectedKolam.lebar_m }}m &times; {{ selectedKolam.kedalaman_m }}m
                                        </span>
                                    </p>
                                    <p class="text-xs text-indigo-600 dark:text-indigo-400 font-bold mt-1 bg-indigo-50 dark:bg-indigo-500/20 inline-block px-2 py-0.5 rounded-md transition-colors duration-300">Bentuk {{ selectedKolam.bentuk_kolam }}</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-indigo-800 dark:text-indigo-300 mb-2 uppercase tracking-wider transition-colors duration-300">Tingkat Kepadatan Aerasi</label>
                                    <select v-model="sistem_budidaya" class="w-full bg-white dark:bg-slate-900 rounded-xl border-indigo-200 dark:border-slate-600 text-indigo-900 dark:text-indigo-300 shadow-sm font-semibold focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 text-sm transition-colors duration-300">
                                        <option :value="50">Konvensional (50 ekor/m³)</option>
                                        <option :value="80">Semi-Intensif (80 ekor/m³)</option>
                                        <option :value="120">Intensif / Bioflok (120 ekor/m³)</option>
                                    </select>
                                </div>

                                <div class="bg-indigo-600 dark:bg-indigo-500/90 p-5 rounded-2xl shadow-lg shadow-indigo-200 dark:shadow-none text-white text-center relative overflow-hidden transition-colors duration-300">
                                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-5 rounded-full blur-xl"></div>
                                    <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-16 h-16 bg-white opacity-10 rounded-full blur-lg"></div>
                                    
                                    <div class="relative z-10">
                                        <div class="flex justify-between items-end mb-4 pb-4 border-b border-indigo-500/50 dark:border-white/10">
                                            <div class="text-left">
                                                <p class="text-[10px] text-indigo-200 dark:text-indigo-100 font-bold uppercase tracking-wider">Volume Air</p>
                                                <p class="text-xl font-black">{{ kalkulasi.volume }} <span class="text-xs font-normal">m³</span></p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-[10px] text-indigo-200 dark:text-indigo-100 font-bold uppercase tracking-wider">Kolam Terisi</p>
                                                <p class="text-xl font-black text-amber-300">{{ selectedKolam.jumlah_ikan }} <span class="text-xs font-normal">ekor</span></p>
                                            </div>
                                        </div>

                                        <p class="text-[10px] text-indigo-200 dark:text-indigo-100 font-bold uppercase tracking-widest mb-1">Daya Tampung Maksimal</p>
                                        <p class="text-4xl font-black mb-4">{{ kalkulasi.rekomendasi.toLocaleString('id-ID') }} <span class="text-sm font-bold opacity-80">Ekor</span></p>
                                        
                                        <button @click="pakaiRekomendasi" type="button" class="bg-white dark:bg-slate-900 text-indigo-700 dark:text-indigo-400 text-sm font-bold px-4 py-2.5 rounded-xl hover:bg-indigo-50 dark:hover:bg-slate-800 w-full transition-colors duration-300 shadow-sm flex justify-center items-center gap-2">
                                            Gunakan Angka Ini
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7-7m7-7H3" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                
                                <p class="text-[10px] text-indigo-600 dark:text-indigo-300 font-medium leading-relaxed bg-indigo-100/50 dark:bg-indigo-500/10 p-3 rounded-xl border border-indigo-100 dark:border-indigo-500/20 transition-colors duration-300">
                                    <strong class="font-bold">Catatan:</strong> Jika kolam saat ini sudah terisi ikan, pastikan Anda menyesuaikan perhitungan agar total populasi tidak melebihi rekomendasi maksimal untuk menjaga FCR (Feed Conversion Ratio).
                                </p>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>