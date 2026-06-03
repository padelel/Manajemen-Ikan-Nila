<script setup>
import { computed } from "vue";
import { Line } from "vue-chartjs";
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler,
} from "chart.js";

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler,
);

const props = defineProps({
    chartBerat: { type: Object, required: true },
    events: { type: Object, default: () => ({}) },
});

// ─── Warna per tipe event ───────────────────────────────────────────────────
const EVENT_COLORS = {
    panen_full: "#10b981",
    panen_parsial: "#f59e0b",
    kematian: "#ef4444",
    tebar: "#3b82f6",
};

function primaryType(eventList) {
    if (!eventList?.length) return null;
    for (const t of ["panen_full", "panen_parsial", "kematian", "tebar"]) {
        if (eventList.some((e) => e.type === t)) return t;
    }
    return null;
}

// ─── Custom plugin: garis vertikal putus-putus + badge label ────────────────
const eventLinePlugin = {
    id: "growthEventLine",
    afterDatasetsDraw(chart, _args, pluginOpts) {
        const evts = pluginOpts?.events;
        if (!evts || !Object.keys(evts).length) return;
        const { ctx, chartArea, scales } = chart;
        if (!chartArea) return;

        Object.entries(evts).forEach(([lbl, list]) => {
            const idx = chart.data.labels.indexOf(lbl);
            if (idx === -1) return;

            const x = scales.x.getPixelForValue(idx);
            const type = primaryType(list);
            const hex = EVENT_COLORS[type] ?? "#6366f1";

            ctx.save();

            // Garis vertikal putus-putus
            ctx.beginPath();
            ctx.setLineDash([5, 4]);
            ctx.strokeStyle = hex + "b3";
            ctx.lineWidth = 1.5;
            ctx.moveTo(x, chartArea.top);
            ctx.lineTo(x, chartArea.bottom);
            ctx.stroke();
            ctx.setLineDash([]);

            // Badge di area padding atas
            const badgeText =
                type === "panen_full"
                    ? "PANEN FULL"
                    : type === "panen_parsial"
                      ? "PANEN PARSIAL"
                      : type === "kematian"
                        ? "KEMATIAN"
                        : "TEBAR";

            ctx.font = "bold 9px system-ui, sans-serif";
            const tw = ctx.measureText(badgeText).width;
            const bw = tw + 10;
            const bh = 15;
            const bx =
                x + bw / 2 + 2 > chartArea.right
                    ? chartArea.right - bw - 2
                    : x - bw / 2;
            const by = chartArea.top - bh - 4;

            ctx.fillStyle = hex + "22";
            ctx.fillRect(bx, by, bw, bh);
            ctx.strokeStyle = hex + "55";
            ctx.lineWidth = 0.8;
            ctx.strokeRect(bx, by, bw, bh);
            ctx.fillStyle = hex;
            ctx.textAlign = "center";
            ctx.textBaseline = "middle";
            ctx.fillText(badgeText, bx + bw / 2, by + bh / 2);

            ctx.restore();
        });
    },
};

// ─── Data dengan warna per titik berdasarkan event ──────────────────────────
const computedChartData = computed(() => {
    if (!props.chartBerat?.datasets?.length) return props.chartBerat;

    const labels = props.chartBerat.labels ?? [];
    const evts = props.events ?? {};

    const pointBg = labels.map((lbl) => {
        const t = primaryType(evts[lbl]);
        return t ? EVENT_COLORS[t] : "#4f46e5";
    });

    const pointR = labels.map((lbl) => ((evts[lbl] ?? []).length > 0 ? 8 : 2));

    const pointBorder = labels.map((lbl) =>
        (evts[lbl] ?? []).length > 0 ? "#fff" : "transparent",
    );

    return {
        ...props.chartBerat,
        datasets: props.chartBerat.datasets.map((ds) => ({
            ...ds,
            pointBackgroundColor: pointBg,
            pointRadius: pointR,
            pointBorderColor: pointBorder,
            pointBorderWidth: 2,
            pointHoverRadius: 10,
            spanGaps: false,
        })),
    };
});

// ─── Opsi chart reaktif ──────────────────────────────────────────────────────
const chartOptions = computed(() => {
    const evts = props.events ?? {};

    return {
        responsive: true,
        maintainAspectRatio: false,
        layout: { padding: { top: 28 } },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: "#0f172a",
                titleFont: { size: 12, weight: "bold" },
                bodyFont: { size: 13 },
                padding: 14,
                cornerRadius: 10,
                callbacks: {
                    title: (items) => items[0]?.label ?? "",
                    label: (ctx) => {
                        const val = ctx.parsed.y;
                        if (val === null || val === undefined) {
                            return "  Berat: — (tidak ada data)";
                        }
                        return `  Berat rata-rata: ${val.toLocaleString("id-ID")} gram`;
                    },
                    afterBody: (items) => {
                        const lbl = items[0]?.label;
                        const list = evts[lbl] ?? [];
                        if (!list.length) return [];
                        return [
                            "",
                            "  Kejadian:",
                            ...list.map((e) => `    \u2192 ${e.text}`),
                        ];
                    },
                },
            },
            growthEventLine: { events: evts },
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: "rgba(148, 163, 184, 0.2)" },
                ticks: {
                    color: "#94a3b8",
                    font: { size: 11 },
                    callback: (v) => v.toLocaleString("id-ID") + " g",
                },
            },
            x: {
                grid: { display: false },
                ticks: { color: "#94a3b8", font: { size: 11 } },
            },
        },
        elements: {
            line: { tension: 0.4 },
            point: { radius: 2, hoverRadius: 6 },
        },
        interaction: { mode: "index", intersect: false },
    };
});

// ─── Timeline kejadian ────────────────────────────────────────────────────────
const eventTimeline = computed(() => {
    const labels = props.chartBerat?.labels ?? [];
    const result = [];
    labels.forEach((lbl) => {
        (props.events?.[lbl] ?? []).forEach((evt) => {
            result.push({ label: lbl, ...evt });
        });
    });
    return result;
});

const hasEvents = computed(() => eventTimeline.value.length > 0);

const eventTimelineBadge = {
    panen_full:
        "bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20",
    panen_parsial:
        "bg-amber-50   dark:bg-amber-500/10   text-amber-700   dark:text-amber-400   border-amber-200   dark:border-amber-500/20",
    kematian:
        "bg-red-50     dark:bg-red-500/10     text-red-700     dark:text-red-400     border-red-200     dark:border-red-500/20",
    tebar: "bg-blue-50    dark:bg-blue-500/10    text-blue-700    dark:text-blue-400    border-blue-200    dark:border-blue-500/20",
};

const eventTimelineIcon = {
    panen_full: "M5 13l4 4L19 7",
    panen_parsial: "M5 13l4 4L19 7",
    kematian: "M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
    tebar: "M12 4v16m8-8H4",
};
</script>

<template>
    <div
        class="bg-white dark:bg-slate-800 p-8 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_10px_40px_rgb(0,0,0,0.2)] z-0 relative transition-colors duration-300"
    >
        <!-- Header -->
        <div
            class="flex flex-col md:flex-row md:justify-between md:items-start mb-6 gap-4"
        >
            <div>
                <h3
                    class="text-lg font-bold text-slate-900 dark:text-slate-100 transition-colors"
                >
                    Kurva Pertumbuhan Ikan
                </h3>
                <span
                    class="inline-block mt-1 px-3 py-1 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[10px] font-bold rounded-lg uppercase tracking-wider border border-emerald-100 dark:border-emerald-500/20 transition-colors"
                    >Berdasarkan Siklus Berjalan</span
                >
            </div>

            <div class="flex flex-wrap gap-x-4 gap-y-2 justify-end max-w-lg">
                <div
                    v-for="(dataset, idx) in chartBerat?.datasets"
                    :key="idx"
                    class="flex items-center gap-2 bg-slate-50 dark:bg-slate-700/50 px-3 py-1.5 rounded-lg border border-slate-100 dark:border-slate-600 transition-colors"
                >
                    <span
                        class="w-2.5 h-2.5 rounded-full shadow-sm"
                        :style="{ backgroundColor: dataset.borderColor }"
                    ></span>
                    <span
                        class="text-xs font-bold text-slate-700 dark:text-slate-300 transition-colors"
                        >{{ dataset.label }}</span
                    >
                </div>
                <!-- Legend event jika ada -->
                <template v-if="hasEvents">
                    <div
                        v-if="
                            eventTimeline.some((e) => e.type === 'panen_full')
                        "
                        class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20 transition-colors"
                    >
                        <span
                            class="w-2 h-2 rounded-full bg-emerald-500"
                        ></span>
                        <span class="text-[10px] font-bold">Panen Full</span>
                    </div>
                    <div
                        v-if="
                            eventTimeline.some(
                                (e) => e.type === 'panen_parsial',
                            )
                        "
                        class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-500/20 transition-colors"
                    >
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span class="text-[10px] font-bold">Panen Parsial</span>
                    </div>
                    <div
                        v-if="eventTimeline.some((e) => e.type === 'kematian')"
                        class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 border-red-200 dark:border-red-500/20 transition-colors"
                    >
                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                        <span class="text-[10px] font-bold">Kematian</span>
                    </div>
                    <div
                        v-if="eventTimeline.some((e) => e.type === 'tebar')"
                        class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-500/20 transition-colors"
                    >
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        <span class="text-[10px] font-bold">Penebaran</span>
                    </div>
                </template>
            </div>
        </div>

        <!-- Grafik -->
        <div class="relative h-80 w-full">
            <Line
                v-if="computedChartData && computedChartData.datasets"
                :data="computedChartData"
                :options="chartOptions"
                :plugins="[eventLinePlugin]"
            />
            <div
                v-else
                class="absolute inset-0 flex items-center justify-center text-slate-400 dark:text-slate-500 text-sm font-medium transition-colors"
            >
                Memuat data pertumbuhan...
            </div>
        </div>

        <!-- Timeline kejadian di bawah grafik -->
        <div
            v-if="hasEvents"
            class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-700 space-y-2.5"
        >
            <p
                class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-3"
            >
                Kejadian dalam Periode Ini
            </p>
            <div
                v-for="(event, i) in eventTimeline"
                :key="i"
                class="flex items-center gap-3"
            >
                <span
                    class="text-[10px] font-bold text-slate-400 dark:text-slate-500 whitespace-nowrap min-w-[34px]"
                    >{{ event.label }}</span
                >
                <span
                    :class="
                        eventTimelineBadge[event.type] ??
                        'bg-slate-50 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600'
                    "
                    class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg border transition-colors leading-snug"
                >
                    <svg
                        class="w-3.5 h-3.5 flex-shrink-0"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2.5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            :d="
                                eventTimelineIcon[event.type] ??
                                'M13 16h-1v-4h-1m1-4h.01'
                            "
                        />
                    </svg>
                    {{ event.text }}
                </span>
            </div>
        </div>
    </div>
</template>
