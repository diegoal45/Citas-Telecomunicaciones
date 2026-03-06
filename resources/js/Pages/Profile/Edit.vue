<template>
    <AppLayout brand-href="/dashboard" brand-label="Mi Perfil" brand-icon="bi bi-person-circle" page-class="profile-page">
        <main class="container-fluid px-3 px-md-4 py-4">
            <div class="row">
                <!-- COLUMNA IZQUIERDA - Info del Perfil -->
                <div class="col-12 col-lg-8">
                    <!-- TARJETA DE PERFIL -->
                    <div class="card border-0 shadow mb-4">
                        <div class="card-header bg-gradient-primary text-white border-0 py-3">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-person-circle me-2"></i>Mi Perfil
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row mb-4">
                                <div class="col-12 text-center">
                                    <div class="profile-photo mb-3" style="position: relative; display: inline-block;">
                                        <div class="avatar-large d-inline-flex align-items-center justify-content-center overflow-hidden"
                                            style="width: 120px; height: 120px; border-radius: 50%; background: #f0f0f0; border: 3px solid #ddd;">
                                            <img
                                                v-if="user.profile_photo_url"
                                                :src="user.profile_photo_url"
                                                :alt="user.name"
                                                class="w-100 h-100 object-fit-cover"
                                            >
                                            <i v-else class="bi bi-person-fill" style="font-size: 3rem; color: #999;"></i>
                                        </div>
                                        <label for="photoInput" class="btn btn-sm btn-primary" 
                                            style="position: absolute; bottom: 0; right: 0; border-radius: 50%; padding: 0.4rem 0.5rem; cursor: pointer;">
                                            <i class="bi bi-pencil-fill"></i>
                                        </label>
                                        <input
                                            id="photoInput"
                                            type="file"
                                            accept="image/*"
                                            @change="handlePhotoUpload"
                                            style="display: none;"
                                        >
                                    </div>
                                    <p class="text-muted small mt-2" v-if="uploadMessage">{{ uploadMessage }}</p>
                                </div>
                            </div>

                            <form @submit.prevent="updateProfile">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label fw-bold">Nombre</label>
                                        <input
                                            id="name"
                                            v-model="form.name"
                                            type="text"
                                            class="form-control"
                                            required
                                        >
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label fw-bold">Email</label>
                                        <input
                                            id="email"
                                            v-model="form.email"
                                            type="email"
                                            class="form-control"
                                            required
                                        >
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label fw-bold">Teléfono</label>
                                        <input
                                            id="phone"
                                            v-model="form.phone"
                                            type="tel"
                                            class="form-control"
                                        >
                                    </div>
                                    <div class="col-md-6">
                                        <label for="role" class="form-label fw-bold">Rol</label>
                                        <input
                                            id="role"
                                            :value="user.role?.name || 'N/A'"
                                            type="text"
                                            class="form-control"
                                            disabled
                                        >
                                    </div>
                                </div>

                                <div v-if="error" class="alert alert-danger mb-3">{{ error }}</div>
                                <div v-if="success" class="alert alert-success mb-3">{{ success }}</div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary" :disabled="loading">
                                        <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                        {{ loading ? 'Guardando...' : 'Guardar Cambios' }}
                                    </button>
                                    <a href="/dashboard" class="btn btn-secondary">Volver</a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- TARJETA DE CAMBIO DE CONTRASEÑA -->
                    <div class="card border-0 shadow">
                        <div class="card-header bg-gradient-warning text-dark border-0 py-3">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-lock me-2"></i>Cambiar Contraseña
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <form @submit.prevent="updatePassword">
                                <div class="mb-3">
                                    <label for="currentPassword" class="form-label fw-bold">Contraseña Actual</label>
                                    <input
                                        id="currentPassword"
                                        v-model="passwordForm.current_password"
                                        type="password"
                                        class="form-control"
                                        required
                                    >
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="newPassword" class="form-label fw-bold">Nueva Contraseña</label>
                                        <input
                                            id="newPassword"
                                            v-model="passwordForm.password"
                                            type="password"
                                            class="form-control"
                                            required
                                        >
                                    </div>
                                    <div class="col-md-6">
                                        <label for="confirmPassword" class="form-label fw-bold">Confirmar Contraseña</label>
                                        <input
                                            id="confirmPassword"
                                            v-model="passwordForm.password_confirmation"
                                            type="password"
                                            class="form-control"
                                            required
                                        >
                                    </div>
                                </div>

                                <div v-if="passwordError" class="alert alert-danger mb-3">{{ passwordError }}</div>
                                <div v-if="passwordSuccess" class="alert alert-success mb-3">{{ passwordSuccess }}</div>

                                <button type="submit" class="btn btn-warning" :disabled="passwordLoading">
                                    <span v-if="passwordLoading" class="spinner-border spinner-border-sm me-2"></span>
                                    {{ passwordLoading ? 'Actualizando...' : 'Actualizar Contraseña' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- COLUMNA DERECHA - Resumen Rápido -->
                <div class="col-12 col-lg-4">
                    <!-- ESTADÍSTICAS RÁPIDAS -->
                    <div class="card border-0 shadow mb-4">
                        <div class="card-header bg-gradient-info text-white border-0 py-3">
                            <h6 class="mb-0 fw-semibold">
                                <i class="bi bi-bar-chart me-2"></i>Mis Estadísticas
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Citas Activas</span>
                                    <span class="badge text-bg-primary">{{ stats.activeAppointments }}</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Citas Completadas</span>
                                    <span class="badge text-bg-success">{{ stats.completedAppointments }}</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Cotizaciones Pendientes</span>
                                    <span class="badge text-bg-warning">{{ stats.pendingQuotations }}</span>
                                </div>
                            </div>
                            <div class="mb-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Citas Canceladas</span>
                                    <span class="badge text-bg-secondary">{{ stats.cancelledAppointments }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- INFORMACIÓN DEL CUENTA -->
                    <div class="card border-0 shadow">
                        <div class="card-header bg-gradient-success text-white border-0 py-3">
                            <h6 class="mb-0 fw-semibold">
                                <i class="bi bi-info-circle me-2"></i>Información de Cuenta
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="text-muted small">Miembro desde</span>
                                    <span class="small fw-semibold">{{ formatDate(user.created_at) }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="text-muted small">Último acceso</span>
                                    <span class="small fw-semibold">{{ formatDate(user.updated_at) }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="text-muted small">Estado</span>
                                    <span class="badge text-bg-success">Activo</span>
                                </li>
                            </ul>
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
import api from '../../services/api';
import { getStoredUser } from '../../services/auth';

const user = ref(getStoredUser() || {});
const loading = ref(false);
const passwordLoading = ref(false);
const uploadMessage = ref('');
const error = ref('');
const success = ref('');
const passwordError = ref('');
const passwordSuccess = ref('');
const appointments = ref([]);

const form = ref({
    name: user.value.name || '',
    email: user.value.email || '',
    phone: user.value.phone || ''
});

const passwordForm = ref({
    current_password: '',
    password: '',
    password_confirmation: ''
});

const stats = computed(() => {
    const active = appointments.value.filter(a => !['cancelada', 'ejecutada'].includes(a.status));
    const completed = appointments.value.filter(a => a.status === 'ejecutada');
    const cancelled = appointments.value.filter(a => a.status === 'cancelada');
    const pending = appointments.value.filter(a => ['solicitada', 'pendiente_cotizacion'].includes(a.status) && !a.quotation);
    
    return {
        activeAppointments: active.length,
        completedAppointments: completed.length,
        cancelledAppointments: cancelled.length,
        pendingQuotations: pending.length
    };
});

const handlePhotoUpload = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    uploadMessage.value = 'Subiendo foto...';
    const formData = new FormData();
    formData.append('photo', file);

    try {
        const response = await api.post('/api/profile/photo', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        
        // Actualizar la URL de la foto
        const photoUrl = response.data.profile_photo_url || `/storage/${response.data.profile_photo_path}`;
        user.value.profile_photo_url = photoUrl;
        user.value.profile_photo_path = response.data.profile_photo_path;
        
        // Guardar en localStorage
        localStorage.setItem('user', JSON.stringify(user.value));
        
        uploadMessage.value = 'Foto actualizada correctamente ✅';
        setTimeout(() => {
            uploadMessage.value = '';
        }, 3000);
    } catch (err) {
        uploadMessage.value = 'Error al subir la foto';
        console.error('Error uploading photo:', err);
    }
};

const updateProfile = async () => {
    loading.value = true;
    error.value = '';
    success.value = '';

    try {
        const response = await api.put('/api/profile', form.value);
        success.value = 'Perfil actualizado correctamente';
        user.value = response.data.user;
        localStorage.setItem('user', JSON.stringify(user.value));
        
        setTimeout(() => {
            success.value = '';
        }, 3000);
    } catch (err) {
        error.value = err?.response?.data?.message || 'Error al actualizar el perfil';
        console.error(err);
    } finally {
        loading.value = false;
    }
};

const updatePassword = async () => {
    if (passwordForm.value.password !== passwordForm.value.password_confirmation) {
        passwordError.value = 'Las contraseñas no coinciden';
        return;
    }

    if (passwordForm.value.password.length < 8) {
        passwordError.value = 'La contraseña debe tener al menos 8 caracteres';
        return;
    }

    passwordLoading.value = true;
    passwordError.value = '';
    passwordSuccess.value = '';

    try {
        await api.post('/api/profile/password', passwordForm.value);
        passwordSuccess.value = 'Contraseña actualizada correctamente';
        passwordForm.value = {
            current_password: '',
            password: '',
            password_confirmation: ''
        };

        setTimeout(() => {
            passwordSuccess.value = '';
        }, 3000);
    } catch (err) {
        passwordError.value = err?.response?.data?.message || 'Error al actualizar la contraseña';
        console.error(err);
    } finally {
        passwordLoading.value = false;
    }
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const loadStats = async () => {
    try {
        // Intentar obtener datos según el endpoint disponible
        let appointmentsData = [];
        
        // Si el usuario es tech leader, usar su endpoint
        if (user.value.role?.name === 'tech_leader') {
            const response = await api.get('/api/tecnico/dashboard');
            appointmentsData = response.data.appointments || [];
        } else if (user.value.role?.name === 'client') {
            // Si es cliente, usar otro endpoint si existe
            const response = await api.get('/api/appointments');
            appointmentsData = response.data.data || [];
        }
        
        appointments.value = appointmentsData;
    } catch (err) {
        console.log('No se pudo cargar estadísticas, continuando sin ellas');
    }
};

onMounted(async () => {
    // Cargar datos del usuario actual desde el servidor
    try {
        const response = await api.get('/api/profile');
        user.value = response.data.user;
        
        // Agregar URL de foto si existe
        if (user.value.profile_photo_path) {
            user.value.profile_photo_url = `/storage/${user.value.profile_photo_path}`;
        }
        
        // Actualizar form con datos del servidor
        form.value = {
            name: user.value.name || '',
            email: user.value.email || '',
            phone: user.value.phone || ''
        };
        
        // Guardar en localStorage
        localStorage.setItem('user', JSON.stringify(user.value));
    } catch (err) {
        console.log('No se pudo cargar el perfil desde el servidor');
        // Usar datos locales si falla
        const storedUser = getStoredUser();
        if (storedUser) {
            user.value = storedUser;
            form.value = {
                name: storedUser.name || '',
                email: storedUser.email || '',
                phone: storedUser.phone || ''
            };
        }
    }
    
    // Cargar estadísticas
    await loadStats();
});
</script>

<style scoped>
.profile-page {
    background: #f8f9fa;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #0b58ca 100%);
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
}

.bg-gradient-info {
    background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #198754 0%, #155e3b 100%);
}
</style>
