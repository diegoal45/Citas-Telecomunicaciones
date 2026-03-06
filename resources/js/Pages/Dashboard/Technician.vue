<template>
    <AppLayout brand-href="/technician/dashboard" brand-label="Panel Técnico" brand-icon="bi bi-wrench-adjustable" page-class="dashboard-container">
        <template #nav-actions>
            <NotificationBell :count="unreadNotifications" @click="toggleNotifications" />
        </template>

        <main class="container-fluid px-3 px-md-4 py-4">
            <div class="welcome-section mb-4">
                <div>
                    <h3 class="mb-1 fw-bold" style="color: #1a1a1a;">Panel del Técnico</h3>
                    <p class="text-muted mb-0">Mis citas y calendario de trabajo</p>
                </div>
            </div>

            <div v-if="error" class="alert alert-danger mb-3">{{ error }}</div>

            <!-- ESTADÍSTICAS -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 stats-card stats-card-primary">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted small mb-1">Citas Activas</div>
                                    <h2 class="mb-0 fw-bold" style="color: #0d6efd;">{{ stats.pending }}</h2>
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
                                    <h2 class="mb-0 fw-bold" style="color: #198754;">{{ stats.thisWeek }}</h2>
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
                                    <h2 class="mb-0 fw-bold" style="color: #6c757d;">{{ stats.completed }}</h2>
                                </div>
                                <div class="stats-icon stats-icon-warning">
                                    <i class="bi bi-x-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CALENDARIO + CITAS DEL DÍA -->
            <div class="row g-3 mb-3">
                <div class="col-12 col-lg-8 col-xl-8">
                    <div class="card border-0 shadow h-100">
                        <div class="card-header bg-gradient-primary text-white border-0 py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-semibold">
                                    <i class="bi bi-calendar3 me-2"></i>Calendario
                                </h6>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-light btn-sm" @click="previousMonth">
                                        <i class="bi bi-chevron-left"></i>
                                    </button>
                                    <button class="btn btn-light btn-sm" @click="goToToday">Hoy</button>
                                    <button class="btn btn-light btn-sm" @click="nextMonth">
                                        <i class="bi bi-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <h5 class="text-center mb-3">{{ currentMonthName }} {{ currentYear }}</h5>

                            <div class="calendar-grid">
                                <div class="calendar-day-header" v-for="day in weekDays" :key="day">{{ day }}</div>
                                <div
                                    v-for="(day, index) in calendarDays"
                                    :key="index"
                                    class="calendar-day"
                                    :class="{
                                        'other-month': !day.isCurrentMonth,
                                        'today': day.isToday,
                                        'has-appointments': day.appointmentCount > 0,
                                        'selected': selectedDate && day.date === selectedDate
                                    }"
                                    @click="selectDay(day)"
                                >
                                    <div class="day-number">{{ day.dayNumber }}</div>
                                    <div v-if="day.appointmentCount > 0" class="appointment-badge">
                                        {{ day.appointmentCount }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4 col-xl-4">
                    <div class="card border-0 shadow h-100">
                        <div class="card-header bg-gradient-primary text-white border-0 py-3">
                            <h6 class="mb-0 fw-semibold">
                                <i class="bi bi-calendar-day me-2"></i>
                                {{ selectedDate ? formatSelectedDate : 'Citas del Día' }}
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            <div v-if="!selectedDate" class="text-muted text-center py-4">
                                Selecciona un día en el calendario para ver sus citas.
                            </div>

                            <div v-else-if="selectedDayAppointments.length === 0" class="text-muted text-center py-4">
                                No hay citas para este día.
                            </div>

                            <div v-else class="list-group">
                                <button
                                    v-for="apt in selectedDayAppointments"
                                    :key="apt.id"
                                    type="button"
                                    class="list-group-item list-group-item-action"
                                    @click="viewAppointmentDetail(apt)"
                                >
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="fw-semibold">{{ apt.client?.name }}</div>
                                            <div class="text-muted small">
                                                {{ formatTime(apt.scheduled_date) }} - {{ capitalizeFirst(apt.appointment_type) }}
                                            </div>
                                        </div>
                                        <span class="badge" :class="statusBadgeClass(apt.status)">{{ formatStatus(apt.status) }}</span>
                                    </div>
                                    <div class="text-muted mt-1" style="font-size: 0.75rem;">
                                        Equipo: {{ apt.team?.name || 'Sin equipo' }}
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PRÓXIMAS CITAS + INFORMACIÓN DEL EQUIPO -->
            <div class="row g-3 mt-1">
                <div class="col-12 col-lg-6">
                    <div class="card border-0 shadow h-100">
                        <div class="card-header bg-gradient-warning text-white border-0 py-3">
                            <h6 class="mb-0 fw-semibold">
                                <i class="bi bi-list-check me-2"></i>Próximas Citas
                            </h6>
                        </div>
                        <div class="card-body p-2" style="max-height: 320px; overflow-y: auto;">
                            <div v-if="upcomingAppointments.length === 0" class="text-center text-muted py-3 small">
                                No hay citas próximas
                            </div>
                            <div v-else class="d-flex flex-column gap-2">
                                <div
                                    v-for="apt in upcomingAppointments.slice(0, 8)"
                                    :key="apt.id"
                                    class="upcoming-item p-2 rounded"
                                    @click="viewAppointmentDetail(apt)"
                                >
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold small">{{ apt.client?.name }}</div>
                                            <div class="text-muted" style="font-size: 0.75rem;">
                                                {{ formatDateTime(apt.scheduled_date) }}
                                            </div>
                                            <span class="badge mt-1" :class="statusBadgeClass(apt.status)" style="font-size: 0.7rem;">
                                                {{ formatStatus(apt.status) }}
                                            </span>
                                            <div class="text-muted mt-1" style="font-size: 0.72rem;">
                                                Equipo: {{ apt.team?.name || 'Sin equipo' }}
                                            </div>
                                        </div>
                                        <i class="bi bi-chevron-right text-muted"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="card border-0 shadow h-100">
                        <div class="card-header bg-gradient-success text-white border-0 py-3">
                            <h6 class="mb-0 fw-semibold">
                                <i class="bi bi-people me-2"></i>Información del Equipo
                            </h6>
                        </div>
                        <div class="card-body">
                            <div v-if="!primaryTeam" class="text-muted text-center py-3 small">
                                No tienes equipo asignado.
                            </div>
                            <div v-else>
                                <div class="mb-3 pb-2 border-bottom">
                                    <div class="small text-muted">Equipo</div>
                                    <div class="fw-semibold">{{ primaryTeam.name }}</div>
                                </div>

                                <div class="mb-3 pb-2 border-bottom">
                                    <div class="small text-muted">Líder del Equipo</div>
                                    <div class="fw-semibold">{{ primaryTeam.leader?.name || 'Sin líder' }}</div>
                                </div>

                                <div>
                                    <div class="small text-muted mb-2">Miembros</div>
                                    <ul class="list-group list-group-flush">
                                        <li
                                            v-for="member in primaryTeamMembers"
                                            :key="member.id"
                                            class="list-group-item px-0 d-flex justify-content-between align-items-center"
                                        >
                                            <span>{{ member.name }}</span>
                                            <small class="text-muted">{{ member.role?.name || 'Técnico' }}</small>
                                        </li>
                                        <li v-if="primaryTeamMembers.length === 0" class="list-group-item px-0 text-muted small">
                                            Sin miembros registrados.
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

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

        <!-- MODAL DE DETALLE -->
        <div v-if="selectedAppointment" class="modal d-block" style="background-color: rgba(0, 0, 0, 0.5);" @click="closeDetail">
            <div class="modal-dialog modal-lg" @click.stop>
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detalle de Cita #{{ selectedAppointment.id }}</h5>
                        <button type="button" class="btn-close" @click="closeDetail"></button>
                    </div>
                    <div class="modal-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Cliente</span>
                                <span class="fw-semibold">{{ selectedAppointment.client?.name || '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Email</span>
                                <span>{{ selectedAppointment.client?.email || '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Teléfono</span>
                                <span>{{ selectedAppointment.client?.phone || '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Tipo</span>
                                <span>{{ capitalizeFirst(selectedAppointment.appointment_type) }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Estado</span>
                                <span>{{ formatStatus(selectedAppointment.status) }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Equipo</span>
                                <span>{{ selectedAppointment.team?.name || 'Sin equipo' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Fecha y Hora</span>
                                <span>{{ formatDateTime(selectedAppointment.scheduled_date) }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Dirección</span>
                                <span class="text-end ms-3">{{ selectedAppointment.address || '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Descripción</span>
                                <span class="text-end ms-3">{{ selectedAppointment.description || '-' }}</span>
                            </li>
                            <li v-if="selectedAppointment.quotation" class="list-group-item">
                                <div class="text-muted mb-1">Cotización</div>
                                <div class="small">
                                    <strong>Precio:</strong> ${{ selectedAppointment.quotation.total_price || 0 }}<br>
                                    <strong>Descripción:</strong> {{ selectedAppointment.quotation.description || '-' }}
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" @click="closeDetail">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import NotificationBell from '../../Components/NotificationBell.vue';
import NotificationPanel from '../../Components/NotificationPanel.vue';
import { useNotifications } from '../../composables/useNotifications';
import api from '../../services/api';

const teams = ref([]);
const appointments = ref([]);
const loading = ref(false);
const error = ref(null);

const currentMonth = ref(new Date().getMonth());
const currentYear = ref(new Date().getFullYear());
const selectedDate = ref(null);
const selectedAppointment = ref(null);

const weekDays = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

const {
    notifications,
    unreadNotifications,
    markingAllRead,
    loadNotifications,
    toggleNotifications,
    showNotifications,
    markAsRead,
    markAllAsRead,
    startPolling,
    stopPolling,
} = useNotifications({ autoCloseOnMark: false });

const stats = computed(() => {
    const now = new Date();
    const endOfWeek = new Date(now);
    endOfWeek.setDate(now.getDate() + 7);

    const pending = appointments.value.filter(a => 
        !['ejecutada', 'cancelada'].includes(a.status)
    ).length;

    const thisWeek = appointments.value.filter(a => {
        const aptDate = new Date(a.scheduled_date);
        return aptDate >= now && aptDate <= endOfWeek && !['ejecutada', 'cancelada'].includes(a.status);
    }).length;

    const completed = appointments.value.filter(a => a.status === 'ejecutada').length;

    return { pending, thisWeek, completed };
});

const currentMonthName = computed(() => {
    return new Date(currentYear.value, currentMonth.value).toLocaleDateString('es-ES', { month: 'long' });
});

const calendarDays = computed(() => {
    const firstDay = new Date(currentYear.value, currentMonth.value, 1);
    const lastDay = new Date(currentYear.value, currentMonth.value + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startDayOfWeek = firstDay.getDay();

    const days = [];
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    // Días del mes anterior
    const prevMonthLastDay = new Date(currentYear.value, currentMonth.value, 0).getDate();
    for (let i = startDayOfWeek - 1; i >= 0; i--) {
        const dayNum = prevMonthLastDay - i;
        const date = new Date(currentYear.value, currentMonth.value - 1, dayNum);
        days.push({
            dayNumber: dayNum,
            date: formatDateKey(date),
            isCurrentMonth: false,
            isToday: false,
            appointmentCount: getAppointmentCountForDate(date)
        });
    }

    // Días del mes actual
    for (let day = 1; day <= daysInMonth; day++) {
        const date = new Date(currentYear.value, currentMonth.value, day);
        date.setHours(0, 0, 0, 0);
        const isToday = date.getTime() === today.getTime();
        
        days.push({
            dayNumber: day,
            date: formatDateKey(date),
            isCurrentMonth: true,
            isToday,
            appointmentCount: getAppointmentCountForDate(date)
        });
    }

    // Días del siguiente mes
    const remainingDays = 42 - days.length; // 6 semanas completas
    for (let day = 1; day <= remainingDays; day++) {
        const date = new Date(currentYear.value, currentMonth.value + 1, day);
        days.push({
            dayNumber: day,
            date: formatDateKey(date),
            isCurrentMonth: false,
            isToday: false,
            appointmentCount: getAppointmentCountForDate(date)
        });
    }

    return days;
});

const upcomingAppointments = computed(() => {
    const now = new Date();
    return appointments.value
        .filter(a => {
            const aptDate = new Date(a.scheduled_date);
            return aptDate >= now && !['ejecutada', 'cancelada'].includes(a.status);
        })
        .sort((a, b) => new Date(a.scheduled_date) - new Date(b.scheduled_date));
});

const primaryTeam = computed(() => {
    return teams.value[0] || null;
});

const primaryTeamMembers = computed(() => {
    if (!primaryTeam.value?.members) return [];
    return primaryTeam.value.members;
});

const selectedDayAppointments = computed(() => {
    if (!selectedDate.value) return [];
    
    return appointments.value.filter(a => {
        const aptDate = new Date(a.scheduled_date);
        return formatDateKey(aptDate) === selectedDate.value;
    }).sort((a, b) => new Date(a.scheduled_date) - new Date(b.scheduled_date));
});

const formatSelectedDate = computed(() => {
    if (!selectedDate.value) return '';
    const [year, month, day] = selectedDate.value.split('-');
    const date = new Date(year, month - 1, day);
    return date.toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
});

const formatDateKey = (date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const getAppointmentCountForDate = (date) => {
    const dateKey = formatDateKey(date);
    return appointments.value.filter(a => {
        const aptDate = new Date(a.scheduled_date);
        return formatDateKey(aptDate) === dateKey;
    }).length;
};

const previousMonth = () => {
    currentMonth.value--;
    if (currentMonth.value < 0) {
        currentMonth.value = 11;
        currentYear.value--;
    }
};

const nextMonth = () => {
    currentMonth.value++;
    if (currentMonth.value > 11) {
        currentMonth.value = 0;
        currentYear.value++;
    }
};

const goToToday = () => {
    const today = new Date();
    currentMonth.value = today.getMonth();
    currentYear.value = today.getFullYear();
    selectedDate.value = formatDateKey(today);
};

const selectDay = (day) => {
    if (day.isCurrentMonth) {
        selectedDate.value = day.date;
    }
};

const viewAppointmentDetail = (apt) => {
    selectedAppointment.value = apt;
};

const closeDetail = () => {
    selectedAppointment.value = null;
};

const formatDateTime = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatTime = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatStatus = (status) => {
    const statusMap = {
        'solicitada': 'Solicitada',
        'pendiente_cotizacion': 'Pendiente Cotización',
        'pendiente_aprobacion_admin': 'Pendiente Aprobación',
        'cotizada': 'Cotizada',
        'aprobada': 'Aprobada',
        'para_ejecucion': 'Para Ejecución',
        'programada': 'Programada',
        'ejecutada': 'Ejecutada',
        'cancelada': 'Cancelada'
    };
    return statusMap[status] || status;
};

const capitalizeFirst = (str) => {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1).replace(/_/g, ' ');
};

const statusBadgeClass = (status) => {
    const map = {
        'solicitada': 'text-bg-primary',
        'pendiente_cotizacion': 'text-bg-info',
        'cotizada': 'text-bg-info',
        'aprobada': 'text-bg-success',
        'para_ejecucion': 'text-bg-warning',
        'programada': 'text-bg-warning',
        'ejecutada': 'text-bg-success',
        'cancelada': 'text-bg-secondary'
    };
    return map[status] || 'text-bg-secondary';
};

const typeColorClass = (type) => {
    const map = {
        'instalacion': 'text-bg-primary',
        'reparacion': 'text-bg-warning',
        'mantenimiento': 'text-bg-info'
    };
    return map[type] || 'text-bg-secondary';
};

const truncate = (text, length) => {
    if (!text) return '-';
    return text.length > length ? text.substring(0, length) + '...' : text;
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

const loadData = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get('/api/technician/dashboard');
        const data = response.data;
        
        teams.value = data.teams || [];
        appointments.value = data.appointments || [];

        await loadNotifications();
    } catch (err) {
        error.value = err?.response?.data?.message || err.message || 'Error al cargar los datos';
        console.error(err);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadData();
    startPolling(30000);
    
    // Seleccionar el día de hoy por defecto
    const today = new Date();
    selectedDate.value = formatDateKey(today);
});
</script>

<style scoped>
.dashboard-container {
    min-height: 100vh;
    background: #f8f9fa;
}

.welcome-section h3 {
    font-size: 1.75rem;
}

/* Stats Cards */
.stats-card {
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    cursor: default;
    background: white;
}

.stats-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
    pointer-events: none;
}

.stats-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15) !important;
}

.stats-card-primary {
    background: linear-gradient(135deg, #f0f5ff 0%, #ffffff 100%);
    border-left: 4px solid #0d6efd;
}

.stats-card-warning {
    background: linear-gradient(135deg, #fffbf0 0%, #ffffff 100%);
    border-left: 4px solid #ffc107;
}

.stats-card-success {
    background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
    border-left: 4px solid #198754;
}

.stats-card-info {
    background: linear-gradient(135deg, #f0f9ff 0%, #ffffff 100%);
    border-left: 4px solid #0dcaf0;
}

.stats-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    transition: all 0.3s ease;
}

.stats-card:hover .stats-icon {
    transform: rotate(5deg) scale(1.05);
}

.stats-icon-primary {
    background: linear-gradient(135deg, #0d6efd, #0a58ca);
    color: white;
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
}

.stats-icon-warning {
    background: linear-gradient(135deg, #ffc107, #ffb300);
    color: white;
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
}

.stats-icon-warning-yellow {
    background: linear-gradient(135deg, #ffc107, #ffb300);
    color: white;
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
}

.stats-icon-success {
    background: linear-gradient(135deg, #198754, #146c43);
    color: white;
    box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3);
}

.stats-icon-info {
    background: linear-gradient(135deg, #0dcaf0, #0aa2c0);
    color: white;
    box-shadow: 0 4px 12px rgba(13, 202, 240, 0.3);
}

/* Gradientes de cabeceras */
.bg-gradient-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #198754 0%, #146c43 100%);
}

/* Calendario */
.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
}

.calendar-day-header {
    text-align: center;
    font-weight: 600;
    font-size: 0.65rem;
    color: #6c757d;
    padding: 2px;
}

.calendar-day {
    position: relative;
    aspect-ratio: 1;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    padding: 1px;
    cursor: pointer;
    transition: all 0.2s ease;
    background: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 32px;
}

.calendar-day:hover {
    background: #f8f9fa;
    border-color: #0d6efd;
    transform: scale(1.05);
}

.calendar-day.other-month {
    opacity: 0.3;
    cursor: default;
}

.calendar-day.other-month:hover {
    background: white;
    border-color: #e9ecef;
    transform: none;
}

.calendar-day.today {
    background: linear-gradient(135deg, #e7f1ff 0%, #ffffff 100%);
    border: 2px solid #0d6efd;
    font-weight: bold;
}

.calendar-day.has-appointments {
    background: linear-gradient(135deg, #fff3cd 0%, #ffffff 100%);
    border-color: #ffc107;
}

.calendar-day.selected {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    color: white;
    border-color: #0d6efd;
}

.calendar-day.selected .day-number {
    color: white;
}

.calendar-day.selected .appointment-badge {
    background: white;
    color: #0d6efd;
}

.day-number {
    font-size: 0.65rem;
    font-weight: 500;
}

.appointment-badge {
    position: absolute;
    bottom: 0px;
    right: 0px;
    background: #ffc107;
    color: white;
    border-radius: 50%;
    width: 14px;
    height: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.55rem;
    font-weight: bold;
}

/* Próximas Citas */
.upcoming-item {
    border: 1px solid #e9ecef;
    cursor: pointer;
    transition: all 0.2s ease;
    background: white;
}

.upcoming-item:hover {
    background: #f8f9fa;
    border-color: #0d6efd;
    transform: translateX(4px);
}
</style>
