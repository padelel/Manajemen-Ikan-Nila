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
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Catat Tebar Benih</h2>
        </template>

        <div class="py-8">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:rounded-3xl border border-slate-100 flex flex-col md:flex-row overflow-hidden">
                    
                    <!-- BAGIAN KIRI: FORM INPUT -->
                    <div class="w-full md:w-2/3 p-8 border-b md:border-b-0 md:border-r border-slate-100">
                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <h3 class="text-lg font-bold border-b border-slate-100 pb-2 text-slate-800">1. Target Wadah & Waktu</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Target Kolam</label>
                                    <select v-model="form.kolam_id" class="w-full border-slate-200 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50" required>
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

                            <h3 class="text-lg font-bold border-b border-slate-100 pb-2 text-slate-800 mt-8">2. Detail Benih</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Jumlah Benih (Ekor)</label>
                                    <input type="number" v-model="form.jumlah_ikan" placeholder="Cth: 2000" class="w-full border-indigo-300 ring-1 ring-indigo-500/20 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-bold text-indigo-700" required min="1">
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

                    <!-- BAGIAN KANAN: KALKULATOR ESTIMASI -->
                    <div class="w-full md:w-1/3 bg-indigo-50/50 p-8">
                        <div class="sticky top-8">
                            <h4 class="font-black text-indigo-900 text-lg flex items-center gap-2 mb-6">
                                💡 Kalkulator Kapasitas
                            </h4>
                            
                            <div v-if="!selectedKolam" class="bg-white p-6 rounded-2xl border border-indigo-100 text-center">
                                <svg class="w-12 h-12 text-indigo-200 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672L13.684 16.6m0 0l-2.51 2.225.569-9.47 5.227 7.917-3.286-.672zm-7.518-.267A8.25 8.25 0 1120.25 10.5M8.288 14.212A5.25 5.25 0 1117.25 10.5" />
                                </svg>
                                <p class="text-sm text-indigo-800 font-medium leading-relaxed">
                                    Pilih kolam terlebih dahulu di form untuk melihat estimasi daya tampung maksimal.
                                </p>
                            </div>

                            <div v-else class="space-y-5 animate-in fade-in zoom-in duration-300">
                                
                                <div class="bg-white p-4 rounded-2xl border border-indigo-100 shadow-sm">
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-1">Dimensi Terdeteksi</p>
                                    <p class="text-sm font-bold text-slate-800">
                                        <span v-if="selectedKolam.bentuk_kolam === 'Bundar'">
                                            Diameter {{ selectedKolam.panjang_m }}m &times; Tinggi {{ selectedKolam.kedalaman_m }}m
                                        </span>
                                        <span v-else>
                                            {{ selectedKolam.panjang_m }}m &times; {{ selectedKolam.lebar_m }}m &times; {{ selectedKolam.kedalaman_m }}m
                                        </span>
                                    </p>
                                    <p class="text-xs text-indigo-600 font-bold mt-1 bg-indigo-50 inline-block px-2 py-0.5 rounded-md">Bentuk {{ selectedKolam.bentuk_kolam }}</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-indigo-800 mb-2 uppercase tracking-wider">Tingkat Kepadatan Aerasi</label>
                                    <select v-model="sistem_budidaya" class="w-full rounded-xl border-indigo-200 text-indigo-900 shadow-sm font-semibold focus:border-indigo-500 focus:ring-indigo-500 bg-white text-sm">
                                        <option :value="50">Konvensional (50 ekor/m³)</option>
                                        <option :value="80">Semi-Intensif (80 ekor/m³)</option>
                                        <option :value="120">Intensif / Bioflok (120 ekor/m³)</option>
                                    </select>
                                </div>

                                <div class="bg-indigo-600 p-5 rounded-2xl shadow-lg shadow-indigo-200 text-white text-center relative overflow-hidden">
                                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-5 rounded-full blur-xl"></div>
                                    <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-16 h-16 bg-white opacity-10 rounded-full blur-lg"></div>
                                    
                                    <div class="relative z-10">
                                        <div class="flex justify-between items-end mb-4 pb-4 border-b border-indigo-500/50">
                                            <div class="text-left">
                                                <p class="text-[10px] text-indigo-200 font-bold uppercase tracking-wider">Volume Air</p>
                                                <p class="text-xl font-black">{{ kalkulasi.volume }} <span class="text-xs font-normal">m³</span></p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-[10px] text-indigo-200 font-bold uppercase tracking-wider">Kolam Terisi</p>
                                                <p class="text-xl font-black text-amber-300">{{ selectedKolam.jumlah_ikan }} <span class="text-xs font-normal">ekor</span></p>
                                            </div>
                                        </div>

                                        <p class="text-[10px] text-indigo-200 font-bold uppercase tracking-widest mb-1">Daya Tampung Maksimal</p>
                                        <p class="text-4xl font-black mb-4">{{ kalkulasi.rekomendasi.toLocaleString('id-ID') }} <span class="text-sm font-bold opacity-80">Ekor</span></p>
                                        
                                        <button @click="pakaiRekomendasi" type="button" class="bg-white text-indigo-700 text-sm font-bold px-4 py-2.5 rounded-xl hover:bg-indigo-50 w-full transition shadow-sm flex justify-center items-center gap-2">
                                            Gunakan Angka Ini
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                
                                <p class="text-[10px] text-indigo-600 font-medium leading-relaxed bg-indigo-100/50 p-3 rounded-xl border border-indigo-100">
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