<template>
    <AppLayout brand-href="/tecnico/dashboard" brand-label="Panel Tecnico Lider" brand-icon="bi bi-tools" page-class="dashboard-container">
        <template #nav-actions>
            <NotificationBell :count="unreadNotifications" @click="toggleNotifications" />
        </template>

        <main class="container-fluid px-3 px-md-4 py-4">
            <div class="welcome-section mb-4">
                <div>
                    <h3 class="mb-1 fw-bold" style="color: #1a1a1a;">Panel del Técnico Líder</h3>
                    <p class="text-muted mb-0">Gestión de citas, cotizaciones y equipo</p>
                </div>
            </div>

            <div v-if="error" class="alert alert-danger mb-3">{{ error }}</div>

            <!-- ESTADÍSTICAS -->
            <div class="row g-3 mb-4">
                <div class="col-md-6 col-lg-3">
                    <div class="card border-0 shadow-sm h-100 stats-card stats-card-primary">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted small mb-1">Citas Activas</div>
                                    <h2 class="mb-0 fw-bold" style="color: #0d6efd;">{{ stats.totalAppointments }}</h2>
                                </div>
                                <div class="stats-icon stats-icon-primary">
                                    <i class="bi bi-calendar-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card border-0 shadow-sm h-100 stats-card stats-card-success">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted small mb-1">Completadas</div>
                                    <h2 class="mb-0 fw-bold" style="color: #198754;">{{ stats.pendingQuotation }}</h2>
                                </div>
                                <div class="stats-icon stats-icon-success">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card border-0 shadow-sm h-100 stats-card stats-card-warning">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted small mb-1">Canceladas</div>
                                    <h2 class="mb-0 fw-bold" style="color: #6c757d;">{{ stats.scheduled }}</h2>
                                </div>
                                <div class="stats-icon stats-icon-warning">
                                    <i class="bi bi-x-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card border-0 shadow-sm h-100 stats-card stats-card-info">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted small mb-1">Miembros del Equipo</div>
                                    <h2 class="mb-0 fw-bold" style="color: #0dcaf0;">{{ stats.teamMembers }}</h2>
                                </div>
                                <div class="stats-icon stats-icon-info">
                                    <i class="bi bi-people"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CALENDARIO -->
            <div class="row g-3 mb-3">
                <div class="col-12 col-lg-8 col-xl-8">
                    <div class="card border-0 shadow">
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
                            
                            <!-- Días de la semana -->
                            <div class="calendar-grid">
                                <div class="calendar-day-header" v-for="day in weekDays" :key="day">{{ day }}</div>
                                
                                <!-- Días del mes -->
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

                            <!-- Citas del día seleccionado -->
                            <div v-if="selectedDayAppointments.length > 0" class="mt-2 pt-2 border-top">
                                <h6 class="fw-bold mb-1" style="font-size: 0.85rem;">
                                    <i class="bi bi-calendar-day me-1"></i>
                                    {{ formatSelectedDate }}
                                </h6>
                                <div class="list-group">
                                    <button
                                        v-for="apt in selectedDayAppointments"
                                        :key="apt.id"
                                        type="button"
                                        class="list-group-item list-group-item-action p-2"
                                        style="font-size: 0.8rem;"
                                        @click="viewDetails(apt)"
                                    >
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1 text-start">
                                                <div class="fw-semibold" style="font-size: 0.8rem;">{{ apt.client?.name }}</div>
                                                <div class="text-muted" style="font-size: 0.7rem;">
                                                    {{ formatTime(apt.scheduled_date) }} - {{ capitalizeFirst(apt.appointment_type) }}
                                                </div>
                                            </div>
                                            <span class="badge" :class="statusBadgeClass(apt.status)" style="font-size: 0.7rem;">
                                                {{ formatStatus(apt.status) }}
                                            </span>
                                            <div class="text-muted mt-1" style="font-size: 0.7rem;">
                                                Equipo: {{ apt.team?.name || 'Sin equipo' }}
                                            </div>
                                        </div>
                                    </button>
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
                                    @click="viewDetails(apt)"
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

            <!-- EQUIPOS Y COTIZACIONES PENDIENTES -->
            <div class="row g-3 mb-3">
                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow h-100">
                        <div class="card-header bg-gradient-primary text-white border-0 py-3">
                            <h6 class="mb-0 fw-semibold">
                                <i class="bi bi-people me-2"></i>Mis Equipos
                            </h6>
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
                    <div class="card border-0 shadow h-100">
                        <div class="card-header bg-gradient-warning text-dark border-0 py-3">
                            <h6 class="mb-0 fw-semibold">
                                <i class="bi bi-file-earmark-text me-2"></i>Cotizaciones Pendientes
                            </h6>
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
                    <div class="card border-0 shadow h-100">
                        <div class="card-header bg-gradient-success text-white border-0 py-3">
                            <h6 class="mb-0 fw-semibold">
                                <i class="bi bi-bar-chart me-2"></i>Estado de Citas
                            </h6>
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
            <div class="card border-0 shadow">
                <div class="card-header bg-gradient-primary text-white border-0">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h6 class="mb-0 fw-semibold">
                            <i class="bi bi-calendar3 me-2"></i>Citas de Mis Equipos
                        </h6>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-light btn-sm" @click="loadData" :disabled="loading">
                                <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
                                <i v-else class="bi bi-arrow-clockwise me-1"></i>
                                {{ loading ? 'Actualizando...' : 'Actualizar' }}
                            </button>
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
                    <table class="table table-hover align-middle mb-0">
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
            <div class="card border-0 shadow mt-3">
                <div class="card-header bg-gradient-success text-white border-0">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-people me-2"></i>Miembros de Mis Equipos
                    </h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
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
            :unread-count="unreadNotifications"
            :marking-all-read="markingAllRead"
            :show-time="true"
            :format-time="formatNotificationTime"
            @close="showNotifications = false"
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
    unreadNotifications,    markingAllRead,    loadNotifications,
    markAsRead,
    markAllAsRead,
    startPolling,
    stopPolling
} = useNotifications({ autoCloseOnMark: false });

const showNotifications = ref(false);

const currentMonth = ref(new Date().getMonth());
const currentYear = ref(new Date().getFullYear());
const selectedDate = ref(null);

const weekDays = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

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
    const remainingDays = 42 - days.length;
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

const formatTime = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit'
    });
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

onMounted(() => {
    loadData();
    startPolling(30000);
    
    // Seleccionar el día de hoy por defecto
    const today = new Date();
    selectedDate.value = formatDateKey(today);
});

onUnmounted(() => {
    stopPolling();
});
</script>

<style scoped>
.dashboard-container {
    min-height: 100vh;
    background: #f8f9fa;
}

/* Welcome Section */
.welcome-section h3 {
    font-size: 1.75rem;
}

.welcome-section p {
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
    background: linear-gradient(135deg, #ffffff 0%, #fffbf0 100%);
    border-left: 4px solid #ffc107;
}

.stats-card-info {
    background: linear-gradient(135deg, #ffffff 0%, #f0fcff 100%);
    border-left: 4px solid #0dcaf0;
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
    background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
    color: white;
}

.stats-icon-warning-yellow {
    background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
    color: white;
}

.stats-icon-info {
    background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%);
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

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
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
    bottom: 0;
    right: 0;
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
</style>
