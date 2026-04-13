<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({ kolams: Array, inventories: Array, rules: Array });

// State Navigasi Wizard
const step = ref(1);

const form = useForm({
    kolam_id: '',
    
    // Step 1: Mortalitas
    jumlah_mati: 0,
    catatan_kematian: 'Aman, tidak ada indikasi penyakit',
    
    // Step 2: Kualitas Air
    suhu: '',
    ph: '',
    kondisi_visual: 'Jernih',
    berat_sample: '',
    
    // Step 3: Pakan
    frekuensi_pakan: 2, 
    rule_id: '',
    rekomendasi_sistem: '',
    pakan_aktual: '',
    feeds: [{ inventory_id: '', rasio: 1 }]
});

// State untuk menyimpan hasil kalkulasi AI secara Real-Time
const analisis = ref(null);

// AI REAL-TIME: Memantau perubahan input operator & pilihan frekuensi
watch([() => form.kolam_id, () => form.jumlah_mati, () => form.suhu, () => form.ph, () => form.kondisi_visual, () => form.berat_sample, () => form.frekuensi_pakan], () => {
    if (form.kolam_id && form.suhu && form.ph && form.berat_sample) {
        // Ambil data kolam
        const kolam = props.kolams.find(k => k.id === form.kolam_id);
        const sisaIkan = kolam.jumlah_ikan - (form.jumlah_mati || 0);
        
        // 1. Kalkulasi Biomassa
        const biomassaKg = (sisaIkan * form.berat_sample) / 1000;
        
        // 2. Tentukan Feeding Ratio (FR) Dinamis berdasarkan Berat
        const feedingRatio = form.berat_sample >= 200 ? 0.025 : 0.05; // 2.5% atau 5%
        const feedingRatioText = form.berat_sample >= 200 ? '2.5%' : '5%';

        // 3. Hitung Dosis Pakan
        const dosisHarian = (biomassaKg * feedingRatio);
        const dosisPerSesi = dosisHarian / form.frekuensi_pakan; // Dibagi 2 atau 3

        // 4. Mesin Inferensi Rule Pakar Kualitas Air
        let kodeTarget = 'R01'; let pct = 100;
        if (form.ph < 6.5 || form.ph > 8.5 || form.kondisi_visual === 'Berbusa') {
            kodeTarget = 'R03'; pct = 0;
        } else if (form.suhu < 25 || form.suhu > 32 || form.kondisi_visual === 'Keruh') {
            kodeTarget = 'R02'; pct = 50;
        }

        // 5. Rekomendasi Final (Dipotong jika kualitas air buruk)
        const rekomendasiAkhir = dosisPerSesi * (pct / 100);
        
        // Cari ID Rule yang sesuai dari database
        const matchRule = props.rules.find(r => r.kode_rule === kodeTarget);
        
        // Update Form Otomatis
        if(matchRule) form.rule_id = matchRule.id;
        form.rekomendasi_sistem = rekomendasiAkhir.toFixed(2);
        form.pakan_aktual = rekomendasiAkhir.toFixed(2);

        // Update UI Visual
        analisis.value = {
            biomassa: biomassaKg.toFixed(2),
            feedingRatioText: feedingRatioText, // Label FR Dinamis
            dosisHarian: dosisHarian.toFixed(2),
            dosisPerSesi: dosisPerSesi.toFixed(2),
            kode_rule: kodeTarget,
            rekomendasi: rekomendasiAkhir.toFixed(2),
            frekuensi: form.frekuensi_pakan,
            gap_jam: form.frekuensi_pakan === 2 ? 8 : 6
        };
    }
});

// Fungsi Multi-Pakan
const addFeed = () => { form.feeds.push({ inventory_id: '', rasio: 1 }); };
const removeFeed = (index) => { if(form.feeds.length > 1) form.feeds.splice(index, 1); };
const hitungEstimasiKg = (rasio) => {
    const totalRasio = form.feeds.reduce((sum, item) => sum + Number(item.rasio), 0);
    if (!form.pakan_aktual || totalRasio === 0) return 0;
    return ((rasio / totalRasio) * form.pakan_aktual).toFixed(2);
};

const submit = () => { form.post(route('operasi.store')); };
</script>

<template>
    <Head title="Operasi Harian" />
    <AuthenticatedLayout>
        <template #header><h2 class="font-semibold text-xl text-gray-800">Alur Operasi Harian Terpadu</h2></template>
        
        <div class="py-12"><div class="max-w-4xl mx-auto sm:px-6 lg:px-8"><div class="bg-white p-8 shadow-sm sm:rounded-lg">
            
            <div class="flex items-center mb-8 border-b pb-4">
                <div :class="step >= 1 ? 'text-blue-600 font-bold' : 'text-gray-400'" class="flex-1 text-center border-b-4 pb-2 transition-all" :style="step >= 1 ? 'border-color: #2563eb' : ''">1. Mortalitas</div>
                <div :class="step >= 2 ? 'text-blue-600 font-bold' : 'text-gray-400'" class="flex-1 text-center border-b-4 pb-2 transition-all" :style="step >= 2 ? 'border-color: #2563eb' : ''">2. Parameter Air</div>
                <div :class="step >= 3 ? 'text-green-600 font-bold' : 'text-gray-400'" class="flex-1 text-center border-b-4 pb-2 transition-all" :style="step >= 3 ? 'border-color: #16a34a' : ''">3. Pemberian Pakan</div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                
                <div class="bg-gray-50 p-4 rounded-md mb-6 border border-gray-200">
                    <label class="block text-sm font-bold text-gray-700">Pilih Kolam Tujuan</label>
                    <select v-model="form.kolam_id" class="mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                        <option value="" disabled>-- Pilih Kolam --</option>
                        <option v-for="kolam in kolams" :key="kolam.id" :value="kolam.id">{{ kolam.nama_kolam }} (Populasi: {{ kolam.jumlah_ikan }})</option>
                    </select>
                </div>

                <div v-show="step === 1" class="space-y-4">
                    <h3 class="text-lg font-bold border-b pb-2">Pengecekan Kematian Ikan</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jumlah Ekor Mati (Isi 0 jika aman)</label>
                            <input v-model="form.jumlah_mati" type="number" min="0" class="mt-1 w-full border-gray-300 rounded-md" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Catatan Khusus</label>
                            <input v-model="form.catatan_kematian" type="text" class="mt-1 w-full border-gray-300 rounded-md">
                        </div>
                    </div>
                    <div class="flex justify-end mt-6">
                        <button type="button" @click="step = 2" :disabled="!form.kolam_id" class="bg-blue-600 text-white px-6 py-2 rounded font-bold disabled:opacity-50 transition">Lanjut ke Kualitas Air &raquo;</button>
                    </div>
                </div>

                <div v-show="step === 2" class="space-y-4">
                    <h3 class="text-lg font-bold border-b pb-2">Pengecekan Parameter Air & Sample</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium">Suhu (°C)</label>
                            <input v-model="form.suhu" type="number" step="0.1" class="w-full rounded-md border-gray-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Tingkat pH</label>
                            <input v-model="form.ph" type="number" step="0.1" class="w-full rounded-md border-gray-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Kondisi Visual</label>
                            <select v-model="form.kondisi_visual" class="w-full rounded-md border-gray-300">
                                <option value="Jernih">Jernih</option><option value="Keruh">Keruh</option><option value="Berbusa">Berbusa</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Berat Sample (gr)</label>
                            <input v-model="form.berat_sample" type="number" step="0.1" class="w-full rounded-md border-gray-300">
                        </div>
                    </div>
                    <div class="flex justify-between mt-6">
                        <button type="button" @click="step = 1" class="bg-gray-300 text-gray-800 px-6 py-2 rounded font-bold transition">&laquo; Kembali</button>
                        <button type="button" @click="step = 3" :disabled="!form.suhu || !form.ph || !form.berat_sample" class="bg-blue-600 text-white px-6 py-2 rounded font-bold disabled:opacity-50 transition">Lanjut ke Pakan &raquo;</button>
                    </div>
                </div>

                <div v-show="step === 3" class="space-y-6">
                    <h3 class="text-lg font-bold border-b pb-2">Manajemen Pemberian Pakan</h3>
                    
                    <div v-if="analisis" class="bg-indigo-50 p-4 rounded-lg border border-indigo-200 flex items-center justify-between">
                        <div>
                            <label class="block text-sm font-bold text-indigo-900 mb-1">Target Frekuensi Pakan (Sehari)</label>
                            <p class="text-xs text-indigo-700">Mempengaruhi pembagian dosis per sesi dan jadwal pemberian berikutnya.</p>
                        </div>
                        <div class="w-1/3">
                            <select v-model="form.frekuensi_pakan" class="w-full rounded-md border-indigo-300 text-indigo-800 font-semibold focus:ring-indigo-500 shadow-sm">
                                <option :value="2">2 Kali Sehari (Jeda 8 Jam)</option>
                                <option :value="3">3 Kali Sehari (Jeda 6 Jam)</option>
                            </select>
                        </div>
                    </div>

                    <div v-if="analisis" class="bg-blue-50 p-4 rounded-lg border border-blue-200 grid grid-cols-2 gap-4">
                        <div class="text-sm space-y-1 text-blue-900">
                            <p><strong>Biomassa Terkini:</strong> {{ analisis.biomassa }} Kg</p>
                            <p><strong>Total Harian (FR {{ analisis.feedingRatioText }}):</strong> {{ analisis.dosisHarian }} Kg</p>
                            <p><strong>Dosis Sesi Ini (1/{{ analisis.frekuensi }}):</strong> {{ analisis.dosisPerSesi }} Kg</p>
                            <p><strong>Rule Sistem:</strong> <span class="font-bold text-red-600">{{ analisis.kode_rule }}</span></p>
                        </div>
                        <div class="text-center bg-white p-2 rounded shadow-sm flex flex-col justify-center border-b-4" :class="analisis.rekomendasi > 0 ? 'border-green-500' : 'border-red-500'">
                            <p class="text-xs font-bold text-gray-500 uppercase">Rekomendasi Final</p>
                            <p class="text-3xl font-black text-blue-700">{{ analisis.rekomendasi }} <span class="text-sm">Kg</span></p>
                            <p v-if="analisis.rekomendasi > 0" class="text-xs text-green-600 mt-2 font-semibold bg-green-50 rounded py-1">
                                Jadwal Berikutnya: <br>+{{ analisis.gap_jam }} Jam dari sekarang
                            </p>
                        </div>
                    </div>

                    <div v-if="analisis" class="bg-gray-50 p-4 rounded border border-gray-200">
                        <label class="block font-bold text-gray-800 mb-2">Total Pakan Aktual (Kg)</label>
                        <input v-model="form.pakan_aktual" type="number" step="0.01" class="w-1/3 border-gray-300 rounded font-bold text-green-700 text-lg">
                        
                        <div class="mt-4 pt-4 border-t border-gray-300">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-bold text-gray-700">Komposisi Pakan Gudang</span>
                                <button type="button" @click="addFeed" class="text-xs bg-blue-200 text-blue-800 px-3 py-1.5 rounded font-bold hover:bg-blue-300">+ Tambah Bahan</button>
                            </div>
                            <div v-for="(feed, index) in form.feeds" :key="index" class="flex gap-2 mb-2 items-center">
                                <select v-model="feed.inventory_id" class="flex-grow text-sm rounded border-gray-300 focus:ring-blue-500 focus:border-blue-500" required>
                                    <option value="" disabled>Pilih Pakan...</option>
                                    <option v-for="item in inventories" :key="item.id" :value="item.id">{{ item.nama_pakan }} (Stok: {{ item.total_stok_kg }}Kg)</option>
                                </select>
                                <input v-model="feed.rasio" type="number" min="1" class="w-16 text-sm rounded border-gray-300 text-center focus:ring-blue-500 focus:border-blue-500" placeholder="Rasio" required>
                                <div class="w-24 text-center font-bold text-green-700 bg-green-100 p-1.5 rounded border border-green-200">{{ hitungEstimasiKg(feed.rasio) }} Kg</div>
                                <button type="button" @click="removeFeed(index)" v-if="form.feeds.length > 1" class="text-red-500 font-bold px-2 hover:text-red-700">&times;</button>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between mt-8 border-t pt-4">
                        <button type="button" @click="step = 2" class="bg-gray-300 text-gray-800 px-6 py-2 rounded font-bold transition">&laquo; Kembali</button>
                        <button type="submit" class="bg-green-600 text-white px-8 py-3 rounded-lg font-black shadow-lg hover:bg-green-700 transition" :disabled="form.processing || !analisis">SIMPAN OPERASI HARIAN</button>
                    </div>
                </div>

            </form>
        </div></div></div>
    </AuthenticatedLayout>
</template>