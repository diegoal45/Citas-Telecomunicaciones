<template>
    <AppLayout :user="userProfile" page-class="dashboard-container" brand-href="/dashboard">
        <template #nav-actions>
            <NotificationBell :count="unreadNotifications" @click="toggleNotifications" />
        </template>
        <NotificationPanel
            :show="showNotifications"
            :notifications="notifications"
            :show-time="true"
            :format-time="formatNotificationTime"
            @close="showNotifications = false"
            @mark-one="markAsRead"
        />

        <!-- Contenido Principal -->
        <main class="main-content">
            <!-- Welcome Section -->
            <div class="welcome-section">
                <div class="welcome-text">
                    <h1>Hola, {{ userProfile.name || 'Usuario' }} 👋</h1>
                    <p>Bienvenido a tu panel de control</p>
                </div>
                <button class="btn-new-appointment" @click="goToAppointments">
                    <i class="bi bi-plus-circle-fill me-2"></i>
                    Nueva Cita
                </button>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Próximas Citas</span>
                        <span class="stat-value">{{ appointments.length }}</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Completadas</span>
                        <span class="stat-value">0</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="bi bi-clock-fill"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Pendientes</span>
                        <span class="stat-value">0</span>
                    </div>
                </div>
            </div>

            <!-- Main Grid -->
            <div class="content-grid">
                <!-- Appointments Section -->
                <div class="appointments-section">
                    <div class="section-header">
                        <h2>Próximas Citas</h2>
                        <span class="badge-count">{{ appointments.length }}</span>
                    </div>

                    <div v-if="appointments.length === 0" class="empty-state">
                        <i class="bi bi-calendar-x"></i>
                        <h3>No tienes citas agendadas</h3>
                        <p>Agenda tu primera cita para comenzar</p>
                    </div>

                    <div v-else class="appointments-list">
                        <div v-for="apt in appointments" :key="apt.id" class="appointment-card">
                            <div class="apt-status" :class="statusClass(apt.status)"></div>
                            <div class="apt-content">
                                <div class="apt-header">
                                    <h3>{{ apt.appointment_type }}</h3>
                                    <span class="status-badge" :class="statusClass(apt.status)">
                                        {{ apt.status }}
                                    </span>
                                </div>
                                <div class="apt-details">
                                    <div class="apt-detail">
                                        <i class="bi bi-calendar3"></i>
                                        <span>{{ formatDate(apt.scheduled_date) }}</span>
                                    </div>
                                    <div class="apt-detail">
                                        <i class="bi bi-geo-alt-fill"></i>
                                        <span>{{ apt.address }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile & Quotations Sidebar -->
                <div class="sidebar-section">
                    <!-- Profile Card -->
                    <div class="profile-card">
                        <div class="profile-header">
                            <div class="profile-avatar" @click="$refs.photoInput.click()">
                                <img v-if="userProfile.profile_photo_url" :src="userProfile.profile_photo_url" :alt="userProfile.name">
                                <i v-else class="bi bi-person-fill"></i>
                                <div class="avatar-overlay">
                                    <i class="bi bi-camera-fill"></i>
                                </div>
                            </div>
                            <input type="file" ref="photoInput" @change="uploadProfilePhoto" accept="image/*" style="display: none;">
                            <h3>{{ userProfile.name || 'Usuario' }}</h3>
                            <p><i class="bi bi-shield-check-fill me-1"></i>Verificado</p>
                        </div>
                        <div class="profile-info">
                            <div class="info-item">
                                <i class="bi bi-envelope-fill"></i>
                                <div>
                                    <span class="label">Email</span>
                                    <span class="value">{{ userProfile.email || '-' }}</span>
                                </div>
                            </div>
                            <div class="info-item">
                                <i class="bi bi-telephone-fill"></i>
                                <div>
                                    <span class="label">Teléfono</span>
                                    <span class="value">{{ userProfile.phone || '-' }}</span>
                                </div>
                            </div>
                            <div class="info-item">
                                <i class="bi bi-calendar-event-fill"></i>
                                <div>
                                    <span class="label">Miembro desde</span>
                                    <span class="value">Marzo 2026</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quotations Card -->
                    <div class="quotations-card">
                        <h3>Cotizaciones</h3>
                        <div class="empty-state small">
                            <i class="bi bi-file-earmark-text"></i>
                            <p>Sin cotizaciones</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import NotificationBell from '../../Components/NotificationBell.vue';
import NotificationPanel from '../../Components/NotificationPanel.vue';
import api from '../../services/api';

const appointments = ref([]);
const notifications = ref([]);
const userProfile = ref({
    name: 'Usuario',
    email: '',
    phone: '',
    profile_photo_path: null,
    profile_photo_url: null
});
const showNotifications = ref(false);
const loadingAppointments = ref(false);
const loadingProfile = ref(false);
const loadingNotifications = ref(false);

const unreadNotifications = computed(() => {
    return notifications.value.filter(n => !n.is_read).length;
});

const loadUserProfile = async () => {
    loadingProfile.value = true;
    try {
        const response = await api.get('/api/user');
        const userData = response.data.data || response.data;
        
        userProfile.value = {
            ...userData,
            // profile_photo_url ya viene del backend
        };
    } catch (error) {
        console.error('Error cargando perfil:', error);
    } finally {
        loadingProfile.value = false;
    }
};

const loadAppointments = async () => {
    loadingAppointments.value = true;
    try {
        const response = await api.get('/api/appointments');
        appointments.value = response.data.data || response.data || [];
    } catch (error) {
        console.error('Error cargando citas:', error);
        appointments.value = [];
    } finally {
        loadingAppointments.value = false;
    }
};

const loadNotifications = async () => {
    loadingNotifications.value = true;
    try {
        const response = await api.get('/api/notifications');
        notifications.value = response.data.data || response.data || [];
    } catch (error) {
        console.error('Error cargando notificaciones:', error);
        notifications.value = [];
    } finally {
        loadingNotifications.value = false;
    }
};

const markAsRead = async (notificationId) => {
    try {
        await api.put(`/api/notifications/${notificationId}/read`, {});
        
        // Actualizar el estado local
        const notification = notifications.value.find(n => n.id === notificationId);
        if (notification) {
            notification.is_read = true;
        }

        // Cerrar el panel al seleccionar una notificacion
        showNotifications.value = false;
    } catch (error) {
        console.error('Error marcando notificación como leída:', error);
    }
};

const toggleNotifications = () => {
    showNotifications.value = !showNotifications.value;
};

const goToAppointments = () => {
    window.location.href = '/appointments/create';
};

const formatDate = (date) => {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleDateString('es-ES', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit' 
    });
};

const formatNotificationTime = (date) => {
    if (!date) return '';
    const d = new Date(date);
    const now = new Date();
    const diff = now - d;
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);
    
    if (minutes < 1) return 'Ahora';
    if (minutes < 60) return `Hace ${minutes}m`;
    if (hours < 24) return `Hace ${hours}h`;
    if (days < 7) return `Hace ${days}d`;
    return d.toLocaleDateString('es-ES', { month: 'short', day: 'numeric' });
};

const statusClass = (status) => {
    const statusMap = {
        'solicitada': 'status-requested',
        'cotizada': 'status-quoted',
        'confirmada': 'status-confirmed',
        'ejecutada': 'status-completed',
        'cancelada': 'status-cancelled'
    };
    return statusMap[status] || 'status-default';
};

const notificationTypeClass = (type) => {
    const typeMap = {
        'appointment': 'notif-appointment',
        'quotation': 'notif-quotation',
        'system': 'notif-system'
    };
    return typeMap[type] || 'notif-default';
};

const notificationIcon = (type) => {
    const iconMap = {
        'appointment': 'bi bi-calendar-check-fill',
        'quotation': 'bi bi-file-earmark-text-fill',
        'system': 'bi bi-info-circle-fill'
    };
    return iconMap[type] || 'bi bi-bell-fill';
};

const uploadProfilePhoto = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('photo', file);

    try {
        const response = await api.post('/api/profile/photo', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            }
        });

        // Actualizar la foto en el perfil
        userProfile.value.profile_photo_path = response.data.profile_photo_path;
        userProfile.value.profile_photo_url = response.data.profile_photo_url;
    } catch (error) {
        console.error('Error subiendo foto:', error);
        alert('Error al subir la foto. Por favor intenta de nuevo.');
    }
};

onMounted(() => {
    loadUserProfile();
    loadAppointments();
    loadNotifications();
    
    // Recargar notificaciones cada 30 segundos
    setInterval(loadNotifications, 30000);
});
</script>

<style scoped>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.dashboard-container {
    min-height: 100vh;
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    background-attachment: fixed;
}

/* Navbar Moderno */
.modern-navbar {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    padding: 1rem 2rem;
    box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 0;
    z-index: 1001;
}

.nav-content {
    max-width: 1400px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.nav-brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.brand-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
}

.brand-text {
    font-size: 1.5rem;
    font-weight: 700;
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.nav-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.user-menu {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: #f3f4f6;
    padding: 0.5rem 1rem;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s;
}

.user-menu:hover {
    background: #e5e7eb;
}

.user-menu-wrapper {
    position: relative;
}

.user-dropdown {
    position: absolute;
    top: calc(100% + 0.5rem);
    right: 0;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    min-width: 200px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s;
    z-index: 1000;
}

.user-dropdown.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: #1f2937;
    text-decoration: none;
    transition: all 0.2s;
    font-size: 0.9rem;
}

.dropdown-item:first-child {
    border-radius: 12px 12px 0 0;
}

.dropdown-item:last-child {
    border-radius: 0 0 12px 12px;
}

.dropdown-item:hover {
    background: #f3f4f6;
}

.dropdown-item.text-danger {
    color: #dc3545;
}

.dropdown-item.text-danger:hover {
    background: #fee2e2;
}

.dropdown-item i {
    width: 20px;
    font-size: 1.1rem;
}

.dropdown-divider {
    height: 1px;
    background: #e5e7eb;
    margin: 0.5rem 0;
}

.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    overflow: hidden;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-name {
    font-weight: 600;
    color: #1f2937;
    font-size: 0.9rem;
}

/* Contenido Principal */
.main-content {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
}

/* Welcome Section */
.welcome-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.welcome-text h1 {
    font-size: 2rem;
    font-weight: 700;
    color: white;
    margin: 0 0 0.25rem 0;
}

.welcome-text p {
    color: rgba(255, 255, 255, 0.8);
    margin: 0;
    font-size: 1rem;
}

.btn-new-appointment {
    background: white;
    color: #0d6efd;
    border: none;
    padding: 0.875rem 1.75rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    display: flex;
    align-items: center;
}

.btn-new-appointment:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 16px;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    transition: all 0.3s;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    color: white;
    flex-shrink: 0;
}

.stat-icon.blue {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
}

.stat-icon.green {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.stat-icon.orange {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.stat-icon.purple {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.stat-info {
    display: flex;
    flex-direction: column;
}

.stat-label {
    color: #6b7280;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.stat-value {
    color: #1f2937;
    font-size: 1.75rem;
    font-weight: 700;
}

/* Content Grid */
.content-grid {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 2rem;
}

/* Appointments Section */
.appointments-section {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.section-header h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.badge-count {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    color: white;
    padding: 0.375rem 0.875rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.875rem;
}

.appointments-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.appointment-card {
    display: flex;
    gap: 1rem;
    padding: 1.25rem;
    background: #f9fafb;
    border-radius: 12px;
    transition: all 0.3s;
    border-left: 4px solid transparent;
}

.appointment-card:hover {
    background: #f3f4f6;
    transform: translateX(4px);
}

.apt-status {
    width: 4px;
    border-radius: 2px;
    flex-shrink: 0;
}

.apt-status.status-requested {
    background: #3b82f6;
}

.apt-status.status-quoted {
    background: #06b6d4;
}

.apt-status.status-confirmed {
    background: #10b981;
}

.apt-status.status-completed {
    background: #059669;
}

.apt-status.status-cancelled {
    background: #ef4444;
}

.apt-content {
    flex: 1;
}

.apt-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 0.75rem;
    gap: 1rem;
}

.apt-header h3 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
    text-transform: capitalize;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: capitalize;
    white-space: nowrap;
}

.status-badge.status-requested {
    background: #dbeafe;
    color: #1e40af;
}

.status-badge.status-quoted {
    background: #cffafe;
    color: #155e75;
}

.status-badge.status-confirmed {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.status-completed {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.status-cancelled {
    background: #fee2e2;
    color: #991b1b;
}

.apt-details {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.apt-detail {
    display: flex;
    align-items: start;
    gap: 0.5rem;
    color: #6b7280;
    font-size: 0.875rem;
}

.apt-detail i {
    margin-top: 2px;
    flex-shrink: 0;
}

/* Sidebar Section */
.sidebar-section {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Profile Card */
.profile-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.profile-header {
    text-align: center;
    margin-bottom: 1.5rem;
}

.profile-avatar {
    width: 100px;
    height: 100px;
    border-radius: 20px;
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    color: white;
    font-size: 3rem;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(13, 110, 253, 0.4);
}

.profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s;
    color: white;
    font-size: 1.5rem;
}

.profile-avatar:hover .avatar-overlay {
    opacity: 1;
}

.profile-header h3 {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 0.25rem 0;
}

.profile-header p {
    color: #10b981;
    font-size: 0.875rem;
    margin: 0;
    font-weight: 500;
}

.profile-info {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.info-item {
    display: flex;
    align-items: start;
    gap: 1rem;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 12px;
}

.info-item i {
    color: #0d6efd;
    font-size: 1.25rem;
    margin-top: 2px;
}

.info-item div {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    flex: 1;
}

.info-item .label {
    color: #6b7280;
    font-size: 0.75rem;
    font-weight: 500;
}

.info-item .value {
    color: #1f2937;
    font-size: 0.875rem;
    font-weight: 600;
    word-break: break-word;
}

/* Quotations Card */
.quotations-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.quotations-card h3 {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 1rem 0;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #9ca3af;
}

.empty-state i {
    font-size: 4rem;
    opacity: 0.3;
    margin-bottom: 1rem;
}

.empty-state h3 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #6b7280;
    margin: 0 0 0.5rem 0;
}

.empty-state p {
    font-size: 0.875rem;
    margin: 0;
}

.empty-state.small {
    padding: 2rem 1rem;
}

.empty-state.small i {
    font-size: 3rem;
}

.empty-state.small p {
    margin-top: 0.5rem;
}

/* Responsive */
@media (max-width: 1024px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
    
    .sidebar-section {
        grid-template-columns: 1fr 1fr;
        display: grid;
    }
}

@media (max-width: 768px) {
    .main-content {
        padding: 1rem;
    }
    
    .welcome-section {
        flex-direction: column;
        align-items: start;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .user-name {
        display: none;
    }
    
    .sidebar-section {
        grid-template-columns: 1fr;
    }
    
    .apt-header {
        flex-direction: column;
        align-items: start;
    }
}

@media (max-width: 480px) {
    .welcome-text h1 {
        font-size: 1.5rem;
    }
    
    .btn-new-appointment {
        width: 100%;
        justify-content: center;
    }
    
    .nav-content {
        padding: 0 1rem;
    }
    
    .brand-text {
        font-size: 1.25rem;
    }
}
</style>
