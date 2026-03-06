<template>
    <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-5 col-lg-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <!-- Logo/Título -->
                            <div class="text-center mb-4">
                                <h2 class="fw-bold text-primary">Bienvenido</h2>
                                <p class="text-muted">ExCitel</p>
                            </div>

                            <!-- Mensaje de error -->
                            <div v-if="errorMessage" class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-circle"></i> {{ errorMessage }}
                                <button type="button" class="btn-close" @click="errorMessage = ''"></button>
                            </div>

                            <!-- Formulario -->
                            <form @submit.prevent="handleLogin">
                                <!-- Email -->
                                <div class="mb-3">
                                    <label for="email" class="form-label">Correo electrónico</label>
                                    <input 
                                        type="email" 
                                        class="form-control" 
                                        :class="{ 'is-invalid': errors.email }"
                                        id="email" 
                                        v-model="form.email" 
                                        placeholder="ejemplo@correo.com"
                                        required
                                    >
                                    <div v-if="errors.email" class="invalid-feedback">
                                        {{ errors.email }}
                                    </div>
                                </div>

                                <!-- Password -->
                                <div class="mb-3">
                                    <label for="password" class="form-label">Contraseña</label>
                                    <input 
                                        type="password" 
                                        class="form-control" 
                                        :class="{ 'is-invalid': errors.password }"
                                        id="password" 
                                        v-model="form.password" 
                                        placeholder="••••••••"
                                        required
                                    >
                                    <div v-if="errors.password" class="invalid-feedback">
                                        {{ errors.password }}
                                    </div>
                                </div>

                                <!-- Recordarme -->
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="remember" v-model="form.remember">
                                    <label class="form-check-label" for="remember">
                                        Recordarme
                                    </label>
                                </div>

                                <!-- Botón Submit -->
                                <button 
                                    type="submit" 
                                    class="btn btn-primary w-100 py-2 mb-3"
                                    :disabled="loading"
                                >
                                    <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                    {{ loading ? 'Iniciando sesión...' : 'Iniciar Sesión' }}
                                </button>

                                <!-- Link a registro -->
                                <div class="text-center">
                                    <small class="text-muted">
                                        ¿No tienes cuenta? 
                                        <a href="/register" class="text-decoration-none">Regístrate aquí</a>
                                    </small>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="text-center mt-3">
                        <small class="text-muted">© 2026 ExCitel</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import api from '../../services/api';
import { saveAuthSession } from '../../services/auth';

const form = ref({
    email: '',
    password: '',
    remember: false
});

const errors = ref({});
const errorMessage = ref('');
const loading = ref(false);

const handleLogin = async () => {
    loading.value = true;
    errors.value = {};
    errorMessage.value = '';

    try {
        const response = await api.post('/api/login', {
            email: form.value.email,
            password: form.value.password,
        });

        saveAuthSession(response.data);

        // Redirigir segun rol
        const roleName = response.data?.user?.role?.name;
        if (roleName === 'admin') {
            window.location.href = '/admin/dashboard';
        } else {
            window.location.href = '/dashboard';
        }
        
    } catch (error) {
        loading.value = false;
        
        if (error.response) {
            // Errores de validación (422)
            if (error.response.status === 422) {
                errors.value = error.response.data.errors || {};
            }
            // Error de credenciales (401)
            else if (error.response.status === 401) {
                errorMessage.value = 'Credenciales incorrectas. Verifica tu correo y contraseña.';
            }
            // Otros errores
            else {
                errorMessage.value = error.response.data.message || 'Error al iniciar sesión. Intenta de nuevo.';
            }
        } else {
            errorMessage.value = 'No se pudo conectar con el servidor. Verifica tu conexión.';
        }
    }
};
</script>

<style scoped>
.min-vh-100 {
    min-height: 100vh;
}

.card {
    border-radius: 12px;
}

.btn-primary {
    border-radius: 8px;
}

.form-control {
    border-radius: 6px;
}

.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
}
</style>
