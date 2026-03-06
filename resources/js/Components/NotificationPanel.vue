<template>
    <div v-if="show" class="notify-overlay" @click="$emit('close')"></div>
    <div class="notify-panel" :class="{ show }">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 fw-bold">{{ title }}</h6>
            <div class="d-flex gap-2">
                <button
                    v-if="allowMarkAll"
                    class="btn btn-sm btn-outline-primary"
                    :disabled="unreadCount === 0 || markingAllRead"
                    @click="$emit('mark-all')"
                >
                    <span v-if="markingAllRead" class="spinner-border spinner-border-sm me-1"></span>
                    Marcar todas
                </button>
                <button class="btn btn-sm btn-outline-secondary" @click="$emit('close')">Cerrar</button>
            </div>
        </div>

        <div v-if="notifications.length === 0" class="text-muted text-center py-4">
            {{ emptyText }}
        </div>

        <div v-else class="d-flex flex-column gap-2">
            <button
                v-for="n in notifications"
                :key="n.id"
                class="btn text-start border rounded-3 p-2"
                :class="n.is_read ? 'btn-light' : 'btn-primary-subtle border-primary-subtle'"
                @click="handleNotificationClick(n)"
            >
                <div class="fw-semibold small">{{ n.title || 'Notificacion' }}</div>
                <div class="small text-muted">{{ n.message }}</div>
                <div v-if="showTime && typeof formatTime === 'function'" class="xsmall text-muted mt-1">
                    {{ formatTime(n.created_at) }}
                </div>
            </button>
        </div>

        <div v-if="selectedNotification && show" class="notify-detail-backdrop" @click="closeDetail"></div>
        <div v-if="selectedNotification && show" class="notify-detail-modal">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 fw-bold">Detalle de notificacion</h6>
                <button class="btn btn-sm btn-outline-secondary" @click="closeDetail">Cerrar</button>
            </div>

            <div class="mb-2">
                <div class="fw-semibold">{{ selectedNotification.title || 'Notificacion' }}</div>
                <div class="text-muted small">{{ selectedNotification.message }}</div>
            </div>

            <div v-if="showTime && typeof formatTime === 'function'" class="small text-muted mb-3">
                {{ formatTime(selectedNotification.created_at) }}
            </div>

            <div v-if="detailEntries.length > 0" class="border rounded-3 p-2 bg-light-subtle">
                <div class="small fw-semibold mb-2">Informacion adicional</div>
                <div v-for="entry in detailEntries" :key="entry.key" class="small d-flex justify-content-between gap-2 py-1 border-bottom">
                    <span class="text-muted text-capitalize">{{ formatKey(entry.key) }}</span>
                    <span class="text-end">{{ entry.value }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    notifications: {
        type: Array,
        default: () => [],
    },
    title: {
        type: String,
        default: 'Notificaciones',
    },
    emptyText: {
        type: String,
        default: 'No hay notificaciones',
    },
    allowMarkAll: {
        type: Boolean,
        default: false,
    },
    unreadCount: {
        type: Number,
        default: 0,
    },
    markingAllRead: {
        type: Boolean,
        default: false,
    },
    showTime: {
        type: Boolean,
        default: false,
    },
    formatTime: {
        type: Function,
        default: null,
    },
});

const emit = defineEmits(['close', 'mark-one', 'mark-all']);

const selectedNotification = ref(null);

const detailEntries = computed(() => {
    if (!selectedNotification.value || !selectedNotification.value.data) return [];

    const payload = selectedNotification.value.data;
    if (typeof payload !== 'object') return [];

    return Object.entries(payload)
        .filter(([, value]) => value !== null && value !== undefined && `${value}`.trim() !== '')
        .map(([key, value]) => ({
            key,
            value: Array.isArray(value) ? value.join(', ') : `${value}`,
        }));
});

const formatKey = (key) => {
    return `${key}`.replace(/_/g, ' ');
};

const closeDetail = () => {
    selectedNotification.value = null;
};

const handleNotificationClick = (notification) => {
    emit('mark-one', notification.id);
    selectedNotification.value = notification;
};
</script>

<style scoped>
.notify-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.35);
    z-index: 1050;
}

.notify-panel {
    position: fixed;
    top: 0;
    right: -380px;
    width: 360px;
    max-width: 100%;
    height: 100vh;
    background: #fff;
    box-shadow: -0.5rem 0 1rem rgba(0, 0, 0, 0.2);
    z-index: 1051;
    padding: 1rem;
    transition: right 0.2s ease;
    overflow-y: auto;
}

.notify-panel.show {
    right: 0;
}

.xsmall {
    font-size: 0.72rem;
}

.notify-detail-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.45);
    z-index: 1052;
}

.notify-detail-modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: min(560px, 92vw);
    max-height: 80vh;
    overflow-y: auto;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.25);
    z-index: 1053;
    padding: 1rem;
}
</style>
