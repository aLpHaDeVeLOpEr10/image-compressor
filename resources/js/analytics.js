export function track(event, params = {}) {
    if (typeof window.gtag === 'function') {
        window.gtag('event', event, params);
    }
}
