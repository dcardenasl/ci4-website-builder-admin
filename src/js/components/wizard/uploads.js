import { adminFetch } from '../../utils/wizard/adminFetch.js';

export const uploads = {
    async _uploadFile(file) {
        const fd = new FormData();
        fd.append('file', file);
        const res  = await adminFetch(this.wizardBase + '/upload', { method: 'POST', body: fd }, this.csrf);
        const data = await res.json();
        if (!res.ok) throw new Error(data?.message ?? this.strings.error_upload_failed);
        return data?.file ?? data;
    },

    // ── Image upload (entry wizard) ────────────────────────────────────
    async uploadImage(field, file) {
        if (!file) return;
        this.uploading = true;
        this.uploadError = '';
        try {
            const fileData = await this._uploadFile(file);
            this.formData[field.key + '_id']  = fileData?.id ?? null;
            this.formData[field.key + '_url'] = fileData?.url ?? fileData?.variants?.md?.url ?? null;
        } catch {
            this.uploadError = this.strings.error_upload;
        } finally {
            this.uploading = false;
        }
    },

    // ── Image upload (block editor) ────────────────────────────────────
    async uploadBlockImage(field, file) {
        if (!file) return;
        this.uploading = true;
        this.uploadError = '';
        try {
            const fileData = await this._uploadFile(file);
            this.blockEditData[field.key + '_file_id'] = fileData?.id ?? null;
            this.blockEditData[field.key + '_url']     = fileData?.url ?? fileData?.variants?.md?.url ?? null;
        } catch {
            this.uploadError = this.strings.error_upload;
        } finally {
            this.uploading = false;
        }
    },

    // ── Image upload (block content wizard step) ────────────────────────
    async uploadBlockContentImage(stepIdx, field, file) {
        if (!file) return;
        this.uploading = true;
        this.uploadError = '';
        try {
            const fileData = await this._uploadFile(file);
            if (!this.blockContentDrafts[stepIdx]) this.blockContentDrafts[stepIdx] = {};
            this.blockContentDrafts[stepIdx][field.key + '_file_id'] = fileData?.id ?? null;
            this.blockContentDrafts[stepIdx][field.key + '_url']     = fileData?.url ?? fileData?.variants?.md?.url ?? null;
        } catch {
            this.uploadError = this.strings.error_upload;
        } finally {
            this.uploading = false;
        }
    },
};
