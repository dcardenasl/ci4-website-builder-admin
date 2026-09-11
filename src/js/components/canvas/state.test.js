import { afterEach, beforeEach, expect, it, vi } from 'vitest';
import canvasEditor from './index.js';
import { buildOps } from '../../utils/canvas/patchBuilder.js';

const document = (body, title = 'Title') => ({ version: body, locales: [{ code: 'es' }], blocks: [
    { instance_id: 1, block_key: 'text', sort_order: 1, i18n: { es: { body, title, legacy: { untouched: true } } }, config: {} },
] });
const response = (doc) => ({ ok: true, status: 200, json: async () => ({ ok: true, data: { document: doc } }) });
function editor() {
    const component = canvasEditor();
    component.applyDocument(document('Before'));
    component.refreshPreview = vi.fn();
    return component;
}
beforeEach(() => { vi.useFakeTimers(); vi.stubGlobal('fetch', vi.fn()); });
afterEach(() => { vi.useRealTimers(); vi.unstubAllGlobals(); });

it('accepts sanitized HTML and emits only changed fields', async () => {
    const component = editor();
    component.blocks[0].i18n.es.body = '<p onclick="x()">After</p>';
    fetch.mockResolvedValue(response(document('<p>After</p>')));
    await component.save();
    expect(JSON.parse(fetch.mock.calls[0][1].body).ops).toEqual([
        { op: 'upsert', instance_id: 1, i18n: { es: { body: '<p onclick="x()">After</p>' } } },
    ]);
    expect(component.blocks[0].i18n.es.body).toBe('<p>After</p>');
    expect(buildOps(component.baseline, component.blocks)).toEqual([]);
    expect(component.blocks[0].i18n.es.legacy).toEqual({ untouched: true });
});

it('preserves later typing while adopting canonical values of other fields', async () => {
    const component = editor();
    component.blocks[0].i18n.es.body = '<p onclick="x()">First</p>';
    let resolve;
    fetch.mockReturnValue(new Promise((done) => { resolve = done; }));
    const saving = component.save();
    component.blocks[0].i18n.es.title = 'Later title';
    component.touch();
    await vi.advanceTimersByTimeAsync(600);
    expect(fetch).toHaveBeenCalledTimes(1);
    resolve(response(document('<p>First</p>')));
    await saving;
    expect(component.blocks[0].i18n.es).toMatchObject({ body: '<p>First</p>', title: 'Later title' });
    expect(component.status).toBe('dirty');
    fetch.mockResolvedValue(response(document('<p>First</p>', 'Later title')));
    await vi.advanceTimersByTimeAsync(500);
    expect(fetch).toHaveBeenCalledTimes(2);
    expect(component.status).toBe('saved');
});

it('keeps the draft on network failure and allows an explicit retry', async () => {
    const component = editor();
    component.blocks[0].i18n.es.body = 'After';
    fetch.mockRejectedValue(new Error('offline'));
    await component.save();
    expect(component.status).toBe('error');
    expect(component.blocks[0].i18n.es.body).toBe('After');
    fetch.mockResolvedValue(response(document('After')));
    await component.save();
    expect(component.status).toBe('saved');
});

it('pauses all saves on conflict and retains local content', async () => {
    const component = editor();
    component.blocks[0].i18n.es.body = 'Draft';
    fetch.mockResolvedValue({ status: 409, ok: false, json: async () => ({}) });
    await component.save();
    component.touch();
    await component.save();
    expect(component.status).toBe('conflict');
    expect(component.blocks[0].i18n.es.body).toBe('Draft');
    expect(fetch).toHaveBeenCalledTimes(1);
});

it('saves a dirty draft before publishing and surfaces the success message', async () => {
    const component = editor();
    component.endpoints.save = '/admin/cms/editor/pages/1/document';
    component.endpoints.publish = '/admin/cms/editor/pages/1/publish';
    component.blocks[0].i18n.es.body = 'Ready to publish';
    component.status = 'dirty';
    fetch
        .mockResolvedValueOnce(response(document('Ready to publish')))
        .mockResolvedValueOnce({ ok: true, status: 200, json: async () => ({ ok: true, messages: ['Published.'] }) });

    await component.publish();

    expect(fetch).toHaveBeenNthCalledWith(1, component.endpoints.save, expect.anything());
    expect(fetch).toHaveBeenNthCalledWith(2, component.endpoints.publish, expect.objectContaining({ method: 'POST' }));
    expect(component.publishMessage).toBe('Published.');
    expect(component.status).toBe('saved');
    // A success is a status, not an alert: it used to share the red box.
    expect(component.feedbackMessage).toBe('Published.');
    expect(component.feedbackIsError).toBe(false);

    component.publishError = 'Could not publish.';
    expect(component.feedbackIsError).toBe(true);
});

it('does not share nested values between baseline and draft', () => {
    const component = editor();
    component.blocks[0].i18n.es.legacy.untouched = false;
    expect(component.baseline[0].i18n.es.legacy.untouched).toBe(true);
});

it('updates the canvas title when the editing locale changes', () => {
    const component = editor();
    component.documentOwner = {
        title: 'Inicio',
        i18n: {
            es: { title: 'Inicio' },
            en: { title: 'Home' },
        },
    };
    component.documentTitle = 'Inicio';
    component.activeLocale = 'es';

    component.switchLocale('en');

    expect(component.documentTitle).toBe('Home');
    expect(component.refreshPreview).toHaveBeenCalledTimes(1);
});

it('cancels pending autosave and undo timers when the component is destroyed', async () => {
    const component = editor();
    component.removeBlock('id_1');
    component.destroy();
    await vi.advanceTimersByTimeAsync(6000);
    expect(fetch).not.toHaveBeenCalled();
});

it('carries the preview annotations over when a new block receives its real id', () => {
    // The preview annotated the block under its temporary ref; left stale, the
    // first edit of every new block reloaded the whole preview.
    const component = editor();
    component.annotatedRefs = ['tmp_new', 'id_1'];

    component.adoptIds({ tmp_new: 42 });

    expect(component.annotatedRefs).toEqual(['id_42', 'id_1']);
});
