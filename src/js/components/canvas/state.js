import { adoptBlockIds, reconcileDraft } from '../../utils/canvas/reconcileDraft.js';
import { buildOps } from '../../utils/canvas/patchBuilder.js';
import { csrfHeaders } from '../../utils/wizard/adminFetch.js';
import { normalizeDocument } from '../../utils/canvas/draftState.js';
import { saveFailure } from '../../utils/canvas/saveFailure.js';

/**
 * The draft and its relationship with the server: what is saved, what is in
 * flight, and what the editor has typed since.
 */
export default function state() {
    return {
        boot: {},
        owner: {},
        endpoints: {},
        preview: {},
        blocks: [],
        baseline: [],
        catalog: [],
        locales: [],
        documentOwner: {},
        version: '',
        documentTitle: '',
        status: 'saved',
        saveError: '',
        saveTimer: null,
        inFlight: false,
        publishing: false,
        publishMessage: '',
        publishError: '',
        publishMessageTimer: null,
        disposed: false,

        get statusLabel() {
            if (this.status === 'conflict') {
                return this.t('conflict');
            }

            return { saving: this.t('saving'), dirty: this.t('unsaved'), error: this.t('saveFailed') }[this.status] || this.t('saved');
        },

        applyDocument(document) {
            this.blocks = normalizeDocument(document);
            this.baseline = normalizeDocument(document);
            this.version = document.version || '';
            this.locales = document.locales || [];
            this.catalog = document.catalog || [];
            this.documentOwner = document.owner || {};
            this.documentTitle = this.documentOwner.title || '';
        },

        typeOf(blockKey) {
            return this.catalog.find((type) => type.block_key === blockKey) || null;
        },

        blockName(block) {
            const type = block ? this.typeOf(block.block_key) : null;

            return type ? type.name : (block ? block.block_key : '');
        },

        /** A conflict pauses autosave: the draft is kept until the editor decides. */
        touch() {
            if (this.disposed || this.status === 'conflict') {
                return;
            }

            this.status = 'dirty';
            clearTimeout(this.saveTimer);
            this.saveTimer = setTimeout(() => this.save(), 500);
        },

        async save() {
            if (this.disposed || this.inFlight || this.status === 'conflict' || this.undo?.visible) {
                return false;
            }

            const ops = buildOps(this.baseline, this.blocks);
            if (ops.length === 0) {
                this.status = 'saved';

                return true;
            }

            const sent = JSON.parse(JSON.stringify(this.blocks));
            this.inFlight = true;
            this.status = 'saving';
            this.saveError = '';

            try {
                const response = await fetch(this.endpoints.save, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        // CodeIgniter rotates the token on every validated
                        // request, so the one rendered at boot is stale after the
                        // first autosave. The readable mirror cookie always holds
                        // the token the *next* request will be accepted with.
                        ...csrfHeaders(this.boot.csrf || {}),
                    },
                    body: JSON.stringify({ base_version: this.version, ops }),
                });
                // A session that expired answers with the login page, and a
                // fatal error with an HTML trace: neither is this endpoint's
                // envelope, so decoding has to be allowed to fail.
                const payload = await response.json().catch(() => null);
                if (this.disposed) return;

                if (response.status === 409) {
                    this.status = 'conflict';

                    return false;
                }

                if (!response.ok || payload === null || payload.ok !== true) {
                    this.fail(response.status, payload);

                    return;
                }

                this.version = payload.data.document.version;
                this.adoptIds(payload.data.id_map || {});
                adoptBlockIds(sent, payload.data.id_map || {});
                this.baseline = normalizeDocument(payload.data.document);
                this.blocks = reconcileDraft(sent, this.blocks, this.baseline);
                this.status = buildOps(this.baseline, this.blocks).length ? 'dirty' : 'saved';
                if (this.status === 'dirty') this.touch();
                return true;
            } catch {
                // fetch only rejects when the request never completed.
                this.fail(0, null);
                return false;
            } finally {
                this.inFlight = false;
            }
        },

        async publish() {
            if (this.disposed || this.publishing || this.status === 'conflict' || this.undo?.visible) {
                return false;
            }

            this.publishError = '';
            this.publishMessage = '';
            if (this.status === 'dirty' && !await this.save()) {
                return false;
            }
            if (this.inFlight || this.status !== 'saved' || !this.endpoints.publish) {
                return false;
            }

            this.publishing = true;
            try {
                const response = await fetch(this.endpoints.publish, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        ...csrfHeaders(this.boot.csrf || {}),
                    },
                });
                const payload = await response.json().catch(() => null);
                if (!response.ok || payload === null || payload.ok !== true) {
                    const detail = payload?.messages?.filter?.((message) => typeof message === 'string' && message.trim() !== '')?.join(' ') || '';
                    this.publishError = detail || this.t('publishFailed');
                    return false;
                }

                this.publishMessage = payload.messages?.[0] || this.t('publishSuccess');
                clearTimeout(this.publishMessageTimer);
                this.publishMessageTimer = setTimeout(() => { this.publishMessage = ''; }, 5000);
                return true;
            } catch {
                this.publishError = this.t('networkError');
                return false;
            } finally {
                this.publishing = false;
            }
        },

        /**
         * The draft stays in memory either way; what changes is what the editor
         * is told, because an expired session and a rejected payload need
         * different actions from them.
         */
        fail(status, payload) {
            const { key, detail } = saveFailure(status, payload);
            this.status = 'error';
            this.saveError = detail !== '' ? `${this.t(key)} ${detail}` : this.t(key);
        },

        /** The status pill stays compact; the reason belongs to the alert row. */
        get saveErrorMessage() {
            return this.status === 'error' ? this.saveError : '';
        },

        get feedbackMessage() {
            return this.saveErrorMessage || this.publishError || this.previewError || this.publishMessage;
        },

        /** Only a failure is an alert; "published" shared its red box and its `role="alert"`. */
        get feedbackIsError() {
            return Boolean(this.saveErrorMessage || this.publishError || this.previewError);
        },

        /** A created block keeps its identity by adopting the id the server gave it. */
        adoptIds(idMap) {
            const retained = [...this.blocks, ...(this.undo?.removed || []).map((entry) => entry.block)];
            adoptBlockIds(retained, idMap);
            if (idMap[this.selectedRef]) this.selectedRef = `id_${idMap[this.selectedRef]}`;
            if (idMap[this.catalogParent]) this.catalogParent = `id_${idMap[this.catalogParent]}`;
            // The preview annotated the block under its temporary ref. Left
            // stale, the first edit of every new block missed the single-block
            // refresh and reloaded the whole preview instead.
            if (Array.isArray(this.annotatedRefs)) {
                this.annotatedRefs = this.annotatedRefs.map((ref) => (idMap[ref] ? `id_${idMap[ref]}` : ref));
            }
        },

        reload() {
            window.location.reload();
        },
    };
}
