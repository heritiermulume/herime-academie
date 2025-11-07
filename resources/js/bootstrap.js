import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;

let storedAuthorizationHeader = null;

// Intercepteur pour ajouter automatiquement l'en-tête Authorization et withCredentials
window.axios.interceptors.request.use(
    config => {
        // Toujours envoyer les cookies (SSO, sessions cross-domain)
        config.withCredentials = true;

        // Propager l'en-tête Authorization si nous l'avons reçu lors d'une redirection
        if (storedAuthorizationHeader && !config.headers?.Authorization) {
            config.headers = config.headers || {};
            config.headers.Authorization = storedAuthorizationHeader;
        }

        return config;
    },
    error => Promise.reject(error)
);

// Intercepteur pour gérer les erreurs 401 (non autorisé) pour /me et /logout
// Ces endpoints peuvent retourner 401 si l'utilisateur n'est pas authentifié
// On ignore silencieusement ces erreurs pour éviter le bruit dans la console
window.axios.interceptors.response.use(
    response => {
        // Mémoriser l'en-tête Authorization renvoyé par le backend (ex: après redirection SSO)
        const authorizationHeader = response.headers?.authorization || response.headers?.Authorization;
        if (authorizationHeader) {
            storedAuthorizationHeader = authorizationHeader;
        }
        return response;
    },
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

        // Mémoriser l'en-tête Authorization même en cas d'erreur (si présent)
        const errorAuthorizationHeader = error.response?.headers?.authorization || error.response?.headers?.Authorization;
        if (errorAuthorizationHeader) {
            storedAuthorizationHeader = errorAuthorizationHeader;
        }

        return Promise.reject(error);
    }
);
