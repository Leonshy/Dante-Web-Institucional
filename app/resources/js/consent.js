// Consentimiento de cookies (docs/08-seo.md §6). Nada de GTM/GA4/Meta Pixel
// se carga hasta que esta función decida hacerlo — el banner en
// resources/views/components/cookie-consent.blade.php solo llama a estas
// funciones, nunca hay un <script src="...gtm..."> incondicional en el <head>.
const STORAGE_KEY = 'dante_consent';
const COOKIE_MAX_AGE_DAYS = 180;

function readConsent() {
    try {
        const raw = window.localStorage.getItem(STORAGE_KEY);

        return raw ? JSON.parse(raw) : null;
    } catch {
        return null;
    }
}

function writeConsent(consent) {
    window.localStorage.setItem(STORAGE_KEY, JSON.stringify(consent));

    // Se escribe también como cookie (no solo localStorage) para que el
    // servidor pueda leer la preferencia de marketing al procesar un envío
    // de formulario y decidir si manda o no el evento a Meta Conversions API
    // (App\Actions\Forms\StoreFormSubmission).
    const maxAge = COOKIE_MAX_AGE_DAYS * 24 * 60 * 60;
    document.cookie = `dante_consent_marketing=${consent.marketing ? '1' : '0'}; path=/; max-age=${maxAge}; SameSite=Lax`;
    document.cookie = `dante_consent_analytics=${consent.analytics ? '1' : '0'}; path=/; max-age=${maxAge}; SameSite=Lax`;
}

function loadGoogleTagManager() {
    const gtmId = document.documentElement.dataset.gtmId;

    if (!gtmId || window.__danteGtmLoaded) {
        return;
    }
    window.__danteGtmLoaded = true;

    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });

    const script = document.createElement('script');
    script.async = true;
    script.src = `https://www.googletagmanager.com/gtm.js?id=${gtmId}`;
    document.head.appendChild(script);
}

function loadMetaPixel() {
    const pixelId = document.documentElement.dataset.metaPixelId;

    if (!pixelId || window.__dantePixelLoaded) {
        return;
    }
    window.__dantePixelLoaded = true;

    /* eslint-disable */
    !(function (f, b, e, v, n, t, s) {
        if (f.fbq) return;
        n = f.fbq = function () {
            n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments);
        };
        if (!f._fbq) f._fbq = n;
        n.push = n;
        n.loaded = true;
        n.version = '2.0';
        n.queue = [];
        t = b.createElement(e);
        t.async = true;
        t.src = v;
        s = b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t, s);
    })(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
    /* eslint-enable */

    window.fbq('init', pixelId);
    window.fbq('track', 'PageView');

    // Si un formulario se envió en esta misma navegación (redirect con
    // `meta_event_id` en la sesión), disparar el evento del Pixel con el
    // MISMO event_id que ya mandó el servidor — deduplicación real, no dos
    // conversiones por un solo envío (docs/08-seo.md §6).
    if (window.danteQueuedLeadEventId) {
        window.fbq('track', 'Lead', {}, { eventID: window.danteQueuedLeadEventId });
    }
}

function applyConsent(consent) {
    if (consent.analytics) {
        loadGoogleTagManager();
    }
    if (consent.marketing) {
        loadMetaPixel();
    }
}

document.addEventListener('alpine:init', () => {
    window.Alpine.data('danteCookieConsent', () => ({
        visible: false,
        configuring: false,
        analytics: false,
        marketing: false,

        init() {
            const saved = readConsent();

            if (saved) {
                applyConsent(saved);
                this.visible = false;
            } else {
                this.visible = true;
            }

            window.addEventListener('dante:reopen-consent', () => {
                const current = readConsent() ?? { analytics: false, marketing: false };
                this.analytics = current.analytics;
                this.marketing = current.marketing;
                this.configuring = true;
                this.visible = true;
            });
        },

        acceptAll() {
            const consent = { analytics: true, marketing: true };
            writeConsent(consent);
            applyConsent(consent);
            this.visible = false;
        },

        rejectAll() {
            const consent = { analytics: false, marketing: false };
            writeConsent(consent);
            this.visible = false;
        },

        saveConfigured() {
            const consent = { analytics: this.analytics, marketing: this.marketing };
            writeConsent(consent);
            applyConsent(consent);
            this.visible = false;
        },
    }));
});
