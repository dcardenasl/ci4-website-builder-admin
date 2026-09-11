import { isEmptyValue } from './draftState.js';

/**
 * Maps a schema field type to the control the property panel renders.
 * Anything the canvas cannot edit safely says so instead of guessing.
 */
export function controlFor(type) {
    switch (String(type || '').toLowerCase()) {
        case 'text':
        case 'string':
        case 'url':
        case 'date':
        case 'datetime':
            return 'text';
        case 'textarea':
            return 'textarea';
        case 'richtext':
        case 'rich_text':
        case 'rich-text':
        case 'html':
            return 'richtext';
        case 'number':
        case 'integer':
        case 'int':
        case 'float':
        case 'decimal':
            return 'number';
        case 'boolean':
        case 'bool':
            return 'boolean';
        case 'select':
            return 'select';
        case 'color':
            return 'color';
        case 'media_reference':
            return 'media';
        default:
            return 'unsupported';
    }
}

const RICH_TEXT_TYPES = ['richtext', 'rich_text', 'rich-text', 'html'];

function describe(key, definition, scope, value) {
    const type = String(definition.type || '').toLowerCase();

    return {
        key,
        scope,
        label: definition.label || key,
        control: controlFor(definition.type),
        isRichText: RICH_TEXT_TYPES.includes(type),
        accept: typeof definition.accept === 'string' ? definition.accept : 'image',
        options: Array.isArray(definition.options) ? definition.options : [],
        value: value === undefined ? '' : value,
        canCopyFallback: false,
        fallbackValue: null,
    };
}

/**
 * Content fields are per locale; design fields are not. The split mirrors the
 * schema itself, so a colour never becomes a thing you translate.
 */
export function buildFieldGroups(block, type, activeLocale, defaultLocale, labels) {
    if (!block || !type) {
        return [];
    }

    const active = (block.i18n || {})[activeLocale] || {};
    const fallback = (block.i18n || {})[defaultLocale] || {};

    const content = Object.entries(type.fields || {}).map(([key, definition]) => {
        const field = describe(key, definition, 'i18n', active[key]);

        if (activeLocale !== defaultLocale && isEmptyValue(active[key]) && !isEmptyValue(fallback[key])) {
            field.canCopyFallback = true;
            field.fallbackValue = fallback[key];
        }

        return field;
    });

    const design = Object.entries(type.config_fields || {}).map(
        ([key, definition]) => describe(key, definition, 'config', (block.config || {})[key])
    );

    return [
        { key: 'content', label: labels.content, fields: content },
        { key: 'design', label: labels.design, fields: design },
    ].map((group) => ({ ...group, fields: group.fields.map((field) => ({
        ...field, blockRef: block.ref, locale: activeLocale,
        identity: `${block.ref}:${activeLocale}:${field.scope}:${field.key}`,
    })) }));
}
