<template>
    <div class="app-shell min-vh-100" :class="pageClass">
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
            <div class="container-fluid px-3 px-md-4">
                <a class="navbar-brand fw-bold cursor-pointer" @click.prevent="navigateToHome" style="cursor: pointer;">
                    <i :class="`${brandIcon} me-2`"></i>{{ brandLabel }}
                </a>

                <div class="d-flex align-items-center gap-2 ms-auto">
                    <slot name="nav-actions" />

                    <div v-if="showUserMenu" class="user-menu-wrapper">
                        <button class="btn btn-light btn-sm d-flex align-items-center gap-2 px-2 py-1" @click="toggleMenu">
                            <span class="avatar-mini d-inline-flex align-items-center justify-content-center overflow-hidden">
                                <img
                                    v-if="displayUser.profile_photo_url"
                                    :src="displayUser.profile_photo_url"
                                    :alt="displayUser.name || 'Usuario'"
                                    class="w-100 h-100 object-fit-cover"
                                >
                                <i v-else class="bi bi-person-fill"></i>
                            </span>
                            <span class="d-none d-md-inline">{{ displayUser.name || 'Mi Cuenta' }}</span>
                            <i class="bi bi-chevron-down small"></i>
                        </button>

                        <div class="user-dropdown" :class="{ show: menuOpen }">
                            <button @click="router.visit('/profile')" class="dropdown-item" style="background: none; border: none; cursor: pointer; text-align: left; width: 100%;">
                                <i class="bi bi-person-circle me-2"></i>Mi Perfil
                            </button>
                            <button @click="router.visit('/settings')" class="dropdown-item" style="background: none; border: none; cursor: pointer; text-align: left; width: 100%;">
                                <i class="bi bi-gear me-2"></i>Configuracion
                            </button>
                            <hr class="dropdown-divider my-1">
                            <button class="dropdown-item text-danger" @click="handleLogout">
                                <i class="bi bi-box-arrow-right me-2"></i>Cerrar sesion
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <slot />
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import api from '../services/api';
import { clearAuthSession, getStoredUser } from '../services/auth';

const props = defineProps({
    brandHref: {
        type: String,
        default: '/dashboard',
    },
    brandLabel: {
        type: String,
        default: 'ExCitel',
    },
    brandIcon: {
        type: String,
        default: 'bi bi-telephone-fill',
    },
    pageClass: {
        type: String,
        default: 'bg-light',
    },
    showUserMenu: {
        type: Boolean,
        default: true,
    },
    user: {
        type: Object,
        default: null,
    },
});

const menuOpen = ref(false);
const storedUser = ref(getStoredUser());

const displayUser = computed(() => {
    if (props.user && Object.keys(props.user).length > 0) {
        return props.user;
    }

    return storedUser.value || { name: 'Mi Cuenta', profile_photo_url: null };
});

const toggleMenu = () => {
    menuOpen.value = !menuOpen.value;
};

const navigateToHome = () => {
    // Usar Inertia para navegar manteniendo el estado de la SPA
    router.visit(props.brandHref);
};

const handleLogout = async () => {
    try {
        await api.post('/api/logout');
    } catch (error) {
        // Limpiamos sesion local incluso si el endpoint falla.
    } finally {
        clearAuthSession();
        window.location.href = '/login';
    }
};
</script>

<style scoped>
.app-shell {
    position: relative;
}

.user-menu-wrapper {
    position: relative;
}

.avatar-mini {
    width: 26px;
    height: 26px;
    border-radius: 6px;
    background: rgba(13, 110, 253, 0.12);
}

.user-dropdown {
    position: absolute;
    top: calc(100% + 0.5rem);
    right: 0;
    min-width: 220px;
    background: #fff;
    border-radius: 10px;
    border: 1px solid rgba(0, 0, 0, 0.08);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-6px);
    transition: all 0.2s ease;
    z-index: 1060;
    padding: 0.35rem;
}

.user-dropdown.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.user-dropdown .dropdown-item {
    border-radius: 8px;
}
</style>
