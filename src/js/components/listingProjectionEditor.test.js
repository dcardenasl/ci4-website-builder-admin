import { describe, expect, it } from 'vitest';
import { listingProjectionEditor } from './listingProjectionEditor.js';

const catalog = {
    noticias: [
        { value: 'entry.title', label: 'Title', type: 'text', sortable: true, filterable: true },
        { value: 'entry.excerpt', label: 'Excerpt', type: 'text', sortable: false, filterable: false },
        { value: 'entry.published_at', label: 'Published', type: 'date', sortable: true, filterable: true },
        { value: 'taxonomy.tags', label: 'Tags', type: 'taxonomy', sortable: false, filterable: true },
    ],
};

describe('listingProjectionEditor', () => {
    it('normalizes initial values and removes references outside the selected catalog', () => {
        const editor = listingProjectionEditor(catalog, {
            slots: { title: 'entry.secret', summary: 'entry.excerpt' },
            extras: [{ source: 'entry.secret', label: 'Secret' }, { source: 'entry.title', label: 'Title' }],
            order: { field: 'entry.secret', direction: 'invalid' },
            filters: [{ source: 'taxonomy.tags', label: 'Tags' }],
        }, 'noticias');

        editor.init();

        expect(editor.projection.slots.title).toBe('entry.title');
        expect(editor.projection.order).toMatchObject({ field: '', direction: 'desc' });
        expect(editor.projection.extras).toHaveLength(1);
        expect(editor.projection.extras[0].source).toBe('entry.title');
        expect(editor.projection.filters[0].source).toBe('taxonomy.tags');
    });

    it('limits field choices by catalog capabilities and preserves a stable serialized contract', () => {
        const editor = listingProjectionEditor(catalog, {}, 'noticias');
        editor.init();

        expect(editor.availableFields({ sortable: true }).map((field) => field.value)).toEqual([
            'entry.title',
            'entry.published_at',
        ]);
        expect(editor.availableFields({ filterable: true, types: ['taxonomy'] }).map((field) => field.value)).toEqual(['taxonomy.tags']);

        editor.projection.extras = [{ id: 'runtime-id', source: 'entry.title', label: 'Title', operator: 'equals' }];
        const serialized = JSON.parse(editor.serialized());

        expect(serialized.extras).toEqual([{ source: 'entry.title', label: 'Title', operator: 'equals' }]);
        expect(serialized).not.toHaveProperty('extras[0].id');
    });
});
