import { isEmptyValue } from '../../utils/canvas/draftState.js';

const TRANSLATABLE_TYPES = new Set([
    'text', 'string', 'textarea', 'richtext', 'rich_text', 'rich-text', 'html',
]);

export function isTranslatableField(definition) {
    return TRANSLATABLE_TYPES.has(String(definition?.type || '').toLowerCase());
}

/**
 * One language at a time. A visitor never sees two, so neither does the canvas —
 * switching is a change of view, not an edit, and never marks the draft dirty.
 *
 * The translation workflow lives here instead of in the view so it can operate
 * on the same draft the canvas is already editing. It intentionally translates
 * only schema-declared text fields; URLs, media, colours and layout values are
 * content decisions, not language copy.
 */
export default function i18n() {
    return {
        activeLocale: '',
        fallbackRefs: [],
        translationOpen: false,
        translationBusy: false,
        translationOverwrite: false,
        translationSelectedLocales: [],
        translationSelectionInitialized: false,
        translationError: '',
        translationSuccess: '',
        translationProgress: { done: 0, total: 0, locale: '' },

        get defaultLocale() {
            const fallback = this.locales.find((locale) => locale.is_default);

            return fallback ? fallback.code : (this.locales[0] || {}).code || '';
        },

        get defaultLocaleLabel() {
            const locale = this.locales.find((entry) => entry.code === this.defaultLocale);

            return locale ? locale.name : this.defaultLocale.toUpperCase();
        },

        get copyLabel() {
            return this.t('copyFrom').replace('%s', this.defaultLocaleLabel);
        },

        get translationHelpText() {
            return this.t('translateAllHelp').replace('%s', this.defaultLocaleLabel);
        },

        get translationProgressLabel() {
            const progress = this.translationProgress;
            const locale = progress.locale ? ` · ${progress.locale.toUpperCase()}` : '';

            return `${progress.done}/${progress.total}${locale}`;
        },

        localeName(code) {
            const locale = this.locales.find((entry) => entry.code === code);

            return locale ? locale.name : String(code || '').toUpperCase();
        },

        translationFields(block) {
            const type = this.typeOf(block?.block_key);
            const fields = type && type.fields && typeof type.fields === 'object' ? type.fields : {};

            return Object.entries(fields)
                .filter(([, definition]) => isTranslatableField(definition))
                .map(([key, definition]) => ({ key, definition }));
        },

        translationStatsFor(code) {
            const sourceLocale = this.defaultLocale;
            let total = 0;
            let translated = 0;

            this.blocks.forEach((block) => {
                const source = block.i18n?.[sourceLocale] || {};
                const target = block.i18n?.[code] || {};
                this.translationFields(block).forEach(({ key }) => {
                    if (isEmptyValue(source[key])) {
                        return;
                    }

                    total += 1;
                    if (!isEmptyValue(target[key])) {
                        translated += 1;
                    }
                });
            });

            return { total, translated, missing: Math.max(0, total - translated) };
        },

        localeStatusLabel(locale) {
            if (locale.is_default) {
                return this.t('translationDefault');
            }

            const stats = this.translationStatsFor(locale.code);
            if (stats.total === 0) {
                return this.t('translationNoText');
            }

            return stats.missing > 0
                ? this.t('translationMissing').replace('%s', String(stats.missing))
                : this.t('translationComplete');
        },

        isTranslationLocaleSelected(code) {
            const selected = this.translationSelectionInitialized
                ? this.translationSelectedLocales
                : this.locales.filter((locale) => !locale.is_default).map((locale) => locale.code);

            return selected.includes(code);
        },

        translationJobs(overwrite = this.translationOverwrite) {
            const sourceLocale = this.defaultLocale;
            const jobs = [];

            this.locales.forEach((locale) => {
                if (locale.is_default || !this.isTranslationLocaleSelected(locale.code)) {
                    return;
                }

                this.blocks.forEach((block) => {
                    const source = block.i18n?.[sourceLocale] || {};
                    const target = block.i18n?.[locale.code] || {};
                    this.translationFields(block).forEach(({ key }) => {
                        const sourceText = source[key];
                        if (isEmptyValue(sourceText) || (!overwrite && !isEmptyValue(target[key]))) {
                            return;
                        }

                        jobs.push({ block, locale: locale.code, key, text: String(sourceText) });
                    });
                });
            });

            return jobs;
        },

        switchLocale(code) {
            if (code === this.activeLocale) {
                return;
            }

            this.activeLocale = code;
            this.translationOpen = false;
            this.documentTitle = this.documentOwner?.i18n?.[code]?.title || this.documentOwner?.title || '';
            this.syncBrowserTitle();
            this.refreshPreview();
        },

        async translateAll() {
            if (this.translationBusy || !this.endpoints.translate) {
                return;
            }

            if (this.translationOverwrite && typeof window.confirm === 'function'
                && !window.confirm(this.t('translationOverwriteConfirm'))) {
                return;
            }

            const jobs = this.translationJobs();
            this.translationError = '';
            this.translationSuccess = '';
            if (jobs.length === 0) {
                this.translationProgress = { done: 0, total: 0, locale: '' };
                this.translationSuccess = this.t('translationNothingToDo');

                return;
            }

            this.translationBusy = true;
            this.translationProgress = { done: 0, total: jobs.length, locale: '' };
            let completed = 0;
            let applied = 0;
            let failure = null;

            try {
                for (const job of jobs) {
                    this.translationProgress = {
                        done: completed,
                        total: jobs.length,
                        locale: this.localeName(job.locale),
                    };
                    const url = new URL(this.endpoints.translate, window.location.origin);
                    url.searchParams.set('text', job.text);
                    url.searchParams.set('source_lang', this.defaultLocale.toUpperCase());
                    url.searchParams.set('target_lang', job.locale.toUpperCase());
                    const response = await fetch(url, {
                        headers: { Accept: 'application/json' },
                        credentials: 'same-origin',
                    });
                    const payload = await response.json().catch(() => null);
                    if (!response.ok || typeof payload?.translated !== 'string') {
                        throw new Error(payload?.error || this.t('translationFailed'));
                    }

                    const current = job.block.i18n?.[job.locale] || {};
                    job.block.i18n = {
                        ...job.block.i18n,
                        [job.locale]: { ...current, [job.key]: payload.translated },
                    };
                    applied += 1;
                    completed += 1;
                    this.translationProgress = {
                        done: completed,
                        total: jobs.length,
                        locale: this.localeName(job.locale),
                    };
                }
            } catch (error) {
                failure = error instanceof Error ? error.message : String(error);
                this.translationError = failure;
            } finally {
                this.translationBusy = false;
                this.translationProgress = { done: completed, total: jobs.length, locale: '' };
                if (applied > 0) {
                    this.touch();
                    this.refreshPreview();
                }
                if (failure === null) {
                    this.translationSuccess = this.t('translationDone');
                }
            }
        },

        /**
         * The tab carries the same name as the heading. The document is served
         * with its default-language name; switching the content language has to
         * rename both or the two disagree about what is open.
         */
        syncBrowserTitle() {
            const suffix = this.t('title');
            if (this.documentTitle && suffix) {
                document.title = `${this.documentTitle} \u00b7 ${suffix}`;
            }
        },

        copyFallback(field) {
            this.edit(field, field.fallbackValue);
        },

        /** Marks the rail rows whose block is rendering source-language copy. */
        applyFallbackFlags(refs) {
            this.fallbackRefs = refs || [];
            this.blocks.forEach((block) => {
                block.untranslated = this.fallbackRefs.includes(block.ref);
            });
        },
    };
}
