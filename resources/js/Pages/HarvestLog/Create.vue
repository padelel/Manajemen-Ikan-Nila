<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { computed } from 'vue';

const props = defineProps({
    siklus: Object
});

const form = useForm({
    tanggal_panen: new Date().toISOString().split('T')[0],
    jumlah_ikan_panen: props.siklus?.populasi_panen || '',
    catatan: ''
});

const populasiPanen = props.siklus?.populasi_panen || 0;

const srColor = computed(() => {
    const sr = props.siklus?.sr ?? 0;
    if (sr >= 80) return 'text-emerald-600';
    if (sr >= 60) return 'text-amber-500';
    return 'text-rose-600';
});

const submit = () => {
    if (confirm("Apakah Anda yakin? Menyimpan data panen ini akan MENUTUP siklus secara permanen dan tidak dapat diubah lagi.")) {
        form.post(route('panen.store', props.siklus.id));
    }
};

const formatDate = (dateString) => {
    if (!dateString) return "-";
    return new Date(dateString).toLocaleDateString("id-ID", { year: "numeric", month: "long", day: "numeric" });
};
</script>

<template>
    <Head title="Catat Panen Total" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('panen.index')" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <h2 class="font-bold text-xl text-slate-800 leading-tight">Catat Panen Total</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

                <!-- Ringkasan Siklus -->
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">{{ siklus.kolam?.nama_kolam }}</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                        <div class="bg-slate-50 rounded-2xl p-4">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tebar Awal</p>
                            <p class="text-2xl font-black text-slate-800 mt-1">{{ siklus.jumlah_tebar_awal?.toLocaleString('id-ID') }}</p>
                            <p class="text-[10px] text-slate-400">ekor</p>
                        </div>
                        <div class="bg-rose-50 rounded-2xl p-4">
                            <p class="text-[10px] font-bold text-rose-500 uppercase tracking-wider">Total Mati</p>
                            <p class="text-2xl font-black text-rose-600 mt-1">{{ siklus.total_mati?.toLocaleString('id-ID') }}</p>
                            <p class="text-[10px] text-rose-400">ekor</p>
                        </div>
                        <div class="bg-emerald-50 rounded-2xl p-4">
                            <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider">Populasi Panen</p>
                            <p class="text-2xl font-black text-emerald-600 mt-1">{{ siklus.populasi_panen?.toLocaleString('id-ID') }}</p>
                            <p class="text-[10px] text-emerald-400">ekor</p>
                        </div>
                        <div class="bg-blue-50 rounded-2xl p-4">
                            <p class="text-[10px] font-bold text-blue-500 uppercase tracking-wider">Survival Rate</p>
                            <p class="text-2xl font-black mt-1" :class="srColor">{{ siklus.sr ?? '-' }}%</p>
                            <p class="text-[10px]" :class="srColor">SR</p>
                        </div>
                    </div>
                    <p class="text-center text-xs text-slate-400 mt-4">
                        Siklus dimulai {{ formatDate(siklus.tanggal_mulai) }}
                    </p>
                </div>

                <!-- Form Panen -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl p-8 border border-slate-100">
                    <form @submit.prevent="submit" class="space-y-6">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="tanggal_panen" value="Tanggal Panen Total" />
                                <TextInput id="tanggal_panen" type="date" class="mt-2 block w-full bg-slate-50" v-model="form.tanggal_panen" required />
                                <InputError class="mt-2" :message="form.errors.tanggal_panen" />
                            </div>

                            <div>
                                <InputLabel for="jumlah_ikan_panen" value="Jumlah Ikan Dipanen (Ekor)" />
                                <div class="mt-2">
                                    <input id="jumlah_ikan_panen" type="hidden" v-model="form.jumlah_ikan_panen" />
                                    <p class="text-3xl font-black text-emerald-600 bg-slate-50 rounded-xl border border-slate-200 px-4 py-3">
                                        {{ populasiPanen.toLocaleString('id-ID') }}
                                        <span class="text-sm font-bold text-slate-400 ml-2">ekor (panen total)</span>
                                    </p>
                                </div>
                                <InputError class="mt-2" :message="form.errors.jumlah_ikan_panen" />
                            </div>
                        </div>

                        <div>
                            <InputLabel for="catatan" value="Catatan Opsional (Kondisi ikan, kualitas panen, dll)" />
                            <textarea id="catatan" v-model="form.catatan" rows="3" class="mt-2 block w-full border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-xl shadow-sm bg-slate-50" placeholder="Ikan sehat, bobot rata-rata 250 gram/ekor..."></textarea>
                            <InputError class="mt-2" :message="form.errors.catatan" />
                        </div>

                        <div class="flex items-center justify-end mt-8 border-t border-slate-100 pt-6">
                            <button type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-sm shadow-emerald-500/30 transition-all flex items-center gap-2">
                                Simpan Data Panen & Tutup Siklus
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
