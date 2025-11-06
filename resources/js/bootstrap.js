import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Intercepteur pour gérer les erreurs 401 (non autorisé) pour /me et /logout
// Ces endpoints peuvent retourner 401 si l'utilisateur n'est pas authentifié
// On ignore silencieusement ces erreurs pour éviter le bruit dans la console
window.axios.interceptors.response.use(
    response => response,
    error => {
        // Ignorer silencieusement les erreurs 401 pour /me et /logout
        if (error.response && error.response.status === 401) {
            const url = error.config?.url || '';
            if (url.includes('/me') || url.includes('/logout')) {
                // Retourner une réponse vide au lieu de rejeter
                return Promise.resolve({ 
                    data: null, 
                    status: 200, 
                    statusText: 'OK',
                    headers: {},
                    config: error.config
                });
            }
        }
        return Promise.reject(error);
    }
);
