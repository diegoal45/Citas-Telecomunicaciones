<template>
    <AppLayout brand-href="/tecnico/dashboard" brand-label="Panel Tecnico Lider" brand-icon="bi bi-tools">
        <template #nav-actions>
            <NotificationBell :count="unreadNotifications" @click="toggleNotifications" />
        </template>

        <main class="container-fluid px-3 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                <div>
                    <h3 class="mb-1">Panel del Tecnico Lider</h3>
                    <p class="text-muted mb-0">Gestión de citas, cotizaciones y equipo</p>
                </div>
                <button class="btn btn-primary" @click="loadData" :disabled="loading">
                    <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                    {{ loading ? 'Actualizando...' : 'Actualizar datos' }}
                </button>
            </div>

            <div v-if="error" class="alert alert-danger mb-3">{{ error }}</div>

            <!-- ESTADÍSTICAS -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <small class="text-muted">Citas Totales</small>
                            <h4 class="mb-0 fw-bold text-primary">{{ stats.totalAppointments }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <small class="text-muted">Pendientes de Cotizar</small>
                            <h4 class="mb-0 fw-bold text-warning">{{ stats.pendingQuotation }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <small class="text-muted">Programadas para Ejecutar</small>
                            <h4 class="mb-0 fw-bold text-info">{{ stats.scheduled }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <small class="text-muted">Miembros del Equipo</small>
                            <h4 class="mb-0 fw-bold text-success">{{ stats.teamMembers }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- EQUIPOS Y COTIZACIONES PENDIENTES -->
            <div class="row g-3 mb-3">
                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 pb-0">
                            <h6 class="mb-0 fw-bold">Mis Equipos</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item px-0" v-for="team in myTeams" :key="team.id">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-semibold">{{ team.name }}</span>
                                        <span class="badge text-bg-primary">{{ getTeamActiveCitasCount(team.id) }} citas</span>
                                    </div>
                                    <small class="text-muted">
                                        Miembros: {{ team.members?.length || 0 }}
                                    </small>
                                </li>
                                <li v-if="myTeams.length === 0" class="text-muted small">Sin equipos</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 pb-0">
                            <h6 class="mb-0 fw-bold">Cotizaciones Pendientes</h6>
                        </div>
                        <div class="card-body">
                            <div v-if="pendingQuotations.length > 0" class="list-group list-group-flush">
                                <div class="list-group-item px-0 py-2" v-for="apt in pendingQuotations" :key="apt.id">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-semibold small">{{ apt.client?.name }}</span>
                                        <span class="badge text-bg-warning small">{{ apt.status }}</span>
                                    </div>
                                    <small class="text-muted">
                                        {{ formatDate(apt.scheduled_date) }}
                                    </small>
                                </div>
                            </div>
                            <div v-else class="text-muted small text-center py-3">
                                <i class="bi bi-check-circle me-1"></i> Sin cotizaciones pendientes
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 pb-0">
                            <h6 class="mb-0 fw-bold">Estado de Citas</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between px-0" v-for="item in appointmentsByStatus" :key="item.status">
                                    <span class="text-capitalize small">{{ formatStatus(item.status) }}</span>
                                    <span class="fw-bold">{{ item.total }}</span>
                                </li>
                                <li v-if="appointmentsByStatus.length === 0" class="text-muted small">Sin datos</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CITAS DEL EQUIPO -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h6 class="mb-0 fw-bold">Citas de Mis Equipos</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <select class="form-select form-select-sm" style="min-width: 180px;" v-model="statusFilter">
                                <option value="">Citas Activas</option>
                                <option value="solicitada">Solicitada</option>
                                <option value="pendiente_cotizacion">Pendiente de Cotización</option>
                                <option value="cotizada">Cotizada</option>
                                <option value="aprobada">Aprobada</option>
                                <option value="para_ejecucion">Para Ejecución</option>
                                <option value="programada">Programada</option>
                                <option value="ejecutada">Ejecutada</option>
                                <option value="cancelada">Cancelada</option>
                                <option value="historial">Historial (Canceladas y Ejecutadas)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th>Fecha Programada</th>
                                <th>Equipo</th>
                                <th>Cotización</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="apt in filteredAppointments" :key="apt.id">
                                <td>{{ apt.client?.name }}</td>
                                <td>
                                    <span class="badge" :class="typeColorClass(apt.appointment_type)">
                                        {{ capitalizeFirst(apt.appointment_type) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge" :class="statusBadgeClass(apt.status)">{{ formatStatus(apt.status) }}</span>
                                </td>
                                <td>{{ formatDate(apt.scheduled_date) }}</td>
                                <td>{{ apt.team?.name }}</td>
                                <td>
                                    <span v-if="apt.quotation" class="badge text-bg-info">Creada</span>
                                    <span v-else class="badge text-bg-secondary">Falta</span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button
                                            v-if="['solicitada', 'pendiente_cotizacion'].includes(apt.status) && !apt.quotation"
                                            class="btn btn-outline-primary"
                                            @click="openQuotationModal(apt)"
                                            title="Crear cotización"
                                        >
                                            <i class="bi bi-plus"></i>
                                        </button>
                                        <button
                                            v-if="['solicitada', 'pendiente_cotizacion', 'aprobada', 'para_ejecucion', 'programada'].includes(apt.status)"
                                            class="btn btn-outline-danger"
                                            @click="cancelAppointment(apt)"
                                            title="Cancelar cita"
                                        >
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                        <button
                                            v-if="['para_ejecucion', 'programada'].includes(apt.status)"
                                            class="btn btn-outline-success"
                                            @click="markAsExecuted(apt)"
                                            :disabled="executingApts[apt.id]"
                                            title="Marcar como ejecutada"
                                        >
                                            <span v-if="executingApts[apt.id]" class="spinner-border spinner-border-sm"></span>
                                            <i v-else class="bi bi-check-circle"></i>
                                        </button>
                                        <button
                                            class="btn btn-outline-secondary"
                                            @click="viewDetails(apt)"
                                            title="Ver detalles"
                                        >
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredAppointments.length === 0">
                                <td colspan="7" class="text-center text-muted py-4">No hay citas en este estado</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- MIEMBROS DEL EQUIPO -->
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white border-0">
                    <h6 class="mb-0 fw-bold">Miembros de Mis Equipos</h6>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Equipo</th>
                                <th>Rol en el Sistema</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="member in teamMembers" :key="member.id">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <p class="mb-0 fw-semibold">{{ member.name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge text-bg-primary">
                                        {{ getMemberTeams(member) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge text-bg-secondary">{{ member.role?.name || '-' }}</span>
                                </td>
                                <td><small>{{ member.email }}</small></td>
                            </tr>
                            <tr v-if="teamMembers.length === 0">
                                <td colspan="4" class="text-center text-muted py-4">Sin miembros asignados</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <NotificationPanel
            :show="showNotifications"
            :notifications="notifications"
            :allow-mark-all="true"
            @mark-one="markNotificationAsRead"
            @mark-all="markAllNotificationsAsRead"
        />

        <!-- MODAL DE COTIZACIÓN -->
        <div v-if="showQuotationModal" class="modal d-block" style="background-color: rgba(0, 0, 0, 0.5);" @click="closeQuotationModal">
            <div class="modal-dialog modal-lg" @click.stop>
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Crear Cotización</h5>
                        <button type="button" class="btn-close" @click="closeQuotationModal"></button>
                    </div>
                    <div class="modal-body">
                        <div v-if="selectedAppointmentForQuotation" class="mb-3">
                            <div class="alert alert-info">
                                <p class="mb-1"><strong>Cliente:</strong> {{ selectedAppointmentForQuotation.client?.name }}</p>
                                <p class="mb-1"><strong>Equipos:</strong> {{ selectedAppointmentForQuotation.team?.name }}</p>
                                <p class="mb-0"><strong>Fecha:</strong> {{ formatDate(selectedAppointmentForQuotation.scheduled_date) }}</p>
                            </div>

                            <form @submit.prevent="submitQuotation">
                                <div class="mb-3">
                                    <label for="materials" class="form-label">Materiales (opcional)</label>
                                    <textarea
                                        id="materials"
                                        v-model="quotationForm.materials"
                                        class="form-control"
                                        rows="3"
                                        placeholder="Describe los materiales necesarios"
                                    ></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="labor_hours" class="form-label">Horas Hombre *</label>
                                        <input
                                            id="labor_hours"
                                            v-model.number="quotationForm.labor_hours"
                                            type="number"
                                            step="any"
                                            min="0.1"
                                            inputmode="decimal"
                                            class="form-control"
                                            placeholder="Ej: 4.5"
                                            required
                                        />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="required_staff" class="form-label">Personal Requerido *</label>
                                        <input
                                            id="required_staff"
                                            v-model.number="quotationForm.required_staff"
                                            type="number"
                                            min="1"
                                            class="form-control"
                                            placeholder="Ej: 2"
                                            required
                                        />
                                    </div>
                                </div>

                                <div v-if="quotationError" class="alert alert-danger mb-3">{{ quotationError }}</div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary" :disabled="submittingQuotation">
                                        <span v-if="submittingQuotation" class="spinner-border spinner-border-sm me-2"></span>
                                        {{ submittingQuotation ? 'Creando...' : 'Crear Cotización' }}
                                    </button>
                                    <button type="button" class="btn btn-secondary" @click="closeQuotationModal">Cancelar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL DETALLE DE CITA -->
        <div v-if="showDetailsModal" class="modal d-block" style="background-color: rgba(0, 0, 0, 0.5);" @click="closeDetailsModal">
            <div class="modal-dialog" @click.stop>
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detalle de Cita #{{ selectedAppointmentDetail?.id }}</h5>
                        <button type="button" class="btn-close" @click="closeDetailsModal"></button>
                    </div>
                    <div class="modal-body" v-if="selectedAppointmentDetail">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Cliente</span>
                                <span class="fw-semibold">{{ selectedAppointmentDetail.client?.name || '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Tipo</span>
                                <span>{{ capitalizeFirst(selectedAppointmentDetail.appointment_type) }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Estado</span>
                                <span>{{ formatStatus(selectedAppointmentDetail.status) }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Equipo</span>
                                <span>{{ selectedAppointmentDetail.team?.name || 'Sin equipo' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Fecha</span>
                                <span>{{ formatDate(selectedAppointmentDetail.scheduled_date) }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Dirección</span>
                                <span class="text-end ms-3">{{ selectedAppointmentDetail.address || '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Descripción</span>
                                <span class="text-end ms-3">{{ selectedAppointmentDetail.description || '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Cotización</span>
                                <span>{{ selectedAppointmentDetail.quotation ? 'Creada' : 'No creada' }}</span>
                            </li>
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeDetailsModal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import NotificationBell from '../../Components/NotificationBell.vue';
import NotificationPanel from '../../Components/NotificationPanel.vue';
import { useNotifications } from '../../composables/useNotifications';
import api from '../../services/api';

const loading = ref(false);
const error = ref(null);
const statusFilter = ref('');
const executingApts = ref({});

const myTeams = ref([]);
const appointments = ref([]);
const teamMembers = ref([]);

const showQuotationModal = ref(false);
const selectedAppointmentForQuotation = ref(null);
const quotationForm = ref({
    materials: '',
    labor_hours: null,
    required_staff: null
});
const showDetailsModal = ref(false);
const selectedAppointmentDetail = ref(null);
const submittingQuotation = ref(false);
const quotationError = ref(null);

const { 
    notifications, 
    unreadNotifications,
    loadNotifications,
    markAsRead,
    markAllAsRead,
    startPolling,
    stopPolling
} = useNotifications({ autoCloseOnMark: false });

const showNotifications = ref(false);

const stats = computed(() => {
    const active = appointments.value.filter(a => !['cancelada', 'ejecutada'].includes(a.status));
    const total = active.length;
    const pending = active.filter((a) => (
        ['solicitada', 'pendiente_cotizacion'].includes(a.status) && !a.quotation
    )).length;
    const scheduled = active.filter(a => ['para_ejecucion', 'programada'].includes(a.status)).length;
    const members = new Set(teamMembers.value.map(m => m.id)).size;

    return {
        totalAppointments: total,
        pendingQuotation: pending,
        scheduled: scheduled,
        teamMembers: members
    };
});

const pendingQuotations = computed(() => {
    return appointments.value.filter((a) => (
        ['solicitada', 'pendiente_cotizacion'].includes(a.status) && !a.quotation
    )).slice(0, 5);
});

const appointmentsByStatus = computed(() => {
    const statusMap = {};
    appointments.value.forEach(apt => {
        statusMap[apt.status] = (statusMap[apt.status] || 0) + 1;
    });
    return Object.entries(statusMap).map(([status, total]) => ({ status, total }));
});

const filteredAppointments = computed(() => {
    if (!statusFilter.value) {
        // Por defecto: mostrar solo citas activas
        return appointments.value.filter(a => !['cancelada', 'ejecutada'].includes(a.status));
    }
    
    if (statusFilter.value === 'historial') {
        // Mostrar solo historial (canceladas y ejecutadas)
        return appointments.value.filter(a => ['cancelada', 'ejecutada'].includes(a.status));
    }
    
    // Mostrar por estado específico
    return appointments.value.filter(a => a.status === statusFilter.value);
});

const getDefaultRequiredStaff = (appointment) => {
    if (!appointment?.team_id) return 1;

    const team = myTeams.value.find((t) => t.id === appointment.team_id);
    const membersCount = team?.members?.length ?? 0;

    // Minimo 1 para cumplir validacion del backend.
    return Math.max(1, membersCount);
};

const loadData = async () => {
    loading.value = true;
    error.value = null;
    try {
        // Cargar data del técnico líder
        const response = await api.get('/api/tecnico/dashboard');
        const data = response.data;
        myTeams.value = data.teams || [];
        appointments.value = data.appointments || [];
        teamMembers.value = data.teamMembers || [];

        await loadNotifications();
    } catch (err) {
        error.value = err?.response?.data?.message || err.message || 'Error al cargar los datos';
        console.error(err);
    } finally {
        loading.value = false;
    }
};

const markAsExecuted = async (appointment) => {
    if (!confirm('¿Marcar esta cita como ejecutada?')) return;

    executingApts.value[appointment.id] = true;
    try {
        await api.put(`/api/appointments/${appointment.id}/execute`, {});

        await loadData();
    } catch (err) {
        error.value = err?.response?.data?.message || err.message || 'Error al marcar como ejecutada';
    } finally {
        executingApts.value[appointment.id] = false;
    }
};

const cancelAppointment = async (appointment) => {
    const reason = window.prompt('Motivo de la cancelación:');
    if (!reason || !reason.trim()) return;

    try {
        await api.post(`/api/appointments/${appointment.id}/cancel`, {
            reason: reason.trim()
        });

        await loadData();
    } catch (err) {
        error.value = err?.response?.data?.message || err.message || 'Error al cancelar la cita';
    }
};

const openQuotationModal = (appointment) => {
    selectedAppointmentForQuotation.value = appointment;
    quotationForm.value = {
        materials: '',
        labor_hours: null,
        required_staff: getDefaultRequiredStaff(appointment)
    };
    quotationError.value = null;
    showQuotationModal.value = true;
};

const closeQuotationModal = () => {
    showQuotationModal.value = false;
    selectedAppointmentForQuotation.value = null;
    quotationForm.value = {
        materials: '',
        labor_hours: null,
        required_staff: null
    };
    quotationError.value = null;
};

const submitQuotation = async () => {
    if (!quotationForm.value.labor_hours || !quotationForm.value.required_staff) {
        quotationError.value = 'Los campos de horas hombre y personal requerido son obligatorios';
        return;
    }

    submittingQuotation.value = true;
    quotationError.value = null;

    try {
        await api.post('/api/quotations', {
            appointment_id: selectedAppointmentForQuotation.value.id,
            materials: quotationForm.value.materials || null,
            labor_hours: quotationForm.value.labor_hours,
            required_staff: quotationForm.value.required_staff
        });

        // Recargar datos y cerrar modal
        await loadData();
        closeQuotationModal();
    } catch (err) {
        quotationError.value = err?.response?.data?.message || err.message || 'Error al crear la cotización';
        console.error(err);
    } finally {
        submittingQuotation.value = false;
    }
};

const viewDetails = (appointment) => {
    selectedAppointmentDetail.value = appointment;
    showDetailsModal.value = true;
};

const closeDetailsModal = () => {
    showDetailsModal.value = false;
    selectedAppointmentDetail.value = null;
};

const getMemberTeams = (member) => {
    if (!member.teams) return '-';
    return member.teams.map(t => t.name).join(', ');
};

const getTeamActiveCitasCount = (teamId) => {
    return appointments.value.filter(a => 
        a.team_id === teamId && !['cancelada', 'ejecutada'].includes(a.status)
    ).length;
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatStatus = (status) => {
    const statusMap = {
        'solicitada': 'Solicitada',
        'pendiente_cotizacion': 'Pendiente Cotización',
        'pendiente_aprobacion_admin': 'Pendiente Aprobación Admin',
        'cotizada': 'Cotizada',
        'aprobada': 'Aprobada',
        'para_ejecucion': 'Para Ejecución',
        'programada': 'Programada',
        'ejecutada': 'Ejecutada',
        'rechazada': 'Rechazada',
        'cancelada': 'Cancelada'
    };
    return statusMap[status] || status;
};

const statusBadgeClass = (status) => {
    const classMap = {
        'solicitada': 'text-bg-secondary',
        'pendiente_cotizacion': 'text-bg-warning',
        'pendiente_aprobacion_admin': 'text-bg-warning',
        'cotizada': 'text-bg-info',
        'aprobada': 'text-bg-primary',
        'para_ejecucion': 'text-bg-success',
        'programada': 'text-bg-info',
        'ejecutada': 'text-bg-success',
        'rechazada': 'text-bg-danger',
        'cancelada': 'text-bg-secondary'
    };
    return classMap[status] || 'text-bg-secondary';
};

const typeColorClass = (type) => {
    const classMap = {
        'cotizacion': 'text-bg-primary',
        'mantenimiento': 'text-bg-warning',
        'reparacion': 'text-bg-danger',
        'instalacion': 'text-bg-success'
    };
    return classMap[type] || 'text-bg-secondary';
};

const capitalizeFirst = (str) => {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
};

const toggleNotifications = () => {
    showNotifications.value = !showNotifications.value;
};

const markNotificationAsRead = async (notificationId) => {
    await markAsRead(notificationId);
};

const markAllNotificationsAsRead = async () => {
    await markAllAsRead();
};

onMounted(() => {
    loadData();
    startPolling(30000);
});

onUnmounted(() => {
    stopPolling();
});
</script>

<style scoped>
.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>
