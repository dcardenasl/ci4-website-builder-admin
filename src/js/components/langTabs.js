/* global HTMLTextAreaElement, Event */
import { resolveTranslatableFilePreviewUrl } from '../utils/fileUrl.js';

export const langTabs = (defaultId = 0, translateUrl = '', sourceLangCode = 'EN') => ({
    active: defaultId,
    translating: false,
    translateError: '',
    translatingAll: false,
    translateAllProgress: '',

    isActive(id) { return this.active === id; },
    setTab(id) { this.active = id; },

    async _translatePairs(targetLangCode, fieldPairs) {
        for (const pair of fieldPairs) {
            const sourceEl = document.querySelector(pair.from);
            const targetEl = document.querySelector(pair.to);
            if (!(sourceEl instanceof HTMLInputElement || sourceEl instanceof HTMLTextAreaElement)) continue;
            if (!(targetEl instanceof HTMLInputElement || targetEl instanceof HTMLTextAreaElement)) continue;
            const sourceText = sourceEl.value.trim();
            if (sourceText === '') continue;
            const url = new URL(translateUrl, window.location.origin);
            url.searchParams.set('text', sourceText);
            url.searchParams.set('source_lang', sourceLangCode.toUpperCase());
            url.searchParams.set('target_lang', targetLangCode.toUpperCase());
            const res = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            const json = await res.json();
            if (json && typeof json.translated === 'string') {
                targetEl.value = json.translated;
                targetEl.dispatchEvent(new Event('input', { bubbles: true }));
            } else if (json && json.error) {
                throw new Error(json.error);
            }
        }
    },

    async autoTranslate(targetLangCode, fieldPairs) {
        if (translateUrl === '' || this.translating || this.translatingAll) return;
        this.translating = true;
        this.translateError = '';
        try { await this._translatePairs(targetLangCode, fieldPairs); }
        catch (e) { this.translateError = e instanceof Error ? e.message : String(e); }
        finally { this.translating = false; }
    },

    async autoTranslateAll(targets) {
        if (translateUrl === '' || this.translating || this.translatingAll) return;
        this.translatingAll = true;
        this.translateError = '';
        try {
            for (let i = 0; i < targets.length; i++) {
                const { langCode, fieldPairs } = targets[i];
                this.translateAllProgress = langCode + ' (' + (i + 1) + '/' + targets.length + ')';
                await this._translatePairs(langCode, fieldPairs);
            }
            this.translateAllProgress = '';
        } catch (e) {
            this.translateError = e instanceof Error ? e.message : String(e);
            this.translateAllProgress = '';
        } finally { this.translatingAll = false; }
    },

    copyFieldToAll(sourceSelector, targetSelectors) {
        const sourceEl = document.querySelector(sourceSelector);
        if (!(sourceEl instanceof HTMLInputElement || sourceEl instanceof HTMLTextAreaElement)) return;
        const sourceValue = sourceEl.value;
        for (const targetSelector of targetSelectors) {
            const targetEl = document.querySelector(targetSelector);
            if (targetEl instanceof HTMLInputElement || targetEl instanceof HTMLTextAreaElement) {
                targetEl.value = sourceValue;
                targetEl.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }
    },

    copyFileFieldToAll(sourceFileIdSelector, sourceFileUrlSelector, fieldKeyPattern) {
        const allFileIdInputs = document.querySelectorAll(`input[name*="[block_data][${fieldKeyPattern}_file_id]"]`);
        const allFileUrlInputs = document.querySelectorAll(`input[name*="[block_data][${fieldKeyPattern}_url]"]`);
        this.copyFileFieldToTargets(sourceFileIdSelector, sourceFileUrlSelector, allFileIdInputs, allFileUrlInputs);
    },

    copyFileFieldToTargets(sourceFileIdSelector, sourceFileUrlSelector, targetFileIdSelectors, targetFileUrlSelectors) {
        const sourceFileId = document.querySelector(sourceFileIdSelector);
        const sourceFileUrl = document.querySelector(sourceFileUrlSelector);
        if (!sourceFileId || !sourceFileUrl) { console.warn('[langTabs] Could not find source file elements'); return; }

        const sourceFileIdValue = String(sourceFileId.value || '');
        const sourceFileUrlValue = String(sourceFileUrl.value || '');
        const resolvedFileUrl = resolveTranslatableFilePreviewUrl(sourceFileIdValue, sourceFileUrlValue);

        const allFileIdInputs = Array.from(targetFileIdSelectors, (selector) =>
            typeof selector === 'string' ? document.querySelector(selector) : selector
        ).filter((input) => input instanceof HTMLInputElement);
        const allFileUrlInputs = Array.from(targetFileUrlSelectors, (selector) =>
            typeof selector === 'string' ? document.querySelector(selector) : selector
        ).filter((input) => input instanceof HTMLInputElement);
        const updatedComponents = new Set();

        allFileIdInputs.forEach((input) => {
            if (input.value === sourceFileIdValue) return;
            input.value = sourceFileIdValue;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            const fileFieldContainer = input.closest('[x-data*="translatableFileField"]');
            const componentData = fileFieldContainer?._x_dataStack?.[0];
            if (componentData && typeof componentData.applyFile === 'function') updatedComponents.add(componentData);
        });

        allFileUrlInputs.forEach((input) => {
            if (input.value === resolvedFileUrl) return;
            input.value = resolvedFileUrl;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            const fileFieldContainer = input.closest('[x-data*="translatableFileField"]');
            const componentData = fileFieldContainer?._x_dataStack?.[0];
            if (componentData && typeof componentData.applyFile === 'function') updatedComponents.add(componentData);
        });

        updatedComponents.forEach((componentData) => { componentData.applyFile(sourceFileIdValue, resolvedFileUrl); });
    },
});
