/* global confirm, alert */
import { bootLucideIcons } from '../utils/lucide.js';

export const formFieldBuilderFactory = (config = {}) => {
    const readJsonList = (elementId) => {
        const element = elementId ? document.getElementById(elementId) : null;
        if (!element) return [];
        try {
            const parsed = JSON.parse(element.textContent || '[]');
            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            console.warn('[forms] Could not parse field builder JSON data.', error);
            return [];
        }
    };

    const languages = Array.isArray(config.languages) ? config.languages : readJsonList(config.languagesElementId);
    const initialFields = Array.isArray(config.initialFields) ? config.initialFields : readJsonList(config.initialFieldsElementId);
    const toBoolean = (value) => value === true || value === 1 || value === '1';

    const normalizeField = (field) => ({
        ...field,
        is_required: toBoolean(field?.is_required),
        is_active: toBoolean(field?.is_active),
    });

    const defaultTranslations = () => {
        const translations = {};
        languages.forEach((lang) => { translations[lang.id] = { label: '', placeholder: '', help_text: '' }; });
        return translations;
    };

    return {
        fields: initialFields.map(normalizeField),
        showModal: false,
        editingField: null,
        activeFieldLang: String(languages[0]?.code || 'es'),
        fieldError: '',
        fieldForm: { field_key: '', field_type: 'text', is_required: false, is_active: true, translations: defaultTranslations() },

        init() {
            this.$watch('showModal', (value) => { if (!value) this.fieldError = ''; });
        },

        defaultFieldForm() {
            return { field_key: '', field_type: 'text', is_required: false, is_active: true, translations: defaultTranslations() };
        },

        openCreate() {
            this.editingField = null;
            this.fieldForm = this.defaultFieldForm();
            this.activeFieldLang = String(languages[0]?.code || 'es');
            this.showModal = true;
        },

        openEdit(field) {
            this.editingField = field;
            const translations = {};
            languages.forEach((lang) => {
                const existing = (field.translations || []).find((item) => item.language_id == lang.id) || {};
                translations[lang.id] = { label: existing.label || '', placeholder: existing.placeholder || '', help_text: existing.help_text || '' };
            });
            this.fieldForm = { field_key: field.field_key, field_type: field.field_type, is_required: toBoolean(field.is_required), is_active: toBoolean(field.is_active), translations };
            this.activeFieldLang = String(languages[0]?.code || 'es');
            this.showModal = true;
        },

        closeModal() { this.showModal = false; this.editingField = null; },

        buildTranslationsArray() {
            return Object.entries(this.fieldForm.translations).map(([languageId, translation]) => ({
                language_id: parseInt(languageId, 10), ...translation,
            }));
        },

        async saveField() {
            this.fieldError = '';
            const fieldKey = String(this.fieldForm.field_key || '').trim();
            if (fieldKey === '') { this.fieldError = config.fieldKeyRequiredMessage || 'The field key is required.'; return; }

            const payload = { field_key: fieldKey, field_type: this.fieldForm.field_type, is_required: this.fieldForm.is_required, is_active: this.fieldForm.is_active, translations: this.buildTranslationsArray() };
            let url = config.storeUrl;
            if (this.editingField) url = `${config.updateUrlTemplate}/${this.editingField.id}/update`;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': config.csrfToken, [config.csrfName]: config.csrfToken },
                    body: JSON.stringify(payload),
                });
                const data = await response.json();
                if (data.ok) {
                    const field = normalizeField(data.data);
                    if (this.editingField) {
                        const index = this.fields.findIndex((item) => String(item.id) === String(field.id));
                        if (index >= 0) this.fields[index] = field;
                    } else {
                        this.fields.push(field);
                    }
                    this.closeModal();
                    bootLucideIcons();
                } else {
                    this.fieldError = (data.messages && data.messages[0]) || config.saveFieldFailedMessage || 'Could not save the field.';
                }
            } catch (error) {
                this.fieldError = error.message || config.saveFieldFailedMessage || 'Could not save the field.';
            }
        },

        async deleteField(field) {
            if (!confirm(config.confirmDeleteFieldMessage || 'Delete this field?')) return;
            try {
                const response = await fetch(`${config.deleteUrlTemplate}/${field.id}/delete`, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': config.csrfToken, [config.csrfName]: config.csrfToken },
                });
                const data = await response.json();
                if (data.ok) {
                    this.fields = this.fields.filter((item) => String(item.id) !== String(field.id));
                } else {
                    alert((data.messages && data.messages[0]) || config.deleteFailedMessage || 'Could not delete the field.');
                }
            } catch (error) { alert(error.message); }
        },

        async onReorder(event) {
            const ordered = Array.from(event.target.querySelectorAll('[data-id]'))
                .map((item) => parseInt(item.dataset.id, 10))
                .filter((id) => Number.isFinite(id));
            const sorted = [];
            ordered.forEach((id) => {
                const field = this.fields.find((item) => String(item.id) === String(id));
                if (field) sorted.push(field);
            });
            this.fields = sorted;
            await fetch(config.reorderUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': config.csrfToken, [config.csrfName]: config.csrfToken },
                body: JSON.stringify({ ordered_ids: ordered }),
            });
        },
    };
};
