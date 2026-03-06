export const saveAuthSession = (payload) => {
    if (payload?.token) {
        localStorage.setItem('auth_token', payload.token);
        
        // También guardar en cookie para navegaciones normales
        document.cookie = `sanctum_token=${payload.token}; path=/; max-age=${7 * 24 * 60 * 60}; SameSite=Lax`;
    }

    if (payload?.user) {
        localStorage.setItem('user', JSON.stringify(payload.user));
    }
};

export const clearAuthSession = () => {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
    
    // Limpiar cookie
    document.cookie = 'sanctum_token=; path=/; max-age=0';
};

export const getStoredUser = () => {
    try {
        const raw = localStorage.getItem('user');
        return raw ? JSON.parse(raw) : null;
    } catch (error) {
        return null;
    }
};
