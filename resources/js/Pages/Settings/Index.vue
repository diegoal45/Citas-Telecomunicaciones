<template>
    <AppLayout brand-href="/dashboard" brand-label="Configuración" brand-icon="bi bi-gear" page-class="settings-page">
        <main class="container py-4">
            <div class="row">
                <div class="col-12 col-md-8 mx-auto">
                    <!-- CONFIGURACIÓN GENERAL -->
                    <div class="card border-0 shadow mb-4">
                        <div class="card-header bg-gradient-primary text-white border-0 py-3">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-toggles me-2"></i>Configuración General
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input
                                        id="notificationsEnabled"
                                        v-model="settings.notifications_enabled"
                                        type="checkbox"
                                        class="form-check-input"
                                    >
                                    <label for="notificationsEnabled" class="form-check-label fw-bold">
                                        Habilitar Notificaciones
                                    </label>
                                    <p class="text-muted small ms-4">Recibe notificaciones de citas, cotizaciones y eventos importantes</p>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input
                                        id="emailNotifications"
                                        v-model="settings.email_notifications_enabled"
                                        type="checkbox"
                                        class="form-check-input"
                                    >
                                    <label for="emailNotifications" class="form-check-label fw-bold">
                                        Notificaciones por Email
                                    </label>
                                    <p class="text-muted small ms-4">Recibe resúmenes y alertas importantes por correo electrónico</p>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input
                                        id="darkMode"
                                        v-model="settings.dark_mode"
                                        type="checkbox"
                                        class="form-check-input"
                                    >
                                    <label for="darkMode" class="form-check-label fw-bold">
                                        Modo Oscuro
                                    </label>
                                    <p class="text-muted small ms-4">Cambiar a tema oscuro para reducir la fatiga visual</p>
                                </div>
                            </div>

                            <hr>

                            <div class="mb-3">
                                <label for="timezone" class="form-label fw-bold">Zona Horaria</label>
                                <select v-model="settings.timezone" id="timezone" class="form-select">
                                    <option value="UTC">UTC</option>
                                    <option value="America/Bogota">Colombia (Bogotá)</option>
                                    <option value="America/New_York">Eastern Time (US)</option>
                                    <option value="America/Los_Angeles">Pacific Time (US)</option>
                                    <option value="Europe/London">London</option>
                                    <option value="Europe/Madrid">España (Madrid)</option>
                                    <option value="America/Caracas">Venezuela</option>
                                </select>
                            </div>

                            <div v-if="error" class="alert alert-danger mb-3">{{ error }}</div>
                            <div v-if="success" class="alert alert-success mb-3">{{ success }}</div>

                            <button @click="saveSettings" class="btn btn-primary" :disabled="loading">
                                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                {{ loading ? 'Guardando...' : 'Guardar Configuración' }}
                            </button>
                        </div>
                    </div>

                    <!-- SEGURIDAD -->
                    <div class="card border-0 shadow mb-4">
                        <div class="card-header bg-gradient-danger text-white border-0 py-3">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-shield-lock me-2"></i>Seguridad
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <h6 class="fw-bold">Sesiones Activas</h6>
                                <p class="text-muted small mb-3">Tienes 1 sesión activa en este dispositivo</p>
                                <button class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-box-arrow-right me-1"></i>Cerrar Todas las Sesiones
                                </button>
                            </div>

                            <hr>

                            <div class="mb-3">
                                <h6 class="fw-bold">Autenticación de Dos Factores</h6>
                                <p class="text-muted small mb-3">Agrega una capa extra de seguridad a tu cuenta</p>
                                <button class="btn btn-outline-primary btn-sm" disabled>
                                    <i class="bi bi-shield-check me-1"></i>Habilitar 2FA
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- INFORMACIÓN -->
                    <div class="card border-0 shadow">
                        <div class="card-header bg-gradient-info text-white border-0 py-3">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-info-circle me-2"></i>Información del Sistema
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="text-muted">Versión de la Aplicación</span>
                                    <span class="fw-bold">1.0.0</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="text-muted">Última Actualización</span>
                                    <span class="fw-bold">6 de Marzo de 2026</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="text-muted">Soporte</span>
                                    <a href="mailto:support@excitel.com" class="text-primary">support@excitel.com</a>
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
import { ref, onMounted } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import api from '../../services/api';

const loading = ref(false);
const error = ref('');
const success = ref('');

const settings = ref({
    notifications_enabled: true,
    email_notifications_enabled: true,
    dark_mode: false,
    timezone: 'America/Bogota'
});

const saveSettings = async () => {
    loading.value = true;
    error.value = '';
    success.value = '';

    try {
        await api.post('/api/settings', settings.value);
        success.value = 'Configuración guardada correctamente';
        setTimeout(() => {
            success.value = '';
        }, 3000);
    } catch (err) {
        error.value = err?.response?.data?.message || 'Error al guardar la configuración';
        console.error(err);
    } finally {
        loading.value = false;
    }
};

onMounted(async () => {
    try {
        const response = await api.get('/api/settings');
        settings.value = response.data.settings;
    } catch (err) {
        console.error('Error al cargar configuración:', err);
    }
});
</script>

<style scoped>
.settings-page {
    background: #f8f9fa;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #0b58ca 100%);
}

.bg-gradient-danger {
    background: linear-gradient(135deg, #dc3545 0%, #bb2d3b 100%);
}

.bg-gradient-info {
    background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%);
}
</style>
