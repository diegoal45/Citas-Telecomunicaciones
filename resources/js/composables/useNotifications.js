import { computed, onUnmounted, ref } from 'vue';
import api from '../services/api';

export function useNotifications(options = {}) {
    const {
        onError = null,
        autoCloseOnMark = false,
    } = options;

    const notifications = ref([]);
    const showNotifications = ref(false);
    const loadingNotifications = ref(false);
    const markingAllRead = ref(false);
    const pollerId = ref(null);

    const unreadNotifications = computed(() => {
        return notifications.value.filter((n) => !n.is_read).length;
    });

    const handleError = (message, err) => {
        if (typeof onError === 'function') {
            onError(message, err);
        } else {
            console.error(message, err);
        }
    };

    const loadNotifications = async () => {
        loadingNotifications.value = true;
        try {
            const response = await api.get('/api/notifications');
            notifications.value = response.data?.data || response.data || [];
        } catch (err) {
            handleError('No se pudieron cargar las notificaciones.', err);
        } finally {
            loadingNotifications.value = false;
        }
    };

    const toggleNotifications = () => {
        showNotifications.value = !showNotifications.value;
    };

    const closeNotifications = () => {
        showNotifications.value = false;
    };

    const markAsRead = async (notificationId) => {
        try {
            await api.put(`/api/notifications/${notificationId}/read`);

            const targetId = Number(notificationId);
            notifications.value = notifications.value.map((n) =>
                Number(n.id) === targetId ? { ...n, is_read: true } : n
            );

            await loadNotifications();

            if (autoCloseOnMark) {
                closeNotifications();
            }
        } catch (err) {
            handleError('No se pudo marcar la notificacion como leida.', err);
        }
    };

    const markAllAsRead = async () => {
        if (unreadNotifications.value === 0) return;

        markingAllRead.value = true;
        try {
            await api.put('/api/notifications/read-all');
            notifications.value = notifications.value.map((n) => ({ ...n, is_read: true }));
            await loadNotifications();
        } catch (err) {
            handleError('No se pudieron marcar todas las notificaciones como leidas.', err);
        } finally {
            markingAllRead.value = false;
        }
    };

    const startPolling = (intervalMs = 30000) => {
        stopPolling();
        pollerId.value = setInterval(() => {
            loadNotifications();
        }, intervalMs);
    };

    const stopPolling = () => {
        if (pollerId.value) {
            clearInterval(pollerId.value);
            pollerId.value = null;
        }
    };

    onUnmounted(() => {
        stopPolling();
    });

    return {
        notifications,
        showNotifications,
        loadingNotifications,
        markingAllRead,
        unreadNotifications,
        loadNotifications,
        toggleNotifications,
        closeNotifications,
        markAsRead,
        markAllAsRead,
        startPolling,
        stopPolling,
    };
}
