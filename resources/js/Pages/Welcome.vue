<template>
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">Citas Telecomunicaciones</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link active" href="#">Inicio</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Citas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Cotizaciones</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container mt-5">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card shadow">
                        <div class="card-body text-center py-5">
                            <h1 class="card-title mb-3">¡Bienvenido!</h1>
                            <p class="card-text text-muted mb-4">Sistema de Gestión de Citas para Telecomunicaciones</p>
                            <button class="btn btn-primary btn-lg" @click="loadAppointments">
                                Ver Citas
                            </button>
                        </div>
                    </div>

                    <div class="row mt-5" v-if="appointments.length > 0">
                        <div class="col-md-6 mb-3" v-for="appointment in appointments" :key="appointment.id">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">{{ appointment.title }}</h5>
                                    <p class="card-text text-muted">Próxima cita: {{ appointment.date }}</p>
                                    <small class="text-success">Estado: Confirmada</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const appointments = ref([
    { id: 1, title: 'Instalación de línea', date: '2026-03-10' },
    { id: 2, title: 'Revisión técnica', date: '2026-03-15' },
    { id: 3, title: 'Consulta de equipos', date: '2026-03-20' },
]);

function loadAppointments() {
    console.log('Cargando citas...');
}
</script>
