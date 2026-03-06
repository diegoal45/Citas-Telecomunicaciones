<template>
    <button class="notification-bell" type="button" :title="title" @click="$emit('click')">
        <i class="bi bi-bell-fill"></i>
        <span v-if="count > 0" class="notification-badge">{{ count }}</span>
    </button>
</template>

<script setup>
defineProps({
    count: {
        type: Number,
        default: 0,
    },
    title: {
        type: String,
        default: 'Notificaciones',
    },
});

defineEmits(['click']);
</script>

<style scoped>
.notification-bell {
    position: relative;
    background: rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.35);
    color: #fff;
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 1rem;
}

.notification-bell:hover {
    background: rgba(255, 255, 255, 0.28);
    transform: translateY(-1px);
}

.notification-badge {
    position: absolute;
    top: -6px;
    right: -8px;
    background: #dc3545;
    color: #fff;
    font-size: 0.65rem;
    font-weight: 700;
    min-width: 18px;
    height: 18px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 5px;
    border: 2px solid #fff;
}
</style>
