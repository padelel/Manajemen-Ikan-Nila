<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ kolams: Array });
</script>

<template>
    <Head title="Pemantauan Hasil Panen" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl text-slate-800 leading-tight">Pemantauan Hasil Panen</h2>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="bg-white overflow-hidden shadow-sm rounded-3xl border border-slate-100">
                    <div class="p-6 border-b border-slate-100">
                        <h3 class="text-lg font-bold text-slate-800">Daftar Kolam</h3>
                        <p class="text-sm text-slate-500 mt-1">Pilih kolam untuk memantau riwayat hasil panen berdasarkan siklus.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left border-collapse">
                            <thead class="text-xs text-slate-400 uppercase bg-slate-50/50 tracking-widest border-b border-slate-100">
                                <tr>
                                    <th class="px-6 py-4">Nama Kolam</th>
                                    <th class="px-6 py-4 text-right">Riwayat Siklus</th>
                                    <th class="px-6 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="kolam in kolams" :key="kolam.id" class="border-b border-slate-50 hover:bg-slate-50/50 transition duration-200">
                                    <td class="px-6 py-4 font-bold text-slate-800 text-base">
                                        {{ kolam.nama_kolam }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-slate-700 text-lg">
                                        {{ kolam.total_siklus }} <span class="text-xs text-slate-400 ml-1">Siklus</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <Link :href="route('analisis.show', kolam.id)" class="inline-flex items-center justify-center whitespace-nowrap px-5 py-2 bg-slate-100 text-slate-600 hover:bg-slate-200 font-bold text-xs rounded-xl transition-colors border border-slate-200">
                                            Detail Siklus
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="kolams.length === 0">
                                    <td colspan="3" class="px-6 py-12 text-center text-slate-400 font-medium">Belum ada data kolam.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>