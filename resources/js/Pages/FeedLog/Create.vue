<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({ kolams: Array, inventories: Array });

const form = useForm({
    kolam_id: '',
    rule_id: '',
    inventory_id: '',
    tanggal_pakan: new Date().toISOString().split('T')[0],
    rekomendasi_sistem: '',
    pakan_aktual: ''
});

// State untuk menyimpan hasil analisis dari Controller
const hasilAnalisis = ref(null);
const isLoading = ref(false);

// Fungsi untuk memanggil mesin Forward Chaining saat kolam dipilih
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
            // Otomatis mengisi form tersembunyi
            form.rule_id = response.data.rule_id;
            form.rekomendasi_sistem = response.data.rekomendasi_akhir_kg;
            form.pakan_aktual = response.data.rekomendasi_akhir_kg; // Default disamakan dengan sistem
        }
    } catch (error) {
        console.error("Gagal mengambil data API", error);
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
        <template #header><h2 class="font-semibold text-xl text-gray-800 leading-tight">Proses Pemberian Pakan Harian</h2></template>
        
        <div class="py-12"><div class="max-w-4xl mx-auto sm:px-6 lg:px-8"><div class="bg-white p-8 shadow-sm sm:rounded-lg">
            <form @submit.prevent="submit" class="space-y-6">
                
                <div class="grid grid-cols-3 gap-4 border-b pb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Pilih Kolam (Trigger AI)</label>
                        <select v-model="form.kolam_id" @change="cekRekomendasi" class="mt-1 block w-full border-gray-300 rounded-md" required>
                            <option value="" disabled>-- Pilih Kolam --</option>
                            <option v-for="kolam in kolams" :key="kolam.id" :value="kolam.id">{{ kolam.nama_kolam }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Ambil Pakan Dari Gudang</label>
                        <select v-model="form.inventory_id" class="mt-1 block w-full border-gray-300 rounded-md" required>
                            <option value="" disabled>-- Pilih Stok Pakan --</option>
                            <option v-for="item in inventories" :key="item.id" :value="item.id">{{ item.nama_pakan }} (Sisa: {{ item.total_stok_kg }}Kg)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal</label>
                        <input v-model="form.tanggal_pakan" type="date" class="mt-1 block w-full border-gray-300 rounded-md" required>
                    </div>
                </div>

                <div v-if="isLoading" class="text-center py-4 text-blue-600 font-semibold animate-pulse">
                    Sistem sedang menganalisis kualitas air...
                </div>

                <div v-if="hasilAnalisis" class="bg-blue-50 p-6 rounded-lg border border-blue-200">
                    <h3 class="font-bold text-blue-900 mb-4 border-b border-blue-200 pb-2">Analisis Sistem Pakar (Forward Chaining)</h3>
                    
                    <div class="grid grid-cols-2 gap-6 text-sm">
                        <div class="space-y-2 text-blue-800">
                            <p><strong>Parameter Terakhir:</strong> Suhu {{ hasilAnalisis.suhu }}°C | pH {{ hasilAnalisis.ph }}</p>
                            <p><strong>Total Biomassa Ikan:</strong> {{ hasilAnalisis.biomassa_kg }} Kg</p>
                            <p><strong>Dosis Dasar (5%):</strong> {{ hasilAnalisis.dosis_dasar_kg }} Kg</p>
                        </div>
                        <div class="bg-white p-4 rounded border border-blue-100 shadow-sm text-center">
                            <p class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">Keputusan Rule: {{ hasilAnalisis.kode_rule }}</p>
                            <p class="text-3xl font-black text-blue-700">{{ hasilAnalisis.rekomendasi_akhir_kg }} Kg</p>
                            <p class="text-xs text-blue-600 mt-1">Rekomendasi Final</p>
                        </div>
                    </div>
                </div>

                <div v-if="hasilAnalisis" class="pt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pakan Aktual yang Dituang ke Kolam (Kg)</label>
                    <p class="text-xs text-gray-500 mb-2">*Sistem merekomendasikan {{ hasilAnalisis.rekomendasi_akhir_kg }} Kg. Anda bisa mengubah angka ini jika ada kebijakan lapangan lain.</p>
                    <input v-model="form.pakan_aktual" type="number" step="0.01" class="mt-1 block w-1/3 border-gray-300 rounded-md text-lg font-bold text-green-700 focus:ring-green-500 focus:border-green-500" required>
                </div>
                
                <div class="flex justify-end gap-4 mt-6">
                    <Link href="/feedlog" class="text-gray-600 hover:text-gray-900 font-medium pt-2">Batal</Link>
                    <button type="submit" class="bg-green-600 text-white px-8 py-2 rounded-md hover:bg-green-700 font-bold shadow-lg" :disabled="form.processing || !hasilAnalisis">
                        Simpan & Potong Stok Gudang
                    </button>
                </div>
            </form>
        </div></div></div>
    </AuthenticatedLayout>
</template>