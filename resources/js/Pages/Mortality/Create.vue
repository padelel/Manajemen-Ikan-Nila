<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ kolams: Array });

const form = useForm({
    kolam_id: '',
    siklus_budidaya_id: '',
    tanggal_kematian: new Date().toISOString().split('T')[0],
    jumlah_mati: '',
    kategori_kematian: '',
    catatan: ''
});

const selectedKolam = computed(() => {
    return props.kolams.find(k => k.id === Number(form.kolam_id));
});

const kolamInfo = computed(() => {
    const k = selectedKolam.value;
    if (!k) return null;
    return {
        siklusMulai: k.siklus_mulai,
        tebarAwal: k.tebar_awal,
        populasiSaatIni: k.jumlah_ikan,
        totalMatiSiklus: k.total_mati_siklus,
        sr: k.sr,
    };
});

const kategoriOptions = [
    { value: '', label: '-- Pilih Kategori --' },
    { value: 'penyakit', label: 'Penyakit (infeksi, parasit, jamur)' },
    { value: 'kanibalisme', label: 'Kanibalisme' },
    { value: 'stres_lingkungan', label: 'Stres Lingkungan (suhu, pH, DO)' },
    { value: 'predator', label: 'Predator (ular, burung, dll)' },
    { value: 'lainnya', label: 'Lainnya' },
];

const onKolamChange = () => {
    const k = selectedKolam.value;
    form.siklus_budidaya_id = k?.siklus_id || '';
};

const submit = () => { form.post(route('kematian.store')); };
</script>

<template>
    <Head title="Lapor Kematian" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Form Laporan Kematian Ikan</h2>
                    <p class="text-sm text-slate-500 mt-1">Catat insiden mortalitas — populasi akan berkurang secara otomatis.</p>
                </div>
                <Link href="/kematian" class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-bold rounded-xl hover:bg-slate-200 transition-colors">
                    Kembali
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <!-- Info Kolam Terpilih -->
                <div v-if="kolamInfo" class="mb-6 bg-emerald-50 border border-emerald-200 rounded-2xl p-5 shadow-sm">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                        <div>
                            <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Populasi Saat Ini</p>
                            <p class="text-2xl font-black text-emerald-800 mt-1">{{ kolamInfo.populasiSaatIni.toLocaleString('id-ID') }}</p>
                            <p class="text-[10px] text-emerald-500">ekor</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Tebar Awal</p>
                            <p class="text-2xl font-black text-slate-800 mt-1">{{ kolamInfo.tebarAwal?.toLocaleString('id-ID') }}</p>
                            <p class="text-[10px] text-slate-400">ekor</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Total Mati Siklus</p>
                            <p class="text-2xl font-black text-rose-600 mt-1">{{ kolamInfo.totalMatiSiklus?.toLocaleString('id-ID') }}</p>
                            <p class="text-[10px] text-rose-400">ekor</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Survival Rate</p>
                            <p class="text-2xl font-black mt-1" :class="kolamInfo.sr >= 80 ? 'text-emerald-600' : kolamInfo.sr >= 60 ? 'text-amber-500' : 'text-rose-600'">
                                {{ kolamInfo.sr ?? '-' }}%
                            </p>
                        </div>
                    </div>
                    <p class="text-center text-xs text-emerald-600 mt-3 font-medium">
                        Siklus dimulai {{ kolamInfo.siklusMulai }}
                    </p>
                </div>

                <div class="bg-white p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:rounded-3xl border-t-4 border-t-rose-500 border border-slate-100">
                    <form @submit.prevent="submit" class="space-y-6">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Pilih Kolam <span class="text-rose-500">*</span></label>
                                <select v-model="form.kolam_id" @change="onKolamChange" class="w-full bg-white border-slate-200 text-slate-900 rounded-xl focus:border-rose-500 focus:ring-rose-500 text-sm" required>
                                    <option value="" disabled>-- Pilih Kolam --</option>
                                    <option v-for="kolam in kolams" :key="kolam.id" :value="kolam.id">
                                        {{ kolam.nama_kolam }} ({{ kolam.jumlah_ikan }} ekor)
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Tanggal Ditemukan <span class="text-rose-500">*</span></label>
                                <input v-model="form.tanggal_kematian" type="date" class="w-full bg-white border-slate-200 text-slate-900 rounded-xl focus:border-rose-500 focus:ring-rose-500 text-sm" required>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Jumlah Mati (Ekor) <span class="text-rose-500">*</span></label>
                                <input v-model="form.jumlah_mati" type="number" min="1" step="1" class="w-full bg-white border-slate-200 text-slate-900 rounded-xl focus:border-rose-500 focus:ring-rose-500 text-sm" placeholder="Contoh: 5" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Kategori Kematian</label>
                                <select v-model="form.kategori_kematian" class="w-full bg-white border-slate-200 text-slate-900 rounded-xl focus:border-rose-500 focus:ring-rose-500 text-sm">
                                    <option v-for="opt in kategoriOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Catatan / Dugaan Penyebab</label>
                            <textarea v-model="form.catatan" rows="3" class="w-full bg-white border-slate-200 text-slate-900 rounded-xl focus:border-rose-500 focus:ring-rose-500 text-sm" placeholder="Contoh: Ditemukan mengambang di pojok kolam, insang pucat..."></textarea>
                        </div>

                        <div class="pt-6 border-t border-slate-100 flex justify-end items-center gap-4">
                            <Link href="/kematian" class="text-slate-600 font-medium px-4 py-2 hover:text-slate-900">
                                Batal
                            </Link>
                            <button type="submit" class="bg-rose-600 text-white px-8 py-3 rounded-xl shadow-lg shadow-rose-500/30 hover:bg-rose-700 font-bold transition-all disabled:opacity-50" :disabled="form.processing">
                                Simpan & Kurangi Populasi
                            </button>
                        </div>
                    </form>
                </div>

                <div v-if="form.hasErrors" class="mt-6 bg-red-50 border border-red-200 text-red-700 rounded-2xl p-4 text-sm">
                    <ul>
                        <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
