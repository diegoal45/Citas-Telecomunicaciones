import { getStoredUser } from './services/auth';

/**
 * Plugin para proteger rutas que requieren autenticación
 * Se ejecuta cuando se intenta navegar a una ruta protegida
 */
export function checkAuthBeforeNavigate(path) {
    // Rutas que NO requieren autenticación
    const publicRoutes = ['/login', '/register', '/', '/'];
    
    // Rutas que SÍ requieren autenticación
    const protectedRoutes = ['/profile', '/settings', '/dashboard', '/admin', '/tecnico', '/technician'];
    
    // Si es ruta protegida, verificar token
    if (protectedRoutes.some(route => path.startsWith(route))) {
        const user = getStoredUser();
        const token = localStorage.getItem('auth_token');
        
        if (!user || !token) {
            // No tiene autenticación, redirigir a login
            window.location.href = '/login';
            return false;
        }
    }
    
    return true;
}
