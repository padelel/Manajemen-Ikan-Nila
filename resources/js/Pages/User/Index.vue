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
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight transition-colors duration-300">Manajemen Akun</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors duration-300">Kelola akses Pengelola Utama dan Operator Lapangan.</p>
                </div>
                <Link :href="route('users.create')" class="w-full sm:w-auto text-center bg-slate-900 dark:bg-indigo-600 text-white px-5 py-2.5 rounded-xl shadow-lg shadow-slate-200/50 dark:shadow-none hover:bg-slate-800 dark:hover:bg-indigo-500 text-sm font-semibold transition-all">
                    + Tambah Akun
                </Link>
            </div>
        </template>

        <div class="py-6 sm:py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:rounded-3xl border border-slate-100 dark:border-slate-700 transition-colors duration-300">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50/50 dark:bg-slate-700/50 border-b border-slate-100 dark:border-slate-700 transition-colors duration-300">
                                <tr>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest whitespace-nowrap transition-colors">Nama Lengkap</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest whitespace-nowrap transition-colors">Email</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest whitespace-nowrap transition-colors">Role Hak Akses</th>
                                    <th class="px-6 py-5 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest text-right whitespace-nowrap transition-colors">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <tr v-for="user in users" :key="user.id" class="border-b border-slate-50 dark:border-slate-700/50 hover:bg-slate-50/80 dark:hover:bg-slate-700/50 transition duration-200 group">
                                    
                                    <td class="px-6 py-5 font-bold text-slate-900 dark:text-slate-100 whitespace-nowrap transition-colors">{{ user.name }}</td>
                                    
                                    <td class="px-6 py-5 text-slate-500 dark:text-slate-400 font-medium whitespace-nowrap transition-colors">{{ user.email }}</td>
                                    
                                    <td class="px-6 py-5 whitespace-nowrap">
                                        <span :class="user.role === 'admin' 
                                            ? 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-500/20' 
                                            : 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-500/20'" 
                                            class="inline-block px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider border transition-colors duration-300"
                                        >
                                            {{ user.role }}
                                        </span>
                                    </td>
                                    
                                    <td class="px-6 py-5 text-right whitespace-nowrap">
                                        <button v-if="$page.props.auth.user.id !== user.id" @click="hapusUser(user.id, user.name)" class="px-3 py-1.5 text-xs font-bold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 rounded-lg hover:bg-red-100 dark:hover:bg-red-500/20 transition-all sm:opacity-0 sm:group-hover:opacity-100">
                                            Cabut Akses
                                        </button>
                                    </td>
                                </tr>

                                <tr v-if="users.length === 0">
                                    <td colspan="4" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400 italic transition-colors">
                                        Belum ada data pengguna.
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