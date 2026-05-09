<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({ kolams: Array, inventories: Array, rules: Array });

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
    frekuensi: 2, // PERBAIKAN 1: Ubah nama dari frekuensi_pakan menjadi frekuensi
    rule_id: '',
    rekomendasi_sistem: '',
    pakan_aktual: '',
    feeds: [{ inventory_id: '', rasio: 1 }]
});

const analisis = ref(null);

// PERBAIKAN 2: Ubah watcher agar memantau form.frekuensi
watch([() => form.kolam_id, () => form.jumlah_mati, () => form.suhu, () => form.ph, () => form.kondisi_visual, () => form.berat_sample, () => form.frekuensi], () => {
    if (form.kolam_id && form.suhu && form.ph && form.berat_sample) {
        const kolam = props.kolams.find(k => k.id === form.kolam_id);
        const sisaIkan = kolam.jumlah_ikan - (form.jumlah_mati || 0);
        
        const biomassaKg = (sisaIkan * form.berat_sample) / 1000;
        
        const feedingRatio = form.berat_sample >= 200 ? 0.025 : 0.05; 
        const feedingRatioText = form.berat_sample >= 200 ? '2.5%' : '5%';

        const dosisHarian = (biomassaKg * feedingRatio);
        // PERBAIKAN 3: Dibagi menggunakan form.frekuensi
        const dosisPerSesi = dosisHarian / form.frekuensi; 

        let kodeTarget = 'R01'; let pct = 100;
        if (form.ph < 6.5 || form.ph > 8.5 || form.kondisi_visual === 'Berbusa') {
            kodeTarget = 'R03'; pct = 0;
        } else if (form.suhu < 25 || form.suhu > 32 || form.kondisi_visual === 'Keruh') {
            kodeTarget = 'R02'; pct = 50;
        }

        const rekomendasiAkhir = dosisPerSesi * (pct / 100);
        
        const matchRule = props.rules.find(r => r.kode_rule === kodeTarget);
        
        if(matchRule) form.rule_id = matchRule.id;
        form.rekomendasi_sistem = rekomendasiAkhir.toFixed(2);
        form.pakan_aktual = rekomendasiAkhir.toFixed(2);

        analisis.value = {
            biomassa: biomassaKg.toFixed(2),
            feedingRatioText: feedingRatioText, 
            dosisHarian: dosisHarian.toFixed(2),
            dosisPerSesi: dosisPerSesi.toFixed(2),
            kode_rule: kodeTarget,
            rekomendasi: rekomendasiAkhir.toFixed(2),
            frekuensi: form.frekuensi, // PERBAIKAN 4
            gap_jam: form.frekuensi === 2 ? 8 : 6 // PERBAIKAN 5
        };
    }
});

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
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-2xl text-slate-900 dark:text-slate-100 tracking-tight transition-colors duration-300">Alur Operasi Harian Terpadu</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors duration-300">Catat pengecekan harian mulai dari mortalitas hingga pemberian pakan.</p>
                </div>
            </div>
        </template>
        
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-slate-800 p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] sm:rounded-3xl border border-slate-100 dark:border-slate-700 transition-colors duration-300">
            
                    <div class="flex flex-col sm:flex-row items-center mb-8 border-b border-slate-200 dark:border-slate-700 pb-4 transition-colors duration-300">
                        <div :class="step >= 1 ? 'text-blue-600 dark:text-blue-400 border-blue-600 dark:border-blue-400 font-bold' : 'text-slate-400 dark:text-slate-500 border-transparent'" class="flex-1 w-full sm:w-auto text-center border-b-4 pb-2 transition-colors duration-300">
                            1. Mortalitas
                        </div>
                        <div :class="step >= 2 ? 'text-blue-600 dark:text-blue-400 border-blue-600 dark:border-blue-400 font-bold' : 'text-slate-400 dark:text-slate-500 border-transparent'" class="flex-1 w-full sm:w-auto text-center border-b-4 pb-2 transition-colors duration-300">
                            2. Parameter Air
                        </div>
                        <div :class="step >= 3 ? 'text-emerald-600 dark:text-emerald-400 border-emerald-600 dark:border-emerald-400 font-bold' : 'text-slate-400 dark:text-slate-500 border-transparent'" class="flex-1 w-full sm:w-auto text-center border-b-4 pb-2 transition-colors duration-300">
                            3. Pemberian Pakan
                        </div>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <div class="bg-slate-50 dark:bg-slate-700/50 p-5 rounded-2xl mb-6 border border-slate-200 dark:border-slate-600 transition-colors duration-300">
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 transition-colors duration-300">Pilih Kolam Tujuan</label>
                            <select v-model="form.kolam_id" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-600 text-slate-900 dark:text-slate-100 rounded-xl focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400 text-sm shadow-sm transition-colors duration-300" required>
                                <option value="" disabled>-- Pilih Kolam --</option>
                                <option v-for="kolam in kolams" :key="kolam.id" :value="kolam.id">{{ kolam.nama_kolam }} (Populasi: {{ kolam.jumlah_ikan }})</option>
                            </select>
                        </div>

                        <div v-show="step === 1" class="space-y-6 animate-in fade-in zoom-in duration-300">
                            <h3 class="text-lg font-bold border-b border-slate-100 dark:border-slate-700 pb-2 text-slate-800 dark:text-slate-100 transition-colors duration-300">Pengecekan Kematian Ikan</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 transition-colors duration-300">Jumlah Ekor Mati (Isi 0 jika aman)</label>
                                    <input v-model="form.jumlah_mati" type="number" min="0" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-600 text-slate-900 dark:text-slate-100 rounded-xl focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400 text-sm transition-colors duration-300" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 transition-colors duration-300">Catatan Khusus</label>
                                    <input v-model="form.catatan_kematian" type="text" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-600 text-slate-900 dark:text-slate-100 rounded-xl focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400 text-sm transition-colors duration-300">
                                </div>
                            </div>
                            <div class="flex justify-end mt-6 pt-4 border-t border-slate-100 dark:border-slate-700 transition-colors duration-300">
                                <button type="button" @click="step = 2" :disabled="!form.kolam_id" class="bg-blue-600 dark:bg-blue-500 text-white px-8 py-2.5 rounded-xl shadow-lg shadow-blue-500/30 dark:shadow-none font-bold hover:bg-blue-700 dark:hover:bg-blue-400 disabled:opacity-50 transition-all">Lanjut ke Kualitas Air &raquo;</button>
                            </div>
                        </div>

                        <div v-show="step === 2" class="space-y-6 animate-in fade-in zoom-in duration-300">
                            <h3 class="text-lg font-bold border-b border-slate-100 dark:border-slate-700 pb-2 text-slate-800 dark:text-slate-100 transition-colors duration-300">Pengecekan Parameter Air & Sample</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 transition-colors duration-300">Suhu (°C)</label>
                                    <input v-model="form.suhu" type="number" step="0.1" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-600 text-slate-900 dark:text-slate-100 rounded-xl focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400 text-sm transition-colors duration-300">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 transition-colors duration-300">Tingkat pH</label>
                                    <input v-model="form.ph" type="number" step="0.1" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-600 text-slate-900 dark:text-slate-100 rounded-xl focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400 text-sm transition-colors duration-300">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 transition-colors duration-300">Kondisi Visual</label>
                                    <select v-model="form.kondisi_visual" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-600 text-slate-900 dark:text-slate-100 rounded-xl focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400 text-sm transition-colors duration-300">
                                        <option value="Jernih">Jernih</option>
                                        <option value="Keruh">Keruh</option>
                                        <option value="Berbusa">Berbusa</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 transition-colors duration-300">Berat Sample (gr)</label>
                                    <input v-model="form.berat_sample" type="number" step="0.1" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-600 text-slate-900 dark:text-slate-100 rounded-xl focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400 text-sm transition-colors duration-300">
                                </div>
                            </div>
                            <div class="flex justify-between items-center mt-6 pt-4 border-t border-slate-100 dark:border-slate-700 transition-colors duration-300">
                                <button type="button" @click="step = 1" class="bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 px-6 py-2.5 rounded-xl font-bold hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors duration-300">&laquo; Kembali</button>
                                <button type="button" @click="step = 3" :disabled="!form.suhu || !form.ph || !form.berat_sample" class="bg-blue-600 dark:bg-blue-500 text-white px-8 py-2.5 rounded-xl shadow-lg shadow-blue-500/30 dark:shadow-none font-bold hover:bg-blue-700 dark:hover:bg-blue-400 disabled:opacity-50 transition-all">Lanjut ke Pakan &raquo;</button>
                            </div>
                        </div>

                        <div v-show="step === 3" class="space-y-6 animate-in fade-in zoom-in duration-300">
                            <h3 class="text-lg font-bold border-b border-slate-100 dark:border-slate-700 pb-2 text-slate-800 dark:text-slate-100 transition-colors duration-300">Manajemen Pemberian Pakan</h3>
                            
                            <div v-if="analisis" class="bg-indigo-50 dark:bg-indigo-500/10 p-5 rounded-2xl border border-indigo-200 dark:border-indigo-500/20 flex flex-col md:flex-row md:items-center justify-between gap-4 transition-colors duration-300">
                                <div>
                                    <label class="block text-sm font-bold text-indigo-900 dark:text-indigo-300 mb-1 transition-colors duration-300">Target Frekuensi Pakan (Sehari)</label>
                                    <p class="text-xs text-indigo-700 dark:text-indigo-400 transition-colors duration-300">Mempengaruhi pembagian dosis per sesi dan jadwal pemberian berikutnya.</p>
                                </div>
                                <div class="w-full md:w-1/3">
                                    <select v-model="form.frekuensi" class="w-full bg-white dark:bg-slate-900 rounded-xl border-indigo-300 dark:border-indigo-500/40 text-indigo-800 dark:text-indigo-300 font-semibold focus:ring-indigo-500 dark:focus:ring-indigo-400 shadow-sm transition-colors duration-300">
                                        <option :value="2">2 Kali Sehari (Jeda 8 Jam)</option>
                                        <option :value="3">3 Kali Sehari (Jeda 6 Jam)</option>
                                    </select>
                                </div>
                            </div>

                            <div v-if="analisis" class="bg-blue-50 dark:bg-blue-500/10 p-5 rounded-2xl border border-blue-200 dark:border-blue-500/20 grid grid-cols-1 md:grid-cols-2 gap-6 transition-colors duration-300">
                                <div class="text-sm space-y-2 text-blue-900 dark:text-blue-300 transition-colors duration-300">
                                    <p><strong>Biomassa Terkini:</strong> {{ analisis.biomassa }} Kg</p>
                                    <p><strong>Total Harian (FR {{ analisis.feedingRatioText }}):</strong> {{ analisis.dosisHarian }} Kg</p>
                                    <p><strong>Dosis Sesi Ini (1/{{ analisis.frekuensi }}):</strong> {{ analisis.dosisPerSesi }} Kg</p>
                                    <p><strong>Rule Sistem:</strong> <span class="font-bold text-red-600 dark:text-red-400 transition-colors duration-300">{{ analisis.kode_rule }}</span></p>
                                </div>
                                <div class="text-center bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm flex flex-col justify-center border-b-4 transition-colors duration-300" :class="analisis.rekomendasi > 0 ? 'border-emerald-500 dark:border-emerald-400' : 'border-red-500 dark:border-red-400'">
                                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase transition-colors duration-300">Rekomendasi Final</p>
                                    <p class="text-4xl font-black text-blue-700 dark:text-blue-400 mt-2 transition-colors duration-300">{{ analisis.rekomendasi }} <span class="text-sm">Kg</span></p>
                                    <p v-if="analisis.rekomendasi > 0" class="text-xs text-emerald-600 dark:text-emerald-400 mt-3 font-semibold bg-emerald-50 dark:bg-emerald-500/10 rounded-lg py-1.5 transition-colors duration-300">
                                        Jadwal Berikutnya: <br>+{{ analisis.gap_jam }} Jam dari sekarang
                                    </p>
                                </div>
                            </div>

                            <div v-if="analisis" class="bg-slate-50 dark:bg-slate-700/30 p-5 rounded-2xl border border-slate-200 dark:border-slate-600/50 transition-colors duration-300">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-3 transition-colors duration-300">Total Pakan Aktual (Kg)</label>
                                <input v-model="form.pakan_aktual" type="number" step="0.01" class="w-full md:w-1/3 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-600 rounded-xl font-bold text-emerald-700 dark:text-emerald-400 text-lg transition-colors duration-300">
                                
                                <div class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-600 transition-colors duration-300">
                                    <div class="flex justify-between items-center mb-4">
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider transition-colors duration-300">Komposisi Pakan Gudang</span>
                                        <button type="button" @click="addFeed" class="text-xs bg-blue-100 dark:bg-blue-500/20 text-blue-800 dark:text-blue-400 px-3 py-2 rounded-lg font-bold hover:bg-blue-200 dark:hover:bg-blue-500/30 transition-colors duration-300">+ Tambah Bahan</button>
                                    </div>
                                    <div v-for="(feed, index) in form.feeds" :key="index" class="flex flex-wrap md:flex-nowrap gap-3 mb-3 items-center">
                                        <select v-model="feed.inventory_id" class="flex-grow bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm rounded-xl border-slate-300 dark:border-slate-600 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500 dark:focus:border-blue-400 transition-colors duration-300" required>
                                            <option value="" disabled>Pilih Pakan...</option>
                                            <option v-for="item in inventories" :key="item.id" :value="item.id">{{ item.nama_pakan }} (Stok: {{ item.total_stok_kg }}Kg)</option>
                                        </select>
                                        <input v-model="feed.rasio" type="number" min="1" class="w-20 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm rounded-xl border-slate-300 dark:border-slate-600 text-center focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500 dark:focus:border-blue-400 transition-colors duration-300" placeholder="Rasio" required>
                                        <div class="w-28 text-center font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-500/20 p-2 rounded-xl border border-emerald-200 dark:border-emerald-500/30 transition-colors duration-300">{{ hitungEstimasiKg(feed.rasio) }} Kg</div>
                                        <button type="button" @click="removeFeed(index)" v-if="form.feeds.length > 1" class="text-rose-500 dark:text-rose-400 font-bold px-3 py-2 bg-rose-50 dark:bg-rose-500/10 hover:bg-rose-100 dark:hover:bg-rose-500/20 rounded-xl transition-colors duration-300">&times;</button>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-between items-center mt-8 border-t border-slate-100 dark:border-slate-700 pt-6 transition-colors duration-300">
                                <button type="button" @click="step = 2" class="bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 px-6 py-2.5 rounded-xl font-bold hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors duration-300">&laquo; Kembali</button>
                                <button type="submit" class="bg-emerald-600 dark:bg-emerald-500 text-white px-8 py-3 rounded-xl font-black shadow-lg shadow-emerald-500/30 dark:shadow-none hover:bg-emerald-700 dark:hover:bg-emerald-400 transition-all disabled:opacity-50" :disabled="form.processing || !analisis">SIMPAN OPERASI HARIAN</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>