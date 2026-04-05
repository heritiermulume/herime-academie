import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Import Plyr for custom video player
import Plyr from 'plyr';
import 'plyr/dist/plyr.css';
import {
    adjustVideoPreloadForConnection,
    getNetworkPlaybackProfile,
} from './video-preload';
import { attachHlsToVideo } from './video-hls';

window.Plyr = Plyr;
window.adjustVideoPreloadForConnection = adjustVideoPreloadForConnection;
window.herimeGetNetworkPlaybackProfile = getNetworkPlaybackProfile;
window.herimeAttachHlsToVideo = attachHlsToVideo;

/**
 * Vidéos HTML5 « simples » (hors Plyr) : HLS adaptatif (data-hls-url) ou MP4 + preload selon la connexion.
 */
async function initHerimeStreamVideos(root = document) {
    const raw = document.body?.dataset?.herimeVideoPreload || 'metadata';
    const allowed = ['none', 'metadata', 'auto'];
    const base = allowed.includes(raw) ? raw : 'metadata';
    const videos = Array.from(root.querySelectorAll('video.herime-stream-video'));
    for (const videoEl of videos) {
        const hls = videoEl.getAttribute('data-hls-url');
        const fb = videoEl.getAttribute('data-fallback-src');
        if (hls && hls.trim() !== '' && typeof window.herimeAttachHlsToVideo === 'function') {
            await window.herimeAttachHlsToVideo(
                videoEl,
                hls.trim(),
                fb || '',
                'video/mp4',
            );
        } else {
            adjustVideoPreloadForConnection(videoEl, base);
            try {
                videoEl.load();
            } catch (e) {
                /* ignore */
            }
        }
    }
}

window.initHerimeStreamVideos = initHerimeStreamVideos;

document.addEventListener('DOMContentLoaded', () => {
    void initHerimeStreamVideos();
});

function herimeClosestElement(target, selector) {
    if (!target) {
        return null;
    }
    const el = target.nodeType === Node.ELEMENT_NODE ? target : target.parentElement;
    return el?.closest?.(selector) ?? null;
}

document.addEventListener('click', function (e) {
    const btn = herimeClosestElement(e.target, '.add-package-to-cart-btn');
    if (!btn || btn.disabled) {
        return;
    }
    e.preventDefault();
    e.stopPropagation();
    const id = btn.getAttribute('data-package-id');
    if (!id) {
        return;
    }
    const url = document.body?.dataset?.cartAddUrl;
    if (!url) {
        return;
    }
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    btn.disabled = true;
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ package_id: parseInt(id, 10) }),
    })
        .then((r) => r.json())
        .then((data) => {
            if (data.success) {
                if (typeof window.updateCartCount === 'function') {
                    window.updateCartCount();
                }
                if (typeof window.showNotification === 'function') {
                    window.showNotification(data.message || 'Pack ajouté au panier', 'success');
                }
                // Page panier : même comportement que l’ajout d’un contenu (addToCartFromCartPage) — recharger pour afficher la ligne pack.
                if (document.querySelector('[data-reload-after-package-add="1"]')) {
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                    return;
                }
            } else {
                btn.disabled = false;
                if (typeof window.showNotification === 'function') {
                    window.showNotification(data.message || "Impossible d'ajouter le pack", 'error');
                }
            }
        })
        .catch(() => {
            btn.disabled = false;
        });
});
