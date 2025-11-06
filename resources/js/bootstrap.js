import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Intercepteur pour gérer les erreurs 401 (non autorisé)
window.axios.interceptors.response.use(
    response => response,
    error => {
        // Ignorer silencieusement les erreurs 401 pour /me et /logout
        // car ces endpoints peuvent ne pas exister ou ne pas être nécessaires
        if (error.response && error.response.status === 401) {
            const url = error.config?.url || '';
            if (url.includes('/me') || url.includes('/logout')) {
                // Ignorer ces erreurs silencieusement
                console.debug('401 error ignored for:', url);
                return Promise.resolve({ data: null, status: 401, statusText: 'Unauthorized' });
            }
        }
        return Promise.reject(error);
    }
);
