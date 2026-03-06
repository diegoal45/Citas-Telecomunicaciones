<template>
    <AppLayout :user="userProfile" page-class="dashboard-container" brand-href="/dashboard">
        <template #nav-actions>
            <NotificationBell :count="unreadNotifications" @click="toggleNotifications" />
        </template>
        <NotificationPanel
            :show="showNotifications"
            :notifications="notifications"
            :allow-mark-all="true"
            :unread-count="unreadNotifications"
            :marking-all-read="markingAllRead"
            :show-time="true"
            :format-time="formatNotificationTime"
            @close="showNotifications = false"
            @mark-one="markAsRead"
            @mark-all="markAllAsRead"
        />

        <!-- Contenido Principal -->
        <main class="main-content">
            <!-- Welcome Section -->
            <div class="welcome-section">
                <div class="welcome-text">
                    <h1 class="fw-bold mb-0">Hola, {{ userProfile.name || 'Usuario' }} 👋</h1>
                    <p class="text-muted mb-0">Gestiona tus citas de telecomunicaciones</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 stats-card stats-card-primary">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted small mb-1">Citas Activas</div>
                                    <h2 class="mb-0 fw-bold" style="color: #0d6efd;">{{ activeAppointments.length }}</h2>
                                </div>
                                <div class="stats-icon stats-icon-primary">
                                    <i class="bi bi-calendar-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 stats-card stats-card-success">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted small mb-1">Completadas</div>
                                    <h2 class="mb-0 fw-bold" style="color: #198754;">{{ completedAppointments.length }}</h2>
                                </div>
                                <div class="stats-icon stats-icon-success">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 stats-card stats-card-warning">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted small mb-1">Canceladas</div>
                                    <h2 class="mb-0 fw-bold" style="color: #6c757d;">{{ cancelledAppointments.length }}</h2>
                                </div>
                                <div class="stats-icon stats-icon-warning">
                                    <i class="bi bi-x-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="row g-3">
                <!-- Appointments Section -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow h-100">
                        <div class="card-header bg-gradient-primary text-white border-0 py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-semibold">
                                    <i class="bi bi-calendar3 me-2"></i>Mis Citas
                                </h5>
                                <button class="btn btn-light btn-sm" @click="goToAppointments">
                                    <i class="bi bi-plus-circle me-1"></i>Nueva Cita
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <!-- Filters -->
                            <div class="mb-3">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button 
                                        type="button" 
                                        class="btn"
                                        :class="appointmentFilter === 'activas' ? 'btn-primary' : 'btn-outline-primary'"
                                        @click="appointmentFilter = 'activas'"
                                    >
                                        Activas ({{ activeAppointments.length }})
                                    </button>
                                    <button 
                                        type="button" 
                                        class="btn"
                                        :class="appointmentFilter === 'completadas' ? 'btn-success' : 'btn-outline-success'"
                                        @click="appointmentFilter = 'completadas'"
                                    >
                                        Completadas ({{ completedAppointments.length }})
                                    </button>
                                    <button 
                                        type="button" 
                                        class="btn"
                                        :class="appointmentFilter === 'canceladas' ? 'btn-outline-secondary' : 'btn-outline-secondary'"
                                        @click="appointmentFilter = 'canceladas'"
                                    >
                                        Canceladas ({{ cancelledAppointments.length }})
                                    </button>
                                </div>
                            </div>
                            <div v-if="filteredAppointments.length === 0" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x" style="font-size: 3rem; opacity: 0.2;"></i>
                                <h6 class="mt-3 mb-1">No hay citas {{ appointmentFilter === 'activas' ? 'activas' : appointmentFilter }}</h6>
                                <p v-if="appointmentFilter === 'activas'" class="small mb-0">Agenda tu primera cita para comenzar</p>
                            </div>

                            <div v-else class="appointments-list">
                                <div v-for="apt in filteredAppointments" :key="apt.id" class="appointment-item">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-0 fw-semibold text-capitalize">{{ apt.appointment_type }}</h6>
                                        <span class="badge rounded-pill" :class="'bg-' + statusColorClass(apt.status)">
                                            {{ formatStatus(apt.status) }}
                                        </span>
                                    </div>
                                    <div class="appointment-details">
                                        <div class="detail-item">
                                            <i class="bi bi-calendar3 text-muted"></i>
                                            <span>{{ formatDate(apt.scheduled_date) }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <i class="bi bi-geo-alt text-muted"></i>
                                            <span>{{ apt.address }}</span>
                                        </div>
                                        <div v-if="apt.team" class="detail-item">
                                            <i class="bi bi-people text-muted"></i>
                                            <span>{{ apt.team.name }}</span>
                                        </div>
                                    </div>
                                    <div class="mt-2" v-if="!['cancelada', 'ejecutada'].includes(apt.status)">
                                        <button
                                            class="btn btn-sm btn-outline-danger"
                                            :disabled="cancellingAppointments[apt.id]"
                                            @click="showCancelConfirm(apt)"
                                        >
                                            <span v-if="cancellingAppointments[apt.id]" class="spinner-border spinner-border-sm me-1"></span>
                                            <i v-else class="bi bi-x-circle me-1"></i>
                                            Cancelar
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Paginación -->
                            <div v-if="totalPages > 1" class="mt-3">
                                <nav>
                                    <ul class="pagination pagination-sm justify-content-center mb-0">
                                        <li class="page-item" :class="{ disabled: currentPage === 1 }">
                                            <button class="page-link" @click="currentPage = Math.max(1, currentPage - 1)">Anterior</button>
                                        </li>
                                        <li v-for="page in totalPages" :key="page" class="page-item" :class="{ active: currentPage === page }">
                                            <button class="page-link" @click="currentPage = page">{{ page }}</button>
                                        </li>
                                        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                                            <button class="page-link" @click="currentPage = Math.min(totalPages, currentPage + 1)">Siguiente</button>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Quotations Card -->
                    <div class="card border-0 shadow mb-3">
                        <div class="card-header bg-gradient-warning text-dark border-0 py-3">
                            <h6 class="mb-0 fw-semibold">
                                <i class="bi bi-file-earmark-text me-2"></i>Cotizaciones
                                <span v-if="quotationsPendingApproval.length > 0" class="badge bg-white text-dark ms-2">{{ quotationsPendingApproval.length }}</span>
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            <div v-if="quotationsPendingApproval.length === 0" class="text-center py-4 text-muted">
                                <i class="bi bi-file-earmark-text" style="font-size: 2.5rem; opacity: 0.2;"></i>
                                <p class="mb-0 mt-2 small">Sin cotizaciones pendientes</p>
                            </div>
                            <div v-else class="d-flex flex-column gap-2">
                                <div v-for="apt in quotationsPendingApproval" :key="`q-${apt.id}`" class="quotation-item" @click="openQuotationModal(apt)">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <strong class="small">{{ apt.team?.name || 'Equipo' }}</strong>
                                        <span class="badge bg-info">Cotizada</span>
                                    </div>
                                    <div class="small text-muted">
                                        <i class="bi bi-calendar3 me-1"></i>{{ formatDate(apt.scheduled_date) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Card -->
                    <div class="card border-0 shadow">
                        <div class="card-header bg-gradient-success text-white border-0 py-3">
                            <h6 class="mb-0 fw-semibold">
                                <i class="bi bi-person-circle me-2"></i>Mi Perfil
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="text-center mb-3">
                                <div class="profile-photo-wrapper" @click="$refs.photoInput.click()">
                                    <div class="profile-photo">
                                        <img v-if="userProfile.profile_photo_url" :src="userProfile.profile_photo_url" :alt="userProfile.name">
                                        <i v-else class="bi bi-person-fill"></i>
                                    </div>
                                    <div class="profile-photo-edit">
                                        <i class="bi bi-camera-fill"></i>
                                    </div>
                                </div>
                                <input type="file" ref="photoInput" @change="uploadProfilePhoto" accept="image/*" style="display: none;">
                                <h6 class="mt-3 mb-0">{{ userProfile.name || 'Usuario' }}</h6>
                                <small class="text-success"><i class="bi bi-shield-check-fill me-1"></i>Verificado</small>
                            </div>
                            <div class="profile-info">
                                <div class="profile-info-item">
                                    <i class="bi bi-envelope"></i>
                                    <div>
                                        <div class="label">Email</div>
                                        <div class="value">{{ userProfile.email || '-' }}</div>
                                    </div>
                                </div>
                                <div class="profile-info-item">
                                    <i class="bi bi-telephone"></i>
                                    <div>
                                        <div class="label">Teléfono</div>
                                        <div class="value">{{ userProfile.phone || '-' }}</div>
                                    </div>
                                </div>
                                <div class="profile-info-item">
                                    <i class="bi bi-calendar-event"></i>
                                    <div>
                                        <div class="label">Miembro desde</div>
                                        <div class="value">Marzo 2026</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<!-- Quotation Details Modal -->
            <div v-if="showQuotationModal" style="position: fixed; inset: 0; background: rgba(0, 0, 0, 0.5); z-index: 1060; display: flex; align-items: center; justify-content: center;" @click="closeQuotationModal">
                <div v-if="selectedQuotation" class="card" style="width: 100%; max-width: 450px; margin: auto;" @click.stop>
                    <!-- Modal Header -->
                    <div class="bg-primary text-white p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-0 fw-bold">Revisión de Cotización</h6>
                            </div>
                            <button type="button" class="btn-close btn-close-white" style="background: none; border: none; font-size: 1.3rem; cursor: pointer;" @click="closeQuotationModal">×</button>
                        </div>
                    </div>
                    
                    <!-- Modal Body -->
                    <div class="card-body p-3">
                        <!-- Appointment Info -->
                        <div class="mb-2 pb-2 border-bottom">
                            <h6 class="fw-bold mb-2" style="font-size: 0.9rem;">Info de la Cita</h6>
                            <div class="row g-2" style="font-size: 0.85rem;">
                                <div class="col-6">
                                    <small class="text-muted">Equipo</small>
                                    <div class="fw-semibold">{{ selectedQuotation.team?.name || 'Sin equipo' }}</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Dirección</small>
                                    <div class="fw-semibold small">{{ selectedQuotation.address || '—' }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Date and Time -->
                        <div class="mb-2 pb-2 border-bottom">
                            <h6 class="fw-bold mb-2" style="font-size: 0.9rem;">Programación</h6>
                            <div style="font-size: 0.85rem;">
                                <small class="text-muted">Fecha y Hora</small>
                                <div class="fw-semibold">
                                    {{ new Date(selectedQuotation.scheduled_date).toLocaleDateString('es-ES', { weekday: 'short', month: 'short', day: 'numeric' }) }}
                                    {{ selectedQuotation.scheduled_date?.split('T')[1]?.substring(0, 5) || '—' }}
                                    <span v-if="selectedQuotation.quotation?.labor_hours" class="text-muted" style="font-size: 0.75rem;">
                                        - {{ String(parseInt(selectedQuotation.scheduled_date?.split('T')[1]?.split(':')[0] || 0) + parseInt(selectedQuotation.quotation?.labor_hours || 0)).padStart(2, '0') }}:00
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Quotation Details -->
                        <div class="mb-2 pb-2 border-bottom">
                            <h6 class="fw-bold mb-2" style="font-size: 0.9rem;">Detalles</h6>
                            <div style="font-size: 0.85rem;">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <small class="text-muted">Horas</small>
                                        <div class="fw-bold">{{ selectedQuotation.quotation?.labor_hours || '0' }} H</div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Personal</small>
                                        <div class="fw-bold">{{ selectedQuotation.quotation?.required_staff || '0' }} p</div>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">Materiales</small>
                                    <div class="fw-semibold small">{{ selectedQuotation.quotation?.materials || '(Sin especificar)' }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Price Section -->
                        <div class="mb-2">
                            <h6 class="fw-bold mb-2" style="font-size: 0.9rem;">Precio</h6>
                            <div class="fw-bold text-success" style="font-size: 1.3rem;">
                                COP {{ selectedQuotation.quotation?.price ? new Intl.NumberFormat('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(Math.round(selectedQuotation.quotation.price)) : '0' }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="card-footer bg-light d-flex gap-2 justify-content-end p-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" @click="closeQuotationModal">
                            Cancelar
                        </button>
                        <button
                            type="button"
                            class="btn btn-sm btn-danger"
                            :disabled="quotationActionLoading[selectedQuotation.quotation?.id]"
                            @click="rejectAndClose"
                        >
                            <span v-if="quotationActionLoading[selectedQuotation.quotation?.id]" class="spinner-border spinner-border-sm me-1"></span>
                            Rechazar
                        </button>
                        <button
                            type="button"
                            class="btn btn-sm btn-success"
                            :disabled="quotationActionLoading[selectedQuotation.quotation?.id]"
                            @click="approveAndClose"
                        >
                            <span v-if="quotationActionLoading[selectedQuotation.quotation?.id]" class="spinner-border spinner-border-sm me-1"></span>
                            Aprobar
                        </button>
                    </div>
                </div>
            </div>

                </main>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import NotificationBell from '../../Components/NotificationBell.vue';
import NotificationPanel from '../../Components/NotificationPanel.vue';
import { useNotifications } from '../../composables/useNotifications';
import api from '../../services/api';

const appointments = ref([]);
const userProfile = ref({
    name: 'Usuario',
    email: '',
    phone: '',
    profile_photo_path: null,
    profile_photo_url: null
});
const loadingAppointments = ref(false);
const loadingProfile = ref(false);
const quotationActionLoading = ref({});
const cancellingAppointments = ref({});
const showQuotationModal = ref(false);
const selectedQuotation = ref(null);
const appointmentFilter = ref('activas'); // 'activas', 'completadas', 'canceladas', 'todas'
const currentPage = ref(1);
const itemsPerPage = 3;

const {
    notifications,
    showNotifications,
    unreadNotifications,
    markingAllRead,
    loadNotifications,
    toggleNotifications,
    markAsRead,
    markAllAsRead,
    startPolling,
} = useNotifications({
    autoCloseOnMark: false,
    onError: (message, err) => console.error(message, err),
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

const goToAppointments = () => {
    window.location.href = '/appointments/create';
};

const activeAppointments = computed(() => {
    return appointments.value.filter((apt) => !['cancelada', 'ejecutada'].includes(apt.status));
});

const completedAppointments = computed(() => {
    return appointments.value.filter((apt) => apt.status === 'ejecutada');
});

const cancelledAppointments = computed(() => {
    return appointments.value.filter((apt) => apt.status === 'cancelada');
});

const allFilteredAppointments = computed(() => {
    if (appointmentFilter.value === 'activas') return activeAppointments.value;
    if (appointmentFilter.value === 'completadas') return completedAppointments.value;
    if (appointmentFilter.value === 'canceladas') return cancelledAppointments.value;
    return appointments.value; // todas
});

const filteredAppointments = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return allFilteredAppointments.value.slice(start, end);
});

const totalPages = computed(() => {
    return Math.ceil(allFilteredAppointments.value.length / itemsPerPage);
});

// Reset a página 1 cuando cambia el filtro
watch(appointmentFilter, () => {
    currentPage.value = 1;
});

const quotationsPendingApproval = computed(() => {
    return appointments.value.filter((apt) => (
        ['cotizada', 'para_ejecucion'].includes(apt.status) && 
        apt.quotation && 
        apt.quotation.price !== null && 
        apt.quotation.price !== undefined &&
        !apt.quotation.approved_at // Solo mostrar si no ha sido aprobada aún
    ));
});

const approveQuotation = async (quotationId) => {
    if (!quotationId) return;

    quotationActionLoading.value[quotationId] = true;
    try {
        await api.post(`/api/quotations/${quotationId}/approve`);
        await loadAppointments();
        await loadNotifications();
    } catch (error) {
        alert(error?.response?.data?.message || 'No se pudo aprobar la cotización.');
    } finally {
        quotationActionLoading.value[quotationId] = false;
    }
};

const rejectQuotation = async (quotationId) => {
    if (!quotationId) return;

    quotationActionLoading.value[quotationId] = true;
    try {
        await api.post(`/api/quotations/${quotationId}/reject`);
        await loadAppointments();
        await loadNotifications();
    } catch (error) {
        alert(error?.response?.data?.message || 'No se pudo rechazar la cotización.');
    } finally {
        quotationActionLoading.value[quotationId] = false;
    }
};

let appointmentToCancel = null;

const showCancelConfirm = (appointment) => {
    appointmentToCancel = appointment;
    const confirmed = confirm(`¿Estás seguro de que deseas cancelar la cita del ${new Date(appointment.scheduled_date).toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}?`);
    if (confirmed) {
        cancelAppointment(appointment.id);
    }
};

const cancelAppointment = async (appointmentId) => {
    if (!appointmentId) return;

    cancellingAppointments.value[appointmentId] = true;
    try {
        await api.post(`/api/appointments/${appointmentId}/cancel`, {
            reason: 'Cancelada por el cliente'
        });
        await loadAppointments();
        await loadNotifications();
    } catch (error) {
        alert(error?.response?.data?.message || 'No se pudo cancelar la cita.');
    } finally {
        cancellingAppointments.value[appointmentId] = false;
    }
};

const openQuotationModal = (appointment) => {
    selectedQuotation.value = appointment;
    showQuotationModal.value = true;
};

const closeQuotationModal = () => {
    showQuotationModal.value = false;
    selectedQuotation.value = null;
};

const approveAndClose = async () => {
    if (!selectedQuotation.value?.quotation?.id) return;
    await approveQuotation(selectedQuotation.value.quotation.id);
    closeQuotationModal();
};

const rejectAndClose = async () => {
    if (!selectedQuotation.value?.quotation?.id) return;
    await rejectQuotation(selectedQuotation.value.quotation.id);
    closeQuotationModal();
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

const statusColorClass = (status) => {
    const colorMap = {
        'solicitada': 'primary',
        'pendiente_aprobacion_admin': 'primary',
        'cotizada': 'info',
        'para_ejecucion': 'success',
        'confirmada': 'success',
        'ejecutada': 'success',
        'cancelada': 'secondary'
    };
    return colorMap[status] || 'secondary';
};

const formatStatus = (status) => {
    const statusMap = {
        'solicitada': 'Solicitada',
        'pendiente_aprobacion_admin': 'Pendiente',
        'cotizada': 'Cotizada',
        'para_ejecucion': 'Para Ejecución',
        'confirmada': 'Confirmada',
        'ejecutada': 'Completada',
        'cancelada': 'Cancelada'
    };
    return statusMap[status] || status;
};

const formatCurrency = (price) => {
    if (!price) return 'COP 0';
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(price);
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
    startPolling(30000);
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
    background: #f8f9fa;
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

/* Main Content */
.main-content {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
}

/* Welcome Section */
.welcome-section {
    margin-bottom: 2rem;
}

.welcome-text h1 {
    font-size: 2rem;
    color: #1a1a1a;
}

.welcome-text p {
    color: #6c757d;
    font-size: 1rem;
}

/* Stats Cards - Professional Style */
.stats-card {
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stats-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.1) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.stats-card:hover::before {
    opacity: 1;
}

.stats-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.15) !important;
}

.stats-card-primary {
    background: linear-gradient(135deg, #ffffff 0%, #f0f5ff 100%);
    border-left: 4px solid #0d6efd;
}

.stats-card-success {
    background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
    border-left: 4px solid #198754;
}

.stats-card-warning {
    background: linear-gradient(135deg, #ffffff 0%, #fafafa 100%);
    border-left: 4px solid #6c757d;
}

.stats-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.stats-card:hover .stats-icon {
    transform: scale(1.1) rotate(5deg);
}

.stats-icon-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    color: white;
}

.stats-icon-success {
    background: linear-gradient(135deg, #198754 0%, #146c43 100%);
    color: white;
}

.stats-icon-warning {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
}

/* Card Headers with Gradients */
.bg-gradient-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #198754 0%, #146c43 100%);
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
}

/* Appointments List */
.appointments-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.appointment-item {
    padding: 1rem;
    background: #ffffff;
    border-radius: 10px;
    border: 1px solid #e9ecef;
    border-left: 4px solid #0d6efd;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.appointment-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(180deg, #0d6efd 0%, #0a58ca 100%);
    transition: width 0.3s ease;
}

.appointment-item:hover {
    border-color: #0d6efd;
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
    transform: translateX(4px);
}

.appointment-item:hover::before {
    width: 6px;
}

.appointment-details {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #6c757d;
}

.detail-item i {
    width: 16px;
    font-size: 0.875rem;
}

/* Quotations */
.quotation-item {
    padding: 1rem;
    background: linear-gradient(135deg, #ffffff 0%, #fffbf0 100%);
    border-radius: 8px;
    border: 1px solid #ffe69c;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.quotation-item::after {
    content: '';
    position: absolute;
    top: 50%;
    right: 1rem;
    transform: translateY(-50%);
    width: 8px;
    height: 8px;
    background: #ffc107;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
        transform: translateY(-50%) scale(1);
    }
    50% {
        opacity: 0.5;
        transform: translateY(-50%) scale(1.2);
    }
}

.quotation-item:hover {
    background: linear-gradient(135deg, #fffbf0 0%, #fff3cd 100%);
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.2);
    transform: translateY(-2px);
}

/* Profile */
.profile-photo-wrapper {
    display: inline-block;
    position: relative;
    cursor: pointer;
}

.profile-photo {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    color: white;
    font-size: 2.5rem;
    box-shadow: 0 8px 16px rgba(13, 110, 253, 0.3);
    transition: all 0.3s ease;
    border: 4px solid #ffffff;
}

.profile-photo-wrapper:hover .profile-photo {
    transform: scale(1.05);
    box-shadow: 0 12px 24px rgba(13, 110, 253, 0.4);
}

.profile-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-photo-edit {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    border: 3px solid white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.875rem;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.profile-photo-wrapper:hover .profile-photo-edit {
    transform: rotate(15deg) scale(1.1);
    background: linear-gradient(135deg, #0a58ca 0%, #084298 100%);
}

.profile-info {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.profile-info-item {
    display: flex;
    align-items: start;
    gap: 1rem;
    padding: 1rem;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 8px;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.profile-info-item:hover {
    border-color: #0d6efd;
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.1);
    transform: translateX(4px);
}

.profile-info-item i {
    color: #0d6efd;
    font-size: 1.25rem;
    margin-top: 2px;
}

.profile-info-item .label {
    font-size: 0.75rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 500;
}

.profile-info-item .value {
    font-size: 0.875rem;
    font-weight: 600;
    color: #212529;
}

/* Responsive */
@media (max-width: 768px) {
    .modern-navbar {
        padding: 1rem;
    }
    
    .nav-brand {
        flex: 1;
    }
    
    .brand-text {
        font-size: 1.25rem;
    }
    
    .user-name {
        display: none;
    }
    
    .main-content {
        padding: 1rem;
    }
}
</style>
