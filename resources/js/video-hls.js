import Hls from 'hls.js';
import { getNetworkPlaybackProfile } from './video-preload';

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

/**
 * Branche hls.js (ou HLS natif Safari) sur un <video>, avec repli MP4 progressif.
 *
 * @param {HTMLVideoElement} videoEl
 * @param {string|null|undefined} hlsUrl
 * @param {string|null|undefined} fallbackSrc URL MP4 (FileController)
 * @param {string} fallbackMime ex. video/mp4
 * @returns {Promise<void>}
 */
export function attachHlsToVideo(videoEl, hlsUrl, fallbackSrc, fallbackMime = 'video/mp4') {
    if (!videoEl || !hlsUrl) {
        return Promise.resolve();
    }

    return new Promise((resolve) => {
        const done = () => resolve();
        const profile = getNetworkPlaybackProfile();

        if (Hls.isSupported()) {
            const hls = new Hls(buildHlsConfig(profile));
            videoEl._herimeHls = hls;
            hls.loadSource(hlsUrl);
            hls.attachMedia(videoEl);
            hls.on(Hls.Events.MANIFEST_PARSED, () => {
                if (profile === 'slow' && hls.levels && hls.levels.length > 0) {
                    hls.startLevel = 0;
                }
                done();
            });
            hls.on(Hls.Events.ERROR, (_, data) => {
                if (!data.fatal) {
                    return;
                }
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
                    try {
                        videoEl.load();
                    } catch (e) {}
                }
                done();
            });
            return;
        }

        if (videoEl.canPlayType('application/vnd.apple.mpegurl')) {
            videoEl.src = hlsUrl;
            done();
            return;
        }

        if (fallbackSrc) {
            const s = document.createElement('source');
            s.src = fallbackSrc;
            s.type = fallbackMime;
            videoEl.appendChild(s);
            try {
                videoEl.load();
            } catch (e) {}
        }
        done();
    });
}
