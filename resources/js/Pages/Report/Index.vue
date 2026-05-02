<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import GrowthChart from '@/Components/GrowthChart.vue';
import PopulationChart from '@/Components/PopulationChart.vue';

const props = defineProps({
    kolams: Array,
    selectedKolamId: Number,
    kolamInfo: Object,
    chartBerat: Object,
    chartPopulasi: Object
});

// State untuk menyimpan ID kolam yang dipilih di dropdown
const selectedKolam = ref(props.selectedKolamId);

// Ketika dropdown berubah, reload halaman dan kirim parameter kolam_id
watch(selectedKolam, (newVal) => {
    router.get(route('analisis.index'), { kolam_id: newVal }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
});
</script>

<template>
    <Head title="Analisis Kolam" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:justify-between md:items-end gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Analisis Spesifik Kolam</h2>
                    <p class="text-sm text-slate-500 mt-1">Pantau grafik pertumbuhan dan populasi secara mendetail per kolam.</p>
                </div>
                
                <!-- Pemilih Kolam (Dropdown) -->
                <div class="w-full md:w-64">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Pilih Kolam</label>
                    <select v-model="selectedKolam" class="w-full border-slate-200 rounded-xl text-sm font-bold text-slate-700 shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white">
                        <option v-for="kolam in kolams" :key="kolam.id" :value="kolam.id">
                            {{ kolam.nama_kolam }}
                        </option>
                    </select>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <div v-if="kolamInfo" class="bg-indigo-50 border border-indigo-100 p-4 rounded-2xl flex items-center gap-4">
                    <div class="h-12 w-12 rounded-full bg-white flex items-center justify-center text-indigo-500 shadow-sm">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-indigo-900">Menampilkan Data: {{ kolamInfo.nama_kolam }}</h3>
                        <p class="text-sm text-indigo-700">Populasi Saat Ini: <strong>{{ kolamInfo.jumlah_ikan.toLocaleString('id-ID') }} Ekor</strong> | Biomassa Est: <strong>{{ ((kolamInfo.jumlah_ikan * kolamInfo.berat_rata_gram) / 1000).toLocaleString('id-ID') }} Kg</strong></p>
                    </div>
                </div>

                <!-- Memanggil komponen Chart yang sudah modular -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <GrowthChart :chart-berat="chartBerat" />
                    <PopulationChart :chart-populasi="chartPopulasi" />
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>