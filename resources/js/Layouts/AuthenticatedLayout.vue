<script setup>
import { ref, computed } from 'vue'; // Tambahkan computed
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3'; // Tambahkan usePage

const isExpanded = ref(true);
const showingNavigationDropdown = ref(false);

const toggleSidebar = () => {
    isExpanded.value = !isExpanded.value;
};

// Logika pemetaan nama halaman
const page = usePage();
const pageTitle = computed(() => {
    const componentPath = page.component.split('/'); // misal: "Parameter/Index" -> ["Parameter", "Index"]
    const moduleName = componentPath[0];

    const mapping = {
        'Dashboard': 'Ringkasan',
        'Kolam': 'Data Kolam',
        'Inventory': 'Gudang',
        'Parameter': 'Kualitas Air',
        'FeedLog': 'Aktivitas',
        'Mortality': 'Mortalitas',
        'DailyOperation': 'Operasi Harian'
    };

    return mapping[moduleName] || moduleName;
});

// Pemetaan untuk sub-halaman (Create/Edit)
const subPageTitle = computed(() => {
    const action = page.component.split('/')[1];
    if (!action || action === 'Index') return null; // Sembunyikan jika Index
    
    const mapping = {
        'Create': 'Tambah Data',
        'Edit': 'Ubah Data'
    };
    return mapping[action] || action;
});
</script>

<template>
    <div class="min-h-screen bg-slate-50/50 flex">
        <aside 
            :class="[isExpanded ? 'w-64' : 'w-20']"
            class="fixed inset-y-0 left-0 z-50 bg-white border-r border-slate-200 transition-all duration-300 ease-in-out hidden md:flex flex-col shadow-[4px_0_24px_rgba(0,0,0,0.02)]"
        >
            <div class="h-16 flex items-center px-6 border-b border-slate-100 flex-shrink-0 overflow-hidden">
                <Link :href="route('dashboard')" class="flex items-center gap-3">
                    <ApplicationLogo class="h-8 w-8 shrink-0 text-slate-900" />
                    <span 
                        :class="[isExpanded ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-4']"
                        class="font-bold text-slate-900 truncate tracking-tight text-lg transition-all duration-300 whitespace-nowrap"
                    >
                        Nila Manager
                    </span>
                </Link>
            </div>

            <nav class="flex-1 overflow-y-auto py-6 px-3 space-y-1 custom-scrollbar overflow-x-hidden">
                <div class="space-y-1">
                    <div 
                        :class="[isExpanded ? 'opacity-100 max-h-10 mb-2' : 'opacity-0 max-h-0 mb-0']"
                        class="transition-all duration-300 ease-in-out overflow-hidden px-3"
                    >
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 whitespace-nowrap">
                            Menu Utama
                        </p>
                    </div>
                    
                    <div class="space-y-1">
                        <Link :href="route('dashboard')" 
                            :class="[route().current('dashboard') ? 'bg-slate-900 text-white shadow-lg shadow-slate-200' : 'text-slate-600 hover:bg-slate-50']"
                            class="flex items-center gap-4 px-3 py-2.5 rounded-xl transition-all group overflow-hidden">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                            <span :class="[isExpanded ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-4']" class="font-medium transition-all duration-300 whitespace-nowrap">Home</span>
                        </Link>

                        <Link v-if="$page.props.auth.user.role === 'admin'" :href="route('kolam.index')" 
                            :class="[route().current('kolam.*') ? 'bg-slate-900 text-white shadow-lg shadow-slate-200' : 'text-slate-600 hover:bg-slate-50']"
                            class="flex items-center gap-4 px-3 py-2.5 rounded-xl transition-all group overflow-hidden">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                            <span :class="[isExpanded ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-4']" class="font-medium transition-all duration-300 whitespace-nowrap">Kolam</span>
                        </Link>

                        <Link v-if="$page.props.auth.user.role === 'admin'" :href="route('inventory.index')" 
                            :class="[route().current('inventory.*') ? 'bg-slate-900 text-white shadow-lg shadow-slate-200' : 'text-slate-600 hover:bg-slate-50']"
                            class="flex items-center gap-4 px-3 py-2.5 rounded-xl transition-all group overflow-hidden">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                            <span :class="[isExpanded ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-4']" class="font-medium transition-all duration-300 whitespace-nowrap">Gudang</span>
                        </Link>

                        <Link :href="route('parameter.index')" 
                            :class="[route().current('parameter.*') ? 'bg-slate-900 text-white shadow-lg shadow-slate-200' : 'text-slate-600 hover:bg-slate-50']"
                            class="flex items-center gap-4 px-3 py-2.5 rounded-xl transition-all group overflow-hidden">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.613.306a4 4 0 01-2.574.344l-2.484-.497a2 2 0 00-1.032.05L3 16.382V5.562l2.484-.828a2 2 0 011.032-.05l2.484.497a4 4 0 002.574-.344l.613-.306a6 6 0 013.86-.517l2.387.477a2 2 0 011.022.547V15.428z" /></svg>
                            <span :class="[isExpanded ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-4']" class="font-medium transition-all duration-300 whitespace-nowrap">Kualitas Air</span>
                        </Link>

                        <Link :href="route('feedlog.index')" 
                            :class="[route().current('feedlog.*') ? 'bg-slate-900 text-white shadow-lg shadow-slate-200' : 'text-slate-600 hover:bg-slate-50']"
                            class="flex items-center gap-4 px-3 py-2.5 rounded-xl transition-all group overflow-hidden">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span :class="[isExpanded ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-4']" class="font-medium transition-all duration-300 whitespace-nowrap">Aktivitas</span>
                        </Link>

                        <Link :href="route('kematian.index')" 
                            :class="[route().current('kematian.*') ? 'bg-slate-900 text-white shadow-lg shadow-slate-200' : 'text-slate-600 hover:bg-slate-50']"
                            class="flex items-center gap-4 px-3 py-2.5 rounded-xl transition-all group overflow-hidden">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            <span :class="[isExpanded ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-4']" class="font-medium transition-all duration-300 whitespace-nowrap">Mortalitas</span>
                        </Link>

                        <Link :href="route('panen.index')" 
                            :class="[route().current('panen.*') ? 'bg-slate-900 text-white shadow-lg shadow-slate-200' : 'text-slate-600 hover:bg-slate-50']"
                            class="flex items-center gap-4 px-3 py-2.5 rounded-xl transition-all group overflow-hidden">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                            </svg>
                            <span :class="[isExpanded ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-4']" class="font-medium transition-all duration-300 whitespace-nowrap">Panen</span>
                        </Link>

                        <Link :href="route('transfer.index')" 
                            :class="[route().current('transfer.*') ? 'bg-slate-900 text-white shadow-lg shadow-slate-200' : 'text-slate-600 hover:bg-slate-50']"
                            class="flex items-center gap-4 px-3 py-2.5 rounded-xl transition-all group overflow-hidden">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                            </svg>
                            <span :class="[isExpanded ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-4']" class="font-medium transition-all duration-300 whitespace-nowrap">Transfer Ikan</span>
                        </Link>

                        <Link :href="route('tebar.index')" 
                            :class="[route().current('tebar.*') ? 'bg-slate-900 text-white shadow-lg shadow-slate-200' : 'text-slate-600 hover:bg-slate-50']"
                            class="flex items-center gap-4 px-3 py-2.5 rounded-xl transition-all group overflow-hidden">
                            
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            
                            <span :class="[isExpanded ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-4']" class="font-medium transition-all duration-300 whitespace-nowrap">Tebar Benih</span>
                        </Link>
                    </div>
                </div>
            </nav>

            <div class="p-4 border-t border-slate-100">
                <button @click="toggleSidebar" class="w-full flex items-center justify-center p-2 rounded-xl bg-slate-50 text-slate-500 hover:text-slate-900 transition-colors">
                    <svg :class="{'rotate-180': !isExpanded}" class="h-5 w-5 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>
                </button>
            </div>
        </aside>

        <div :class="[isExpanded ? 'md:ml-64' : 'md:ml-20']" class="flex-1 transition-all duration-300 ease-in-out flex flex-col">
            <header class="sticky top-0 z-40 h-16 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-6 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="md:hidden p-2 text-slate-600 hover:bg-slate-100 rounded-lg">
                         <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>
                    
                    <div class="hidden md:flex items-center gap-2 text-xs font-medium text-slate-400">
                        <span>Aplikasi</span>
                        <span>/</span>
                        <span class="text-slate-900 font-semibold">{{ pageTitle }}</span>
                        
                        <template v-if="subPageTitle">
                            <span>/</span>
                            <span class="text-slate-600">{{ subPageTitle }}</span>
                        </template>
                    </div>
                </div>

                <div class="flex items-center gap-6">
                    <Link :href="route('operasi.create')" class="hidden sm:block text-xs font-bold uppercase tracking-wider bg-slate-900 text-white px-4 py-2 rounded-lg hover:bg-slate-800 transition shadow-md shadow-slate-200">
                        + Input Harian
                    </Link>

                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button class="flex items-center gap-3 group">
                                <div class="text-right hidden sm:block">
                                    <p class="text-xs font-bold text-slate-900">{{ $page.props.auth.user.name }}</p>
                                    <p class="text-[10px] text-slate-400 uppercase">{{ $page.props.auth.user.role }}</p>
                                </div>
                                <div class="h-9 w-9 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 group-hover:bg-slate-200 transition font-bold border border-slate-200">
                                    {{ $page.props.auth.user.name.charAt(0) }}
                                </div>
                            </button>
                        </template>
                        <template #content>
                            <DropdownLink :href="route('profile.edit')"> Profile </DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button"> Log Out </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <main class="p-6 md:p-10 flex-1">
                <div v-if="$slots.header" class="mb-8">
                    <slot name="header" />
                </div>
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #f1f5f9;
    border-radius: 10px;
}
.custom-scrollbar:hover::-webkit-scrollbar-thumb {
    background: #e2e8f0;
}
</style>