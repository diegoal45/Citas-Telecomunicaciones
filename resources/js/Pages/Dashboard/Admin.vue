<template>
    <AppLayout brand-href="/admin/dashboard" brand-label="Admin ExCitel" brand-icon="bi bi-shield-lock-fill" page-class="dashboard-container">
        <template #nav-actions>
            <NotificationBell :count="unreadNotifications" @click="toggleNotifications" />
        </template>

        <main class="container-fluid px-3 px-md-4 py-3">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="mb-0 fw-bold">Panel de Administración</h3>
                    <p class="text-muted small mb-0">Gestión integral del sistema</p>
                </div>
                <button class="btn btn-sm btn-outline-primary" @click="loadData" :disabled="loading">
                    <i class="bi bi-arrow-clockwise me-1"></i>
                    {{ loading ? 'Actualizando...' : 'Actualizar' }}
                </button>
            </div>

            <div v-if="error" class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                {{ error }}
                <button type="button" class="btn-close" @click="error = ''"></button>
            </div>

            <!-- Métricas principales -->
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 stats-card stats-card-primary">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted small mb-1">Citas Activas</div>
                                    <h2 class="mb-0 fw-bold" style="color: #0d6efd;">{{ metrics.totalUsers }}</h2>
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
                                    <h2 class="mb-0 fw-bold" style="color: #198754;">{{ metrics.totalTeams }}</h2>
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
                                    <h2 class="mb-0 fw-bold" style="color: #6c757d;">{{ metrics.pendingAttention }}</h2>
                                </div>
                                <div class="stats-icon stats-icon-warning">
                                    <i class="bi bi-x-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs de navegación -->
            <ul class="nav nav-pills mb-3" role="tablist">
                <li class="nav-item" role="presentation">
                    <button 
                        class="nav-link" 
                        :class="{ active: activeTab === 'resumen' }"
                        @click="activeTab = 'resumen'"
                        type="button"
                    >
                        <i class="bi bi-speedometer2 me-1"></i> Resumen
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button 
                        class="nav-link" 
                        :class="{ active: activeTab === 'citas' }"
                        @click="activeTab = 'citas'"
                        type="button"
                    >
                        <i class="bi bi-calendar-event me-1"></i> Citas
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button 
                        class="nav-link" 
                        :class="{ active: activeTab === 'equipos' }"
                        @click="activeTab = 'equipos'"
                        type="button"
                    >
                        <i class="bi bi-people me-1"></i> Equipos
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button 
                        class="nav-link" 
                        :class="{ active: activeTab === 'usuarios' }"
                        @click="activeTab = 'usuarios'"
                        type="button"
                    >
                        <i class="bi bi-person-gear me-1"></i> Usuarios
                    </button>
                </li>
            </ul>

            <!-- Contenido de pestañas -->
            
            <!-- Tab: Resumen -->
            <div v-show="activeTab === 'resumen'">
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0">
                                <h6 class="mb-0 fw-bold"><i class="bi bi-person-badge me-2"></i>Usuarios por Rol</h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2" v-for="(count, role) in roleCounts" :key="role">
                                    <span class="text-capitalize small">{{ role.replace('_', ' ') }}</span>
                                    <span class="badge bg-primary">{{ count }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0">
                                <h6 class="mb-0 fw-bold"><i class="bi bi-bar-chart me-2"></i>Citas por Estado</h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2" v-for="item in appointmentsByStatus" :key="item.status">
                                    <span class="small">{{ formatStatus(item.status) }}</span>
                                    <span class="badge" :class="statusBadgeClass(item.status)">{{ item.total }}</span>
                                </div>
                                <p v-if="appointmentsByStatus.length === 0" class="text-muted small mb-0">Sin datos</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0">
                                <h6 class="mb-0 fw-bold"><i class="bi bi-trophy me-2"></i>Top Equipos</h6>
                            </div>
                            <div class="card-body p-3" style="max-height: 300px; overflow-y: auto;">
                                <div class="mb-3 pb-2 border-bottom" v-for="team in topTeams.slice(0, 3)" :key="team.id">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="fw-semibold small">{{ team.name }}</span>
                                        <span class="badge bg-dark small">{{ team.totalAppointments }}</span>
                                    </div>
                                    <div class="d-flex gap-1">
                                        <span class="badge bg-warning text-dark" style="font-size: 0.65rem;">{{ team.activeAppointments }}</span>
                                        <span class="badge bg-success" style="font-size: 0.65rem;">{{ team.completedAppointments }}</span>
                                        <span class="badge bg-secondary" style="font-size: 0.65rem;">{{ team.cancelledAppointments }}</span>
                                    </div>
                                </div>
                                <p v-if="topTeams.length === 0" class="text-muted small mb-0">Sin equipos</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Citas -->
            <div v-show="activeTab === 'citas'">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-exclamation-circle me-2"></i>Requieren Gestión</h6>
                        <span class="badge bg-warning">{{ adminQueueTotal }}</span>
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
                <!-- Paginación -->
                <div v-if="adminQueueTotalPages > 1" class="card-footer bg-white border-0">
                    <nav>
                        <ul class="pagination pagination-sm mb-0 justify-content-center">
                            <li class="page-item" :class="{ disabled: adminQueuePage === 1 }">
                                <button class="page-link" @click="adminQueuePage = Math.max(1, adminQueuePage - 1)">Anterior</button>
                            </li>
                            <li v-for="page in adminQueueTotalPages" :key="page" class="page-item" :class="{ active: adminQueuePage === page }">
                                <button class="page-link" @click="adminQueuePage = page">{{ page }}</button>
                            </li>
                            <li class="page-item" :class="{ disabled: adminQueuePage === adminQueueTotalPages }">
                                <button class="page-link" @click="adminQueuePage = Math.min(adminQueueTotalPages, adminQueuePage + 1)">Siguiente</button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-list-check me-2"></i>Seguimiento General</h6>
                    <span class="badge bg-info">{{ normalQueueTotal }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Cliente</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th>Fecha Programada</th>
                                <th>Equipo</th>
                                    <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="apt in normalQueue" :key="`normal-${apt.id}`">
                                <td class="small">{{ apt.clientName }}</td>
                                <td class="text-capitalize small">{{ apt.appointment_type }}</td>
                                <td><span class="badge" :class="statusBadgeClass(apt.status)">{{ formatStatus(apt.status) }}</span></td>
                                <td class="small">{{ formatDate(apt.scheduled_date) }}</td>
                                <td class="small">{{ apt.teamName || 'Sin equipo' }}</td>
                                    <td>
                                        <button 
                                            v-if="apt.status === 'ejecutada'" 
                                            class="btn btn-sm btn-outline-danger"
                                            @click="downloadPdf(apt.id)"
                                            title="Descargar PDF"
                                        >
                                            <i class="bi bi-file-pdf me-1"></i>PDF
                                        </button>
                                        <span v-else class="text-muted small">-</span>
                                    </td>
                            </tr>
                            <tr v-if="normalQueue.length === 0">
                                  <td colspan="6" class="text-center text-muted py-4">No hay citas</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Paginación -->
                <div v-if="normalQueueTotalPages > 1" class="card-footer bg-white border-0">
                    <nav>
                        <ul class="pagination pagination-sm mb-0 justify-content-center">
                            <li class="page-item" :class="{ disabled: normalQueuePage === 1 }">
                                <button class="page-link" @click="normalQueuePage = Math.max(1, normalQueuePage - 1)">Anterior</button>
                            </li>
                            <li v-for="page in normalQueueTotalPages" :key="page" class="page-item" :class="{ active: normalQueuePage === page }">
                                <button class="page-link" @click="normalQueuePage = page">{{ page }}</button>
                            </li>
                            <li class="page-item" :class="{ disabled: normalQueuePage === normalQueueTotalPages }">
                                <button class="page-link" @click="normalQueuePage = Math.min(normalQueueTotalPages, normalQueuePage + 1)">Siguiente</button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
            </div>

            <!-- Tab: Equipos -->
            <div v-show="activeTab === 'equipos'">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-diagram-3 me-2"></i>Gestión de Equipos</h6>
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
            </div>

            <!-- Tab: Usuarios -->
            <div v-show="activeTab === 'usuarios'">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-person-gear me-2"></i>Usuarios y Roles</h6>
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
                            <button class="btn btn-sm btn-outline-primary" @click="loadUsers(1)"><i class="bi bi-search me-1"></i>Filtrar</button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-sm">
                        <thead class="table-light">
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
                                <td class="small">{{ u.name }}</td>
                                <td class="small">{{ u.email }}</td>
                                <td class="small">{{ getUserTeamDisplay(u) }}</td>
                                <td><span class="badge text-bg-secondary small">{{ u.role?.name || '-' }}</span></td>
                                <td style="min-width: 200px;">
                                    <select class="form-select form-select-sm" v-model.number="selectedRoles[u.id]">
                                        <option :value="null" disabled>Seleccionar rol...</option>
                                        <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</option>
                                    </select>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button
                                            class="btn btn-outline-secondary"
                                            @click="openEditUser(u)"
                                            title="Editar"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button
                                            class="btn btn-primary"
                                            :disabled="!canUpdateRole(u) || savingRoles[u.id]"
                                            @click="updateUserRole(u)"
                                            title="Guardar rol"
                                        >
                                            <span v-if="savingRoles[u.id]" class="spinner-border spinner-border-sm"></span>
                                            <i v-else class="bi bi-check"></i>
                                        </button>
                                        <button
                                            class="btn btn-outline-danger"
                                            :disabled="isSelf(u) || deletingUsers[u.id]"
                                            @click="deleteUser(u)"
                                            title="Eliminar"
                                        >
                                            <span v-if="deletingUsers[u.id]" class="spinner-border spinner-border-sm"></span>
                                            <i v-else class="bi bi-trash"></i>
                                        </button>
                                    </div>
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
                            <i class="bi bi-chevron-left"></i> Anterior
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" :disabled="usersPagination.currentPage >= usersPagination.lastPage || loadingUsers" @click="loadUsers(usersPagination.currentPage + 1)">
                            Siguiente <i class="bi bi-chevron-right"></i>
                        </button>
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
            <div class="admin-modal" @click.stop style="max-width: 450px; padding: 1.25rem;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0 fw-bold" style="font-size: 1rem;">Asignar Precio</h6>
                    <button class="btn btn-sm btn-outline-secondary" @click="closePriceModal">×</button>
                </div>

                <!-- Quotation Details Review - Compact -->
                <div class="bg-light p-2 rounded mb-2" v-if="priceModalData.quotation" style="font-size: 0.9rem;">
                    <div class="row g-2">
                        <div class="col-12">
                            <div class="text-muted" style="font-size: 0.75rem;">Horas-Hombre Totales</div>
                            <div class="fw-bold text-primary" style="font-size: 1rem;">{{ parseInt(priceModalData.quotation.labor_hours) }} H/H</div>
                        </div>
                    </div>
                </div>

                <!-- Price Input -->
                <div class="mb-2">
                    <label class="form-label fw-bold" style="font-size: 0.9rem; margin-bottom: 0.4rem;">Precio (COP)</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">$</span>
                        <input
                            v-model.number="priceModalData.price"
                            type="number"
                            class="form-control"
                            :class="{ 'is-invalid': priceError }"
                            placeholder="0"
                            step="0.01"
                            min="0"
                            style="font-size: 0.9rem;"
                        >
                    </div>
                    <div v-if="priceError" class="invalid-feedback d-block" style="font-size: 0.75rem;">{{ priceError }}</div>
                </div>

                <!-- Date Selection Calendar - Compact Grid -->
                <div class="mb-2">
                    <label class="form-label fw-bold" style="font-size: 0.9rem; margin-bottom: 0.4rem;">
                        <i class="bi bi-calendar-event"></i> Fecha
                    </label>
                    <div class="d-grid gap-1" style="grid-template-columns: repeat(7, 1fr); margin-bottom: 0.5rem;">
                        <button
                            v-for="dateOption in priceModalData.availableDates"
                            :key="dateOption.date"
                            @click="priceModalData.scheduledDate = dateOption.available ? dateOption.date : priceModalData.scheduledDate"
                            :disabled="!dateOption.available"
                            :class="{
                                'btn-success': priceModalData.scheduledDate === dateOption.date && dateOption.available,
                                'btn-outline-success': priceModalData.scheduledDate !== dateOption.date && dateOption.available,
                                'btn-outline-danger disabled opacity-50': !dateOption.available
                            }"
                            class="btn btn-sm"
                            style="font-size: 0.65rem; padding: 0.35rem 0.15rem; height: 100%;"
                            :title="dateOption.date"
                        >
                            <div>{{ dateOption.display.split(' ')[0].substring(0, 3) }}</div>
                            <div style="font-size: 0.6rem;">{{ dateOption.display.split(' ')[2] }}</div>
                        </button>
                    </div>
                    <div v-if="dateError" class="text-danger" style="font-size: 0.75rem;">{{ dateError }}</div>
                </div>

                <!-- Time Selection - With Availability -->
                <div class="mb-2">
                    <label class="form-label fw-bold" style="font-size: 0.9rem; margin-bottom: 0.4rem;">
                        <i class="bi bi-clock"></i> Hora
                    </label>
                    <select
                        v-model="priceModalData.scheduledTime"
                        class="form-select form-select-sm"
                        :disabled="!priceModalData.scheduledDate"
                        style="font-size: 0.9rem;"
                    >
                        <option value="">-- Selecciona una hora --</option>
                        <option
                            v-for="timeOption in priceModalData.availableTimes"
                            :key="timeOption.time"
                            :value="timeOption.time"
                            :disabled="!timeOption.available"
                        >
                            {{ timeOption.time }} {{ !timeOption.available ? '(Ocupado)' : '' }}
                        </option>
                    </select>
                </div>

                <!-- Selected Date and Time Summary -->
                <div v-if="priceModalData.scheduledDate && priceModalData.scheduledTime" class="alert alert-info mb-2 py-2 px-2" style="font-size: 0.85rem; background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%); border: 1px solid #0c5460; margin-bottom: 1rem;">
                    <i class="bi bi-info-circle-fill"></i><br>
                    <div style="margin-top: 0.3rem;">
                        <strong>{{ new Date(priceModalData.scheduledDate + 'T' + priceModalData.scheduledTime).toLocaleDateString('es-ES', { weekday: 'short', month: 'short', day: 'numeric' }) }}</strong><br>
                        <strong style="color: #0c5460;">{{ priceModalData.scheduledTime }} - {{ String(parseInt(priceModalData.scheduledTime.split(':')[0]) + parseInt(priceModalData.quotation?.labor_hours || 2)).padStart(2, '0') }}:00</strong>
                    </div>
                    <small style="font-size: 0.75rem; margin-top: 0.3rem; display: block;">
                        Bloquea: {{ parseInt(priceModalData.quotation?.labor_hours || 2) }} horas
                    </small>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button class="btn btn-sm btn-outline-secondary" @click="closePriceModal" :disabled="savingPrice">Cancelar</button>
                    <button class="btn btn-sm btn-success" @click="submitPrice" :disabled="!priceModalData.price || !priceModalData.scheduledDate || !priceModalData.scheduledTime || savingPrice">
                        <span v-if="savingPrice" class="spinner-border spinner-border-sm me-1"></span>
                        {{ savingPrice ? 'Guardando' : 'Guardar' }}
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
import { ref, computed, onMounted, watch } from 'vue';
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

// Paginación de citas
const adminQueuePage = ref(1);
const normalQueuePage = ref(1);
const itemsPerPage = 3;

// Tab activo
const activeTab = ref('resumen'); // 'resumen', 'citas', 'equipos', 'usuarios'

// Price modal refs
const showPriceModal = ref(false);
const priceModalData = ref({
    quotationId: null,
    appointment: null,
    quotation: null,
    price: '',
    scheduledDate: '',
    scheduledTime: '08:00',
    availableDates: [],
    availableTimes: [],
});
const savingPrice = ref(false);
const priceError = ref('');
const dateError = ref('');

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
            const completedAppointments = appointments.value.filter((a) => a.team_id === t.id && a.status === 'ejecutada').length;
            const cancelledAppointments = appointments.value.filter((a) => a.team_id === t.id && a.status === 'cancelada').length;
            return {
                id: t.id,
                name: t.name,
                leaderName: t.leader?.name || 'Sin lider',
                membersCount: Array.isArray(t.members) ? t.members.length : 0,
                activeAppointments,
                completedAppointments,
                cancelledAppointments,
                totalAppointments: activeAppointments + completedAppointments + cancelledAppointments,
            };
        })
        .sort((a, b) => b.totalAppointments - a.totalAppointments)
        .slice(0, 6);
});

const adminQueue = computed(() => {
    const filtered = appointments.value
        .filter((a) => a.status === 'cotizada' && a.quotation && !a.quotation.price)
        .map((a) => ({
            ...a,
            clientName: a.client?.name || 'Cliente',
            teamName: a.team?.name || null,
        }))
        .sort((a, b) => new Date(a.scheduled_date) - new Date(b.scheduled_date));
    
    return filtered.slice((adminQueuePage.value - 1) * itemsPerPage, adminQueuePage.value * itemsPerPage);
});

const adminQueueTotal = computed(() => {
    return appointments.value.filter((a) => a.status === 'cotizada' && a.quotation && !a.quotation.price).length;
});

const adminQueueTotalPages = computed(() => {
    return Math.ceil(adminQueueTotal.value / itemsPerPage);
});

const normalQueue = computed(() => {
    const filtered = appointments.value
        .filter((a) => !(a.status === 'cotizada' && a.quotation && !a.quotation.price))
        .map((a) => ({
            ...a,
            clientName: a.client?.name || 'Cliente',
            teamName: a.team?.name || null,
        }))
        .sort((a, b) => new Date(b.scheduled_date) - new Date(a.scheduled_date));
    
    return filtered.slice((normalQueuePage.value - 1) * itemsPerPage, normalQueuePage.value * itemsPerPage);
});

const normalQueueTotal = computed(() => {
    return appointments.value.filter((a) => !(a.status === 'cotizada' && a.quotation && !a.quotation.price)).length;
});

const normalQueueTotalPages = computed(() => {
    return Math.ceil(normalQueueTotal.value / itemsPerPage);
});

const metrics = computed(() => ({
    totalUsers: usersSummary.value.total,
    totalTeams: teams.value.length,
    pendingAttention: adminQueueTotal.value,
    scheduledExecutions: appointments.value.filter((a) => ['para_ejecucion', 'programada'].includes(a.status)).length,
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
    // Calcular fechas disponibles para los próximos 14 días
    const availableDates = [];
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    for (let i = 1; i <= 14; i++) {
        const date = new Date(today);
        date.setDate(date.getDate() + i);
        const dateString = date.toISOString().split('T')[0];
        
        // Contar citas de este equipo en esa fecha
        const hasConflict = appointments.value.some(a => 
            a.team_id === appointment.team_id && 
            a.id !== appointment.id &&
            a.scheduled_date.split('T')[0] === dateString &&
            !['cancelada', 'ejecutada'].includes(a.status)
        );
        
        availableDates.push({
            date: dateString,
            dateObject: date,
            available: !hasConflict,
            display: date.toLocaleDateString('es-ES', { weekday: 'short', month: 'short', day: 'numeric' })
        });
    }
    
    priceModalData.value = {
        quotationId,
        appointment,
        quotation,
        price: '',
        scheduledDate: availableDates.find(d => d.available)?.date || '',
        scheduledTime: '08:00',
        availableDates,
        availableTimes: [],
    };
    // Generate available times for the selected date
    if (priceModalData.value.scheduledDate) {
        updateAvailableTimes(priceModalData.value.scheduledDate, appointment.team_id);
    }
    priceError.value = '';
    dateError.value = '';
    showPriceModal.value = true;
};

const closePriceModal = () => {
    showPriceModal.value = false;
    priceModalData.value = {
        quotationId: null,
        appointment: null,
        quotation: null,
        price: '',
        scheduledDate: '',
        scheduledTime: '08:00',
        availableDates: [],
        availableTimes: [],
    };
    priceError.value = '';
    dateError.value = '';
};

// Watch for date changes and update available times
watch(() => priceModalData.value.scheduledDate, (newDate) => {
    if (newDate && priceModalData.value.appointment) {
        updateAvailableTimes(newDate, priceModalData.value.appointment.team_id);
        priceModalData.value.scheduledTime = '';
    }
});

const updateAvailableTimes = (dateString, teamId) => {
    const times = [];
    // Duración de bloqueo = labor_hours de la cotización (ya es el total)
    const newJobDuration = parseInt(priceModalData.value.quotation?.labor_hours || 2);
    console.log('Updated times for date:', dateString, 'Duration:', newJobDuration, 'Hours (from quotation)');
    
    // Generate ALL hours: 00:00 to 23:00
    for (let hour = 0; hour <= 23; hour++) {
        const timeStr = hour.toString().padStart(2, '0') + ':00';
        const proposedStartHour = hour;
        const proposedEndHour = hour + newJobDuration;
        
        // Check if this time conflicts with any existing appointments (all statuses except cancelled/executed)
        const hasTimeConflict = appointments.value.some(a => {
            // Skip if not same team or if it's the same appointment being edited
            if (a.team_id !== teamId || a.id === priceModalData.value.appointment.id) return false;
            // Skip cancelled and executed appointments
            if (['cancelada', 'ejecutada'].includes(a.status)) return false;
            
            const existingDate = a.scheduled_date.split('T')[0];
            const existingTime = a.scheduled_date.split('T')[1]?.substring(0, 5);
            
            // Only check appointments on the same date
            if (existingDate !== dateString) return false;
            
            // Get existing appointment duration (usar labor_hours directo, convertir a número)
            const existingHour = parseInt(existingTime.split(':')[0]);
            const existingDuration = parseInt(a.quotation?.labor_hours || 2);
            const existingEndHour = existingHour + existingDuration;
            
            console.log(`Checking hour ${timeStr}: proposed ${proposedStartHour}-${proposedEndHour}, existing ${existingHour}-${existingEndHour}`);
            
            // Check for time overlap
            // Conflict exists if: proposed start < existing end AND proposed end > existing start
            return proposedStartHour < existingEndHour && proposedEndHour > existingHour;
        });
        
        times.push({
            time: timeStr,
            available: !hasTimeConflict
        });
    }
    console.log('Available times:', times);
    
    priceModalData.value.availableTimes = times;
};

const submitPrice = async () => {
    priceError.value = '';
    dateError.value = '';

    const price = Number(priceModalData.value.price);
    if (Number.isNaN(price) || price <= 0) {
        priceError.value = 'El precio debe ser un número mayor a cero.';
        return;
    }

    if (!priceModalData.value.scheduledDate) {
        dateError.value = 'Debes seleccionar una fecha para la ejecución.';
        return;
    }

    if (!priceModalData.value.scheduledTime) {
        dateError.value = 'Debes seleccionar una hora para la ejecución.';
        return;
    }

    savingPrice.value = true;
    try {
        await api.put(`/api/quotations/${priceModalData.value.quotationId}/price`, { 
            price,
            scheduled_date: priceModalData.value.scheduledDate + 'T' + priceModalData.value.scheduledTime + ':00Z'
        });
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
        para_ejecucion: 'Para Ejecución',
        programada: 'Programada',
        rechazada: 'Rechazada',
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
        para_ejecucion: 'text-bg-success',
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

    const downloadPdf = async (appointmentId) => {
        try {
            const response = await api.get(`/api/appointments/${appointmentId}/pdf`, {
                responseType: 'blob'
            });
        
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', `cita-${appointmentId}.pdf`);
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.URL.revokeObjectURL(url);
        } catch (err) {
            console.error('Error al descargar PDF:', err);
            alert('Error al descargar el PDF. Por favor intenta de nuevo.');
        }
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
});
</script>

<style scoped>
.dashboard-container {
    min-height: 100vh;
    background: #f8f9fa;
}

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
    background: linear-gradient(135deg, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0.1) 100%);
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