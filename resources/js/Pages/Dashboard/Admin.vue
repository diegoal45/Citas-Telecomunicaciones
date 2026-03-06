<template>
    <AppLayout brand-href="/admin/dashboard" brand-label="Admin ExCitel" brand-icon="bi bi-shield-lock-fill">
        <template #nav-actions>
            <NotificationBell :count="unreadNotifications" @click="toggleNotifications" />
        </template>

        <main class="container-fluid px-3 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                <div>
                    <h3 class="mb-1">Panel de Administracion</h3>
                    <p class="text-muted mb-0">Gestion de usuarios, equipos y flujo de citas</p>
                </div>
                <button class="btn btn-primary" @click="loadData" :disabled="loading">
                    <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                    {{ loading ? 'Actualizando...' : 'Actualizar datos' }}
                </button>
            </div>

            <div v-if="error" class="alert alert-danger mb-3">{{ error }}</div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <small class="text-muted">Usuarios Totales</small>
                            <h4 class="mb-0 fw-bold text-primary">{{ metrics.totalUsers }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <small class="text-muted">Equipos</small>
                            <h4 class="mb-0 fw-bold text-info">{{ metrics.totalTeams }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <small class="text-muted">Citas por atender</small>
                            <h4 class="mb-0 fw-bold text-warning">{{ metrics.pendingAttention }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <small class="text-muted">Ejecuciones Programadas</small>
                            <h4 class="mb-0 fw-bold text-success">{{ metrics.scheduledExecutions }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 pb-0">
                            <h6 class="mb-0 fw-bold">Usuarios por Rol</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span>Administradores</span>
                                    <span class="fw-bold">{{ roleCounts.admin }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span>Tecnicos Lideres</span>
                                    <span class="fw-bold">{{ roleCounts.tecnico_lider }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span>Tecnicos</span>
                                    <span class="fw-bold">{{ roleCounts.tecnico }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span>Clientes</span>
                                    <span class="fw-bold">{{ roleCounts.cliente }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 pb-0">
                            <h6 class="mb-0 fw-bold">Citas por Estado</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between px-0" v-for="item in appointmentsByStatus" :key="item.status">
                                    <span class="text-capitalize">{{ formatStatus(item.status) }}</span>
                                    <span class="fw-bold">{{ item.total }}</span>
                                </li>
                                <li v-if="appointmentsByStatus.length === 0" class="text-muted small">Sin datos</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 pb-0">
                            <h6 class="mb-0 fw-bold">Equipos y Carga</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item px-0" v-for="team in topTeams" :key="team.id">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-semibold">{{ team.name }}</span>
                                        <span class="badge text-bg-primary">{{ team.activeAppointments }} citas</span>
                                    </div>
                                    <small class="text-muted">
                                        Lider: {{ team.leaderName }} | Miembros: {{ team.membersCount }}
                                    </small>
                                </li>
                                <li v-if="topTeams.length === 0" class="text-muted small">Sin equipos</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h6 class="mb-0 fw-bold">Citas que Requieren Gestion del Admin</h6>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Equipo</th>
                                <th>Cotización</th>
                                <th>Precio</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="apt in adminQueue" :key="apt.id">
                                <td>{{ apt.clientName }}</td>
                                <td class="text-capitalize">{{ apt.appointment_type }}</td>
                                <td><span class="badge" :class="statusBadgeClass(apt.status)">{{ formatStatus(apt.status) }}</span></td>
                                <td>{{ formatDate(apt.scheduled_date) }}</td>
                                <td>{{ apt.teamName || 'Sin equipo' }}</td>
                                <td>
                                    <div class="small">Horas: {{ apt.quotation?.labor_hours ?? '-' }} | Personal: {{ apt.quotation?.required_staff ?? '-' }}</div>
                                    <div class="small text-muted">{{ shortenText(apt.quotation?.materials || 'Sin materiales', 55) }}</div>
                                </td>
                                <td>{{ apt.quotation?.price ? apt.quotation.price : 'Sin precio' }}</td>
                                <td>
                                    <button
                                        v-if="apt.status === 'cotizada' && apt.quotation && !apt.quotation.price"
                                        class="btn btn-sm btn-outline-success"
                                        :disabled="savingPrice"
                                        @click="openPriceModal(apt.quotation.id, apt, apt.quotation)"
                                    >
                                        <i class="bi bi-tag me-1"></i>Poner precio
                                    </button>
                                    <span v-else class="text-muted small">Sin acciones</span>
                                </td>
                            </tr>
                            <tr v-if="adminQueue.length === 0">
                                <td colspan="8" class="text-center text-muted py-4">No hay citas pendientes de gestion</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white border-0">
                    <h6 class="mb-0 fw-bold">Citas Normales (Seguimiento)</h6>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Equipo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="apt in normalQueue" :key="`normal-${apt.id}`">
                                <td>{{ apt.clientName }}</td>
                                <td class="text-capitalize">{{ apt.appointment_type }}</td>
                                <td><span class="badge" :class="statusBadgeClass(apt.status)">{{ formatStatus(apt.status) }}</span></td>
                                <td>{{ formatDate(apt.scheduled_date) }}</td>
                                <td>{{ apt.teamName || 'Sin equipo' }}</td>
                            </tr>
                            <tr v-if="normalQueue.length === 0">
                                <td colspan="5" class="text-center text-muted py-4">No hay citas normales</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white border-0">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h6 class="mb-0 fw-bold">Gestión de Equipos</h6>
                        <button class="btn btn-sm btn-success" @click="openCreateTeam">
                            <i class="bi bi-plus-circle me-1"></i>Crear Equipo
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nombre del Equipo</th>
                                <th>Lider</th>
                                <th>Miembros</th>
                                <th>Citas Activas</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="team in teams" :key="team.id">
                                <td><strong>{{ team.name }}</strong></td>
                                <td>{{ team.leader?.name || 'Sin lider' }}</td>
                                <td>
                                    <span class="badge text-bg-info">{{ team.members?.length || 0 }} miembros</span>
                                </td>
                                <td>{{ appointments.filter(a => a.team_id === team.id && !['cancelada', 'ejecutada'].includes(a.status)).length }}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary me-2" @click="openAssignMembers(team)">
                                        Miembros
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning me-2" @click="openEditTeam(team)">
                                        Editar
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" :disabled="deletingTeams[team.id]" @click="deleteTeam(team)">
                                        <span v-if="deletingTeams[team.id]" class="spinner-border spinner-border-sm me-1"></span>
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="teams.length === 0">
                                <td colspan="5" class="text-center text-muted py-4">No hay equipos registrados</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white border-0">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h6 class="mb-0 fw-bold">Usuarios y Asignacion de Roles</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <input
                                v-model="userSearch"
                                type="text"
                                class="form-control form-control-sm"
                                style="min-width: 240px;"
                                placeholder="Buscar por nombre, email o telefono"
                                @keyup.enter="loadUsers(1)"
                            >
                            <select class="form-select form-select-sm" style="min-width: 180px;" v-model.number="roleFilter" @change="loadUsers(1)">
                                <option :value="null">Todos los roles</option>
                                <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</option>
                            </select>
                            <button class="btn btn-sm btn-outline-primary" @click="loadUsers(1)">Filtrar</button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Equipo</th>
                                <th>Rol actual</th>
                                <th>Nuevo rol</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="u in users" :key="u.id">
                                <td>{{ u.name }}</td>
                                <td>{{ u.email }}</td>
                                <td>{{ getUserTeamDisplay(u) }}</td>
                                <td><span class="badge text-bg-secondary">{{ u.role?.name || '-' }}</span></td>
                                <td style="min-width: 220px;">
                                    <select class="form-select form-select-sm" v-model.number="selectedRoles[u.id]">
                                        <option :value="null" disabled>Seleccionar rol...</option>
                                        <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</option>
                                    </select>
                                </td>
                                <td>
                                    <button
                                        class="btn btn-sm btn-outline-secondary me-2"
                                        @click="openEditUser(u)"
                                    >
                                        Editar
                                    </button>
                                    <button
                                        class="btn btn-sm btn-primary"
                                        :disabled="!canUpdateRole(u) || savingRoles[u.id]"
                                        @click="updateUserRole(u)"
                                    >
                                        <span v-if="savingRoles[u.id]" class="spinner-border spinner-border-sm me-1"></span>
                                        {{ isSelf(u) ? 'No permitido' : 'Guardar rol' }}
                                    </button>
                                    <button
                                        class="btn btn-sm btn-outline-danger ms-2"
                                        :disabled="isSelf(u) || deletingUsers[u.id]"
                                        @click="deleteUser(u)"
                                    >
                                        <span v-if="deletingUsers[u.id]" class="spinner-border spinner-border-sm me-1"></span>
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="users.length === 0">
                                <td colspan="6" class="text-center text-muted py-4">No hay usuarios</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white d-flex justify-content-between align-items-center" v-if="usersPagination.total > 0">
                    <small class="text-muted">
                        Mostrando {{ users.length }} de {{ usersPagination.total }} usuarios
                    </small>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary" :disabled="usersPagination.currentPage <= 1 || loadingUsers" @click="loadUsers(usersPagination.currentPage - 1)">
                            Anterior
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" :disabled="usersPagination.currentPage >= usersPagination.lastPage || loadingUsers" @click="loadUsers(usersPagination.currentPage + 1)">
                            Siguiente
                        </button>
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
            @close="showNotifications = false"
            @mark-one="markAsRead"
            @mark-all="markAllAsRead"
        />

        <div v-if="editingUser" class="admin-modal-backdrop" @click="closeEditUser"></div>
        <div v-if="editingUser" class="admin-modal-wrapper">
            <div class="admin-modal" @click.stop>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-bold">Editar usuario</h6>
                    <button class="btn btn-sm btn-outline-secondary" @click="closeEditUser">Cerrar</button>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input v-model="editForm.name" type="text" class="form-control" :class="{ 'is-invalid': editErrors.name }">
                    <div v-if="editErrors.name" class="invalid-feedback">{{ editErrors.name[0] }}</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Correo</label>
                    <input v-model="editForm.email" type="email" class="form-control" :class="{ 'is-invalid': editErrors.email }">
                    <div v-if="editErrors.email" class="invalid-feedback">{{ editErrors.email[0] }}</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Telefono</label>
                    <input v-model="editForm.phone" type="text" class="form-control" :class="{ 'is-invalid': editErrors.phone }">
                    <div v-if="editErrors.phone" class="invalid-feedback">{{ editErrors.phone[0] }}</div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button class="btn btn-outline-secondary" @click="closeEditUser" :disabled="savingEdit">Cancelar</button>
                    <button class="btn btn-primary" @click="saveUserEdit" :disabled="savingEdit">
                        <span v-if="savingEdit" class="spinner-border spinner-border-sm me-2"></span>
                        Guardar cambios
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal: Crear/Editar Equipo -->
        <div v-if="showTeamModal" class="admin-modal-backdrop" @click="closeTeamModal"></div>
        <div v-if="showTeamModal" class="admin-modal-wrapper">
            <div class="admin-modal" @click.stop>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-bold">{{ editingTeam ? 'Editar Equipo' : 'Crear Nuevo Equipo' }}</h6>
                    <button class="btn btn-sm btn-outline-secondary" @click="closeTeamModal">Cerrar</button>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nombre del Equipo</label>
                    <input v-model="teamForm.name" type="text" class="form-control" :class="{ 'is-invalid': teamErrors.name }" placeholder="Ej: Equipo Telecomunicaciones">
                    <div v-if="teamErrors.name" class="invalid-feedback">{{ teamErrors.name[0] }}</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Líder del Equipo</label>
                    <select v-model.number="teamForm.leader_id" class="form-select" :class="{ 'is-invalid': teamErrors.leader_id }">
                        <option :value="null" disabled>Seleccionar líder...</option>
                        <option v-for="u in technicianUsers" :key="u.id" :value="u.id">{{ u.name }} ({{ u.role?.name }})</option>
                    </select>
                    <div v-if="teamErrors.leader_id" class="invalid-feedback">{{ teamErrors.leader_id[0] }}</div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button class="btn btn-outline-secondary" @click="closeTeamModal" :disabled="savingTeam">Cancelar</button>
                    <button class="btn btn-primary" @click="saveTeam" :disabled="savingTeam">
                        <span v-if="savingTeam" class="spinner-border spinner-border-sm me-2"></span>
                        {{ editingTeam ? 'Actualizar' : 'Crear Equipo' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal: Asignar Precio a Cotización -->
        <div v-if="showPriceModal" class="admin-modal-backdrop" @click="closePriceModal"></div>
        <div v-if="showPriceModal" class="admin-modal-wrapper">
            <div class="admin-modal" @click.stop style="max-width: 500px;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-bold">Asignar Precio a Cotización</h6>
                    <button class="btn btn-sm btn-outline-secondary" @click="closePriceModal">Cerrar</button>
                </div>

                <!-- Quotation Details Review -->
                <div class="bg-light p-3 rounded mb-3" v-if="priceModalData.quotation">
                    <div class="row g-2 small">
                        <div class="col-6">
                            <div class="text-muted">Horas de Trabajo</div>
                            <div class="fw-bold">{{ priceModalData.quotation.labor_hours }} horas</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted">Personal Requerido</div>
                            <div class="fw-bold">{{ priceModalData.quotation.required_staff }} personas</div>
                        </div>
                        <div class="col-12 mt-2 pt-2 border-top">
                            <div class="text-muted">Materiales</div>
                            <div class="fw-bold small">{{ priceModalData.quotation.materials || '(Sin especificar)' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Price Input -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Precio Final (RD$)</label>
                    <div class="input-group">
                        <span class="input-group-text">RD$</span>
                        <input
                            v-model.number="priceModalData.price"
                            type="number"
                            class="form-control"
                            :class="{ 'is-invalid': priceError }"
                            placeholder="0.00"
                            step="0.01"
                            min="0"
                            @keyup.enter="submitPrice"
                        >
                    </div>
                    <div v-if="priceError" class="invalid-feedback d-block">{{ priceError }}</div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button class="btn btn-outline-secondary" @click="closePriceModal" :disabled="savingPrice">Cancelar</button>
                    <button class="btn btn-success" @click="submitPrice" :disabled="!priceModalData.price || savingPrice">
                        <span v-if="savingPrice" class="spinner-border spinner-border-sm me-2"></span>
                        {{ savingPrice ? 'Guardando...' : 'Asignar Precio' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal: Asignar Miembros -->
        <div v-if="showAssignModal" class="admin-modal-backdrop" @click="closeAssignModal"></div>
        <div v-if="showAssignModal" class="admin-modal-wrapper">
            <div class="admin-modal" style="max-width: 620px;" @click.stop>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-bold">Asignar Miembros a {{ selectedTeamForMembers?.name }}</h6>
                    <button class="btn btn-sm btn-outline-secondary" @click="closeAssignModal">Cerrar</button>
                </div>

                <div class="mb-3">
                    <label class="form-label">Usuarios Disponibles</label>
                    <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                        <div v-if="availableUsers.length === 0" class="text-muted text-center py-4">
                            No hay usuarios disponibles para asignar
                        </div>
                        <div v-else class="d-flex flex-column gap-2">
                            <div v-for="u in availableUsers" :key="u.id" class="form-check">
                                <input
                                    :id="'user-' + u.id"
                                    v-model.number="selectedMembersToAdd"
                                    type="checkbox"
                                    :value="u.id"
                                    class="form-check-input"
                                >
                                <label :for="'user-' + u.id" class="form-check-label">
                                    <strong>{{ u.name }}</strong>
                                    <span class="badge text-bg-secondary ms-2">{{ u.role?.name }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Miembros Actuales</label>
                    <div class="border rounded p-3">
                        <div v-if="selectedTeamForMembers?.members?.length === 0" class="text-muted text-center py-4">
                            Sin miembros asignados
                        </div>
                        <div v-else class="d-flex flex-column gap-2">
                            <div v-for="m in selectedTeamForMembers?.members" :key="m.id" class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                                <div>
                                    <strong>{{ m.name }}</strong>
                                    <span class="badge text-bg-secondary ms-2">{{ m.role?.name }}</span>
                                </div>
                                <button class="btn btn-sm btn-outline-danger" :disabled="removingMembers[m.id]" @click="removeMember(m.id)">
                                    <span v-if="removingMembers[m.id]" class="spinner-border spinner-border-sm me-1"></span>
                                    Remover
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button class="btn btn-outline-secondary" @click="closeAssignModal" :disabled="addingMembers">Cancelar</button>
                    <button class="btn btn-primary" :disabled="selectedMembersToAdd.length === 0 || addingMembers" @click="addMembersToTeam">
                        <span v-if="addingMembers" class="spinner-border spinner-border-sm me-2"></span>
                        Agregar Miembros ({{ selectedMembersToAdd.length }})
                    </button>
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
import { getStoredUser } from '../../services/auth';

const loading = ref(false);
const error = ref('');
const users = ref([]);
const teams = ref([]);
const appointments = ref([]);
const roles = ref([]);
const selectedRoles = ref({});
const savingRoles = ref({});
const deletingUsers = ref({});
const loadingUsers = ref(false);
const userSearch = ref('');
const roleFilter = ref(null);
const editingUser = ref(null);
const savingEdit = ref(false);
const editErrors = ref({});
const editForm = ref({
    name: '',
    email: '',
    phone: '',
});

// Team management refs
const showTeamModal = ref(false);
const editingTeam = ref(null);
const savingTeam = ref(false);
const teamErrors = ref({});
const teamForm = ref({
    name: '',
    leader_id: null,
});
const showAssignModal = ref(false);
const selectedTeamForMembers = ref(null);
const selectedMembersToAdd = ref([]);
const addingMembers = ref(false);
const removingMembers = ref({});
const deletingTeams = ref({});
const approvingQuotations = ref({});

// Price modal refs
const showPriceModal = ref(false);
const priceModalData = ref({
    quotationId: null,
    appointment: null,
    quotation: null,
    price: '',
});
const savingPrice = ref(false);
const priceError = ref('');

const usersPagination = ref({
    currentPage: 1,
    lastPage: 1,
    total: 0,
});
const currentUserId = ref(getStoredUser()?.id || null);

const {
    notifications,
    showNotifications,
    markingAllRead,
    unreadNotifications,
    loadNotifications,
    toggleNotifications,
    markAsRead,
    markAllAsRead,
    startPolling,
} = useNotifications({
    onError: (message) => {
        error.value = message;
    },
});

const roleCounts = computed(() => {
    return usersSummary.value.byRole;
});

const usersSummary = ref({
    total: 0,
    byRole: {
        admin: 0,
        tecnico_lider: 0,
        tecnico: 0,
        cliente: 0,
    },
});

const appointmentsByStatus = computed(() => {
    const map = new Map();
    appointments.value.forEach((a) => {
        map.set(a.status, (map.get(a.status) || 0) + 1);
    });
    return Array.from(map.entries())
        .map(([status, total]) => ({ status, total }))
        .sort((a, b) => b.total - a.total);
});

const topTeams = computed(() => {
    return teams.value
        .map((t) => {
            const activeAppointments = appointments.value.filter((a) => a.team_id === t.id && !['cancelada', 'ejecutada'].includes(a.status)).length;
            return {
                id: t.id,
                name: t.name,
                leaderName: t.leader?.name || 'Sin lider',
                membersCount: Array.isArray(t.members) ? t.members.length : 0,
                activeAppointments,
            };
        })
        .sort((a, b) => b.activeAppointments - a.activeAppointments)
        .slice(0, 6);
});

const adminQueue = computed(() => {
    return appointments.value
        .filter((a) => a.status === 'cotizada' && a.quotation && !a.quotation.price)
        .map((a) => ({
            ...a,
            clientName: a.client?.name || 'Cliente',
            teamName: a.team?.name || null,
        }))
        .sort((a, b) => new Date(a.scheduled_date) - new Date(b.scheduled_date))
        .slice(0, 12);
});

const normalQueue = computed(() => {
    return appointments.value
        .filter((a) => !(a.status === 'cotizada' && a.quotation && !a.quotation.price))
        .map((a) => ({
            ...a,
            clientName: a.client?.name || 'Cliente',
            teamName: a.team?.name || null,
        }))
        .sort((a, b) => new Date(b.scheduled_date) - new Date(a.scheduled_date))
        .slice(0, 12);
});

const metrics = computed(() => ({
    totalUsers: usersSummary.value.total,
    totalTeams: teams.value.length,
    pendingAttention: adminQueue.value.length,
    scheduledExecutions: appointments.value.filter((a) => a.status === 'programada').length,
}));

const technicianUsers = computed(() => {
    // Solo usuarios con rol tecnico_lider (excluyendo al admin actual)
    return users.value.filter(u => 
        u.role?.name === 'tecnico_lider' &&
        Number(u.id) !== Number(currentUserId.value)
    );
});

const availableUsers = computed(() => {
    // Obtener todos los usuarios que ya están en ALGÚN equipo
    const allMemberIds = new Set();
    teams.value.forEach(team => {
        team.members?.forEach(member => {
            allMemberIds.add(member.id);
        });
    });

    return users.value.filter(u => 
        u.role?.name === 'tecnico' && // Solo técnicos (NO técnico_lider)
        !allMemberIds.has(u.id) && // No está en ningún equipo
        Number(u.id) !== Number(currentUserId.value) // No es el admin actual
    );
});

const loadUsers = async (page = 1) => {
    loadingUsers.value = true;
    try {
        const response = await api.get('/api/users', {
            params: {
                page,
                per_page: 10,
                search: userSearch.value || undefined,
                role_id: roleFilter.value || undefined,
            },
        });

        const payload = response.data || {};
        users.value = payload.data || [];
        usersPagination.value = {
            currentPage: payload.current_page || 1,
            lastPage: payload.last_page || 1,
            total: payload.total || 0,
        };

        const map = { ...selectedRoles.value };
        users.value.forEach((u) => {
            map[u.id] = u.id_rol ?? u.role?.id ?? null;
        });
        selectedRoles.value = map;
    } finally {
        loadingUsers.value = false;
    }
};

const loadUsersSummary = async () => {
    const response = await api.get('/api/users', { params: { summary: true } });
    const payload = response.data || {};
    usersSummary.value = {
        total: payload.total || 0,
        byRole: payload.by_role || usersSummary.value.byRole,
    };
};

const loadData = async () => {
    loading.value = true;
    error.value = '';

    try {
        const [teamsRes, appointmentsRes, rolesRes] = await Promise.all([
            api.get('/api/teams'),
            api.get('/api/appointments'),
            api.get('/api/roles'),
            loadNotifications(),
        ]);

        teams.value = teamsRes.data || [];
        appointments.value = appointmentsRes.data || [];
        roles.value = rolesRes.data || [];

        await Promise.all([loadUsersSummary(), loadUsers(1)]);
    } catch (err) {
        if (err?.response?.status === 403) {
            error.value = 'No autorizado. Esta vista es solo para administradores.';
        } else {
            error.value = 'No se pudo cargar la informacion administrativa.';
        }
    } finally {
        loading.value = false;
    }
};

const canUpdateRole = (user) => {
    if (isSelf(user)) return false;
    const selected = selectedRoles.value[user.id];
    const current = user.id_rol ?? user.role?.id ?? null;
    return selected && selected !== current;
};

const getUserTeamDisplay = (user) => {
    const roleName = user.role?.name;

    // Admin y cliente no muestran equipo en la tabla
    if (roleName === 'admin' || roleName === 'cliente' || roleName === 'user') {
        return '-';
    }

    // Si lidera equipo, priorizar ese nombre
    if (Array.isArray(user.led_teams) && user.led_teams.length > 0) {
        return user.led_teams[0]?.name || '-';
    }

    // Si es miembro, mostrar su primer equipo
    if (Array.isArray(user.teams) && user.teams.length > 0) {
        return user.teams[0]?.name || '-';
    }

    return '-';
};

const isSelf = (user) => {
    return Number(user.id) === Number(currentUserId.value);
};

const updateUserRole = async (user) => {
    const id = user.id;
    const roleId = selectedRoles.value[id];

    if (!roleId) return;

    savingRoles.value[id] = true;
    try {
        const response = await api.put(`/api/users/${id}`, { id_rol: roleId });
        const updated = response.data;

        const idx = users.value.findIndex((u) => u.id === id);
        if (idx !== -1) {
            users.value[idx] = updated;
        }

        await loadUsersSummary();
    } catch (err) {
        error.value = err?.response?.data?.message || 'No se pudo actualizar el rol del usuario.';
    } finally {
        savingRoles.value[id] = false;
    }
};

const openEditUser = (user) => {
    editingUser.value = user;
    editErrors.value = {};
    editForm.value = {
        name: user.name || '',
        email: user.email || '',
        phone: user.phone || '',
    };
};

const closeEditUser = () => {
    editingUser.value = null;
    editErrors.value = {};
};

const saveUserEdit = async () => {
    if (!editingUser.value) return;

    savingEdit.value = true;
    editErrors.value = {};

    try {
        const payload = {
            name: editForm.value.name,
            email: editForm.value.email,
            phone: editForm.value.phone,
        };

        const response = await api.put(`/api/users/${editingUser.value.id}`, payload);
        const updated = response.data;

        const idx = users.value.findIndex((u) => u.id === updated.id);
        if (idx !== -1) {
            users.value[idx] = updated;
        }

        closeEditUser();
    } catch (err) {
        if (err?.response?.status === 422) {
            editErrors.value = err.response.data.errors || {};
        } else {
            error.value = err?.response?.data?.message || 'No se pudo editar el usuario.';
        }
    } finally {
        savingEdit.value = false;
    }
};

const deleteUser = async (user) => {
    if (isSelf(user)) return;

    const ok = window.confirm(`¿Seguro que deseas eliminar al usuario ${user.name}? Esta acción no se puede deshacer.`);
    if (!ok) return;

    deletingUsers.value[user.id] = true;
    try {
        await api.delete(`/api/users/${user.id}`);
        await Promise.all([loadUsers(usersPagination.value.currentPage), loadUsersSummary()]);
    } catch (err) {
        error.value = err?.response?.data?.message || 'No se pudo eliminar el usuario.';
    } finally {
        deletingUsers.value[user.id] = false;
    }
};

// ===== Team Management Functions =====

const openCreateTeam = () => {
    editingTeam.value = null;
    teamErrors.value = {};
    teamForm.value = {
        name: '',
        leader_id: null,
    };
    showTeamModal.value = true;
};

const openEditTeam = (team) => {
    editingTeam.value = team;
    teamErrors.value = {};
    teamForm.value = {
        name: team.name,
        leader_id: team.leader_id,
    };
    showTeamModal.value = true;
};

const closeTeamModal = () => {
    showTeamModal.value = false;
    editingTeam.value = null;
    teamErrors.value = {};
};

const saveTeam = async () => {
    teamErrors.value = {};

    if (!teamForm.value.name?.trim()) {
        teamErrors.value.name = ['El nombre del equipo es requerido'];
        return;
    }

    if (!teamForm.value.leader_id) {
        teamErrors.value.leader_id = ['Debes seleccionar un líder'];
        return;
    }

    savingTeam.value = true;
    try {
        if (editingTeam.value) {
            // Actualizar
            const response = await api.put(`/api/teams/${editingTeam.value.id}`, {
                name: teamForm.value.name,
                leader_id: teamForm.value.leader_id,
            });
            const idx = teams.value.findIndex(t => t.id === editingTeam.value.id);
            if (idx !== -1) {
                teams.value[idx] = response.data;
            }
        } else {
            // Crear
            const response = await api.post('/api/teams', {
                name: teamForm.value.name,
                leader_id: teamForm.value.leader_id,
            });
            teams.value.push(response.data);
        }
        closeTeamModal();
    } catch (err) {
        if (err?.response?.status === 422) {
            teamErrors.value = err.response.data.errors || {};
        } else {
            error.value = err?.response?.data?.message || 'No se pudo guardar el equipo.';
        }
    } finally {
        savingTeam.value = false;
    }
};

const openAssignMembers = (team) => {
    selectedTeamForMembers.value = { ...team };
    selectedMembersToAdd.value = [];
    showAssignModal.value = true;
};

const closeAssignModal = () => {
    showAssignModal.value = false;
    selectedTeamForMembers.value = null;
    selectedMembersToAdd.value = [];
};

const addMembersToTeam = async () => {
    if (selectedMembersToAdd.value.length === 0 || !selectedTeamForMembers.value) return;

    addingMembers.value = true;
    try {
        await api.post(`/api/teams/${selectedTeamForMembers.value.id}/members`, {
            member_ids: selectedMembersToAdd.value,
        });
        await loadData();
        closeAssignModal();
    } catch (err) {
        error.value = err?.response?.data?.message || 'No se pudieron asignar los miembros.';
    } finally {
        addingMembers.value = false;
    }
};

const removeMember = async (memberId) => {
    if (!selectedTeamForMembers.value) return;

    removingMembers.value[memberId] = true;
    try {
        await api.delete(`/api/teams/${selectedTeamForMembers.value.id}/members/${memberId}`);
        await loadData();
        openAssignMembers(selectedTeamForMembers.value);
    } catch (err) {
        error.value = err?.response?.data?.message || 'No se pudo remover el miembro.';
    } finally {
        removingMembers.value[memberId] = false;
    }
};

const deleteTeam = async (team) => {
    const ok = window.confirm(`¿Seguro que deseas eliminar el equipo "${team.name}"? Esta acción no se puede deshacer.`);
    if (!ok) return;

    deletingTeams.value[team.id] = true;
    try {
        await api.delete(`/api/teams/${team.id}`);
        teams.value = teams.value.filter(t => t.id !== team.id);
    } catch (err) {
        error.value = err?.response?.data?.message || 'No se pudo eliminar el equipo.';
    } finally {
        deletingTeams.value[team.id] = false;
    }
};

const openPriceModal = (quotationId, appointment, quotation) => {
    priceModalData.value = {
        quotationId,
        appointment,
        quotation,
        price: '',
    };
    priceError.value = '';
    showPriceModal.value = true;
};

const closePriceModal = () => {
    showPriceModal.value = false;
    priceModalData.value = {
        quotationId: null,
        appointment: null,
        quotation: null,
        price: '',
    };
    priceError.value = '';
};

const submitPrice = async () => {
    priceError.value = '';

    const price = Number(priceModalData.value.price);
    if (Number.isNaN(price) || price <= 0) {
        priceError.value = 'El precio debe ser un número mayor a cero.';
        return;
    }

    savingPrice.value = true;
    try {
        await api.put(`/api/quotations/${priceModalData.value.quotationId}/price`, { price });
        closePriceModal();
        await loadData();
    } catch (err) {
        priceError.value = err?.response?.data?.message || 'No se pudo asignar el precio a la cotización.';
    } finally {
        savingPrice.value = false;
    }
};

// ===== End Team Management =====

const formatStatus = (status) => {
    const names = {
        solicitada: 'Solicitada',
        pendiente_cotizacion: 'Pendiente cotizacion',
        cotizada: 'Cotizada',
        aprobada: 'Aprobada',
        rechazada: 'Rechazada',
        programada: 'Programada',
        ejecutada: 'Ejecutada',
        cancelada: 'Cancelada',
    };
    return names[status] || status;
};

const statusBadgeClass = (status) => {
    const map = {
        solicitada: 'text-bg-primary',
        pendiente_cotizacion: 'text-bg-warning',
        cotizada: 'text-bg-info',
        aprobada: 'text-bg-success',
        programada: 'text-bg-success',
        rechazada: 'text-bg-secondary',
        ejecutada: 'text-bg-dark',
        cancelada: 'text-bg-danger',
    };
    return map[status] || 'text-bg-secondary';
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleString('es-ES', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const shortenText = (value, max = 60) => {
    const text = `${value || ''}`;
    if (text.length <= max) return text;
    return `${text.slice(0, max)}...`;
};

onMounted(() => {
    loadData();
    startPolling(30000);
});
</script>

<style scoped>
.card {
    border-radius: 12px;
}

.admin-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.45);
    z-index: 1060;
}

.admin-modal-wrapper {
    position: fixed;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    z-index: 1061;
}

.admin-modal {
    width: 100%;
    max-width: 520px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.2);
    padding: 1rem;
}
</style>