import { toPreviewPayload } from '../../utils/canvas/draftState.js';
import { csrfHeaders } from '../../utils/wizard/adminFetch.js';

/**
 * The panel half of the iframe protocol.
 *
 * The first render is a real form POST into a named iframe, so the browser — not
 * the panel — carries the draft to the public site and the document keeps its
 * own origin, assets and CSP nonce. Later updates ask the same endpoint for a
 * single block and hand the markup back over postMessage.
 */
const PROTOCOL = 1;

/** Seconds from `Retry-After`, bounded so a missing or absurd value neither
 *  hammers the endpoint nor leaves the preview stale for minutes. */
export function retryDelay(response) {
    const seconds = Number(response?.headers?.get?.('Retry-After'));

    return Math.min(30, Math.max(2, Number.isFinite(seconds) && seconds > 0 ? seconds : 5)) * 1000;
}

export default function bridge() {
    let onMessage;
    let frameObserver = null;
    return {
        viewport: 'desktop',
        previewTimer: null,
        previewRenewTimer: null,
        previewRenewInFlight: false,
        previewError: '',
        previewSequence: 0,
        pendingRefs: new Set(),
        annotatedRefs: [],
        previewDeferred: false,

        /** The URL locale must match the payload locale the canvas is showing. */
        get previewUrl() {
            return `${this.endpoints.previewBase}/${this.activeLocale}/_editor/preview`;
        },

        get previewAuthorized() {
            return this.preview.authorized === true && this.preview.origin !== '';
        },

        // Same-origin preview code must retain browser storage and the public
        // bundle's normal runtime. In that deployment the iframe is trusted by
        // the same CSP and exact bridge channel; a separately hosted preview
        // keeps the stricter sandbox permissions.
        get previewSandbox() {
            if (!this.previewAuthorized || typeof window === 'undefined') return null;

            return this.preview.origin === window.location.origin ? null : 'allow-same-origin allow-scripts';
        },

        startBridge() {
            this.stopBridge();
            onMessage = (event) => this.receive(event);
            window.addEventListener('message', onMessage);
            this.schedulePreviewRenewal();
        },

        stopBridge() {
            window.removeEventListener('message', onMessage);
            clearTimeout(this.previewRenewTimer);
            frameObserver?.disconnect();
            frameObserver = null;
        },

        /**
         * In the narrow layout the canvas shares the screen with the block list
         * and the properties behind tabs, and a panel that is not showing is
         * `display: none`. A document rendered into a frame with no box came
         * back with its hero image broken for good, so renders wait while the
         * frame is hidden and run once it is laid out again — by a tab or by a
         * window wide enough for the three-column layout.
         */
        observePreviewFrame() {
            frameObserver?.disconnect();
            frameObserver = null;
            const frame = this.$refs?.previewFrame;
            if (typeof window.ResizeObserver === 'undefined' || !frame) return;

            frameObserver = new window.ResizeObserver(() => this.revealPreview());
            frameObserver.observe(frame);
        },

        previewFrameHidden() {
            const frame = this.$refs?.previewFrame;

            return typeof frame?.getClientRects === 'function' && frame.getClientRects().length === 0;
        },

        /** Renders whatever was held back while the frame was hidden. */
        revealPreview() {
            if (!this.previewDeferred || this.previewFrameHidden()) return;

            this.previewDeferred = false;
            this.submitPreviewForm();
        },

        schedulePreviewRenewal(delayOverride = 0) {
            clearTimeout(this.previewRenewTimer);
            if (!this.previewAuthorized || !this.preview.expires || !this.endpoints.previewRenew) return;

            const expiresAt = Number(this.preview.expires) * 1000;
            const safetyWindow = 5 * 60 * 1000;
            const delay = delayOverride || Math.max(1000, expiresAt - Date.now() - safetyWindow);
            this.previewRenewTimer = setTimeout(() => { void this.renewPreview(); }, delay);
        },

        async renewPreview() {
            if (this.previewRenewInFlight || !this.endpoints.previewRenew) return false;
            this.previewRenewInFlight = true;

            try {
                const response = await fetch(this.endpoints.previewRenew, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        ...csrfHeaders(this.boot.csrf || {}),
                    },
                });
                const payload = await response.json().catch(() => null);
                if (!response.ok || payload?.ok !== true || !payload.data?.expires || !payload.data?.sig) {
                    if (Date.now() >= Number(this.preview.expires || 0) * 1000) {
                        this.preview.authorized = false;
                        this.previewError = this.t('previewRenewFailed');
                    } else {
                        this.schedulePreviewRenewal(30000);
                    }
                    return false;
                }

                this.preview.expires = payload.data.expires;
                this.preview.sig = payload.data.sig;
                this.preview.authorized = true;
                this.previewError = '';
                this.schedulePreviewRenewal();
                return true;
            } catch {
                if (Date.now() >= Number(this.preview.expires || 0) * 1000) {
                    this.preview.authorized = false;
                    this.previewError = this.t('previewRenewFailed');
                } else {
                    this.schedulePreviewRenewal(30000);
                }
                return false;
            } finally {
                this.previewRenewInFlight = false;
            }
        },

        receive(event) {
            if (!this.previewAuthorized || event.origin !== this.preview.origin) {
                return;
            }

            const frame = this.$refs.previewFrame;
            if (!frame || event.source !== frame.contentWindow) {
                return;
            }

            const message = event.data;
            if (!message || message.protocol !== PROTOCOL || message.channel !== this.preview.channel) {
                return;
            }

            if (message.type === 'doc:ready') {
                this.annotatedRefs = (message.payload.blocks || []).map((b) => b.ref);
                this.applyFallbackFlags((message.payload.blocks || []).filter((b) => b.fallback).map((b) => b.ref));
                this.sendToFrame('canvas:select', { ref: this.selectedRef });
                // A full reload lands the preview at the top of the page, so the
                // block being edited scrolled out of view after every field.
                const target = this.scrollTargetFor(this.selectedRef);
                if (target) this.sendToFrame('canvas:scrollTo', { ref: target });

                return;
            }

            if (message.type === 'block:select') {
                this.selectedRef = message.payload.ref;
                this.mobilePanel = 'properties';
            }
        },

        /**
         * Containers that render their children from a view model (a card grid,
         * a slider) give those children no node of their own, so the nearest
         * annotated ancestor is the closest thing on the page to scroll to.
         */
        scrollTargetFor(ref) {
            const seen = new Set();
            let current = ref;
            while (current && !seen.has(current)) {
                if (this.annotatedRefs.includes(current)) return current;
                seen.add(current);
                current = (this.blocks || []).find((block) => block.ref === current)?.parent_ref || null;
            }

            return null;
        },

        sendToFrame(type, payload) {
            if (!this.previewAuthorized) {
                return;
            }

            const frame = this.$refs.previewFrame;
            if (!frame || !frame.contentWindow) {
                return;
            }

            frame.contentWindow.postMessage(
                { protocol: PROTOCOL, channel: this.preview.channel, type, payload: payload || {} },
                this.preview.origin,
            );
        },

        /** A structural change reloads the document; a field edit refreshes one block. */
        refreshPreview(ref) {
            if (!this.previewAuthorized) {
                return;
            }

            if (ref) {
                this.pendingRefs.add(ref);
            } else {
                this.pendingRefs.clear();
                this.pendingRefs.add(null);
            }

            clearTimeout(this.previewTimer);
            this.previewTimer = setTimeout(() => this.flushPreview(), 400);
        },

        flushPreview() {
            const refs = Array.from(this.pendingRefs);
            this.pendingRefs.clear();

            // Whatever changed while the frame was hidden — or before a missed
            // reveal — one full render covers it once the frame can show it.
            if (this.previewDeferred || this.previewFrameHidden()) {
                this.previewDeferred = true;
                this.revealPreview();

                return;
            }

            // Some containers render their own children from a view model, so a
            // child block has no node of its own to replace. Refreshing the
            // nearest annotated ancestor re-renders the child with it; only when
            // there is none does the canvas fall back to a full render rather
            // than silently leaving the preview stale.
            const target = refs.length === 1 && refs[0] !== null ? this.scrollTargetFor(refs[0]) : null;
            if (target === null) {
                this.submitPreviewForm();

                return;
            }

            void this.refreshBlock(target);
        },

        submitPreviewForm() {
            if (this.disposed || !this.previewAuthorized) return;
            const form = this.$refs.previewForm;
            if (!form) {
                return;
            }

            if (this.previewFrameHidden()) {
                this.previewDeferred = true;

                return;
            }

            this.$refs.previewPayload.value = JSON.stringify(
                toPreviewPayload(this.blocks, this.activeLocale),
            );
            form.submit();
        },

        async refreshBlock(ref) {
            const sequence = ++this.previewSequence;

            try {
                const response = await fetch(this.previewUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        owner_type: this.owner.type,
                        owner_id: this.owner.id,
                        expires: this.preview.expires,
                        sig: this.preview.sig,
                        channel: this.preview.channel,
                        payload: toPreviewPayload(this.blocks, this.activeLocale, { type: 'block', ref }),
                    }),
                });

                if (response.status === 429) {
                    // A full reload would be throttled as well and paint the raw
                    // error inside the canvas. Keep the current preview and ask
                    // for the block again once the window allows it.
                    this.pendingRefs.add(ref);
                    clearTimeout(this.previewTimer);
                    this.previewTimer = setTimeout(() => this.flushPreview(), retryDelay(response));

                    return;
                }

                if (!response.ok) {
                    this.submitPreviewForm();

                    return;
                }

                const html = await response.text();
                // The panel may have been hidden while the block was on its way.
                if (this.previewFrameHidden()) {
                    this.previewDeferred = true;

                    return;
                }

                this.sendToFrame('canvas:replaceBlock', { ref, html, sequence });
            } catch {
                this.submitPreviewForm();
            }
        },
    };
}
