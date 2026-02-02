@php
    /** @var \Illuminate\Http\Request $request */
    $request = request();

    // Ne pas tracker l'admin
    $isAdmin = $request->is('admin/*');

    $enabled = !$isAdmin && (bool) \App\Models\Setting::get('meta_tracking_enabled', false);

    // Les IDs Pixel proviennent uniquement de la BDD
    $pixelIds = $enabled
        ? \App\Models\MetaPixel::query()
            ->where('is_active', true)
            ->orderByDesc('id')
            ->pluck('pixel_id')
            ->filter(fn ($v) => is_string($v) && trim($v) !== '')
            ->map(fn ($v) => trim((string) $v))
            ->values()
        : collect();

    // Les événements autres que PageView peuvent être chargés dynamiquement via la BDD (triggers)
    $metaTriggers = [];
    if ($enabled && $pixelIds->count() > 0) {
        $metaTriggers = \App\Models\MetaEventTrigger::query()
            ->with(['event:id,event_name,is_active,default_payload'])
            ->where('is_active', true)
            ->orderByDesc('id')
            ->get()
            ->filter(function (\App\Models\MetaEventTrigger $t) {
                if (!$t->event || !$t->event->is_active) {
                    return false;
                }
                // PageView est envoyé via le snippet officiel (donc on évite le double-envoi).
                return $t->event->event_name !== 'PageView';
            })
            ->values()
            ->map(function (\App\Models\MetaEventTrigger $t) {
                $eventPayload = is_array($t->event->default_payload) ? $t->event->default_payload : [];
                $triggerPayload = is_array($t->payload) ? $t->payload : [];
                return [
                    'id' => $t->id,
                    'trigger_type' => $t->trigger_type,
                    'css_selector' => $t->css_selector,
                    'match_path_pattern' => $t->match_path_pattern,
                    'event_name' => $t->event->event_name,
                    'payload' => array_replace_recursive($eventPayload, $triggerPayload),
                    'pixel_ids' => is_array($t->pixel_ids) ? array_values(array_filter($t->pixel_ids)) : null,
                    'once_per_page' => (bool) $t->once_per_page,
                ];
            })
            ->all();
    }
@endphp

@if($enabled && $pixelIds->count() > 0)
    <!-- Meta Pixel Code -->
    <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    @foreach($pixelIds as $pid)
    fbq('init', '{{ $pid }}');
    @endforeach
    fbq('track', 'PageView');
    </script>
    <noscript>
        @foreach($pixelIds as $pid)
            <img height="1" width="1" style="display:none"
                 src="https://www.facebook.com/tr?id={{ $pid }}&ev=PageView&noscript=1"
            />
        @endforeach
    </noscript>
    <!-- End Meta Pixel Code -->

    @if(!empty($metaTriggers))
        <script>
            window.__META_TRIGGERS__ = @json($metaTriggers);

            (function () {
                if (typeof window.fbq !== 'function') return;
                const triggers = window.__META_TRIGGERS__;
                if (!Array.isArray(triggers) || !triggers.length) return;

                const fired = new Set();

                function normalizePath(p) {
                    p = String(p || '').trim();
                    if (p === '') return '/';
                    // keep "/" for root, otherwise remove leading "/"
                    return p === '/' ? '/' : p.replace(/^\/+/, '');
                }

                function pathMatches(currentPath, pattern) {
                    const pat = normalizePath(pattern);
                    if (!pat || pat === '/') {
                        return normalizePath(currentPath) === '/';
                    }
                    const cur = normalizePath(currentPath);
                    // wildcard "*" match
                    const escaped = pat.replace(/[.+^${}()|[\]\\]/g, '\\$&').replace(/\*/g, '.*');
                    const re = new RegExp('^' + escaped + '$');
                    return re.test(cur);
                }

                function triggerAppliesToCurrentPage(t) {
                    const pat = t && t.match_path_pattern ? String(t.match_path_pattern).trim() : '';
                    if (pat === '__all__') return true;
                    if (!pat) return true;
                    const cur = window.location && window.location.pathname ? window.location.pathname : '/';
                    return pathMatches(cur, pat);
                }

                function fire(t, el) {
                    if (!t || !t.event_name) return;
                    if (!triggerAppliesToCurrentPage(t)) return;
                    const uniqKey = String(t.id || t.event_name) + '::' + String(t.trigger_type || '');
                    if (t.once_per_page && fired.has(uniqKey)) return;

                    const payload = (t.payload && typeof t.payload === 'object') ? t.payload : {};

                    try {
                        if (Array.isArray(t.pixel_ids) && t.pixel_ids.length) {
                            t.pixel_ids.forEach(function (id) {
                                try { window.fbq('trackSingle', String(id), String(t.event_name), payload); } catch (e) {}
                            });
                        } else {
                            window.fbq('track', String(t.event_name), payload);
                        }
                    } catch (e) {}

                    if (t.once_per_page) fired.add(uniqKey);
                }

                // page_load
                triggers.filter(t => t.trigger_type === 'page_load').forEach(t => fire(t, null));

                // click (delegation)
                const clickTriggers = triggers.filter(t => t.trigger_type === 'click' && t.css_selector);
                if (clickTriggers.length) {
                    document.addEventListener('click', function (ev) {
                        const target = ev.target;
                        clickTriggers.forEach(function (t) {
                            try {
                                if (!triggerAppliesToCurrentPage(t)) return;
                                const el = target && target.closest ? target.closest(t.css_selector) : null;
                                if (el) fire(t, el);
                            } catch (e) {}
                        });
                    }, { capture: true });
                }

                // form_submit
                const submitTriggers = triggers.filter(t => t.trigger_type === 'form_submit' && t.css_selector);
                if (submitTriggers.length) {
                    document.addEventListener('submit', function (ev) {
                        const form = ev.target;
                        submitTriggers.forEach(function (t) {
                            try {
                                if (!triggerAppliesToCurrentPage(t)) return;
                                if (form && form.matches && form.matches(t.css_selector)) {
                                    fire(t, form);
                                }
                            } catch (e) {}
                        });
                    }, { capture: true });
                }
            })();
        </script>
    @endif
@endif

