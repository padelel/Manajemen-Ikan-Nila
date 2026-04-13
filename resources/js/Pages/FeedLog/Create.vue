<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({ kolams: Array, inventories: Array });

const form = useForm({
    kolam_id: '',
    rule_id: '',
    tanggal_pakan: new Date().toISOString().split('T')[0],
    rekomendasi_sistem: '',
    pakan_aktual: '',
    // Array dinamis untuk multi-pakan
    feeds: [
        { inventory_id: '', rasio: 1 } 
    ]
});

const hasilAnalisis = ref(null);
const isLoading = ref(false);

// Fungsi Tambah/Hapus Form Pakan
const addFeed = () => { form.feeds.push({ inventory_id: '', rasio: 1 }); };
const removeFeed = (index) => { if(form.feeds.length > 1) form.feeds.splice(index, 1); };

// Computed untuk menghitung estimasi Kg per item secara Real-Time di UI
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

const submit = () => { form.post(route('feedlog.store')); };
</script>

<template>
    <Head title="Beri Pakan" />
    <AuthenticatedLayout>
        <template #header><h2 class="font-semibold text-xl text-gray-800">Proses Pemberian Pakan (Multi-Pakan)</h2></template>
        
        <div class="py-12"><div class="max-w-4xl mx-auto sm:px-6 lg:px-8"><div class="bg-white p-8 shadow-sm sm:rounded-lg">
            <form @submit.prevent="submit" class="space-y-6">
                
                <div class="grid grid-cols-2 gap-4 border-b pb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Pilih Kolam (Trigger AI)</label>
                        <select v-model="form.kolam_id" @change="cekRekomendasi" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                            <option value="" disabled>-- Pilih Kolam --</option>
                            <option v-for="kolam in kolams" :key="kolam.id" :value="kolam.id">{{ kolam.nama_kolam }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Pakan</label>
                        <input v-model="form.tanggal_pakan" type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                    </div>
                </div>

                <div v-if="isLoading" class="text-center py-4 text-blue-600 font-medium animate-pulse">Sistem sedang menganalisis data kolam...</div>

                <div v-if="hasilAnalisis" class="bg-blue-50 p-6 rounded-lg border border-blue-200">
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
                        
                        <div class="bg-white p-4 rounded-lg border border-blue-100 shadow-sm text-center flex flex-col justify-center">
                            <p class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">Keputusan Rule: <span class="text-blue-600">{{ hasilAnalisis.kode_rule }}</span></p>
                            <p class="text-4xl font-black text-blue-700">{{ hasilAnalisis.rekomendasi_akhir_kg }} <span class="text-lg text-blue-500">Kg</span></p>
                            <p class="text-xs text-blue-600 mt-1">Rekomendasi Pakan Sesi Ini</p>
                        </div>
                    </div>
                </div>

                <div v-if="hasilAnalisis" class="pt-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Total Pakan Aktual (Kg)</label>
                    <p class="text-xs text-gray-500 mb-3">*Secara *default* disamakan dengan rekomendasi sistem, ubah jika ada kebijakan lapangan.</p>
                    <input v-model="form.pakan_aktual" type="number" step="0.01" class="mt-1 block w-1/3 border-gray-300 rounded-md shadow-sm text-xl font-bold text-green-700 focus:ring-green-500 focus:border-green-500" required>
                </div>

                <div v-if="hasilAnalisis" class="bg-gray-50 p-6 rounded-lg border border-gray-200 mt-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-gray-800">Komposisi Pakan & Rasio</h3>
                        <button type="button" @click="addFeed" class="text-sm bg-blue-100 text-blue-700 px-3 py-1.5 rounded font-medium hover:bg-blue-200 transition-colors">+ Tambah Bahan Pakan</button>
                    </div>

                    <div v-for="(feed, index) in form.feeds" :key="index" class="flex items-center gap-4 mb-4 bg-white p-3 rounded-lg shadow-sm border border-gray-100">
                        <div class="flex-grow">
                            <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Bahan Pakan</label>
                            <select v-model="feed.inventory_id" class="block w-full border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="" disabled>Pilih Bahan Pakan</option>
                                <option v-for="item in inventories" :key="item.id" :value="item.id">{{ item.nama_pakan }} (Stok Gudang: {{ item.total_stok_kg }}Kg)</option>
                            </select>
                        </div>
                        <div class="w-24">
                            <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Rasio</label>
                            <input v-model="feed.rasio" type="number" min="1" class="block w-full border-gray-300 rounded-md text-sm text-center focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        <div class="w-32 bg-green-50 p-2 rounded-md border border-green-100 text-center">
                            <label class="block text-xs font-medium text-green-700 uppercase mb-1">Alokasi</label>
                            <span class="font-bold text-green-700">{{ hitungEstimasiKg(feed.rasio) }} Kg</span>
                        </div>
                        <div class="pt-5">
                            <button type="button" @click="removeFeed(index)" v-if="form.feeds.length > 1" class="text-red-400 hover:text-red-600 bg-red-50 hover:bg-red-100 rounded-md w-8 h-8 flex items-center justify-center font-bold transition-colors" title="Hapus Bahan">&times;</button>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end gap-4 mt-8 border-t pt-6">
                    <Link href="/feedlog" class="text-gray-600 hover:text-gray-900 font-medium px-4 py-2">Batal</Link>
                    <button type="submit" class="bg-green-600 text-white px-8 py-2.5 rounded-md font-bold hover:bg-green-700 shadow-md disabled:opacity-50 disabled:cursor-not-allowed transition-all" :disabled="form.processing || !hasilAnalisis">
                        Simpan & Potong Stok Gudang
                    </button>
                </div>
            </form>
        </div></div></div>
    </AuthenticatedLayout>
</template>