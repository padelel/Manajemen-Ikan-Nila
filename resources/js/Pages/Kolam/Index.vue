<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    kolams: Array,
    operators: Array,
});

// State untuk Modal Penugasan
const isModalOpen = ref(false);
const selectedKolam = ref(null);

const form = useForm({
    operator_ids: [],
});

const openAssignModal = (kolam) => {
    selectedKolam.value = kolam;
    // Otomatis centang operator yang sudah ditugaskan sebelumnya
    form.operator_ids = kolam.operators.map(op => op.id);
    isModalOpen.value = true;
};

const closeAssignModal = () => {
    isModalOpen.value = false;
    setTimeout(() => {
        selectedKolam.value = null;
        form.reset();
        form.clearErrors();
    }, 300); // Tunggu animasi tutup selesai
};

const submitAssignment = () => {
    form.post(route('kolam.assign', selectedKolam.value.id), {
        preserveScroll: true,
        onSuccess: () => closeAssignModal(),
    });
};
</script>

<template>
    <Head title="Master Kolam" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-xl text-slate-800 leading-tight">Kelola Master Kolam</h2>
                <Link :href="route('kolam.create')" class="px-5 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/30">
                    + Tambah Kolam
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-white overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:rounded-3xl border border-slate-100">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50/50 border-b border-slate-100">
                                <tr>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Nama Kolam</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Status</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Spesifikasi</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Operator Bertugas</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <tr v-for="kolam in kolams" :key="kolam.id" class="border-b border-slate-50 hover:bg-slate-50/50 transition duration-200">
                                    
                                    <td class="px-6 py-5">
                                        <p class="font-bold text-slate-900 text-base">{{ kolam.nama_kolam }}</p>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ kolam.lokasi }}</p>
                                    </td>

                                    <td class="px-6 py-5 text-center">
                                        <span :class="kolam.status_kolam === 'aktif' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-200'" class="px-3 py-1.5 rounded-lg text-xs font-black uppercase tracking-widest border shadow-sm inline-block">
                                            {{ kolam.status_kolam }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-5 text-center text-slate-500 font-medium">
                                        {{ kolam.panjang_m }}m x {{ kolam.lebar_m }}m <br> 
                                        <span class="text-xs">Kedalaman: {{ kolam.kedalaman_m }}m</span>
                                    </td>

                                    <td class="px-6 py-5">
                                        <div class="flex flex-wrap gap-2">
                                            <span v-if="kolam.operators.length === 0" class="text-xs text-slate-400 font-medium italic">Belum ada operator</span>
                                            <span v-for="operator in kolam.operators" :key="operator.id" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs font-bold">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                {{ operator.name }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-5 text-right space-x-2">
                                        <button @click="openAssignModal(kolam)" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 transition-colors">
                                            Atur Penugasan
                                        </button>
                                        <Link :href="route('kolam.edit', kolam.id)" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 border border-slate-200 transition-colors">
                                            Edit
                                        </Link>
                                    </td>

                                </tr>
                                <tr v-if="kolams.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-400 font-medium">Belum ada data kolam.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal Penugasan Operator -->
        <Modal :show="isModalOpen" @close="closeAssignModal">
            <div class="p-6">
                <h2 class="text-lg font-bold text-slate-900 mb-1">
                    Atur Penugasan Operator
                </h2>
                <p class="text-sm text-slate-500 mb-6">
                    Pilih operator yang akan bertanggung jawab untuk mengecek kualitas air di <span class="font-bold text-indigo-600">{{ selectedKolam?.nama_kolam }}</span>.
                </p>

                <form @submit.prevent="submitAssignment">
                    <div class="space-y-3 mb-8 max-h-60 overflow-y-auto pr-2">
                        <label v-for="operator in operators" :key="operator.id" class="flex items-center p-4 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors has-[:checked]:bg-indigo-50 has-[:checked]:border-indigo-300">
                            <input type="checkbox" :value="operator.id" v-model="form.operator_ids" class="w-5 h-5 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500" />
                            <div class="ml-4 flex flex-col">
                                <span class="font-bold text-slate-800 text-sm">{{ operator.name }}</span>
                                <span class="text-xs text-slate-400">{{ operator.email }}</span>
                            </div>
                        </label>
                        <div v-if="operators.length === 0" class="text-center text-sm text-slate-400 py-4 italic">
                            Belum ada pengguna dengan Role Operator di dalam sistem.
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" @click="closeAssignModal" class="px-5 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-700 transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-blue-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-blue-500/30 hover:bg-blue-700 disabled:opacity-50 transition-all">
                            Simpan Penugasan
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>