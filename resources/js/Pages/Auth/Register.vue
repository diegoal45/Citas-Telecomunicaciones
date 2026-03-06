<template>
    <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light py-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-7 col-lg-5">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4 p-md-5">
                            <div class="text-center mb-4">
                                <h2 class="fw-bold text-primary">Crear Cuenta</h2>
                                <p class="text-muted mb-0">Comienza a usar Citas Telecomunicaciones</p>
                            </div>

                            <div v-if="errorMessage" class="alert alert-danger" role="alert">
                                {{ errorMessage }}
                            </div>

                            <form @submit.prevent="handleRegister">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nombre completo</label>
                                    <input id="name" v-model="form.name" type="text" class="form-control" :class="{ 'is-invalid': errors.name }" required>
                                    <div v-if="errors.name" class="invalid-feedback">{{ errors.name }}</div>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Correo</label>
                                    <input id="email" v-model="form.email" type="email" class="form-control" :class="{ 'is-invalid': errors.email }" required>
                                    <div v-if="errors.email" class="invalid-feedback">{{ errors.email }}</div>
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">Telefono (opcional)</label>
                                    <input id="phone" v-model="form.phone" type="text" class="form-control" :class="{ 'is-invalid': errors.phone }">
                                    <div v-if="errors.phone" class="invalid-feedback">{{ errors.phone }}</div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="password" class="form-label">Contrasena</label>
                                        <input id="password" v-model="form.password" type="password" class="form-control" :class="{ 'is-invalid': errors.password }" required>
                                        <div v-if="errors.password" class="invalid-feedback">{{ errors.password }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="password_confirmation" class="form-label">Confirmar contrasena</label>
                                        <input id="password_confirmation" v-model="form.password_confirmation" type="password" class="form-control" required>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-2 mt-2" :disabled="loading">
                                    <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                    {{ loading ? 'Registrando...' : 'Crear cuenta' }}
                                </button>
                            </form>

                            <p class="text-center mt-4 mb-0 text-muted">
                                Ya tienes cuenta? <a href="/login" class="text-decoration-none">Inicia sesion</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const form = ref({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

const errors = ref({});
const errorMessage = ref('');
const loading = ref(false);

const handleRegister = async () => {
    loading.value = true;
    errors.value = {};
    errorMessage.value = '';

    try {
        const response = await axios.post('/api/register', form.value);

        if (response.data.token) {
            localStorage.setItem('auth_token', response.data.token);
            localStorage.setItem('user', JSON.stringify(response.data.user));
        }

        window.location.href = '/dashboard';
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
        } else {
            errorMessage.value = error.response?.data?.message || 'No se pudo registrar el usuario.';
        }
    } finally {
        loading.value = false;
    }
};
</script>
