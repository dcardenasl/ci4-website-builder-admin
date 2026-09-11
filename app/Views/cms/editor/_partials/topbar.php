<?php declare(strict_types=1); ?>
<header class="flex min-h-14 flex-none flex-wrap items-center justify-between gap-2 border-b border-gray-200 bg-white px-4 py-2 lg:flex-nowrap lg:gap-4">
    <div class="flex w-full min-w-0 items-center gap-3 lg:w-auto">
        <a :href="endpoints.back" class="rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-900" :aria-label="t('back')">
            <span aria-hidden="true">&larr;</span>
        </a>
        <h1 class="truncate text-sm font-semibold text-gray-900" x-text="documentTitle"></h1>
    </div>

    <div class="flex items-center gap-2">
        <div class="flex rounded-lg bg-gray-100 p-1" role="group" :aria-label="t('translationLanguages')">
            <template x-for="locale in locales" :key="locale.code">
                <button type="button"
                        class="inline-flex items-center gap-1.5 rounded-md px-3 py-1 text-xs font-semibold"
                        :class="locale.code === activeLocale ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500'"
                        :aria-pressed="locale.code === activeLocale"
                        :title="localeName(locale.code)"
                        @click="switchLocale(locale.code)">
                    <span x-text="locale.code.toUpperCase()"></span>
                    <span class="h-1.5 w-1.5 rounded-full"
                          :class="locale.is_default || localeStatusLabel(locale) === t('translationComplete') ? 'bg-green-500' : 'bg-amber-500'"
                          aria-hidden="true"></span>
                </button>
            </template>
        </div>

        <div class="relative" @keydown.escape.window="translationOpen = false">
            <button type="button"
                    class="inline-flex min-h-9 items-center gap-1.5 rounded-lg border border-brand-200 bg-brand-50 px-3 text-xs font-semibold text-brand-700 transition hover:bg-brand-100 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2"
                    :aria-expanded="translationOpen"
                    aria-controls="canvas-translation-panel"
                    @click="translationOpen = !translationOpen">
                <?= ui_icon('languages', 'h-4 w-4') ?>
                <span x-text="t('translateAll')"></span>
                <span x-show="translationStatsFor(activeLocale).missing > 0" x-cloak
                      class="rounded-full bg-amber-100 px-1.5 py-0.5 text-[10px] text-amber-800"
                      x-text="translationStatsFor(activeLocale).missing"></span>
            </button>

            <div id="canvas-translation-panel" x-show="translationOpen" x-cloak @click.outside="translationOpen = false"
                 class="absolute right-0 top-full z-50 mt-2 w-[min(21rem,calc(100vw-2rem))] rounded-xl border border-gray-200 bg-white p-4 text-left shadow-xl">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900" x-text="t('translateAllContent')"></h2>
                        <p class="mt-1 text-xs leading-5 text-gray-500" x-text="translationHelpText"></p>
                    </div>
                    <span class="rounded-full bg-brand-50 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-brand-700"
                          x-text="defaultLocale.toUpperCase()"></span>
                </div>

                <div class="mt-4 space-y-1 border-y border-gray-100 py-2" role="list" :aria-label="t('translationLanguages')">
                    <template x-for="locale in locales" :key="locale.code">
                        <div role="listitem" class="flex items-center gap-2 rounded-lg px-2 py-2 transition hover:bg-gray-50">
                            <input x-show="!locale.is_default" type="checkbox"
                                   class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500"
                                   :value="locale.code" x-model="translationSelectedLocales"
                                   :aria-label="locale.name" :disabled="translationBusy">
                            <span x-show="locale.is_default" class="h-4 w-4" aria-hidden="true"></span>
                            <button type="button" class="flex min-w-0 flex-1 items-center justify-between gap-2 rounded-md text-left focus:outline-none focus:ring-2 focus:ring-brand-500"
                                    @click="switchLocale(locale.code)">
                                <span class="flex items-center gap-2">
                                    <span class="w-7 text-xs font-bold text-gray-700" x-text="locale.code.toUpperCase()"></span>
                                    <span class="truncate text-xs text-gray-500" x-text="locale.name"></span>
                                </span>
                                <span class="shrink-0 text-[11px] font-semibold"
                                      :class="locale.is_default ? 'text-gray-400' : (translationStatsFor(locale.code).missing ? 'text-amber-700' : 'text-green-700')"
                                      x-text="localeStatusLabel(locale)"></span>
                            </button>
                        </div>
                    </template>
                </div>

                <label class="mt-3 flex cursor-pointer items-start gap-2 rounded-lg bg-gray-50 px-2.5 py-2">
                    <input type="checkbox" class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500"
                           x-model="translationOverwrite" :disabled="translationBusy">
                    <span>
                        <span class="block text-xs font-semibold text-gray-700" x-text="t('translationOverwrite')"></span>
                        <span class="mt-0.5 block text-[11px] leading-4 text-gray-500" x-text="t('translationOverwriteHelp')"></span>
                    </span>
                </label>

                <button type="button"
                        class="btn-primary mt-3 w-full text-xs"
                        :disabled="translationBusy || !translationJobs().length"
                        :aria-busy="translationBusy"
                        @click="translateAll()">
                    <span x-show="!translationBusy" class="inline-flex items-center gap-2">
                        <?= ui_icon('languages', 'h-4 w-4') ?>
                        <span x-text="t('translationStart')"></span>
                    </span>
                    <span x-show="translationBusy" x-cloak class="inline-flex items-center gap-2">
                        <?= ui_icon('loader', 'h-4 w-4 animate-spin') ?>
                        <span x-text="t('translationTranslating')"></span>
                        <span x-text="translationProgressLabel"></span>
                    </span>
                </button>

                <p x-show="translationSuccess" x-text="translationSuccess" x-cloak class="mt-2 text-xs text-green-700" role="status"></p>
                <p x-show="translationError" x-text="translationError" x-cloak class="mt-2 text-xs text-red-700" role="alert"></p>
            </div>
        </div>

        <div class="flex rounded-lg bg-gray-100 p-1" role="group">
            <button type="button" class="rounded-md px-3 py-1 text-xs font-semibold"
                    :class="viewport === 'desktop' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500'"
                    :aria-pressed="viewport === 'desktop'"
                    x-text="t('desktop')" @click="viewport = 'desktop'"></button>
            <button type="button" class="rounded-md px-3 py-1 text-xs font-semibold"
                    :class="viewport === 'mobile' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500'"
                    :aria-pressed="viewport === 'mobile'"
                    x-text="t('mobile')" @click="viewport = 'mobile'"></button>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <p class="flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-600" role="status" aria-live="polite">
            <span class="h-2 w-2 rounded-full"
                  :class="{ 'bg-green-500': status === 'saved', 'bg-amber-500': status === 'dirty' || status === 'saving', 'bg-red-500': status === 'error' || status === 'conflict' }"></span>
            <span x-text="statusLabel"></span>
        </p>
        <button type="button" x-show="endpoints.publish" class="btn-primary text-xs" :disabled="publishing || inFlight || status === 'conflict' || undo.visible"
                :aria-busy="publishing" x-text="publishing ? t('publishing') : t('publish')" @click="publish()"></button>
        <button type="button" x-show="status === 'error'" class="btn-secondary text-xs" x-text="t('retry')" @click="save()"></button>
        <button type="button" x-show="status === 'conflict'" class="btn-secondary text-xs" x-text="t('reload')" @click="reload()"></button>
        </div>

    <p class="w-full rounded-md px-3 py-2 text-xs"
       :class="feedbackIsError ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700'"
       :role="feedbackIsError ? 'alert' : 'status'"
       x-show="feedbackMessage" x-text="feedbackMessage"></p>
</header>
