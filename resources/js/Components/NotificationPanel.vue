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
                @click="$emit('mark-one', n.id)"
            >
                <div class="fw-semibold small">{{ n.title || 'Notificacion' }}</div>
                <div class="small text-muted">{{ n.message }}</div>
                <div v-if="showTime && typeof formatTime === 'function'" class="xsmall text-muted mt-1">
                    {{ formatTime(n.created_at) }}
                </div>
            </button>
        </div>
    </div>
</template>

<script setup>
defineProps({
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

defineEmits(['close', 'mark-one', 'mark-all']);
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
</style>
