<template>
    <AppLayout brand-href="/dashboard" brand-label="Mi Perfil" brand-icon="bi bi-person-circle" page-class="profile-page">
        <main class="container py-4">
            <div class="row">
                <div class="col-12 col-md-8 mx-auto">
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

                                <div class="mb-3">
                                    <label for="address" class="form-label fw-bold">Dirección</label>
                                    <input
                                        id="address"
                                        v-model="form.address"
                                        type="text"
                                        class="form-control"
                                    >
                                </div>

                                <div v-if="error" class="alert alert-danger mb-3">{{ error }}</div>
                                <div v-if="success" class="alert alert-success mb-3">{{ success }}</div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary" :disabled="loading">
                                        <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                        {{ loading ? 'Guardando...' : 'Guardar Cambios' }}
                                    </button>
                                    <a href="/dashboard" class="btn btn-secondary">Cancelar</a>
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
            </div>
        </main>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
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

const form = ref({
    name: user.value.name || '',
    email: user.value.email || '',
    phone: user.value.phone || '',
    address: user.value.address || ''
});

const passwordForm = ref({
    current_password: '',
    password: '',
    password_confirmation: ''
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
        user.value.profile_photo_url = response.data.profile_photo_url;
        uploadMessage.value = 'Foto actualizada correctamente';
        setTimeout(() => {
            uploadMessage.value = '';
        }, 3000);
    } catch (err) {
        uploadMessage.value = 'Error al subir la foto';
        console.error(err);
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

onMounted(() => {
    // Cargar datos del usuario actual
    const storedUser = getStoredUser();
    if (storedUser) {
        user.value = storedUser;
        form.value = {
            name: storedUser.name || '',
            email: storedUser.email || '',
            phone: storedUser.phone || '',
            address: storedUser.address || ''
        };
    }
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
</style>
