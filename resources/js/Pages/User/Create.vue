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
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Buat Akun Baru</h2>
                    <p class="text-sm text-slate-500 mt-1">Daftarkan staff baru untuk mengakses sistem.</p>
                </div>
                <Link :href="route('users.index')" class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-bold rounded-xl hover:bg-slate-200 transition">
                    Kembali
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:rounded-3xl border border-slate-100">
                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nama Lengkap</label>
                            <input v-model="form.name" type="text" class="w-full border-slate-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                            <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Email (Untuk Login)</label>
                            <input v-model="form.email" type="email" class="w-full border-slate-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                            <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Hak Akses (Role)</label>
                            <select v-model="form.role" class="w-full border-slate-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                                <option value="operator">Operator Lapangan (Input Data)</option>
                                <!-- <option value="admin">Pengelola Utama / Admin (Monitoring)</option> -->
                            </select>
                            <p v-if="form.errors.role" class="text-red-500 text-xs mt-1">{{ form.errors.role }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Password</label>
                                <input v-model="form.password" type="password" class="w-full border-slate-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                                <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Konfirmasi Password</label>
                                <input v-model="form.password_confirmation" type="password" class="w-full border-slate-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100 flex justify-end">
                            <button type="submit" :disabled="form.processing" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 transition">
                                Buat Akun
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>