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

    it('limits public filters to the API matrix and keeps operators compatible with their field', () => {
        const editor = listingProjectionEditor(catalog, {
            filters: [
                { source: 'entry.published_at', label: 'Published', operator: 'contains' },
                { source: 'entry.excerpt', label: 'Excerpt', operator: 'equals' },
                { source: 'entry.secret', label: 'Secret', operator: 'equals' },
            ],
        }, 'noticias', {
            filter_operator_before: 'Before',
            filter_operator_after: 'After',
        });
        editor.init();

        expect(editor.availableFields({ filterable: true, publicFilter: true }).map((field) => field.value)).toEqual([
            'entry.title',
            'entry.published_at',
            'taxonomy.tags',
        ]);
        expect(editor.projection.filters).toHaveLength(2);
        expect(editor.projection.filters[0].operator).toBe('equals');
        expect(editor.operatorOptions('entry.published_at').map((option) => option.value)).toEqual(['equals', 'before', 'after']);

        editor.setFilterSource(editor.projection.filters[0], 'taxonomy.tags');
        expect(editor.projection.filters[0].operator).toBe('equals');
        editor.projection.filters[0].operator = 'in';
        expect(JSON.parse(editor.serialized()).filters[0]).toEqual({
            source: 'taxonomy.tags',
            label: 'Published',
            operator: 'in',
        });
    });
});
