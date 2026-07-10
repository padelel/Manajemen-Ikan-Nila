<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    kolams: Array
});

const form = useForm({
    kolam_id: '',
    tanggal_tebar: new Date().toISOString().split('T')[0],
    jumlah_ikan: '',
    sumber_benih: '',
});

// Reaktivitas untuk membaca status kesiapan kolam yang sedang dipilih
const selectedKolam = computed(() => {
    if (!form.kolam_id) return null;
    return props.kolams.find(k => k.id === form.kolam_id);
});

const isSiapTebar = computed(() => {
    return selectedKolam.value?.status_kesiapan === 'siap';
});

const submit = () => {
    if (isSiapTebar.value) {
        form.post(route('tebar.store'));
    }
};
</script>

<template>
    <Head title="Catat Tebar Benih" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl text-slate-800 leading-tight">Catat Tebar Benih Baru</h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl border border-slate-100 p-8">
                    <form @submit.prevent="submit" class="space-y-6">

                        <!-- Pilihan Kolam -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Pilih Kolam Tujuan</label>
                            <select v-model="form.kolam_id" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 bg-slate-50">
                                <option value="" disabled>-- Pilih Kolam --</option>
                                <option v-for="kolam in kolams" :key="kolam.id" :value="kolam.id">
                                    {{ kolam.nama_kolam }} ({{ kolam.lokasi }})
                                </option>
                            </select>
                            <div v-if="form.errors.kolam_id" class="text-red-500 text-xs mt-1 font-medium">{{ form.errors.kolam_id }}</div>
                        </div>

                        <!-- Banner Peringatan Kesiapan Air Kolam -->
                        <div v-if="selectedKolam" class="p-4 rounded-xl border transition-all duration-300"
                             :class="isSiapTebar ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-800'">
                            <div class="flex items-start gap-3">
                                <div class="text-2xl mt-0.5">
                                    {{ isSiapTebar ? '✅' : '⛔' }}
                                </div>
                                <div>
                                    <h4 class="font-black text-sm tracking-wide">
                                        {{ isSiapTebar ? 'STATUS: SIAP TEBAR' : 'STATUS: KOLAM DITAHAN' }}
                                    </h4>
                                    <p class="text-sm mt-1 font-medium">
                                        {{ selectedKolam.pesan_kesiapan }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Area Input (Menjadi redup & tidak bisa diklik jika air kolam belum beres) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 transition-opacity duration-300" 
                             :class="{ 'opacity-40 pointer-events-none': !isSiapTebar && form.kolam_id }">
                            
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Tanggal Tebar</label>
                                <input type="date" v-model="form.tanggal_tebar" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500" />
                                <div v-if="form.errors.tanggal_tebar" class="text-red-500 text-xs mt-1">{{ form.errors.tanggal_tebar }}</div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Sumber Benih (Opsional)</label>
                                <input type="text" v-model="form.sumber_benih" placeholder="Misal: Balai Benih Lokal" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500" />
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Jumlah Ikan (Ekor)</label>
                                <input type="number" v-model="form.jumlah_ikan" placeholder="Misal: 1000" class="w-full border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500" />
                                <div v-if="form.errors.jumlah_ikan" class="text-red-500 text-xs mt-1">{{ form.errors.jumlah_ikan }}</div>
                            </div>

                        </div>

                        <div class="flex justify-end pt-6 mt-6 border-t border-slate-100">
                            <Link :href="route('tebar.index')" class="px-5 py-2.5 text-slate-500 hover:text-slate-700 font-bold text-sm mr-4 transition-colors">Batal</Link>
                            <button type="submit" :disabled="form.processing || !isSiapTebar" 
                                class="px-6 py-2.5 bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                                Simpan & Mulai Siklus
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>