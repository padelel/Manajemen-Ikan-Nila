<script setup>
import { ref, computed, onMounted } from "vue";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import { Link, usePage } from "@inertiajs/vue3";

const isExpanded = ref(true);
const showingNavigationDropdown = ref(false);

const toggleSidebar = () => {
    isExpanded.value = !isExpanded.value;
};

// --- Dark Mode Logic ---
const isDark = ref(false);

onMounted(() => {
    isDark.value = document.documentElement.classList.contains("dark");
});

const toggleDarkMode = () => {
    isDark.value = !isDark.value;
    if (isDark.value) {
        document.documentElement.classList.add("dark");
        localStorage.setItem("color-theme", "dark");
    } else {
        document.documentElement.classList.remove("dark");
        localStorage.setItem("color-theme", "light");
    }
};
// -----------------------

// Logika pemetaan nama halaman
const page = usePage();
const pageTitle = computed(() => {
    const componentPath = page.component.split("/");
    const moduleName = componentPath[0];

    const mapping = {
        Dashboard: "Ringkasan",
        Kolam: "Data Kolam",
        Inventory: "Gudang",
        Parameter: "Kualitas Air",
        FeedLog: "Aktivitas",
        Mortality: "Mortalitas",
        DailyOperation: "Operasi Harian",
        Transfer: "Transfer Ikan",
        TebarLog: "Tebar Benih",
        HarvestLog: "Panen",
    };

    return mapping[moduleName] || moduleName;
});

const subPageTitle = computed(() => {
    const action = page.component.split("/")[1];
    if (!action || action === "Index") return null;

    const mapping = {
        Create: "Tambah Data",
        Edit: "Ubah Data",
        Show: "Detail",
    };
    return mapping[action] || action;
});

const isMenuUtamaActive = computed(
    () =>
        route().current("dashboard") ||
        route().current("kolam.*") ||
        (route().current("inventory.*") &&
            !route().current("inventory.history")),
);

const isRiwayatActive = computed(
    () =>
        route().current("parameter.*") ||
        route().current("feedlog.*") ||
        route().current("inventory.history"),
);

const isPopulasiActive = computed(
    () =>
        route().current("kematian.*") ||
        route().current("panen.*") ||
        route().current("transfer.*") ||
        route().current("tebar.*"),
);

// Daftar Menu
const menuItems = computed(() => [
    {
        group: "Menu Utama",
        active: isMenuUtamaActive.value,
        items: [
            {
                label: "Home",
                routeName: "dashboard",
                active: route().current("dashboard"),
                icon: "M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6",
                show: true,
            },
            {
                label: "Kolam",
                routeName: "kolam.index",
                active: route().current("kolam.*"),
                icon: "M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10",
                show: true,
            },
            {
                label: "Gudang",
                routeName: "inventory.index",
                active:
                    route().current("inventory.*") &&
                    !route().current("inventory.history"),
                icon: "M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4",
                show: page.props.auth.user.role === "operator",
            },
            {
                label: "Kelola Akun",
                routeName: "users.index",
                active: route().current("users.*"),
                icon: "M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z",
                show: page.props.auth.user.role === "admin",
            },
            {
                label: "Analisis Kolam",
                routeName: "analisis.index",
                active: route().current("analisis.*"),
                icon: "M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z",
                show: page.props.auth.user.role === "admin",
            },
        ],
    },
    {
        group: "Riwayat",
        active: isRiwayatActive.value,
        items: [
            {
                label: "Kualitas Air",
                routeName: "parameter.index",
                active: route().current("parameter.*"),
                icon: "M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.613.306a4 4 0 01-2.574.344l-2.484-.497a2 2 0 00-1.032.05L3 16.382V5.562l2.484-.828a2 2 0 011.032-.05l2.484.497a4 4 0 002.574-.344l.613-.306a6 6 0 013.86-.517l2.387.477a2 2 0 011.022.547V15.428z",
                show: true,
            },
            {
                label: "Aktivitas",
                routeName: "feedlog.index",
                active: route().current("feedlog.*"),
                icon: "M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z",
                show: true,
            },
            {
                label: "Riwayat Stok",
                routeName: "inventory.history",
                active: route().current("inventory.history"),
                icon: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01",
                show: page.props.auth.user.role === "admin",
            },
        ],
    },
    {
        group: "Pengaturan Populasi",
        active: isPopulasiActive.value,
        items: [
            {
                label: "Mortalitas",
                routeName: "kematian.index",
                active: route().current("kematian.*"),
                icon: "M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z",
                show: true,
            },
            {
                label: "Panen",
                routeName: "panen.index",
                active: route().current("panen.*"),
                icon: "M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4",
                show: true,
            },
            {
                label: "Transfer Ikan",
                routeName: "transfer.index",
                active: route().current("transfer.*"),
                icon: "M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5",
                show: true,
            },
            {
                label: "Tebar Benih",
                routeName: "tebar.index",
                active: route().current("tebar.*"),
                icon: "M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3",
                show: true,
            },
        ],
    },
]);
</script>

<template>
    <div
        class="min-h-screen bg-slate-50/50 dark:bg-slate-900 transition-colors duration-300 flex"
    >
        <aside
            :class="[isExpanded ? 'w-64' : 'w-20']"
            class="fixed inset-y-0 left-0 z-50 bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 transition-all duration-300 ease-in-out hidden md:flex flex-col shadow-[4px_0_24px_rgba(0,0,0,0.02)]"
        >
            <div
                class="h-16 flex items-center px-6 border-b border-slate-100 dark:border-slate-700 flex-shrink-0 overflow-hidden"
            >
                <Link
                    :href="route('dashboard')"
                    class="flex items-center gap-3"
                >
                    <ApplicationLogo
                        class="h-8 w-8 shrink-0 text-slate-900 dark:text-slate-100"
                    />
                    <span
                        :class="[
                            isExpanded
                                ? 'opacity-100 translate-x-0'
                                : 'opacity-0 -translate-x-4',
                        ]"
                        class="font-bold text-slate-900 dark:text-slate-100 truncate tracking-tight text-lg transition-all duration-300 whitespace-nowrap"
                    >
                        Nila Manager
                    </span>
                </Link>
            </div>

            <nav
                class="flex-1 overflow-y-auto py-6 px-3 space-y-4 custom-scrollbar overflow-x-hidden"
            >
                <template v-for="(group, gIndex) in menuItems" :key="gIndex">
                    <div class="space-y-1">
                        <div
                            :class="[
                                isExpanded
                                    ? 'opacity-100 max-h-10 mb-2'
                                    : 'opacity-0 max-h-0 mb-0',
                            ]"
                            class="transition-all duration-300 ease-in-out overflow-hidden px-3"
                        >
                            <p
                                class="text-[10px] font-bold uppercase tracking-widest whitespace-nowrap flex items-center gap-2 transition-colors duration-300"
                                :class="[
                                    group.active
                                        ? 'text-blue-600 dark:text-indigo-400'
                                        : 'text-slate-400 dark:text-slate-500',
                                ]"
                            >
                                <span
                                    v-if="group.active"
                                    class="w-1.5 h-1.5 rounded-full bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.6)]"
                                ></span>
                                {{ group.group }}
                            </p>
                        </div>

                        <template
                            v-for="(item, iIndex) in group.items"
                            :key="iIndex"
                        >
                            <Link
                                v-if="item.show"
                                :href="route(item.routeName)"
                                :class="[
                                    item.active
                                        ? 'bg-slate-900 dark:bg-indigo-600 text-white shadow-lg shadow-slate-200 dark:shadow-none'
                                        : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50',
                                ]"
                                class="flex items-center gap-4 px-3 py-2.5 rounded-xl transition-all group overflow-hidden"
                            >
                                <svg
                                    class="h-5 w-5 shrink-0"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path :d="item.icon" />
                                </svg>
                                <span
                                    :class="[
                                        isExpanded
                                            ? 'opacity-100 translate-x-0'
                                            : 'opacity-0 -translate-x-4',
                                    ]"
                                    class="font-medium transition-all duration-300 whitespace-nowrap"
                                >
                                    {{ item.label }}
                                </span>
                            </Link>
                        </template>
                    </div>
                </template>
            </nav>

            <div class="p-4 border-t border-slate-100 dark:border-slate-700">
                <button
                    @click="toggleSidebar"
                    class="w-full flex items-center justify-center p-2 rounded-xl bg-slate-50 dark:bg-slate-700 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 transition-colors"
                >
                    <svg
                        :class="{ 'rotate-180': !isExpanded }"
                        class="h-5 w-5 transition-transform duration-300"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>
                </button>
            </div>
        </aside>

        <div
            v-if="showingNavigationDropdown"
            @click="showingNavigationDropdown = false"
            class="fixed inset-0 z-[60] bg-slate-900/50 backdrop-blur-sm md:hidden transition-opacity"
        ></div>

        <aside
            :class="
                showingNavigationDropdown
                    ? 'translate-x-0'
                    : '-translate-x-full'
            "
            class="fixed inset-y-0 left-0 z-[70] w-72 bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 transition-transform duration-300 ease-in-out md:hidden flex flex-col shadow-2xl"
        >
            <div
                class="h-16 flex items-center justify-between px-6 border-b border-slate-100 dark:border-slate-700 flex-shrink-0"
            >
                <Link
                    :href="route('dashboard')"
                    class="flex items-center gap-3"
                    @click="showingNavigationDropdown = false"
                >
                    <ApplicationLogo
                        class="h-8 w-8 shrink-0 text-slate-900 dark:text-slate-100"
                    />
                    <span
                        class="font-bold text-slate-900 dark:text-slate-100 tracking-tight text-lg"
                        >Nila Manager</span
                    >
                </Link>
                <button
                    @click="showingNavigationDropdown = false"
                    class="p-2 -mr-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                >
                    <svg
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            <nav
                class="flex-1 overflow-y-auto py-6 px-4 space-y-6 custom-scrollbar"
            >
                <template v-for="(group, gIndex) in menuItems" :key="gIndex">
                    <div class="space-y-2">
                        <div class="px-2">
                            <p
                                class="text-xs font-bold uppercase tracking-widest flex items-center gap-2"
                                :class="[
                                    group.active
                                        ? 'text-blue-600 dark:text-indigo-400'
                                        : 'text-slate-400 dark:text-slate-500',
                                ]"
                            >
                                <span
                                    v-if="group.active"
                                    class="w-2 h-2 rounded-full bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.6)]"
                                ></span>
                                {{ group.group }}
                            </p>
                        </div>

                        <template
                            v-for="(item, iIndex) in group.items"
                            :key="iIndex"
                        >
                            <Link
                                v-if="item.show"
                                :href="route(item.routeName)"
                                @click="showingNavigationDropdown = false"
                                :class="[
                                    item.active
                                        ? 'bg-slate-900 dark:bg-indigo-600 text-white shadow-md shadow-slate-200 dark:shadow-none'
                                        : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50',
                                ]"
                                class="flex items-center gap-4 px-4 py-3 rounded-xl transition-all"
                            >
                                <svg
                                    class="h-5 w-5 shrink-0"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path :d="item.icon" />
                                </svg>
                                <span class="font-semibold">{{
                                    item.label
                                }}</span>
                            </Link>
                        </template>
                    </div>
                </template>
            </nav>
        </aside>

        <div
            :class="[isExpanded ? 'md:ml-64' : 'md:ml-20']"
            class="flex-1 transition-all duration-300 ease-in-out flex flex-col min-w-0"
        >
            <header
                class="sticky top-0 z-50 h-16 bg-white/80 dark:bg-slate-800/80 backdrop-blur-md border-b border-slate-200/60 dark:border-slate-700/60 px-4 sm:px-6 flex items-center justify-between transition-colors duration-300"
            >
                <div class="flex items-center gap-2 sm:gap-4">
                    <button
                        @click="
                            showingNavigationDropdown =
                                !showingNavigationDropdown
                        "
                        class="md:hidden p-2 -ml-2 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg shrink-0"
                    >
                        <svg
                            class="h-6 w-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                        </svg>
                    </button>

                    <div
                        class="hidden sm:flex items-center gap-2 text-xs font-medium text-slate-400 dark:text-slate-500 truncate"
                    >
                        <span>Aplikasi</span>
                        <span>/</span>
                        <span
                            class="text-slate-900 dark:text-slate-200 font-semibold"
                            >{{ pageTitle }}</span
                        >

                        <template v-if="subPageTitle">
                            <span>/</span>
                            <span
                                class="text-slate-600 dark:text-slate-400 truncate"
                                >{{ subPageTitle }}</span
                            >
                        </template>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-6">
                    <Link
                        v-if="$page.props.auth.user.role === 'operator'"
                        :href="route('operasi.create')"
                        class="text-[10px] sm:text-xs font-bold uppercase tracking-wider bg-slate-900 dark:bg-indigo-600 text-white px-3 py-2 sm:px-4 sm:py-2.5 rounded-lg hover:bg-slate-800 dark:hover:bg-indigo-500 transition shadow-md shadow-slate-200 dark:shadow-none shrink-0 whitespace-nowrap"
                    >
                        <span class="sm:hidden">+ Input</span>
                        <span class="hidden sm:inline">+ Input Harian</span>
                    </Link>

                    <button
                        @click="toggleDarkMode"
                        class="p-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-600 focus:outline-none transition-colors duration-200 shrink-0"
                    >
                        <svg
                            v-if="isDark"
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-5 h-5 text-amber-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
                            />
                        </svg>
                        <svg
                            v-else
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-5 h-5 text-slate-600"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
                            />
                        </svg>
                    </button>

                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button
                                class="flex items-center gap-3 group focus:outline-none shrink-0"
                            >
                                <div class="text-right hidden sm:block">
                                    <p
                                        class="text-xs font-bold text-slate-900 dark:text-slate-200"
                                    >
                                        {{ $page.props.auth.user.name }}
                                    </p>
                                    <p
                                        class="text-[10px] text-slate-400 dark:text-slate-500 uppercase"
                                    >
                                        {{ $page.props.auth.user.role }}
                                    </p>
                                </div>
                                <div
                                    class="h-9 w-9 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 group-hover:bg-slate-200 dark:group-hover:bg-slate-600 transition font-bold border border-slate-200 dark:border-slate-600"
                                >
                                    {{ $page.props.auth.user.name.charAt(0) }}
                                </div>
                            </button>
                        </template>
                        <template #content>
                            <DropdownLink :href="route('profile.edit')">
                                Profile
                            </DropdownLink>
                            <DropdownLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Log Out
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <main
                class="p-4 sm:p-6 md:p-10 flex-1 text-slate-900 dark:text-slate-100 transition-colors duration-300 overflow-x-hidden"
            >
                <div v-if="$slots.header" class="mb-6 md:mb-8">
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
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #334155;
}
.custom-scrollbar:hover::-webkit-scrollbar-thumb {
    background: #e2e8f0;
}
.dark .custom-scrollbar:hover::-webkit-scrollbar-thumb {
    background: #475569;
}
</style>
