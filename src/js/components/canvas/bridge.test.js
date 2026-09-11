import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import bridge, { retryDelay } from './bridge.js';

describe('canvas preview bridge', () => {
    beforeEach(() => {
        vi.useFakeTimers();
        vi.stubGlobal('fetch', vi.fn());
    });

    afterEach(() => {
        vi.useRealTimers();
        vi.unstubAllGlobals();
    });

    it('does not add a known-unsafe sandbox combination for same-origin preview', () => {
        const component = bridge();
        component.preview = { authorized: true, origin: window.location.origin };
        expect(component.previewSandbox).toBeNull();
        component.preview.origin = 'https://public.example.test';
        expect(component.previewSandbox).toBe('allow-same-origin allow-scripts');
    });

    it('renews the signed preview capability before the old expiry', async () => {
        const component = bridge();
        component.boot = { csrf: { cookie: 'csrf_cookie_readable' } };
        component.endpoints = { previewRenew: '/admin/cms/editor/pages/1/preview/renew' };
        component.preview = { authorized: true, origin: window.location.origin, expires: 100, sig: 'old' };
        component.t = (key) => key;
        fetch.mockResolvedValue({ ok: true, status: 200, json: async () => ({ ok: true, data: { expires: 200, sig: 'new' } }) });

        await component.renewPreview();

        expect(component.preview).toMatchObject({ expires: 200, sig: 'new', authorized: true });
        expect(fetch).toHaveBeenCalledWith(component.endpoints.previewRenew, expect.objectContaining({ method: 'POST' }));
    });

    function readyComponent(selectedRef) {
        const component = bridge();
        const frameWindow = {};
        component.preview = { authorized: true, origin: 'http://127.0.0.1:8192', channel: 'c1' };
        component.$refs = { previewFrame: { contentWindow: frameWindow } };
        component.selectedRef = selectedRef;
        component.applyFallbackFlags = vi.fn();
        component.sendToFrame = vi.fn();
        component.receive({
            origin: 'http://127.0.0.1:8192',
            source: frameWindow,
            data: { protocol: 1, channel: 'c1', type: 'doc:ready', payload: { blocks: [{ ref: 'id_7' }] } },
        });

        return component;
    }

    it('brings the edited block back into view once a full reload is ready', () => {
        // A reload lands the preview at the top of the page; without this the
        // block being edited scrolled out of view after every field.
        const component = readyComponent('id_7');

        expect(component.sendToFrame).toHaveBeenCalledWith('canvas:select', { ref: 'id_7' });
        expect(component.sendToFrame).toHaveBeenCalledWith('canvas:scrollTo', { ref: 'id_7' });
    });

    it('leaves the scroll alone when nothing is selected', () => {
        const component = readyComponent(null);

        expect(component.sendToFrame).not.toHaveBeenCalledWith('canvas:scrollTo', expect.anything());
    });

    it('scrolls to the nearest annotated ancestor of a child rendered by its container', () => {
        // A slider renders its cards from a view model, so a card has no node
        // of its own; its container is what the reader should be looking at.
        const component = bridge();
        component.annotatedRefs = ['id_1'];
        component.blocks = [
            { ref: 'id_1', parent_ref: null },
            { ref: 'id_2', parent_ref: 'id_1' },
        ];

        expect(component.scrollTargetFor('id_2')).toBe('id_1');
        expect(component.scrollTargetFor('id_1')).toBe('id_1');
        expect(component.scrollTargetFor('missing')).toBeNull();
    });

    it('waits out a throttled block refresh instead of reloading into the error', async () => {
        const component = bridge();
        component.preview = { authorized: true, origin: 'http://127.0.0.1:8192', expires: 1, sig: 's', channel: 'c1' };
        component.endpoints = { previewBase: 'http://127.0.0.1:8192' };
        component.activeLocale = 'es';
        component.owner = { type: 'page', id: 1 };
        component.blocks = [];
        component.submitPreviewForm = vi.fn();
        fetch.mockResolvedValue({ ok: false, status: 429, headers: { get: () => '3' } });

        await component.refreshBlock('id_7');

        expect(component.submitPreviewForm).not.toHaveBeenCalled();
        expect(component.pendingRefs.has('id_7')).toBe(true);
    });

    it('refreshes the container when an edited child has no node of its own', () => {
        // A metric inside a metrics grid is rendered by the grid; reloading the
        // whole preview for every keystroke-pause was the old fallback.
        const component = bridge();
        component.annotatedRefs = ['id_grid'];
        component.blocks = [
            { ref: 'id_grid', parent_ref: null },
            { ref: 'id_metric', parent_ref: 'id_grid' },
        ];
        component.refreshBlock = vi.fn();
        component.submitPreviewForm = vi.fn();

        component.pendingRefs = new Set(['id_metric']);
        component.flushPreview();
        expect(component.refreshBlock).toHaveBeenCalledWith('id_grid');
        expect(component.submitPreviewForm).not.toHaveBeenCalled();

        // A structural change still reloads the whole document.
        component.pendingRefs = new Set([null]);
        component.flushPreview();
        expect(component.submitPreviewForm).toHaveBeenCalledTimes(1);
    });

    /** A canvas whose frame reports a box only while `show()` is in effect. */
    function frameComponent() {
        let rects = [];
        const component = bridge();
        const form = { submit: vi.fn() };
        component.preview = { authorized: true, origin: 'http://127.0.0.1:8192', expires: 1, sig: 's', channel: 'c1' };
        component.endpoints = { previewBase: 'http://127.0.0.1:8192' };
        component.activeLocale = 'es';
        component.owner = { type: 'page', id: 1 };
        component.blocks = [];
        component.annotatedRefs = ['id_7'];
        component.$refs = {
            previewForm: form,
            previewPayload: { value: '' },
            previewFrame: { contentWindow: {}, getClientRects: () => rects },
        };
        component.sendToFrame = vi.fn();

        return {
            component,
            form,
            show: () => { rects = [{ width: 800, height: 600 }]; },
            hide: () => { rects = []; },
        };
    }

    it('holds every render while the preview panel is hidden and renders once it shows', () => {
        // In the narrow layout the canvas panel is display:none behind a tab; a
        // document rendered into that frame came back with its hero image broken.
        const { component, form, show } = frameComponent();

        component.submitPreviewForm();
        component.pendingRefs = new Set(['id_7']);
        component.flushPreview();
        expect(form.submit).not.toHaveBeenCalled();
        expect(fetch).not.toHaveBeenCalled();

        component.revealPreview();
        expect(form.submit).not.toHaveBeenCalled();

        show();
        component.revealPreview();
        component.revealPreview();
        expect(form.submit).toHaveBeenCalledTimes(1);
        expect(component.previewDeferred).toBe(false);
    });

    it('holds back a block refresh that lands after the panel was hidden', async () => {
        const { component, form, show, hide } = frameComponent();
        show();
        let respond;
        fetch.mockReturnValue(new Promise((resolve) => { respond = resolve; }));

        const refresh = component.refreshBlock('id_7');
        hide();
        respond({ ok: true, status: 200, text: async () => '<section></section>' });
        await refresh;

        expect(component.sendToFrame).not.toHaveBeenCalled();
        show();
        component.revealPreview();
        expect(form.submit).toHaveBeenCalledTimes(1);
    });

    it('still refreshes a single block while the frame is showing', () => {
        const { component, form, show } = frameComponent();
        show();
        component.refreshBlock = vi.fn();

        component.pendingRefs = new Set(['id_7']);
        component.flushPreview();

        expect(component.refreshBlock).toHaveBeenCalledWith('id_7');
        expect(form.submit).not.toHaveBeenCalled();
    });

    it('bounds the retry delay a throttled response asks for', () => {
        const withHeader = (value) => ({ headers: { get: () => value } });

        expect(retryDelay(withHeader('3'))).toBe(3000);
        expect(retryDelay(withHeader('600'))).toBe(30000);
        expect(retryDelay(withHeader(null))).toBe(5000);
    });
});
