export const saveAuthSession = (payload) => {
    if (payload?.token) {
        localStorage.setItem('auth_token', payload.token);
    }

    if (payload?.user) {
        localStorage.setItem('user', JSON.stringify(payload.user));
    }
};

export const clearAuthSession = () => {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
};

export const getStoredUser = () => {
    try {
        const raw = localStorage.getItem('user');
        return raw ? JSON.parse(raw) : null;
    } catch (error) {
        return null;
    }
};
