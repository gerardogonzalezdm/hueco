<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import VueCal from 'vue-cal';
import 'vue-cal/dist/vuecal.css';

const props = defineProps({
    spaces: Array,
    bookings: Array,
});

const view = ref('day'); // 'day' | 'week' | 'month'

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

const handleCellClick = (date) => {
    // Click en celda vacía → ir al formulario de nueva reserva con la fecha pre-rellenada
    const pad = (n) => String(n).padStart(2, '0');
    const d = new Date(date);
    const iso = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
    router.visit(route('bookings.create') + `?time_start=${iso}`);
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
                        class="hueco-calendar"
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
