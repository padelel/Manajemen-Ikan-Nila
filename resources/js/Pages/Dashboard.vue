<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler } from 'chart.js';

// Mendaftarkan elemen Chart.js
ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler);

// Menerima data dari Controller yang sudah dikelompokkan
const props = defineProps({
    ringkasan: Object,
    inventory: Object,
    sma: Object,
    chartPakan: Object,
    chartBerat: Object
});

// Konfigurasi Visual Umum (digunakan untuk kedua grafik)
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false } // Sembunyikan legenda karena judul sudah jelas
    },
    scales: {
        y: { beginAtZero: true },
        x: { grid: { display: false } }
    },
    elements: {
        line: { tension: 0.4 } // Membuat garisnya melengkung halus (curved)
    }
};

// 1. Konfigurasi Grafik Tren Pakan (Biru)
const chartPakanConfig = {
    labels: props.chartPakan.labels,
    datasets: [{
        label: 'Total Pakan Diberikan (Kg)',
        borderColor: '#3b82f6', // Warna biru Tailwind
        backgroundColor: 'rgba(59, 130, 246, 0.1)', 
        borderWidth: 3,
        pointBackgroundColor: '#2563eb',
        pointRadius: 4,
        fill: true,
        data: props.chartPakan.data,
    }]
};

// 2. Konfigurasi Grafik Pertumbuhan Berat Ikan (Hijau)
const chartBeratConfig = {
    labels: props.chartBerat.labels,
    datasets: [{
        label: 'Berat Rata-rata Ikan (gram)',
        borderColor: '#10b981', // Warna Emerald Tailwind
        backgroundColor: 'rgba(16, 185, 129, 0.1)',
        borderWidth: 3,
        pointBackgroundColor: '#059669',
        pointRadius: 4,
        fill: true,
        data: props.chartBerat.berat,
    }]
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Sistem Cerdas Manajemen Tambak</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-500">
                        <p class="text-sm font-medium text-gray-500 uppercase">Total Kolam Aktif</p>
                        <p class="mt-2 text-3xl font-black text-gray-800">{{ ringkasan.totalKolam }} <span class="text-lg font-normal text-gray-500">Unit</span></p>
                    </div>
                    
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 border-l-4 border-teal-500">
                        <p class="text-sm font-medium text-gray-500 uppercase">Total Populasi Ikan</p>
                        <p class="mt-2 text-3xl font-black text-gray-800">{{ ringkasan.totalIkan.toLocaleString('id-ID') }} <span class="text-lg font-normal text-gray-500">Ekor</span></p>
                    </div>

                    <div class="bg-white shadow-sm sm:rounded-lg p-6 border-l-4 border-indigo-500">
                        <p class="text-sm font-medium text-gray-500 uppercase">Estimasi Biomassa</p>
                        <p class="mt-2 text-3xl font-black text-gray-800">{{ ringkasan.totalBiomassaKg.toLocaleString('id-ID') }} <span class="text-lg font-normal text-gray-500">Kg</span></p>
                    </div>

                    <div class="bg-white shadow-sm sm:rounded-lg p-6 border-l-4 border-yellow-500">
                        <p class="text-sm font-medium text-gray-500 uppercase">Stok ({{ inventory.namaPakan }})</p>
                        <p class="mt-2 text-3xl font-black text-gray-800">{{ inventory.stokPakan }} <span class="text-lg font-normal text-gray-500">Kg</span></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 border border-gray-100 flex flex-col">
                        <h3 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4">Tren Konsumsi Pakan (7 Hari)</h3>
                        <div class="flex-grow relative h-64 w-full">
                            <Line :data="chartPakanConfig" :options="chartOptions" />
                        </div>
                    </div>

                    <div class="bg-white shadow-sm sm:rounded-lg p-6 border border-gray-100 flex flex-col justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4">Prediksi SMA & SPK</h3>
                            <div class="flex justify-between items-center mb-6">
                                <span class="text-gray-600">Laju Rata-rata Konsumsi:</span>
                                <span class="text-xl font-bold text-gray-900">{{ sma.rata_rata_harian }} Kg/Hari</span>
                            </div>
                            
                            <div class="p-6 rounded-xl text-center"
                                :class="{
                                    'bg-green-100 text-green-800': sma.status === 'Aman',
                                    'bg-yellow-100 text-yellow-800': sma.status === 'Waspada',
                                    'bg-red-100 text-red-800 animate-pulse': sma.status === 'Kritis' || sma.status === 'Habis'
                                }">
                                <p class="text-sm font-bold uppercase tracking-widest opacity-80 mb-2">Estimasi Stok Habis Dalam</p>
                                <p v-if="sma.rata_rata_harian > 0" class="text-5xl font-black mb-2">{{ sma.estimasi_hari }} <span class="text-2xl opacity-75">Hari</span></p>
                                <p v-else class="text-xl font-bold mb-2 py-2">Menunggu Data</p>
                                
                                <p v-if="sma.status === 'Aman'" class="text-sm font-medium">Status Aman. Stok mencukupi.</p>
                                <p v-if="sma.status === 'Waspada'" class="text-sm font-medium">Peringatan: Stok menipis. Persiapkan restock.</p>
                                <p v-if="sma.status === 'Kritis'" class="text-sm font-bold">Kritis! Segera lakukan pemesanan pakan.</p>
                                <p v-if="sma.status === 'Habis'" class="text-sm font-bold">Gudang Kosong!</p>
                            </div>
                        </div>

                        <div class="mt-6 flex gap-3 pt-4 border-t border-gray-100">
                            <Link href="/feedlog/create" class="flex-1 text-center bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 font-medium">Beri Pakan</Link>
                            <Link href="/inventory" class="flex-1 text-center bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded shadow-sm hover:bg-gray-50 font-medium">Restock</Link>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6 border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4">Grafik Pertumbuhan Ikan (Berdasarkan Sample)</h3>
                    <div class="relative h-80 w-full">
                        <Line :data="chartBeratConfig" :options="chartOptions" />
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>