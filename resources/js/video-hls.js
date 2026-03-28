import Hls from 'hls.js';

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

        if (Hls.isSupported()) {
            const hls = new Hls({
                capLevelToPlayerSize: true,
                maxBufferLength: 30,
                maxMaxBufferLength: 120,
                abrEwmaDefaultEstimate: 400000,
                enableWorker: true,
            });
            videoEl._herimeHls = hls;
            hls.loadSource(hlsUrl);
            hls.attachMedia(videoEl);
            hls.on(Hls.Events.MANIFEST_PARSED, done);
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
