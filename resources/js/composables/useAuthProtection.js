import { onMounted } from 'vue';
import { getStoredUser } from '../services/auth';

/**
 * Composable para proteger rutas que requieren autenticación
 * Se ejecuta al montar el componente y verifica si hay token
 */
export function useAuthProtection() {
    onMounted(() => {
        const token = localStorage.getItem('auth_token');
        const user = getStoredUser();
        
        if (!token || !user) {
            // No hay autenticación, redirigir a login
            window.location.href = '/login';
        }
    });
}
