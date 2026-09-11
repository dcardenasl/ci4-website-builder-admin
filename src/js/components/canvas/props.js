import { buildFieldGroups } from '../../utils/canvas/schemaToFields.js';

/**
 * `<input type="color">` only takes `#rrggbb`. The catalog's own overlay default
 * is an `rgba()` value, which the browser rejected with a console warning and
 * painted black; the swatch now shows its colour and the exact value stays in
 * the text beside it.
 */
export function toSwatch(value) {
    const text = String(value || '').trim();
    if (/^#[0-9a-f]{6}$/i.test(text)) return text.toLowerCase();
    if (/^#[0-9a-f]{3}$/i.test(text)) {
        return `#${text.slice(1).split('').map((digit) => digit + digit).join('')}`.toLowerCase();
    }
    const rgb = text.match(/^rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})/i);
    if (rgb) {
        return `#${rgb.slice(1, 4).map((part) => Math.min(255, Number(part)).toString(16).padStart(2, '0')).join('')}`;
    }

    return '#000000';
}

/** The property panel: schema in, controls out. */
export default function props() {
    return {
        get fieldGroups() {
            return buildFieldGroups(
                this.selected,
                this.selected ? this.typeOf(this.selected.block_key) : null,
                this.activeLocale,
                this.defaultLocale,
                { content: this.t('content'), design: this.t('design') },
            );
        },

        edit(field, value) {
            const block = this.blocks.find((item) => item.ref === field.blockRef);
            if (!block) {
                return;
            }

            if (field.scope === 'config') {
                block.config = { ...block.config, [field.key]: value };
            } else {
                block.i18n = {
                    ...block.i18n,
                    [field.locale]: { ...(block.i18n[field.locale] || {}), [field.key]: value },
                };
            }

            this.touch();
            this.refreshPreview(block.ref);
        },

        mediaLabel(field) {
            const value = field.value;
            if (!value || typeof value !== 'object') {
                return '';
            }

            return value.url ? String(value.url).split('/').pop() : '';
        },

        /** Reuses the panel-wide picker so the canvas and the classic forms
         *  choose media from the same library. */
        pickMedia(field) {
            Alpine.store('filePicker').show({
                filterType: field.accept,
                accept: field.accept === 'image' ? 'image/*' : '',
                multi: false,
                onSelect: (file) => {
                    this.edit(field, {
                        source_kind: 'media_file',
                        file_id: Number(file.id),
                        url: String(file.url || ''),
                    });
                },
            });
        },

        clearMedia(field) {
            this.edit(field, null);
        },

        swatchColor(value) {
            return toSwatch(value);
        },
    };
}
