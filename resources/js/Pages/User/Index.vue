<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    users: Array
});

const hapusUser = (id, name) => {
    if (confirm(`Yakin ingin menghapus akun ${name}?`)) {
        router.delete(route('users.destroy', id));
    }
};
</script>

<template>
    <Head title="Kelola Akun" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-end">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen Akun</h2>
                    <p class="text-sm text-slate-500 mt-1">Kelola akses Pengelola Utama dan Operator Lapangan.</p>
                </div>
                <Link :href="route('users.create')" class="bg-slate-900 text-white px-5 py-2.5 rounded-xl shadow-lg shadow-slate-200/50 hover:bg-slate-800 text-sm font-semibold transition-all">
                    + Tambah Akun
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:rounded-3xl border border-slate-100">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50/50 border-b border-slate-100">
                                <tr>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Nama Lengkap</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Email</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Role Hak Akses</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <tr v-for="user in users" :key="user.id" class="border-b border-slate-50 hover:bg-slate-50/80 transition duration-200 group">
                                    <td class="px-6 py-5 font-bold text-slate-900">{{ user.name }}</td>
                                    <td class="px-6 py-5 text-slate-500 font-medium">{{ user.email }}</td>
                                    <td class="px-6 py-5">
                                        <span :class="user.role === 'admin' ? 'bg-blue-50 text-blue-600 border-blue-200' : 'bg-amber-50 text-amber-600 border-amber-200'" class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider border">
                                            {{ user.role }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 text-right">
                                        <button v-if="$page.props.auth.user.id !== user.id" @click="hapusUser(user.id, user.name)" class="px-3 py-1.5 text-xs font-bold text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition opacity-0 group-hover:opacity-100">
                                            Cabut Akses
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>