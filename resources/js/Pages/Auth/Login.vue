<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Masuk" />

        <div class="mb-8 text-center">
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Selamat Datang</h2>
            <p class="text-sm text-slate-500 mt-2 font-medium">Silakan masuk ke akun Anda untuk melanjutkan</p>
        </div>

        <div v-if="status" class="mb-6 p-4 text-sm font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl shadow-sm">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <InputLabel for="email" value="Alamat Email" class="text-slate-700 font-bold mb-1" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500 shadow-sm transition-colors px-4 py-2.5"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Kata Sandi" class="text-slate-700 font-bold mb-1" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500 shadow-sm transition-colors px-4 py-2.5"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between pt-2">
                <label class="flex items-center cursor-pointer group">
                    <Checkbox name="remember" v-model:checked="form.remember" class="rounded text-blue-600 focus:ring-blue-500 border-slate-300 group-hover:border-blue-500 transition-colors" />
                    <span class="ms-2 text-sm text-slate-600 font-medium group-hover:text-slate-900 transition-colors">Ingat Saya</span>
                </label>

                <!-- <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm font-bold text-blue-600 hover:text-blue-500 hover:underline focus:outline-none transition-colors"
                >
                    Lupa sandi?
                </Link> -->
            </div>

            <div class="pt-4">
                <PrimaryButton
                    class="w-full flex justify-center items-center py-3.5 bg-blue-600 hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 rounded-xl font-bold text-sm tracking-widest uppercase shadow-lg shadow-blue-500/30 transition-all duration-200"
                    :class="{ 'opacity-70 cursor-wait': form.processing }"
                    :disabled="form.processing"
                >
                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    
                    <span v-if="!form.processing">Masuk Sekarang</span>
                    <span v-else>Memproses...</span>
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>