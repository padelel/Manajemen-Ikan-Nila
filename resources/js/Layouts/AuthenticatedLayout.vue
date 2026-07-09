<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

// State untuk Sidebar
const isSidebarOpen = ref(true);

// Fungsi untuk mengecek ukuran layar
const checkWindowSize = () => {
    if (window.innerWidth < 768) {
        isSidebarOpen.value = false; // Tutup otomatis di layar HP
    } else {
        isSidebarOpen.value = true; // Buka otomatis di layar Desktop
    }
};

onMounted(() => {
    checkWindowSize();
    window.addEventListener('resize', checkWindowSize);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkWindowSize);
});

// Fungsi untuk menutup sidebar di HP saat menu diklik (agar tidak menutupi layar)
const closeOnMobile = () => {
    if (window.innerWidth < 768) {
        isSidebarOpen.value = false;
    }
};
</script>

<template>
    <div class="flex h-screen bg-slate-50 font-sans overflow-hidden">

        <!-- MOBILE OVERLAY (Latar belakang gelap saat sidebar terbuka di HP) -->
        <div v-show="isSidebarOpen"
             @click="isSidebarOpen = false"
             class="fixed inset-0 z-40 bg-slate-900/20 transition-opacity md:hidden"></div>

        <!-- SIDEBAR -->
        <aside :class="[
            isSidebarOpen ? 'w-64 translate-x-0' : 'w-64 -translate-x-full md:w-20 md:translate-x-0',
            'fixed inset-y-0 left-0 z-50 flex flex-col bg-white text-slate-700 transition-all duration-300 ease-in-out md:relative shadow-xl border-r border-slate-200'
        ]">
            <!-- Header Sidebar (Logo) -->
            <div class="flex items-center justify-center h-16 bg-white border-b border-slate-200">
                <Link :href="route('dashboard')" class="flex items-center justify-center w-full">
                    <img src="/fish.png" alt="SMI NILA" class="h-10 w-auto" />
                </Link>
            </div>

            <!-- Menu Sidebar -->
            <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">

                <!-- MENU: DASHBOARD (Semua Role) -->
                <Link :href="route('dashboard')" @click="closeOnMobile"
                      :class="['flex items-center px-3 py-3 rounded-md text-sm font-medium transition-colors',
                               route().current('dashboard') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-100 hover:text-slate-900']">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="ml-3 truncate" v-show="isSidebarOpen">Dashboard</span>
                </Link>

                <!-- ================= KHUSUS SUPERVISOR ================= -->
                <template v-if="$page.props.auth.user.role === 'supervisor'">
                    <div v-show="isSidebarOpen" class="px-3 pt-4 pb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        Master Data
                    </div>

                    <Link :href="route('kolam.index')" @click="closeOnMobile"
                          :class="['flex items-center px-3 py-3 rounded-md text-sm font-medium transition-colors',
                                   route().current('kolam.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-100 hover:text-slate-900']">
                        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                        </svg>
                        <span class="ml-3 truncate" v-show="isSidebarOpen">Master Kolam</span>
                    </Link>

                    <Link :href="route('users.index')" @click="closeOnMobile"
                          :class="['flex items-center px-3 py-3 rounded-md text-sm font-medium transition-colors',
                                   route().current('users.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-100 hover:text-slate-900']">
                        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span class="ml-3 truncate" v-show="isSidebarOpen">Pengguna</span>
                    </Link>

                    <div v-show="isSidebarOpen" class="px-3 pt-4 pb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        Analitik
                    </div>

                    <Link :href="route('analisis.index')" @click="closeOnMobile"
                          :class="['flex items-center px-3 py-3 rounded-md text-sm font-medium transition-colors',
                                   route().current('analisis.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-100 hover:text-slate-900']">
                        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span class="ml-3 truncate" v-show="isSidebarOpen">Laporan & Analisis</span>
                    </Link>
                </template>

                <!-- ================= KHUSUS OPERATOR ================= -->
                <template v-if="$page.props.auth.user.role === 'operator'">
                    <div v-show="isSidebarOpen" class="px-3 pt-4 pb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        Operasional
                    </div>

                    <Link :href="route('parameter.index')" @click="closeOnMobile"
                          :class="['flex items-center px-3 py-3 rounded-md text-sm font-medium transition-colors',
                                   route().current('parameter.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-100 hover:text-slate-900']">
                        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                        <span class="ml-3 truncate" v-show="isSidebarOpen">Kualitas Air</span>
                    </Link>

                    <Link :href="route('tebar.index')" @click="closeOnMobile"
                          :class="['flex items-center px-3 py-3 rounded-md text-sm font-medium transition-colors',
                                   route().current('tebar.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-100 hover:text-slate-900']">
                        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                        </svg>
                        <span class="ml-3 truncate" v-show="isSidebarOpen">Log Tebar</span>
                    </Link>

                    <Link :href="route('kematian.index')" @click="closeOnMobile"
                          :class="['flex items-center px-3 py-3 rounded-md text-sm font-medium transition-colors',
                                   route().current('kematian.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-100 hover:text-slate-900']">
                        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span class="ml-3 truncate" v-show="isSidebarOpen">Log Kematian</span>
                    </Link>

                    <Link :href="route('panen.index')" @click="closeOnMobile"
                          :class="['flex items-center px-3 py-3 rounded-md text-sm font-medium transition-colors',
                                   route().current('panen.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-100 hover:text-slate-900']">
                        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span class="ml-3 truncate" v-show="isSidebarOpen">Log Panen</span>
                    </Link>
                </template>

                <!-- MENU BERSAMA -->
                <div v-show="isSidebarOpen" class="px-3 pt-4 pb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    Sistem Pakar
                </div>

                <Link :href="route('tiket.index')" @click="closeOnMobile"
                      :class="['flex items-center px-3 py-3 rounded-md text-sm font-medium transition-colors',
                               route().current('tiket.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-100 hover:text-slate-900']">
                    <svg class="w-6 h-6 flex-shrink-0 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span class="ml-3 truncate" v-show="isSidebarOpen">Tiket Mitigasi</span>
                </Link>

            </nav>
        </aside>

        <!-- MAIN KONTEN KANAN -->
        <div class="flex-1 flex flex-col min-w-0 bg-gray-100">

            <!-- TOP NAVBAR (Putih) -->
            <header class="h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 bg-white border-b border-gray-200 shadow-sm z-10">
                <!-- Tombol Toggle Sidebar -->
                <button @click="isSidebarOpen = !isSidebarOpen"
                        class="p-2 mr-4 text-gray-500 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- Profil Dropdown -->
                <div class="flex items-center ml-auto">
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button type="button" class="flex items-center text-sm font-medium text-gray-700 hover:text-indigo-600 focus:outline-none transition">
                                <div class="hidden md:block mr-2 text-right">
                                    <div class="font-bold leading-none">{{ $page.props.auth.user.name }}</div>
                                    <div class="text-xs text-gray-500 mt-1 uppercase">{{ $page.props.auth.user.role }}</div>
                                </div>
                                <!-- Avatar Placeholder -->
                                <div class="h-8 w-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold">
                                    {{ $page.props.auth.user.name.charAt(0) }}
                                </div>
                                <svg class="ml-1 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </template>

                        <template #content>
                            <DropdownLink :href="route('profile.edit')">Profil Saya</DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">Keluar</DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <!-- KONTEN HALAMAN (Scrollable) -->
            <main class="flex-1 overflow-y-auto">
                <!-- Page Header yang dikirim dari page Vue (opsional) -->
                <div v-if="$slots.header" class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                        <slot name="header" />
                    </div>
                </div>

                <!-- Konten Utama -->
                <slot />
            </main>

        </div>
    </div>
</template>
