export const bootSessionExpiryWatcher = () => {
    const meta = document.querySelector('meta[name="session-expires-at"]');
    if (!(meta instanceof HTMLMetaElement)) return;
    const expiresAt = parseInt(meta.getAttribute('content') || '0', 10);
    if (!Number.isFinite(expiresAt) || expiresAt <= 0) return;

    const WARN_BEFORE_SECONDS = 60;
    let warned = false;

    const tick = () => {
        const remaining = expiresAt - Math.floor(Date.now() / 1000);
        if (!warned && remaining > 0 && remaining <= WARN_BEFORE_SECONDS) {
            warned = true;
            console.warn(`[session] Token expires in ~${remaining}s. Save your work.`);
            window.dispatchEvent(new CustomEvent('session:expiring-soon', { detail: { remainingSeconds: remaining } }));
        }
        if (remaining <= 0) {
            window.dispatchEvent(new CustomEvent('session:expired'));
            clearInterval(handle);
        }
    };

    const handle = setInterval(tick, 5000);
    tick();
};
