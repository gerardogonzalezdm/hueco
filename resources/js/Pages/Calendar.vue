<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import VueCal from 'vue-cal';
import 'vue-cal/dist/vuecal.css';

const props = defineProps({
    spaces: Array,
    bookings: Array,
});

const view = ref('day'); // 'day' | 'week' | 'month'

// Selección de rango con 2 clicks (funciona en desktop y móvil)
// pendingStart guarda el primer click: { date: Date, spaceId, label }
const pendingStart = ref(null);

const spaceName = (id) => props.spaces.find((s) => s.id === id)?.name ?? '';

const formatHourLabel = (date) =>
    new Date(date).toLocaleString('es-ES', {
        weekday: 'short',
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    });

const cancelRangeSelection = () => {
    pendingStart.value = null;
};

// ESC cancela la selección
const onKeydown = (e) => {
    if (e.key === 'Escape' && pendingStart.value) {
        cancelRangeSelection();
    }
};

onMounted(() => window.addEventListener('keydown', onKeydown));
onUnmounted(() => window.removeEventListener('keydown', onKeydown));

// vue-cal "split days": columnas dentro del día. Una por espacio.
const splits = computed(() =>
    props.spaces.map((space) => ({
        id: space.id,
        label: space.name,
        class: `split-${space.id}`,
    })),
);

// Mapear bookings al formato que espera vue-cal.
const events = computed(() =>
    props.bookings.map((b) => ({
        start: b.start,
        end: b.end,
        title: b.title,
        content: b.space_name,
        class: `event event-${b.status}`,
        split: b.space_id,
        bookingId: b.id,
    })),
);

const handleEventDrop = ({ event, originalEvent }) => {
    const start = new Date(event.start);
    const end = new Date(event.end);
    const duration = Math.round((end - start) / 60000);

    // Si vue-cal cambió la columna (split), interpretarlo como cambio de espacio.
    const newSpaceId = event.split ?? originalEvent.split;

    const pad = (n) => String(n).padStart(2, '0');
    const timeStart = `${start.getFullYear()}-${pad(start.getMonth() + 1)}-${pad(start.getDate())} ${pad(start.getHours())}:${pad(start.getMinutes())}:00`;

    router.patch(
        route('calendar.reschedule', originalEvent.bookingId),
        {
            time_start: timeStart,
            duration_minutes: duration,
            space_id: newSpaceId,
        },
        {
            preserveScroll: true,
            onError: (errors) => {
                const firstError = Object.values(errors)[0];
                alert(firstError || 'No se pudo mover la reserva.');
            },
        },
    );
};

const handleEventClick = (event) => {
    router.visit(route('bookings.edit', event.bookingId));
};

const toQueryDateTime = (date) => {
    const pad = (n) => String(n).padStart(2, '0');
    const d = new Date(date);
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
};

const goToCreateBooking = (start, end, spaceId) => {
    const params = new URLSearchParams();
    params.set('time_start', toQueryDateTime(start));
    if (end) params.set('time_end', toQueryDateTime(end));
    if (spaceId) params.set('space_id', String(spaceId));
    router.visit(route('bookings.create') + '?' + params.toString());
};

const handleCellClick = (payload) => {
    // vue-cal emite { date, split } cuando hay split-days, o solo date sin split.
    const date = payload?.date ?? payload;
    const spaceId = payload?.split ?? null;

    // Si no estamos en vista día, comportamiento clásico (click puntual, 1h default)
    if (view.value !== 'day') {
        pendingStart.value = null;
        goToCreateBooking(date, null, spaceId);
        return;
    }

    // Primer click: guardar como inicio del rango
    if (!pendingStart.value) {
        pendingStart.value = {
            date: new Date(date),
            spaceId,
            label: formatHourLabel(date),
        };
        return;
    }

    // Segundo click
    // Si es un espacio distinto del primer click, descartar y arrancar nuevo
    if (pendingStart.value.spaceId !== spaceId) {
        pendingStart.value = {
            date: new Date(date),
            spaceId,
            label: formatHourLabel(date),
        };
        return;
    }

    // Ordenar para que start <= end (por si el usuario clicó al revés)
    const a = pendingStart.value.date;
    const b = new Date(date);
    let start = a <= b ? a : b;
    let end = a <= b ? b : a;

    // Si son la misma celda, defecto = 1 hora
    if (start.getTime() === end.getTime()) {
        end = new Date(start.getTime() + 60 * 60000);
    }

    pendingStart.value = null;
    goToCreateBooking(start, end, spaceId);
};
</script>

<template>
    <Head title="Calendario" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-hueco-black dark:text-white">
                    Calendario
                </h2>
                <Link
                    :href="route('bookings.create')"
                    class="rounded-full bg-hueco-yellow px-4 py-2 text-sm font-bold text-hueco-black shadow-sm transition hover:bg-yellow-300"
                >
                    + Nueva reserva
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Banner de selección de rango (2 clicks) -->
                <div
                    v-if="pendingStart"
                    class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-hueco-yellow px-5 py-3 text-sm font-semibold text-hueco-black shadow-sm"
                >
                    <div>
                        🎯 Inicio: <span class="font-bold">{{ pendingStart.label }}</span>
                        <span v-if="pendingStart.spaceId" class="ml-1 font-normal">
                            en {{ spaceName(pendingStart.spaceId) }}
                        </span>
                        — toca o haz clic en la hora de fin del rango (misma columna).
                    </div>
                    <button
                        type="button"
                        @click="cancelRangeSelection"
                        class="rounded-full bg-hueco-black px-4 py-1.5 text-xs font-bold text-white hover:bg-gray-800"
                    >
                        Cancelar
                    </button>
                </div>

                <div v-if="spaces.length === 0" class="rounded-2xl bg-white p-12 text-center shadow-sm">
                    <div class="text-5xl">📅</div>
                    <h3 class="mt-4 text-lg font-semibold text-hueco-black">
                        Necesitas crear espacios para usar el calendario
                    </h3>
                    <Link
                        :href="route('spaces.create')"
                        class="mt-6 inline-block rounded-full bg-hueco-yellow px-5 py-2 text-sm font-bold text-hueco-black shadow-sm transition hover:bg-yellow-300"
                    >
                        Crear primer espacio
                    </Link>
                </div>

                <div v-else class="rounded-2xl bg-white p-4 shadow-sm dark:bg-gray-800">
                    <VueCal
                        :events="events"
                        :split-days="view === 'day' ? splits : []"
                        :time-from="7 * 60"
                        :time-to="22 * 60"
                        :time-step="30"
                        :active-view="view"
                        :disable-views="[]"
                        locale="es"
                        small
                        sticky-split-labels
                        hide-weekends
                        :snap-to-time="15"
                        editable-events
                        :on-event-click="handleEventClick"
                        @event-drop="handleEventDrop"
                        @cell-click="handleCellClick"
                        @view-change="(e) => (view = e.view)"
                        :class="['hueco-calendar', { 'selecting-range': pendingStart }]"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
/* Tema Hueco para vue-cal */
.hueco-calendar .vuecal__title-bar {
    background-color: #fcefe6;
    color: #0a0a0a;
    border-radius: 12px 12px 0 0;
}

.hueco-calendar .vuecal__menu {
    background-color: #0a0a0a;
}

.hueco-calendar .vuecal__menu .vuecal__view-btn--active {
    background-color: #ffd500;
    color: #0a0a0a;
    font-weight: bold;
}

.hueco-calendar .vuecal__menu .vuecal__view-btn {
    color: white;
}

.hueco-calendar .vuecal__time-column,
.hueco-calendar .vuecal__weekdays-headings,
.hueco-calendar .vuecal__split-days-headers {
    background-color: #fcefe6;
    color: #0a0a0a;
}

.hueco-calendar .vuecal__cell--today {
    background-color: rgba(255, 213, 0, 0.08);
}

.hueco-calendar .vuecal__cell--current {
    background-color: rgba(0, 184, 165, 0.05);
}

.hueco-calendar .vuecal__cell:hover {
    background-color: rgba(252, 239, 230, 0.6);
    cursor: pointer;
}

/* Modo selección de rango: cursor crosshair y fondo más cálido al hover */
.hueco-calendar.selecting-range .vuecal__cell {
    cursor: crosshair;
}
.hueco-calendar.selecting-range .vuecal__cell:hover {
    background-color: rgba(255, 213, 0, 0.15);
}

.hueco-calendar .vuecal__event {
    background-color: #00b8a5;
    color: white;
    border-radius: 6px;
    border-left: 3px solid #0a0a0a;
    padding: 4px 6px;
    font-size: 12px;
    cursor: grab;
}

.hueco-calendar .vuecal__event.event-confirmed {
    background-color: #3dd183;
}

.hueco-calendar .vuecal__event.event-pending {
    background-color: #ffd500;
    color: #0a0a0a;
}

.hueco-calendar .vuecal__event-title {
    font-weight: 600;
}

.hueco-calendar .vuecal__event-content {
    font-size: 11px;
    opacity: 0.9;
}

.hueco-calendar .vuecal__event-resize-handle {
    background-color: #0a0a0a;
}

.hueco-calendar .vuecal__split-days-headers > .day-split-header {
    background-color: #0a0a0a;
    color: white;
    font-weight: bold;
    padding: 8px;
}
</style>
