/**
 * Profil réseau grossier (Network Information API) pour HLS / preload.
 * @returns {'slow'|'medium'|'fast'}
 */
export function getNetworkPlaybackProfile() {
    const conn =
        navigator.connection ||
        navigator.mozConnection ||
        navigator.webkitConnection;
    if (!conn) {
        return 'medium';
    }
    if (
        conn.saveData === true ||
        conn.effectiveType === 'slow-2g' ||
        conn.effectiveType === '2g'
    ) {
        return 'slow';
    }
    if (conn.effectiveType === '3g') {
        return 'medium';
    }
    return 'fast';
}

/**
 * Ajuste video.preload selon la connexion (Network Information API) pour limiter
 * la consommation data et laisser le navigateur bufferiser au fil de la lecture (HTTP Range).
 *
 * Sur 2G / slow-2G on garde `metadata` (pas `none`) : la durée et un minimum de contexte
 * se chargent avant lecture, ce qui évite l’impression que « rien ne se passe » au premier clic.
 *
 * @param {HTMLVideoElement} videoEl
 * @param {'none'|'metadata'|'auto'} basePreload Valeur issue de la config serveur
 */
export function adjustVideoPreloadForConnection(videoEl, basePreload) {
    if (!videoEl || videoEl.tagName !== 'VIDEO') {
        return;
    }
    const allowed = ['none', 'metadata', 'auto'];
    let preload = allowed.includes(basePreload) ? basePreload : 'metadata';
    const conn =
        navigator.connection ||
        navigator.mozConnection ||
        navigator.webkitConnection;
    if (conn) {
        if (conn.saveData === true) {
            preload = 'metadata';
        } else if (
            conn.effectiveType === 'slow-2g' ||
            conn.effectiveType === '2g'
        ) {
            preload = 'metadata';
        } else if (conn.effectiveType === '3g' && preload === 'auto') {
            preload = 'metadata';
        }
    }
    videoEl.preload = preload;
}
