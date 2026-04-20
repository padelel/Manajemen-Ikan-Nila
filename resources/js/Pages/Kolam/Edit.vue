<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

// 1. Tangkap data kolam yang dikirim dari fungsi edit() di KolamController
const props = defineProps({
    kolam: Object,
});

// 2. Inisialisasi useForm dengan data dari props
const form = useForm({
    nama_kolam: props.kolam.nama_kolam,
    lokasi: props.kolam.lokasi,
    bentuk_kolam: props.kolam.bentuk_kolam,
    panjang_m: props.kolam.panjang_m,
    lebar_m: props.kolam.lebar_m,
    kedalaman_m: props.kolam.kedalaman_m,
    tanggal_tebar: props.kolam.tanggal_tebar,
    jumlah_ikan: props.kolam.jumlah_ikan,
    berat_rata_gram: props.kolam.berat_rata_gram,
});

const sistem_budidaya = ref(80); 

// Kalkulator Volume Air Real-Time
const kalkulasi = computed(() => {
    let p = parseFloat(form.panjang_m) || 0;
    let l = parseFloat(form.lebar_m) || 0;
    let t = parseFloat(form.kedalaman_m) || 0;
    
    let volume = 0;
    if (form.bentuk_kolam === 'Bundar') {
        let r = p / 2;
        volume = 3.14159 * Math.pow(r, 2) * t;
    } else {
        volume = p * l * t;
    }
    
    let rekomendasi = Math.floor(volume * sistem_budidaya.value);
    return { volume: volume.toFixed(2), rekomendasi: rekomendasi > 0 ? rekomendasi : 0 };
});

// Auto-sinkronisasi Lebar jika Bundar
watch([() => form.panjang_m, () => form.bentuk_kolam], () => {
    if (form.bentuk_kolam === 'Bundar') {
        form.lebar_m = form.panjang_m;
    }
});

const pakaiRekomendasi = () => {
    if (kalkulasi.value.rekomendasi > 0) form.jumlah_ikan = kalkulasi.value.rekomendasi;
};

// 3. Gunakan method PUT dan arahkan ke route update beserta ID-nya
const submit = () => {
    form.put(route('kolam.update', props.kolam.id), { 
        preserveScroll: true 
    });
};
</script>

<template>
    <Head title="Tambah Kolam Baru" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Update Kolam & Tebar Benih</h2>
        </template>
        
        <div class="py-12"><div class="max-w-5xl mx-auto sm:px-6 lg:px-8"><div class="bg-white shadow-sm sm:rounded-lg flex flex-col md:flex-row">
            
            <div class="w-full md:w-2/3 p-8 border-r border-gray-200">
                <form @submit.prevent="submit" class="space-y-6">
                    
                    <div v-if="Object.keys(form.errors).length > 0" class="p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-md">
                        <p class="font-bold text-sm">Gagal menyimpan data! Periksa isian berikut:</p>
                        <ul class="list-disc pl-5 mt-1 text-sm">
                            <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
                        </ul>
                    </div>

                    <h3 class="text-lg font-bold border-b pb-2 text-gray-700">1. Identitas Kolam</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama/Kode Kolam</label>
                            <input v-model="form.nama_kolam" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" placeholder="Misal: Kolam A1" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Lokasi / Blok</label>
                            <input v-model="form.lokasi" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold border-b pb-2 text-gray-700 mt-6">2. Dimensi Kolam</h3>
                    
                    <div class="flex gap-4 mb-4">
                        <label class="inline-flex items-center">
                            <input type="radio" v-model="form.bentuk_kolam" value="Bundar" class="text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 font-medium text-gray-700">Kolam Bundar (Terpal/Bioflok)</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" v-model="form.bentuk_kolam" value="Persegi" class="text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 font-medium text-gray-700">Kolam Persegi (Tanah/Beton)</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                {{ form.bentuk_kolam === 'Bundar' ? 'Diameter (meter)' : 'Panjang (meter)' }}
                            </label>
                            <input v-model="form.panjang_m" type="number" step="0.1" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" placeholder="0.0" required>
                        </div>
                        
                        <div v-show="form.bentuk_kolam === 'Persegi'">
                            <label class="block text-sm font-medium text-gray-700">Lebar (meter)</label>
                            <input v-model="form.lebar_m" type="number" step="0.1" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" placeholder="0.0">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tinggi Air (meter)</label>
                            <input v-model="form.kedalaman_m" type="number" step="0.1" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" placeholder="0.0" required>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold border-b pb-2 text-gray-700 mt-6">3. Data Penebaran Benih</h3>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tanggal Tebar</label>
                            <input v-model="form.tanggal_tebar" type="date" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700">Populasi (Ekor)</label>
                            <input v-model="form.jumlah_ikan" type="number" class="mt-1 w-full rounded-md border-indigo-500 ring-1 ring-indigo-500 shadow-sm font-bold text-indigo-700" placeholder="0" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Berat Rata (gr)</label>
                            <input v-model="form.berat_rata_gram" type="number" step="0.1" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                        </div>
                    </div>

                    <div class="flex justify-end gap-4 mt-8 pt-4 border-t border-gray-200">
                        <Link href="/kolam" class="text-gray-600 font-medium px-4 py-2 hover:text-gray-900">Batal</Link>
                        <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-black shadow-lg hover:bg-blue-700 transition" :disabled="form.processing">
                            UPDATE KOLAM
                        </button>
                    </div>
                </form>
            </div>

            <div class="w-full md:w-1/3 bg-blue-50 p-6 rounded-r-lg">
                <div class="sticky top-6">
                    <h4 class="font-black text-blue-900 text-lg flex items-center gap-2 mb-4">💡 Kalkulator Kapasitas</h4>
                    
                    <label class="block text-sm font-bold text-blue-800 mb-2">Tingkat Kepadatan Aerasi</label>
                    <select v-model="sistem_budidaya" class="w-full rounded-md border-blue-300 text-blue-900 shadow-sm font-semibold mb-6">
                        <option :value="50">Konvensional (50 ekor/m³)</option>
                        <option :value="80">Semi-Intensif (80 ekor/m³)</option>
                        <option :value="120">Intensif / Bioflok (120 ekor/m³)</option>
                    </select>

                    <div class="space-y-4">
                        <div class="bg-white p-4 rounded border border-blue-200">
                            <p class="text-xs text-gray-500 font-bold uppercase">Estimasi Volume Air</p>
                            <p class="text-2xl font-black text-gray-800">{{ kalkulasi.volume }} <span class="text-sm font-normal">m³</span></p>
                        </div>
                        
                        <div class="bg-indigo-600 p-4 rounded shadow-md text-white text-center">
                            <p class="text-xs text-indigo-200 font-bold uppercase mb-1">Rekomendasi Maksimal</p>
                            <p class="text-4xl font-black">{{ kalkulasi.rekomendasi }} <span class="text-sm font-normal">Ekor</span></p>
                            
                            <button @click="pakaiRekomendasi" type="button" class="mt-4 bg-white text-indigo-700 text-sm font-bold px-4 py-2 rounded-full hover:bg-indigo-50 w-full transition shadow">
                                Gunakan Angka Ini &rarr;
                            </button>
                        </div>
                    </div>
                    
                    <p class="text-xs text-blue-600 mt-6 leading-relaxed">*Perhitungan ini adalah rekomendasi batas aman. Memasukkan ikan lebih dari kapasitas dapat menyebabkan FCR bengkak.</p>
                </div>
            </div>

        </div></div></div>
    </AuthenticatedLayout>
</template>