<div x-data="blockPreview()"
     x-show="isOpen"
     x-cloak
     class="fixed inset-0 z-[60] flex items-center justify-center p-4"
     @keydown.escape.window="close()"
     @block-preview-open.window="openWithEvent($event)">

    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="close()"></div>

    <!-- Modal -->
    <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-3xl max-h-[90vh] flex flex-col overflow-hidden">

        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 shrink-0">
            <div>
                <h3 class="text-base font-semibold text-gray-900">Vista Previa del Bloque</h3>
                <p x-show="blockKey" class="text-xs text-gray-500 mt-0.5">
                    Diseño: <code x-text="blockKey" class="font-mono bg-gray-100 px-1 rounded"></code>
                </p>
            </div>
            <button @click="close()" class="p-2 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Body -->
        <div class="overflow-y-auto flex-1 p-6">

            <!-- Loading -->
            <div x-show="loading" class="flex items-center justify-center py-16">
                <div class="w-8 h-8 border-4 border-brand-600 border-t-transparent rounded-full animate-spin"></div>
                <span class="ml-3 text-sm text-gray-500">Generando preview…</span>
            </div>

            <!-- Error -->
            <div x-show="!loading && error" class="rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-700">
                <p x-text="error"></p>
            </div>

            <!-- Preview HTML -->
            <div x-show="!loading && !error && html" x-html="html" class="preview-container"></div>

        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-gray-200 shrink-0 flex justify-end">
            <button @click="close()" class="btn-secondary text-sm">Cerrar</button>
        </div>
    </div>
</div>
