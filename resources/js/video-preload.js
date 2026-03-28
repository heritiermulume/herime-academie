/**
 * Ajuste video.preload selon la connexion (Network Information API) pour limiter
 * la consommation data et laisser le navigateur bufferiser au fil de la lecture (HTTP Range).
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
            preload = 'none';
        } else if (
            conn.effectiveType === 'slow-2g' ||
            conn.effectiveType === '2g'
        ) {
            preload = 'none';
        } else if (conn.effectiveType === '3g' && preload === 'auto') {
            preload = 'metadata';
        }
    }
    videoEl.preload = preload;
}
