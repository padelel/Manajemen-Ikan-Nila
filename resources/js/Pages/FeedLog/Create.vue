<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({ kolams: Array, inventories: Array });

// PERBAIKAN 1: Menambahkan 'frekuensi' ke dalam form state
const form = useForm({
    kolam_id: '',
    rule_id: '',
    tanggal_pakan: new Date().toISOString().split('T')[0],
    frekuensi: 2, // Tambahkan ini dengan default 2 kali
    rekomendasi_sistem: '',
    pakan_aktual: '',
    // Array dinamis untuk multi-pakan (Nama 'feeds' sudah benar sesuai Controller lama Anda)
    feeds: [
        { inventory_id: '', rasio: 1 } 
    ]
});

const hasilAnalisis = ref(null);
const isLoading = ref(false);

const addFeed = () => { form.feeds.push({ inventory_id: '', rasio: 1 }); };
const removeFeed = (index) => { if(form.feeds.length > 1) form.feeds.splice(index, 1); };

const totalRasio = computed(() => form.feeds.reduce((sum, item) => sum + Number(item.rasio), 0));
const hitungEstimasiKg = (rasio) => {
    if (!form.pakan_aktual || totalRasio.value === 0) return 0;
    return ((rasio / totalRasio.value) * form.pakan_aktual).toFixed(2);
};

const cekRekomendasi = async () => {
    if (!form.kolam_id) return;
    isLoading.value = true;
    hasilAnalisis.value = null;

    try {
        const response = await axios.get(`/api/hitung-pakan/${form.kolam_id}`);
        if (response.data.error) {
            alert(response.data.error);
        } else {
            hasilAnalisis.value = response.data;
            form.rule_id = response.data.rule_id;
            form.rekomendasi_sistem = response.data.rekomendasi_akhir_kg;
            form.pakan_aktual = response.data.rekomendasi_akhir_kg; 
        }
    } catch (error) {
        alert("Terjadi kesalahan saat menghitung algoritma.");
    } finally {
        isLoading.value = false;
    }
};

const submit = () => { form.post(route('operasi-harian.store')); }; // PASTIKAN ROUTE BENAR
</script>

<template>
    <Head title="Beri Pakan" />
    <AuthenticatedLayout>
        <template #header><h2 class="font-semibold text-xl text-gray-800">Proses Pemberian Pakan (Multi-Pakan)</h2></template>
        
        <div class="py-12"><div class="max-w-4xl mx-auto sm:px-6 lg:px-8"><div class="bg-white p-8 shadow-sm sm:rounded-lg border border-slate-100">
            <form @submit.prevent="submit" class="space-y-6">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 border-b pb-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Pilih Kolam (Trigger AI)</label>
                        <select v-model="form.kolam_id" @change="cekRekomendasi" class="mt-1 block w-full border-slate-200 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                            <option value="" disabled>-- Pilih Kolam --</option>
                            <option v-for="kolam in kolams" :key="kolam.id" :value="kolam.id">{{ kolam.nama_kolam }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Tanggal Pakan</label>
                        <input v-model="form.tanggal_pakan" type="date" class="mt-1 block w-full border-slate-200 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Frekuensi Harian</label>
                        <div class="flex gap-2 mt-1">
                            <label v-for="n in [2, 3]" :key="n" class="flex-1 cursor-pointer">
                                <input type="radio" :value="n" v-model="form.frekuensi" class="hidden peer">
                                <div class="text-center py-2 rounded-lg border border-slate-200 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-700 font-bold transition-all text-sm">
                                    {{ n }} Kali
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <div v-if="isLoading" class="text-center py-4 text-blue-600 font-medium animate-pulse">Sistem sedang menganalisis data kolam...</div>

                <div v-if="hasilAnalisis" class="bg-blue-50 p-6 rounded-2xl border border-blue-100 shadow-sm">
                    <h3 class="font-bold text-blue-900 mb-4 border-b border-blue-200 pb-2">Detail Analisis Sistem Pakar</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                        <div class="space-y-3 text-blue-900">
                            <div class="flex justify-between">
                                <span>Parameter Air Terakhir:</span>
                                <span class="font-semibold">Suhu {{ hasilAnalisis.suhu }}°C | pH {{ hasilAnalisis.ph }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Estimasi Biomassa Kolam:</span>
                                <span class="font-semibold">{{ hasilAnalisis.biomassa_kg }} Kg</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Dosis Dasar Normal:</span>
                                <span class="font-semibold">{{ hasilAnalisis.dosis_dasar_kg }} Kg / Sesi</span>
                            </div>
                        </div>
                        
                        <div class="bg-white p-4 rounded-xl border border-blue-100 shadow-[0_4px_15px_rgb(59,130,246,0.1)] text-center flex flex-col justify-center relative overflow-hidden">
                            <div class="absolute -right-4 -top-4 w-16 h-16 bg-blue-50 rounded-full opacity-50"></div>
                            <p class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1 relative z-10">Keputusan Rule: <span class="text-blue-600">{{ hasilAnalisis.kode_rule }}</span></p>
                            <p class="text-4xl font-black text-blue-700 relative z-10">{{ hasilAnalisis.rekomendasi_akhir_kg }} <span class="text-lg text-blue-500 font-bold">Kg</span></p>
                            <p class="text-[10px] text-blue-500 font-bold mt-1 uppercase tracking-widest relative z-10">Saran Sistem</p>
                        </div>
                    </div>
                </div>

                <div v-if="hasilAnalisis" class="pt-4 pb-2 border-b border-slate-100">
                    <label class="block text-sm font-bold text-slate-800 mb-1">Konfirmasi Pakan Aktual (Kg)</label>
                    <p class="text-[11px] font-medium text-slate-500 mb-3">*Ubah angka ini jika Anda menabur pakan lebih banyak atau lebih sedikit dari saran sistem.</p>
                    <div class="relative w-1/3">
                        <input v-model="form.pakan_aktual" type="number" step="0.01" class="block w-full border-slate-300 rounded-xl shadow-sm text-2xl font-black text-emerald-700 focus:ring-emerald-500 focus:border-emerald-500 pr-12" required>
                        <span class="absolute right-4 top-2 text-slate-400 font-bold">Kg</span>
                    </div>
                </div>

                <div v-if="hasilAnalisis" class="bg-slate-50 p-6 rounded-2xl border border-slate-200 mt-6 shadow-inner">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="font-bold text-slate-800">Komposisi Pakan</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Tentukan jenis pelet dan persentasenya.</p>
                        </div>
                        <button type="button" @click="addFeed" class="text-sm bg-white border border-blue-200 shadow-sm text-blue-700 px-4 py-2 rounded-xl font-bold hover:bg-blue-50 transition-all">+ Tambah Campuran</button>
                    </div>

                    <div v-for="(feed, index) in form.feeds" :key="index" class="flex items-end gap-4 mb-4 bg-white p-4 rounded-xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] border border-slate-100 group">
                        <div class="flex-grow">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Pilih Stok Pelet</label>
                            <select v-model="feed.inventory_id" class="block w-full border-slate-200 rounded-lg text-sm font-medium text-slate-700 focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="" disabled>-- Pilih Bahan Pakan --</option>
                                <option v-for="item in inventories" :key="item.id" :value="item.id">{{ item.nama_pakan }} (Sisa: {{ item.total_stok_kg }}Kg)</option>
                            </select>
                        </div>
                        
                        <div class="w-28">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 text-center">Rasio/Bagian</label>
                            <input v-model="feed.rasio" type="number" min="1" class="block w-full border-slate-200 rounded-lg text-sm font-bold text-center text-slate-700 focus:ring-blue-500 focus:border-blue-500 bg-slate-50" required>
                        </div>
                        
                        <div class="w-32 bg-emerald-50 py-2 px-3 rounded-lg border border-emerald-100 text-center">
                            <label class="block text-[9px] font-bold text-emerald-600 uppercase tracking-widest mb-1">Dipotong</label>
                            <span class="font-black text-emerald-700 text-lg">{{ hitungEstimasiKg(feed.rasio) }} <span class="text-xs font-bold text-emerald-500">Kg</span></span>
                        </div>
                        
                        <div>
                            <button type="button" @click="removeFeed(index)" v-if="form.feeds.length > 1" class="text-red-400 hover:text-white bg-red-50 hover:bg-red-500 rounded-lg w-10 h-10 flex items-center justify-center font-bold transition-all border border-red-100 hover:border-red-500" title="Hapus Bahan">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end items-center gap-4 mt-8 border-t border-slate-100 pt-6">
                    <Link href="/operasi-harian" class="text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors">Batal</Link>
                    <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-xl shadow-lg shadow-blue-200/50 font-bold hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center gap-2" :disabled="form.processing || !hasilAnalisis">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        Simpan Riwayat Pakan
                    </button>
                </div>
            </form>
        </div></div></div>
    </AuthenticatedLayout>
</template>