<template>
    <div class="bg-body-tertiary min-vh-100">
        <nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
            <div class="container-fluid px-3 px-md-4">
                <a class="navbar-brand fw-bold text-primary" href="/dashboard">Citas Telecom</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div id="mainNav" class="collapse navbar-collapse">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item"><a class="nav-link active" href="/dashboard">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Citas</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Cotizaciones</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Equipos</a></li>
                    </ul>
                    <a href="/login" class="btn btn-outline-primary btn-sm">Cambiar usuario</a>
                </div>
            </div>
        </nav>

        <main class="container-fluid px-3 px-md-4 py-4">
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6 col-xl-3" v-for="card in stats" :key="card.title">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-muted mb-2">{{ card.title }}</p>
                            <h3 class="mb-1">{{ card.value }}</h3>
                            <small :class="card.trendClass">{{ card.trend }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-xl-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Proximas citas</h5>
                            <button class="btn btn-primary btn-sm">Nueva cita</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in appointments" :key="item.id">
                                        <td>{{ item.client }}</td>
                                        <td>{{ item.date }}</td>
                                        <td>{{ item.type }}</td>
                                        <td><span class="badge" :class="statusClass(item.status)">{{ item.status }}</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0">
                            <h5 class="mb-0">Actividad reciente</h5>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item" v-for="n in notifications" :key="n.id">
                                <p class="mb-1 fw-semibold">{{ n.title }}</p>
                                <small class="text-muted">{{ n.time }}</small>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
const stats = [
    { title: 'Citas hoy', value: 14, trend: '+2 vs ayer', trendClass: 'text-success' },
    { title: 'Pendientes', value: 8, trend: '-1 vs ayer', trendClass: 'text-success' },
    { title: 'Cotizaciones', value: 5, trend: '+3 esta semana', trendClass: 'text-primary' },
    { title: 'Equipos activos', value: 4, trend: '100% operativos', trendClass: 'text-success' },
];

const appointments = [
    { id: 1, client: 'Ana Gomez', date: '2026-03-06 09:00', type: 'Instalacion', status: 'Programada' },
    { id: 2, client: 'Carlos Ruiz', date: '2026-03-06 11:30', type: 'Mantenimiento', status: 'Cotizada' },
    { id: 3, client: 'Laura Diaz', date: '2026-03-06 15:00', type: 'Revision', status: 'Programada' },
];

const notifications = [
    { id: 1, title: 'Nueva cotizacion aprobada', time: 'hace 10 minutos' },
    { id: 2, title: 'Cita reasignada al equipo B', time: 'hace 35 minutos' },
    { id: 3, title: 'Cliente confirmo horario', time: 'hace 1 hora' },
];

const statusClass = (status) => {
    if (status === 'Programada') return 'text-bg-primary';
    if (status === 'Cotizada') return 'text-bg-warning';
    return 'text-bg-secondary';
};
</script>
