@php
    /** @var \Illuminate\Http\Request $request */
    $request = request();
    // Ne pas tracker l'admin par défaut
    if ($request->is('admin/*')) {
        $metaTrackingConfig = ['enabled' => false, 'pixels' => [], 'triggers' => [], 'context' => []];
    } else {
    $metaTrackingConfig = app(\App\Services\MetaTrackingService::class)->getClientConfig($request);
    }

    // Consent mode (prod hardening)
    $consentRequired = (bool) \App\Models\Setting::get('meta_consent_required', false);
    $consentCookieName = (string) \App\Models\Setting::get('meta_consent_cookie_name', 'meta_consent');
    $consentGranted = !$consentRequired || ($request->cookie($consentCookieName) === '1');

    // CAPI (prod hardening)
    $capiEnabled = (bool) \App\Models\Setting::get('meta_capi_enabled', false);
    $capiTestEventCode = (string) \App\Models\Setting::get('meta_capi_test_event_code', '');

    $metaTrackingConfig['consent'] = [
        'required' => $consentRequired,
        'cookie_name' => $consentCookieName,
        'granted' => $consentGranted,
    ];
    $metaTrackingConfig['capi'] = [
        'enabled' => $capiEnabled,
        'endpoint' => route('meta.capi'),
        'test_event_code' => $capiTestEventCode,
    ];
@endphp

@if(($metaTrackingConfig['enabled'] ?? false) && !empty($metaTrackingConfig['pixels']) && (($metaTrackingConfig['consent']['granted'] ?? true) === true))
    {{-- Meta Pixel base code (multi-pixels), events déclenchés dynamiquement via config DB --}}
    <script>
        (function(f,b,e,v,n,t,s){
            if(f.fbq) return; n=f.fbq=function(){ n.callMethod ?
                n.callMethod.apply(n,arguments) : n.queue.push(arguments) };
            if(!f._fbq) f._fbq=n; n.push=n; n.loaded=!0; n.version='2.0';
            n.queue=[]; t=b.createElement(e); t.async=!0;
            t.src=v; s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s);
        })(window, document,'script','https://connect.facebook.net/en_US/fbevents.js');
    </script>
@endif

@if(($metaTrackingConfig['enabled'] ?? false)
    && !empty($metaTrackingConfig['pixels'])
    && (($metaTrackingConfig['consent']['required'] ?? false) === true)
    && (($metaTrackingConfig['consent']['granted'] ?? true) === false))
    {{-- Bouton minimal de test consent (à remplacer plus tard par votre CMP/banner) --}}
    <div id="meta-consent-test"
         class="d-flex flex-column gap-2"
         style="position: fixed; right: 16px; bottom: 16px; z-index: 2147483647; max-width: 320px;">
        <button type="button" class="btn btn-dark btn-sm shadow" id="meta-consent-btn">
            Donner le consentement Meta
        </button>
        <div class="small text-muted bg-white bg-opacity-75 rounded px-2 py-1 shadow-sm">
            Test rapide: charge le Pixel et active les événements. À intégrer ensuite dans votre bannière cookies.
        </div>
    </div>
    <script>
        (function () {
            const btn = document.getElementById('meta-consent-btn');
            const wrap = document.getElementById('meta-consent-test');
            if (!btn || !wrap) return;
            btn.addEventListener('click', function () {
                try {
                    if (window.MetaTracking && typeof window.MetaTracking.grantConsent === 'function') {
                        window.MetaTracking.grantConsent().then(() => {
                            try { wrap.remove(); } catch (e) { wrap.style.display = 'none'; }
                        });
                    }
                } catch (e) {}
            });
        })();
    </script>
@endif

<script>
    window.__META_TRACKING__ = @json($metaTrackingConfig);

    (function() {
        const cfg = window.__META_TRACKING__;
        if (!cfg || !cfg.enabled) return;

        function hasConsent() {
            const c = cfg && cfg.consent ? cfg.consent : null;
            return !c || !c.required || !!c.granted;
        }

        function setConsentCookie() {
            const c = cfg && cfg.consent ? cfg.consent : null;
            if (!c || !c.cookie_name) return;
            // 180 jours
            const days = 180;
            const expires = new Date(Date.now() + days * 864e5).toUTCString();
            document.cookie = encodeURIComponent(c.cookie_name) + '=1; expires=' + expires + '; path=/; SameSite=Lax';
            try { cfg.consent.granted = true; } catch (e) {}
        }

        function loadFbqBaseCode() {
            if (typeof window.fbq === 'function') return Promise.resolve();
            return new Promise(resolve => {
                // Inject the standard Meta base script
                (function(f,b,e,v,n,t,s){
                    if(f.fbq) return; n=f.fbq=function(){ n.callMethod ?
                        n.callMethod.apply(n,arguments) : n.queue.push(arguments) };
                    if(!f._fbq) f._fbq=n; n.push=n; n.loaded=!0; n.version='2.0';
                    n.queue=[]; t=b.createElement(e); t.async=!0;
                    t.src=v; s=b.getElementsByTagName(e)[0];
                    s.parentNode.insertBefore(t,s);
                })(window, document,'script','https://connect.facebook.net/en_US/fbevents.js');

                // naive wait until fbq exists
                const start = Date.now();
                (function poll() {
                    if (typeof window.fbq === 'function') return resolve();
                    if (Date.now() - start > 5000) return resolve();
                    setTimeout(poll, 50);
                })();
            });
        }

        const pixels = (Array.isArray(cfg.pixels) ? cfg.pixels : [])
            .slice()
            .sort((a, b) => (Number(b?.priority || 0) - Number(a?.priority || 0)));
        function initPixels() {
            if (typeof window.fbq !== 'function') return;
            pixels.forEach(p => {
                if (p && p.pixel_id) {
                    try { window.fbq('init', String(p.pixel_id)); } catch (e) {}
                }
            });
        }

        const triggers = (Array.isArray(cfg.triggers) ? cfg.triggers : [])
            .slice()
            .sort((a, b) => (Number(b?.priority || 0) - Number(a?.priority || 0)));
        if (!triggers.length) return;

        const fired = new Set();

        function safeJsonParse(raw) {
            try { return JSON.parse(raw); } catch (e) { return null; }
        }

        function datasetToPayload(el) {
            if (!el || !el.dataset) return {};
            const out = {};
            // Support:
            // - data-meta-payload='{"value":123,"currency":"USD"}'
            // - data-meta-value="123" (=> { value: "123" })
            const raw = el.getAttribute && el.getAttribute('data-meta-payload');
            if (raw) {
                const parsed = safeJsonParse(raw);
                if (parsed && typeof parsed === 'object') {
                    Object.assign(out, parsed);
                }
            }
            Object.keys(el.dataset).forEach(k => {
                if (!k.startsWith('meta') || k === 'metaPayload') return;
                const key = k.slice(4);
                if (!key) return;
                const normalizedKey = key.charAt(0).toLowerCase() + key.slice(1);
                out[normalizedKey] = el.dataset[k];
            });
            return out;
        }

        function mergePayload(base, extra) {
            const out = Object.assign({}, base || {});
            if (extra && typeof extra === 'object') {
                Object.keys(extra).forEach(k => { out[k] = extra[k]; });
            }
            return out;
        }

        function uuid() {
            try {
                if (window.crypto && typeof window.crypto.randomUUID === 'function') {
                    return window.crypto.randomUUID();
                }
            } catch (e) {}
            return String(Date.now()) + '-' + Math.random().toString(16).slice(2);
        }

        function capiSend(eventName, payload, pixelIds, eventId) {
            const capi = cfg && cfg.capi ? cfg.capi : null;
            if (!capi || !capi.enabled || !capi.endpoint) return;
            if (!hasConsent()) return;

            const ids = Array.isArray(pixelIds) ? pixelIds.filter(Boolean).map(String) : null;
            const pixelsToSend = (ids && ids.length) ? ids : pixels.map(p => String(p.pixel_id));

            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            fetch(String(capi.endpoint), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    event_name: String(eventName),
                    event_id: String(eventId),
                    payload: payload && typeof payload === 'object' ? payload : {},
                    pixel_ids: pixelsToSend,
                    event_source_url: window.location.href,
                }),
            }).catch(() => {});
        }

        function sendToMeta(eventName, payload, pixelIds, eventId) {
            if (typeof window.fbq !== 'function') return;
            const p = payload && typeof payload === 'object' ? payload : {};
            const ids = Array.isArray(pixelIds) ? pixelIds.filter(Boolean).map(String) : null;

            try {
                if (ids && ids.length) {
                    ids.forEach(id => window.fbq('trackSingle', id, String(eventName), p, { eventID: String(eventId) }));
                } else {
                    window.fbq('track', String(eventName), p, { eventID: String(eventId) });
                }
            } catch (e) {}
        }

        function fire(trigger, element) {
            if (!trigger || !trigger.event_name) return;
            if (!hasConsent()) return;
            const uniqKey = String(trigger.id || trigger.event_name) + '::' + String(trigger.trigger_type || '');
            if (trigger.once_per_page && fired.has(uniqKey)) return;

            const payloadFromCfg = trigger.payload || {};
            const payloadFromEl = datasetToPayload(element);
            const finalPayload = mergePayload(payloadFromCfg, payloadFromEl);
            const eventId = 'mt_' + String(trigger.id || 'x') + '_' + uuid();
            sendToMeta(trigger.event_name, finalPayload, trigger.pixel_ids, eventId);
            capiSend(trigger.event_name, finalPayload, trigger.pixel_ids, eventId);

            if (trigger.once_per_page) fired.add(uniqKey);
        }

        // click (delegation) + form_submit listeners
        let listenersBound = false;
        function bindListeners() {
            if (listenersBound) return;
            listenersBound = true;

            const clickTriggers = triggers.filter(t => t.trigger_type === 'click' && t.css_selector);
            if (clickTriggers.length) {
                document.addEventListener('click', function(ev) {
                    const target = ev.target;
                    clickTriggers.forEach(t => {
                        try {
                            const el = target && target.closest ? target.closest(t.css_selector) : null;
                            if (el) fire(t, el);
                        } catch (e) {}
                    });
                }, { capture: true });
            }

            const submitTriggers = triggers.filter(t => t.trigger_type === 'form_submit' && t.css_selector);
            if (submitTriggers.length) {
                document.addEventListener('submit', function(ev) {
                    const form = ev.target;
                    submitTriggers.forEach(t => {
                        try {
                            if (form && form.matches && form.matches(t.css_selector)) {
                                fire(t, form);
                            }
                        } catch (e) {}
                    });
                }, { capture: true });
            }
        }

        // Public API for consent-driven init
        window.MetaTracking = window.MetaTracking || {};
        window.MetaTracking.grantConsent = function () {
            setConsentCookie();
            return loadFbqBaseCode().then(() => {
                initPixels();
                bindListeners();
                // fire page_load triggers after consent (single-shot)
                triggers.filter(t => t.trigger_type === 'page_load').forEach(t => fire(t, null));
            });
        };

        // If no consent required (or already granted), init immediately
        if (!hasConsent()) {
            return;
        }

        // Ensure fbq is present (base code may have been injected by Blade)
        loadFbqBaseCode().then(() => {
            initPixels();
            bindListeners();
            // page_load
            triggers.filter(t => t.trigger_type === 'page_load').forEach(t => fire(t, null));
        });
    })();
</script>

