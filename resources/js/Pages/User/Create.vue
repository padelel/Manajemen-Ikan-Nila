<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    role: 'operator',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('users.store'), {
        onSuccess: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Tambah Akun Baru" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight transition-colors duration-300">Buat Akun Baru</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors duration-300">Daftarkan staff baru untuk mengakses sistem.</p>
                </div>
                <Link :href="route('users.index')" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-bold rounded-xl hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors duration-300">
                    Kembali
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <!-- Main Container -->
                <div class="bg-white dark:bg-slate-800 p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] sm:rounded-3xl border border-slate-100 dark:border-slate-700 transition-colors duration-300">
                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 transition-colors duration-300">Nama Lengkap</label>
                            <input v-model="form.name" type="text" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-600 text-slate-900 dark:text-slate-100 rounded-xl focus:border-blue-500 focus:ring-blue-500 text-sm transition-colors duration-300" required>
                            <p v-if="form.errors.name" class="text-red-500 dark:text-red-400 text-xs mt-1 transition-colors duration-300">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 transition-colors duration-300">Email (Untuk Login)</label>
                            <input v-model="form.email" type="email" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-600 text-slate-900 dark:text-slate-100 rounded-xl focus:border-blue-500 focus:ring-blue-500 text-sm transition-colors duration-300" required>
                            <p v-if="form.errors.email" class="text-red-500 dark:text-red-400 text-xs mt-1 transition-colors duration-300">{{ form.errors.email }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 transition-colors duration-300">Hak Akses (Role)</label>
                            <select v-model="form.role" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-600 text-slate-900 dark:text-slate-100 rounded-xl focus:border-blue-500 focus:ring-blue-500 text-sm transition-colors duration-300" required>
                                <option value="operator">Operator Lapangan (Input Data)</option>
                                <!-- <option value="admin">Pengelola Utama / Admin (Monitoring)</option> -->
                            </select>
                            <p v-if="form.errors.role" class="text-red-500 dark:text-red-400 text-xs mt-1 transition-colors duration-300">{{ form.errors.role }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 transition-colors duration-300">Password</label>
                                <input v-model="form.password" type="password" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-600 text-slate-900 dark:text-slate-100 rounded-xl focus:border-blue-500 focus:ring-blue-500 text-sm transition-colors duration-300" required>
                                <p v-if="form.errors.password" class="text-red-500 dark:text-red-400 text-xs mt-1 transition-colors duration-300">{{ form.errors.password }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 transition-colors duration-300">Konfirmasi Password</label>
                                <input v-model="form.password_confirmation" type="password" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-600 text-slate-900 dark:text-slate-100 rounded-xl focus:border-blue-500 focus:ring-blue-500 text-sm transition-colors duration-300" required>
                            </div>
                        </div>

                        <!-- Footer Form -->
                        <div class="pt-6 border-t border-slate-100 dark:border-slate-700 flex justify-end transition-colors duration-300">
                            <button type="submit" :disabled="form.processing" class="px-8 py-3 bg-blue-600 dark:bg-blue-500 hover:bg-blue-700 dark:hover:bg-blue-400 text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 dark:shadow-none transition-all disabled:opacity-50">
                                Buat Akun
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>