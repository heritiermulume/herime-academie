import Hls from 'hls.js';
import { getNetworkPlaybackProfile } from './video-preload';

/**
 * Permet à l’UI (ex. shell d’aspect sur la page apprentissage) de recalculer la taille
 * une fois les dimensions intrinsèques disponibles (souvent après HLS / premier segment).
 */
function dispatchVideoDisplaySize(videoEl) {
    if (!videoEl || typeof CustomEvent === 'undefined') {
        return;
    }
    requestAnimationFrame(() => {
        try {
            videoEl.dispatchEvent(new CustomEvent('herime:video-display-size', { bubbles: true }));
        } catch {
            /* ignore */
        }
    });
}

function buildHlsConfig(profile) {
    const base = {
        capLevelToPlayerSize: true,
        enableWorker: true,
        lowLatencyMode: false,
        nudgeMaxRetry: 12,
        maxBufferHole: 0.5,
        startFragPrefetch: true,
    };

    if (profile === 'slow') {
        return {
            ...base,
            maxBufferLength: 60,
            maxMaxBufferLength: 300,
            abrEwmaDefaultEstimate: 160000,
            abrBandWidthFactor: 0.85,
            abrBandWidthUpFactor: 0.45,
            manifestLoadingTimeOut: 90000,
            manifestLoadingMaxRetry: 8,
            manifestLoadingRetryDelay: 2000,
            levelLoadingTimeOut: 90000,
            levelLoadingMaxRetry: 8,
            levelLoadingRetryDelay: 2000,
            fragLoadingTimeOut: 90000,
            fragLoadingMaxRetry: 10,
            fragLoadingRetryDelay: 2000,
        };
    }

    if (profile === 'medium') {
        return {
            ...base,
            maxBufferLength: 40,
            maxMaxBufferLength: 180,
            abrEwmaDefaultEstimate: 280000,
            abrBandWidthFactor: 0.9,
            abrBandWidthUpFactor: 0.55,
            manifestLoadingTimeOut: 45000,
            manifestLoadingMaxRetry: 6,
            manifestLoadingRetryDelay: 1500,
            levelLoadingTimeOut: 45000,
            fragLoadingTimeOut: 45000,
            fragLoadingMaxRetry: 8,
            fragLoadingRetryDelay: 1500,
        };
    }

    return {
        ...base,
        maxBufferLength: 30,
        maxMaxBufferLength: 120,
        abrEwmaDefaultEstimate: 400000,
        manifestLoadingTimeOut: 30000,
        fragLoadingTimeOut: 30000,
        fragLoadingMaxRetry: 6,
        fragLoadingRetryDelay: 1000,
    };
}

function resolveDeferUntilInteraction(videoEl, options) {
    if (videoEl?.dataset?.herimeHlsEager === '1') {
        return false;
    }
    if (options && typeof options.deferUntilInteraction === 'boolean') {
        return options.deferUntilInteraction;
    }
    if (typeof document !== 'undefined' && document.body?.dataset?.herimeHlsDefer === '0') {
        return false;
    }
    return true;
}

/**
 * Branche hls.js (ou HLS natif Safari) sur un <video>, avec repli MP4 progressif.
 *
 * Par défaut, aucune requête HLS n’est lancée avant la première interaction utilisateur
 * (pointerdown sur le conteneur ou lecture), pour éviter de lier la vidéo au chargement
 * initial du document (arrêt du chargement du navigateur).
 *
 * @param {HTMLVideoElement} videoEl
 * @param {string|null|undefined} hlsUrl
 * @param {string|null|undefined} fallbackSrc URL MP4 (FileController)
 * @param {string} fallbackMime ex. video/mp4
 * @param {{ deferUntilInteraction?: boolean }} [options]
 * @returns {Promise<void>}
 */
export function attachHlsToVideo(
    videoEl,
    hlsUrl,
    fallbackSrc,
    fallbackMime = 'video/mp4',
    options = {},
) {
    if (!videoEl || !hlsUrl) {
        return Promise.resolve();
    }

    const deferUntilInteraction = resolveDeferUntilInteraction(videoEl, options);

    if (deferUntilInteraction) {
        if (videoEl.dataset.herimeHlsDeferScheduled === '1') {
            return Promise.resolve();
        }
        videoEl.dataset.herimeHlsDeferScheduled = '1';

        const root =
            videoEl.closest('[data-herime-video-interact-root]') ||
            videoEl.parentElement ||
            videoEl;

        return new Promise((resolve) => {
            let attachPromise = null;
            let attachDone = false;

            const cleanup = () => {
                root.removeEventListener('pointerdown', onPointer, true);
                videoEl.removeEventListener('play', onPlay, true);
            };

            const ensureAttached = () => {
                if (attachDone) {
                    return Promise.resolve();
                }
                if (!attachPromise) {
                    attachPromise = attachHlsToVideo(
                        videoEl,
                        hlsUrl,
                        fallbackSrc,
                        fallbackMime,
                        { ...options, deferUntilInteraction: false },
                    ).then(() => {
                        attachDone = true;
                        cleanup();
                    });
                }
                return attachPromise;
            };

            const onPointer = () => {
                void ensureAttached();
            };

            const onPlay = () => {
                if (attachDone) {
                    return;
                }
                videoEl.pause();
                void ensureAttached().then(() => {
                    try {
                        void videoEl.play();
                    } catch (e) {
                        /* ignore */
                    }
                });
            };

            root.addEventListener('pointerdown', onPointer, { capture: true, once: true });
            videoEl.addEventListener('play', onPlay, { capture: true });
            resolve();
        });
    }

    return new Promise((resolve) => {
        const done = () => resolve();
        const profile = getNetworkPlaybackProfile();

        if (Hls.isSupported()) {
            const hls = new Hls(buildHlsConfig(profile));
            videoEl._herimeHls = hls;
            const onVideoMeta = () => dispatchVideoDisplaySize(videoEl);
            videoEl.addEventListener('loadedmetadata', onVideoMeta);
            hls.loadSource(hlsUrl);
            hls.attachMedia(videoEl);
            hls.on(Hls.Events.MANIFEST_PARSED, () => {
                if (profile === 'slow' && hls.levels && hls.levels.length > 0) {
                    hls.startLevel = 0;
                }
                dispatchVideoDisplaySize(videoEl);
                done();
            });
            hls.on(Hls.Events.ERROR, (_, data) => {
                if (!data.fatal) {
                    return;
                }
                videoEl.removeEventListener('loadedmetadata', onVideoMeta);
                hls.destroy();
                videoEl._herimeHls = null;
                if (fallbackSrc) {
                    videoEl.removeAttribute('data-hls-url');
                    while (videoEl.firstChild) {
                        videoEl.removeChild(videoEl.firstChild);
                    }
                    const s = document.createElement('source');
                    s.src = fallbackSrc;
                    s.type = fallbackMime;
                    videoEl.appendChild(s);
                    videoEl.addEventListener('loadedmetadata', onVideoMeta, { once: true });
                    try {
                        videoEl.load();
                    } catch (e) {}
                }
                done();
            });
            return;
        }

        if (videoEl.canPlayType('application/vnd.apple.mpegurl')) {
            videoEl.addEventListener('loadedmetadata', () => dispatchVideoDisplaySize(videoEl), { once: true });
            videoEl.src = hlsUrl;
            dispatchVideoDisplaySize(videoEl);
            done();
            return;
        }

        if (fallbackSrc) {
            const s = document.createElement('source');
            s.src = fallbackSrc;
            s.type = fallbackMime;
            videoEl.appendChild(s);
            videoEl.addEventListener('loadedmetadata', () => dispatchVideoDisplaySize(videoEl), { once: true });
            try {
                videoEl.load();
            } catch (e) {}
        }
        dispatchVideoDisplaySize(videoEl);
        done();
    });
}
