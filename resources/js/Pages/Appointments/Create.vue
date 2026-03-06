<template>
    <AppLayout>
        <template #nav-actions>
            <a href="/dashboard" class="btn btn-sm btn-outline-light">
                <i class="bi bi-arrow-left me-1"></i>Volver
            </a>
        </template>

        <!-- Header -->
        <div class="bg-primary text-white py-4">
            <div class="container-fluid px-3 px-md-4">
                <h2 class="mb-1">Agendar Cita</h2>
                <p class="mb-0 opacity-75">Completa el formulario para solicitar una nueva cita</p>
            </div>
        </div>

        <!-- Formulario -->
        <main class="container py-4 px-3 px-md-4">
            <div class="row">
                <div class="col-12 col-lg-8 mx-auto">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <form @submit.prevent="submitForm">
                                <!-- Fecha y Hora -->
                                <div class="mb-3">
                                    <label for="scheduledDate" class="form-label fw-semibold">
                                        <i class="bi bi-calendar me-2 text-primary"></i>Fecha y Hora
                                    </label>
                                    <input 
                                        v-model="form.scheduledDate"
                                        type="datetime-local" 
                                        class="form-control form-control-lg" 
                                        id="scheduledDate"
                                        required
                                    />
                                    <small class="text-muted">Selecciona la fecha y hora que prefieres para tu cita</small>
                                    <div v-if="errors.scheduledDate" class="text-danger small mt-2">
                                        {{ errors.scheduledDate[0] }}
                                    </div>
                                </div>

                                <!-- Dirección -->
                                <div class="mb-3">
                                    <label for="address" class="form-label fw-semibold">
                                        <i class="bi bi-geo-alt me-2 text-primary"></i>Dirección
                                    </label>
                                    <input 
                                        v-model="form.address"
                                        type="text" 
                                        class="form-control form-control-lg" 
                                        id="address"
                                        placeholder="Calle, número, ciudad"
                                        required
                                    />
                                    <small class="text-muted">Completa la dirección donde se realizará la cita</small>
                                    <div v-if="errors.address" class="text-danger small mt-2">
                                        {{ errors.address[0] }}
                                    </div>
                                </div>

                                <!-- Descripción -->
                                <div class="mb-4">
                                    <label for="description" class="form-label fw-semibold">
                                        <i class="bi bi-chat-left me-2 text-primary"></i>Descripción (Opcional)
                                    </label>
                                    <textarea 
                                        v-model="form.description"
                                        class="form-control" 
                                        id="description"
                                        rows="4"
                                        placeholder="Cuéntanos qué necesitas..."
                                    ></textarea>
                                    <small class="text-muted">Proporciona detalles sobre tu solicitud</small>
                                </div>

                                <!-- Botones -->
                                <div class="d-grid gap-2 d-sm-flex justify-content-sm-end">
                                    <a href="/dashboard" class="btn btn-outline-secondary btn-lg">
                                        <i class="bi bi-x-lg me-1"></i>Cancelar
                                    </a>
                                    <button 
                                        type="submit" 
                                        class="btn btn-primary btn-lg"
                                        :disabled="loading"
                                    >
                                        <i class="bi bi-check-lg me-1"></i>{{ loading ? 'Agendando...' : 'Agendar Cita' }}
                                    </button>
                                </div>

                                <!-- Mensaje de éxito -->
                                <div v-if="successMessage" class="alert alert-success mt-3" role="alert">
                                    <i class="bi bi-check-circle me-2"></i>{{ successMessage }}
                                </div>

                                <!-- Mensaje de error general -->
                                <div v-if="generalError" class="alert alert-danger mt-3" role="alert">
                                    <i class="bi bi-exclamation-triangle me-2"></i>{{ generalError }}
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import api from '../../services/api';

const form = ref({
    scheduledDate: '',
    address: '',
    description: ''
});

const loading = ref(false);
const errors = ref({});
const successMessage = ref('');
const generalError = ref('');

const submitForm = async () => {
    loading.value = true;
    errors.value = {};
    successMessage.value = '';
    generalError.value = '';

    try {
        // Convertir datetime-local a formato ISO
        const scheduledDate = new Date(form.value.scheduledDate).toISOString();

        await api.post('/api/appointments', {
            scheduled_date: scheduledDate,
            address: form.value.address,
            description: form.value.description
        });

        successMessage.value = '¡Cita agendada exitosamente! Serás redirigido al dashboard en 2 segundos...';
        
        setTimeout(() => {
            window.location.href = '/dashboard';
        }, 2000);

    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
        } else if (error.response?.data?.error) {
            generalError.value = error.response.data.error;
        } else {
            generalError.value = 'Ocurrió un error al agendar la cita. Intenta de nuevo.';
        }
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
.form-label {
    color: #333;
    margin-bottom: 0.5rem;
}

.form-control-lg:focus,
.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}
</style>
