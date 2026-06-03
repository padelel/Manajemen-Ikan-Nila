<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    harvest: Object,
    siklus: Object,
    tebarLog: Object,
    statistik: Object,
    riwayat_panen: Array,
    mortality_logs: Array,
});

const formatDate = (dateString) => {
    if (!dateString) return "—";
    return new Date(dateString).toLocaleDateString("id-ID", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};

const formatNumber = (n) => {
    if (n === null || n === undefined) return "—";
    return Number(n).toLocaleString("id-ID");
};

const isFull = computed(() => props.harvest.jenis_panen === "Full");

const survivalRateColor = computed(() => {
    const v = props.statistik?.survival_rate_persen;
    if (v == null)
        return { text: "text-slate-400 dark:text-slate-500", label: "" };
    if (v >= 90)
        return {
            text: "text-emerald-600 dark:text-emerald-400",
            label: "Sangat Baik",
        };
    if (v >= 80)
        return { text: "text-blue-600 dark:text-blue-400", label: "Baik" };
    if (v >= 70)
        return { text: "text-amber-600 dark:text-amber-400", label: "Cukup" };
    return { text: "text-red-600 dark:text-red-400", label: "Kurang Baik" };
});

const fcrColor = computed(() => {
    const v = props.statistik?.fcr;
    if (v == null)
        return { text: "text-slate-400 dark:text-slate-500", label: "" };
    if (v < 1.5)
        return {
            text: "text-emerald-600 dark:text-emerald-400",
            label: "Sangat Efisien",
        };
    if (v < 1.8)
        return { text: "text-blue-600 dark:text-blue-400", label: "Efisien" };
    if (v < 2.2)
        return { text: "text-amber-600 dark:text-amber-400", label: "Cukup" };
    return { text: "text-red-600 dark:text-red-400", label: "Boros" };
});

const adgColor = computed(() => {
    const v = props.statistik?.adg_gram_hari;
    if (v == null)
        return { text: "text-slate-400 dark:text-slate-500", label: "" };
    if (v >= 3)
        return {
            text: "text-emerald-600 dark:text-emerald-400",
            label: "Sangat Cepat",
        };
    if (v >= 2)
        return { text: "text-blue-600 dark:text-blue-400", label: "Normal" };
    return { text: "text-amber-600 dark:text-amber-400", label: "Lambat" };
});

const survivalBarWidth = computed(() => {
    const v = props.statistik?.survival_rate_persen;
    if (v == null) return 0;
    return Math.min(100, v);
});

const mortalityBarWidth = computed(() => {
    const total = props.statistik?.jumlah_tebar_awal;
    const mati = props.statistik?.total_mati;
    if (!total || mati == null) return 0;
    return Math.min(100, (mati / total) * 100);
});
</script>

<template>
    <Head title="Detail Panen" />

    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col md:flex-row md:justify-between md:items-end gap-4"
            >
                <div>
                    <h2
                        class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight transition-colors duration-300"
                    >
                        Detail Panen
                    </h2>
                    <p
                        class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors duration-300"
                    >
                        Ringkasan lengkap hasil panen dan kinerja siklus
                        budidaya.
                    </p>
                </div>
                <Link
                    :href="route('panen.index')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-bold rounded-xl hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors duration-300 flex-shrink-0"
                >
                    <svg
                        class="w-4 h-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2.5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15 19l-7-7 7-7"
                        />
                    </svg>
                    Kembali ke Riwayat
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Hero Card -->
                <div
                    class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 transition-colors duration-300"
                >
                    <div
                        class="flex flex-col md:flex-row md:items-center gap-6"
                    >
                        <!-- Left: Icon + Identity -->
                        <div class="flex items-start gap-5 flex-1 min-w-0">
                            <div
                                :class="
                                    isFull
                                        ? 'bg-emerald-50 dark:bg-emerald-500/10 border-emerald-200 dark:border-emerald-500/20 text-emerald-600 dark:text-emerald-400'
                                        : 'bg-amber-50 dark:bg-amber-500/10 border-amber-200 dark:border-amber-500/20 text-amber-600 dark:text-amber-400'
                                "
                                class="h-16 w-16 rounded-2xl flex items-center justify-center border flex-shrink-0 transition-colors duration-300"
                            >
                                <svg
                                    class="w-8 h-8"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"
                                    />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <span
                                    :class="
                                        isFull
                                            ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20'
                                            : 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20'
                                    "
                                    class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest inline-block mb-2 transition-colors duration-300"
                                >
                                    {{
                                        isFull ? "Panen Full" : "Panen Parsial"
                                    }}
                                </span>
                                <h3
                                    class="text-xl font-bold text-slate-900 dark:text-slate-100 transition-colors duration-300"
                                >
                                    {{ harvest.kolam.nama_kolam }}
                                </h3>
                                <p
                                    class="text-sm text-slate-500 dark:text-slate-400 mt-0.5 transition-colors duration-300"
                                >
                                    {{ harvest.kolam.lokasi }}
                                </p>
                            </div>
                        </div>

                        <!-- Right: Meta -->
                        <div
                            class="flex flex-col gap-2 md:items-end flex-shrink-0"
                        >
                            <div class="flex items-center gap-2">
                                <svg
                                    class="w-4 h-4 text-slate-400 dark:text-slate-500"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    />
                                </svg>
                                <span
                                    class="text-sm font-bold text-slate-700 dark:text-slate-200 transition-colors duration-300"
                                    >{{
                                        formatDate(harvest.tanggal_panen)
                                    }}</span
                                >
                            </div>
                            <p
                                class="text-xs text-slate-500 dark:text-slate-400 transition-colors duration-300"
                            >
                                Dicatat oleh
                                <span
                                    class="font-bold text-slate-700 dark:text-slate-200 transition-colors duration-300"
                                    >{{ harvest.user?.name ?? "—" }}</span
                                >
                            </p>
                            <p
                                class="text-xs text-slate-500 dark:text-slate-400 transition-colors duration-300"
                            >
                                Durasi Siklus:
                                <span
                                    class="font-bold text-slate-700 dark:text-slate-200 transition-colors duration-300"
                                    >{{
                                        statistik?.durasi_hari != null
                                            ? statistik.durasi_hari + " hari"
                                            : "—"
                                    }}</span
                                >
                            </p>
                        </div>
                    </div>
                </div>

                <!-- KPI Cards — only for Full harvest -->
                <div
                    v-if="isFull"
                    class="grid grid-cols-2 lg:grid-cols-4 gap-4"
                >
                    <!-- Survival Rate -->
                    <div
                        class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-colors duration-300"
                    >
                        <div class="flex items-start justify-between mb-1">
                            <p
                                class="text-xs font-black text-slate-900 dark:text-slate-100 uppercase tracking-wider transition-colors duration-300"
                            >
                                Survival Rate
                            </p>
                            <span
                                v-if="statistik?.survival_rate_persen != null"
                                :class="survivalRateColor.text"
                                class="text-[10px] font-black uppercase tracking-widest text-right transition-colors duration-300"
                                >{{ survivalRateColor.label }}</span
                            >
                        </div>
                        <p
                            :class="survivalRateColor.text"
                            class="text-3xl font-black mt-3 tabular-nums transition-colors duration-300"
                        >
                            {{
                                statistik?.survival_rate_persen != null
                                    ? Number(
                                          statistik.survival_rate_persen,
                                      ).toLocaleString("id-ID") + "%"
                                    : "—"
                            }}
                        </p>
                        <p
                            class="text-[10px] text-slate-400 dark:text-slate-500 mt-2 font-medium transition-colors duration-300"
                        >
                            Tingkat kelangsungan hidup
                        </p>
                    </div>

                    <!-- FCR -->
                    <div
                        class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-colors duration-300"
                    >
                        <div class="flex items-start justify-between mb-1">
                            <p
                                class="text-xs font-black text-slate-900 dark:text-slate-100 uppercase tracking-wider transition-colors duration-300"
                            >
                                FCR
                            </p>
                            <span
                                v-if="statistik?.fcr != null"
                                :class="fcrColor.text"
                                class="text-[10px] font-black uppercase tracking-widest text-right transition-colors duration-300"
                                >{{ fcrColor.label }}</span
                            >
                        </div>
                        <p
                            :class="fcrColor.text"
                            class="text-3xl font-black mt-3 tabular-nums transition-colors duration-300"
                        >
                            {{
                                statistik?.fcr != null
                                    ? Number(statistik.fcr).toLocaleString(
                                          "id-ID",
                                      )
                                    : "—"
                            }}
                        </p>
                        <p
                            class="text-[10px] text-slate-400 dark:text-slate-500 mt-2 font-medium transition-colors duration-300"
                        >
                            Konversi pakan (target &lt; 1.8)
                        </p>
                    </div>

                    <!-- ADG -->
                    <div
                        class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-colors duration-300"
                    >
                        <div class="flex items-start justify-between mb-1">
                            <p
                                class="text-xs font-black text-slate-900 dark:text-slate-100 uppercase tracking-wider transition-colors duration-300"
                            >
                                ADG
                            </p>
                            <span
                                v-if="statistik?.adg_gram_hari != null"
                                :class="adgColor.text"
                                class="text-[10px] font-black uppercase tracking-widest text-right transition-colors duration-300"
                                >{{ adgColor.label }}</span
                            >
                        </div>
                        <p
                            :class="adgColor.text"
                            class="text-3xl font-black mt-3 tabular-nums transition-colors duration-300"
                        >
                            {{
                                statistik?.adg_gram_hari != null
                                    ? Number(
                                          statistik.adg_gram_hari,
                                      ).toLocaleString("id-ID") + " g/hari"
                                    : "—"
                            }}
                        </p>
                        <p
                            class="text-[10px] text-slate-400 dark:text-slate-500 mt-2 font-medium transition-colors duration-300"
                        >
                            Pertumbuhan harian rata-rata
                        </p>
                    </div>

                    <!-- Total Hasil Panen -->
                    <div
                        class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-colors duration-300"
                    >
                        <p
                            class="text-xs font-black text-slate-900 dark:text-slate-100 uppercase tracking-wider mb-1 transition-colors duration-300"
                        >
                            Total Hasil Panen
                        </p>
                        <p
                            class="text-3xl font-black text-slate-700 dark:text-slate-200 mt-3 tabular-nums transition-colors duration-300"
                        >
                            {{
                                statistik?.total_biomassa_panen_kg != null
                                    ? Number(
                                          statistik.total_biomassa_panen_kg,
                                      ).toLocaleString("id-ID") + " kg"
                                    : "—"
                            }}
                        </p>
                        <p
                            class="text-[10px] text-slate-400 dark:text-slate-500 mt-2 font-medium transition-colors duration-300"
                        >
                            dari
                            {{ formatNumber(statistik?.total_ekor_panen) }} ekor
                        </p>
                    </div>
                </div>

                <!-- Two-column grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Left: Ringkasan Siklus Budidaya -->
                    <div
                        class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden transition-colors duration-300"
                    >
                        <div
                            class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 transition-colors duration-300"
                        >
                            <h3
                                class="text-xs font-black text-slate-900 dark:text-slate-100 uppercase tracking-wider transition-colors duration-300"
                            >
                                Ringkasan Siklus Budidaya
                            </h3>
                        </div>
                        <div
                            class="divide-y divide-slate-50 dark:divide-slate-700/50"
                        >
                            <div
                                class="flex items-center justify-between px-6 py-4"
                            >
                                <span
                                    class="text-sm text-slate-500 dark:text-slate-400 transition-colors duration-300"
                                    >Tanggal Mulai Siklus</span
                                >
                                <span
                                    class="text-sm font-bold text-slate-900 dark:text-slate-100 transition-colors duration-300"
                                    >{{
                                        formatDate(siklus?.tanggal_mulai)
                                    }}</span
                                >
                            </div>
                            <div
                                class="flex items-center justify-between px-6 py-4"
                            >
                                <span
                                    class="text-sm text-slate-500 dark:text-slate-400 transition-colors duration-300"
                                    >Tanggal Panen (Selesai)</span
                                >
                                <span
                                    class="text-sm font-bold text-slate-900 dark:text-slate-100 transition-colors duration-300"
                                    >{{
                                        formatDate(harvest.tanggal_panen)
                                    }}</span
                                >
                            </div>
                            <div
                                class="flex items-center justify-between px-6 py-4"
                            >
                                <span
                                    class="text-sm text-slate-500 dark:text-slate-400 transition-colors duration-300"
                                    >Durasi Siklus</span
                                >
                                <span
                                    class="text-sm font-bold text-slate-900 dark:text-slate-100 transition-colors duration-300"
                                >
                                    {{
                                        statistik?.durasi_hari != null
                                            ? statistik.durasi_hari + " hari"
                                            : "—"
                                    }}
                                </span>
                            </div>
                            <div
                                class="flex items-center justify-between px-6 py-4"
                            >
                                <span
                                    class="text-sm text-slate-500 dark:text-slate-400 transition-colors duration-300"
                                    >Sumber Benih</span
                                >
                                <span
                                    class="text-sm font-bold text-slate-900 dark:text-slate-100 transition-colors duration-300"
                                    >{{ tebarLog?.sumber_benih ?? "—" }}</span
                                >
                            </div>
                            <div
                                class="flex items-center justify-between px-6 py-4"
                            >
                                <span
                                    class="text-sm text-slate-500 dark:text-slate-400 transition-colors duration-300"
                                    >Berat Awal Tebar</span
                                >
                                <span
                                    class="text-sm font-bold text-slate-900 dark:text-slate-100 transition-colors duration-300"
                                >
                                    {{
                                        statistik?.berat_awal_tebar_gram != null
                                            ? Number(
                                                  statistik.berat_awal_tebar_gram,
                                              ).toLocaleString("id-ID") +
                                              " gram/ekor"
                                            : "—"
                                    }}
                                </span>
                            </div>
                            <div
                                class="flex items-center justify-between px-6 py-4"
                            >
                                <span
                                    class="text-sm text-slate-500 dark:text-slate-400 transition-colors duration-300"
                                    >Berat Rata-rata Panen</span
                                >
                                <span
                                    class="text-sm font-bold text-slate-900 dark:text-slate-100 transition-colors duration-300"
                                >
                                    {{
                                        statistik?.berat_rata_panen_gram != null
                                            ? Number(
                                                  statistik.berat_rata_panen_gram,
                                              ).toLocaleString("id-ID") +
                                              " gram/ekor"
                                            : "—"
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Perbandingan Populasi -->
                    <div
                        class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden transition-colors duration-300"
                    >
                        <div
                            class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 transition-colors duration-300"
                        >
                            <h3
                                class="text-xs font-black text-slate-900 dark:text-slate-100 uppercase tracking-wider transition-colors duration-300"
                            >
                                Perbandingan Populasi
                            </h3>
                        </div>
                        <div class="p-6 space-y-5">
                            <!-- Tebar Awal -->
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <div
                                        class="h-7 w-7 rounded-lg bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20 flex items-center justify-center text-blue-600 dark:text-blue-400 font-black text-sm flex-shrink-0 transition-colors duration-300"
                                    >
                                        +
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div
                                            class="flex items-baseline justify-between gap-2"
                                        >
                                            <span
                                                class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider transition-colors duration-300"
                                                >Tebar Awal</span
                                            >
                                            <span
                                                class="text-sm font-black text-slate-900 dark:text-slate-100 tabular-nums transition-colors duration-300"
                                                >{{
                                                    formatNumber(
                                                        statistik?.jumlah_tebar_awal,
                                                    )
                                                }}
                                                ekor</span
                                            >
                                        </div>
                                        <p
                                            class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5 transition-colors duration-300"
                                        >
                                            @
                                            {{
                                                formatNumber(
                                                    statistik?.berat_awal_tebar_gram,
                                                )
                                            }}
                                            g/ekor
                                        </p>
                                    </div>
                                </div>
                                <div
                                    class="h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden transition-colors duration-300"
                                >
                                    <div
                                        class="h-full bg-blue-500 dark:bg-blue-400 rounded-full"
                                        style="width: 100%"
                                    ></div>
                                </div>
                            </div>

                            <!-- Total Dipanen -->
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <div
                                        class="h-7 w-7 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400 font-black text-sm flex-shrink-0 transition-colors duration-300"
                                    >
                                        ✓
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div
                                            class="flex items-baseline justify-between gap-2"
                                        >
                                            <span
                                                class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider transition-colors duration-300"
                                                >Total Dipanen</span
                                            >
                                            <span
                                                class="text-sm font-black text-slate-900 dark:text-slate-100 tabular-nums transition-colors duration-300"
                                                >{{
                                                    formatNumber(
                                                        statistik?.total_ekor_panen,
                                                    )
                                                }}
                                                ekor</span
                                            >
                                        </div>
                                        <p
                                            class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5 transition-colors duration-300"
                                        >
                                            @
                                            {{
                                                formatNumber(
                                                    statistik?.berat_rata_panen_gram,
                                                )
                                            }}
                                            g/ekor ·
                                            {{
                                                formatNumber(
                                                    statistik?.total_biomassa_panen_kg,
                                                )
                                            }}
                                            kg
                                        </p>
                                    </div>
                                </div>
                                <div
                                    class="h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden transition-colors duration-300"
                                >
                                    <div
                                        class="h-full bg-emerald-500 dark:bg-emerald-400 rounded-full transition-all duration-700"
                                        :style="{
                                            width: survivalBarWidth + '%',
                                        }"
                                    ></div>
                                </div>
                            </div>

                            <!-- Total Kematian -->
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <div
                                        class="h-7 w-7 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-100 dark:border-red-500/20 flex items-center justify-center text-red-600 dark:text-red-400 font-black text-sm flex-shrink-0 transition-colors duration-300"
                                    >
                                        ✕
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div
                                            class="flex items-baseline justify-between gap-2"
                                        >
                                            <span
                                                class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider transition-colors duration-300"
                                                >Total Kematian</span
                                            >
                                            <span
                                                class="text-sm font-black text-slate-900 dark:text-slate-100 tabular-nums transition-colors duration-300"
                                                >{{
                                                    formatNumber(
                                                        statistik?.total_mati,
                                                    )
                                                }}
                                                ekor</span
                                            >
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden transition-colors duration-300"
                                >
                                    <div
                                        class="h-full bg-red-500 dark:bg-red-400 rounded-full transition-all duration-700"
                                        :style="{
                                            width: mortalityBarWidth + '%',
                                        }"
                                    ></div>
                                </div>
                            </div>

                            <!-- Divider + Pakan & FCR summary -->
                            <div
                                class="pt-4 border-t border-slate-100 dark:border-slate-700 transition-colors duration-300"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p
                                            class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider transition-colors duration-300"
                                        >
                                            Total Pakan Digunakan
                                        </p>
                                        <p
                                            class="text-sm font-black text-slate-900 dark:text-slate-100 mt-0.5 tabular-nums transition-colors duration-300"
                                        >
                                            {{
                                                statistik?.total_pakan_kg !=
                                                null
                                                    ? Number(
                                                          statistik.total_pakan_kg,
                                                      ).toLocaleString(
                                                          "id-ID",
                                                      ) + " kg"
                                                    : "—"
                                            }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p
                                            class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider transition-colors duration-300"
                                        >
                                            FCR
                                        </p>
                                        <p
                                            :class="fcrColor.text"
                                            class="text-sm font-black mt-0.5 tabular-nums transition-colors duration-300"
                                        >
                                            {{
                                                statistik?.fcr != null
                                                    ? Number(
                                                          statistik.fcr,
                                                      ).toLocaleString("id-ID")
                                                    : "—"
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Riwayat Panen dalam Siklus Ini -->
                <div
                    class="bg-white dark:bg-slate-800 overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-3xl border border-slate-100 dark:border-slate-700 transition-colors duration-300"
                >
                    <div
                        class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 transition-colors duration-300"
                    >
                        <h3
                            class="text-xs font-black text-slate-900 dark:text-slate-100 uppercase tracking-wider transition-colors duration-300"
                        >
                            Riwayat Panen dalam Siklus Ini
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead
                                class="bg-slate-50/50 dark:bg-slate-700/50 border-b border-slate-100 dark:border-slate-700 transition-colors duration-300"
                            >
                                <tr>
                                    <th
                                        class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest transition-colors"
                                    >
                                        Tanggal
                                    </th>
                                    <th
                                        class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest transition-colors"
                                    >
                                        Jenis
                                    </th>
                                    <th
                                        class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest text-right transition-colors"
                                    >
                                        Jumlah (Ekor)
                                    </th>
                                    <th
                                        class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest text-right transition-colors"
                                    >
                                        Berat Total (Kg)
                                    </th>
                                    <th
                                        class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest text-right transition-colors"
                                    >
                                        Berat Rata-rata (g/ekor)
                                    </th>
                                    <th
                                        class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest transition-colors"
                                    >
                                        Catatan
                                    </th>
                                    <th
                                        class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest transition-colors"
                                    >
                                        Pencatat
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <tr
                                    v-for="p in riwayat_panen"
                                    :key="p.id"
                                    class="border-b border-slate-50 dark:border-slate-700/50 transition duration-200"
                                    :class="
                                        p.id === harvest.id
                                            ? 'bg-emerald-50/60 dark:bg-emerald-500/5'
                                            : 'hover:bg-slate-50/80 dark:hover:bg-slate-700/50'
                                    "
                                >
                                    <td
                                        class="px-6 py-4 font-medium text-slate-500 dark:text-slate-400 whitespace-nowrap transition-colors"
                                    >
                                        <div class="flex items-center gap-2">
                                            {{ formatDate(p.tanggal_panen) }}
                                            <span
                                                v-if="p.id === harvest.id"
                                                class="px-1.5 py-0.5 bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 text-[9px] font-black uppercase tracking-widest rounded-md border border-emerald-200 dark:border-emerald-500/30 transition-colors duration-300"
                                                >ini</span
                                            >
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            :class="
                                                p.jenis_panen === 'Full'
                                                    ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20'
                                                    : 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-500/20'
                                            "
                                            class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border inline-block transition-colors duration-300"
                                            >{{ p.jenis_panen }}</span
                                        >
                                    </td>
                                    <td
                                        class="px-6 py-4 text-right font-bold text-slate-700 dark:text-slate-200 tabular-nums transition-colors"
                                    >
                                        {{ formatNumber(p.jumlah_ikan) }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-right font-bold text-emerald-600 dark:text-emerald-400 tabular-nums transition-colors"
                                    >
                                        {{ formatNumber(p.berat_total_kg) }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-right font-medium text-slate-600 dark:text-slate-300 tabular-nums transition-colors"
                                    >
                                        {{
                                            p.jumlah_ikan > 0 &&
                                            p.berat_total_kg
                                                ? Number(
                                                      (p.berat_total_kg /
                                                          p.jumlah_ikan) *
                                                          1000,
                                                  ).toLocaleString("id-ID", {
                                                      maximumFractionDigits: 0,
                                                  })
                                                : "—"
                                        }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-slate-500 dark:text-slate-400 max-w-[180px] truncate transition-colors"
                                    >
                                        {{ p.catatan || "—" }}
                                    </td>
                                    <td
                                        class="px-6 py-4 font-medium text-slate-700 dark:text-slate-200 whitespace-nowrap transition-colors"
                                    >
                                        {{ p.user?.name ?? "—" }}
                                    </td>
                                </tr>
                                <tr
                                    v-if="
                                        !riwayat_panen ||
                                        riwayat_panen.length === 0
                                    "
                                >
                                    <td
                                        colspan="7"
                                        class="px-6 py-12 text-center text-sm text-slate-400 dark:text-slate-500 transition-colors duration-300"
                                    >
                                        Tidak ada data riwayat panen.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Riwayat Kematian dalam Siklus Ini -->
                <div
                    class="bg-white dark:bg-slate-800 overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-3xl border border-slate-100 dark:border-slate-700 transition-colors duration-300"
                >
                    <div
                        class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 transition-colors duration-300"
                    >
                        <div class="flex items-center justify-between">
                            <h3
                                class="text-xs font-black text-slate-900 dark:text-slate-100 uppercase tracking-wider transition-colors duration-300"
                            >
                                Riwayat Kematian dalam Siklus Ini
                            </h3>
                            <span
                                class="text-xs text-slate-500 dark:text-slate-400 transition-colors duration-300"
                            >
                                Total mati:
                                <span
                                    class="font-bold text-red-600 dark:text-red-400 transition-colors duration-300"
                                    >{{
                                        formatNumber(statistik?.total_mati)
                                    }}
                                    ekor</span
                                >
                            </span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead
                                class="bg-slate-50/50 dark:bg-slate-700/50 border-b border-slate-100 dark:border-slate-700 transition-colors duration-300"
                            >
                                <tr>
                                    <th
                                        class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest transition-colors"
                                    >
                                        Tanggal
                                    </th>
                                    <th
                                        class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest text-right transition-colors"
                                    >
                                        Jumlah Mati
                                    </th>
                                    <th
                                        class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest transition-colors"
                                    >
                                        Catatan / Penyebab
                                    </th>
                                    <th
                                        class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest transition-colors"
                                    >
                                        Dilaporkan Oleh
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <tr
                                    v-for="log in mortality_logs"
                                    :key="log.id"
                                    class="border-b border-slate-50 dark:border-slate-700/50 hover:bg-slate-50/80 dark:hover:bg-slate-700/50 transition duration-200"
                                >
                                    <td
                                        class="px-6 py-4 font-medium text-slate-500 dark:text-slate-400 whitespace-nowrap transition-colors"
                                    >
                                        {{ formatDate(log.tanggal_kematian) }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-right font-bold text-red-600 dark:text-red-400 tabular-nums transition-colors"
                                    >
                                        {{ formatNumber(log.jumlah_mati) }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-slate-500 dark:text-slate-400 transition-colors"
                                    >
                                        {{ log.catatan || "—" }}
                                    </td>
                                    <td
                                        class="px-6 py-4 font-medium text-slate-700 dark:text-slate-200 whitespace-nowrap transition-colors"
                                    >
                                        {{ log.user?.name ?? "—" }}
                                    </td>
                                </tr>
                                <tr
                                    v-if="
                                        !mortality_logs ||
                                        mortality_logs.length === 0
                                    "
                                >
                                    <td
                                        colspan="4"
                                        class="px-6 py-12 text-center text-sm text-slate-400 dark:text-slate-500 transition-colors duration-300"
                                    >
                                        Tidak ada data kematian tercatat dalam
                                        siklus ini.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Catatan Panen — only if catatan is not empty -->
                <div
                    v-if="harvest.catatan"
                    class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 transition-colors duration-300"
                >
                    <h3
                        class="text-xs font-black text-slate-900 dark:text-slate-100 uppercase tracking-wider mb-4 transition-colors duration-300"
                    >
                        Catatan Panen
                    </h3>
                    <p
                        class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed transition-colors duration-300"
                    >
                        {{ harvest.catatan }}
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
