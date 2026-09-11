import blocks from './blocks.js';
import bridge from './bridge.js';
import i18n from './i18n.js';
import props from './props.js';
import state from './state.js';
import tree from './tree.js';

/**
 * Object spread would invoke the getters once and freeze their result, which
 * breaks Alpine's reactivity. Copying the descriptors keeps them accessors.
 */
function assign(target, source) {
    Object.defineProperties(target, Object.getOwnPropertyDescriptors(source));

    return target;
}

export default function canvasEditor() {
    const component = {};
    let onBeforeUnload;

    assign(component, state());
    assign(component, blocks());
    assign(component, tree());
    assign(component, props());
    assign(component, i18n());
    assign(component, bridge());

    component.t = function (key) {
        return (this.boot.strings || {})[key] || key;
    };

    component.init = function () {
        this.boot = window.__canvasBoot || {};
        this.owner = this.boot.owner || {};
        this.endpoints = this.boot.endpoints || {};
        this.preview = this.boot.preview || {};
        this.applyDocument(this.boot.document || {});
        this.activeLocale = this.defaultLocale;
        this.translationSelectedLocales = this.locales
            .filter((locale) => !locale.is_default)
            .map((locale) => locale.code);
        this.translationSelectionInitialized = true;
        this.selectedRef = this.blocks.length > 0 ? this.blocks[0].ref : null;

        this.startBridge();
        this.$nextTick(() => {
            this.startSorting();
            this.observePreviewFrame();
            this.submitPreviewForm();
        });

        // A draft that never reached the server must not disappear silently.
        onBeforeUnload = (event) => {
            if (this.status !== 'saved' || this.undo.visible) {
                event.preventDefault();
                event.returnValue = '';
            }
        };
        window.addEventListener('beforeunload', onBeforeUnload);
    };

    component.destroy = function () {
        this.disposed = true;
        clearTimeout(this.saveTimer);
        clearTimeout(this.publishMessageTimer);
        clearTimeout(this.undo.timer);
        clearTimeout(this.previewTimer);
        this.stopSorting();
        this.stopBridge();
        window.removeEventListener('beforeunload', onBeforeUnload);
    };

    return component;
}
