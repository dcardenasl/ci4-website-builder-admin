import { bestFilePreviewUrl } from '../utils/fileUrl.js';

export const blockRepeaterField = (existingItems = [], itemFields = {}, fieldKey = '', langIdx = 0) => {
    const initItems = (existingItems || []).map((item) => {
        const out = {};
        Object.keys(itemFields || {}).forEach((subKey) => {
                if ((itemFields[subKey] || {}).type === 'file') {
                    out[subKey + '_file_id']     = String(item[subKey + '_file_id'] || '');
                    out[subKey + '_preview_url'] = '';
                    out[subKey + '_url']         = String(item[subKey + '_url'] || '');
                } else {
                out[subKey] = item[subKey] ?? '';
            }
        });
        return out;
    });

    return {
        items: initItems,
        itemFields: itemFields || {},
        fieldKey,
        langIdx,

        addItem() {
            const item = {};
            Object.keys(this.itemFields).forEach((subKey) => {
                if ((this.itemFields[subKey] || {}).type === 'file') {
                    item[subKey + '_file_id']     = '';
                    item[subKey + '_preview_url'] = '';
                    item[subKey + '_url']         = '';
                } else {
                    item[subKey] = '';
                }
            });
            this.items.push(item);
        },

        removeItem(idx) { this.items.splice(idx, 1); },

        openPickerForItem(itemIdx, subKey, accept) {
            const filterTypeMap = { video: 'video', document: 'document', audio: 'audio' };
            const filterType = filterTypeMap[accept] ?? 'image';
            const mimeAccept = accept === 'any' ? ''
                : accept.includes('/') ? accept
                : accept + '/*';
            Alpine.store('filePicker').show({
                filterType, accept: mimeAccept, multi: false,
                onSelect: (file) => {
                    const fileId = String(file.id ?? '');
                    const canonicalUrl = String(file.url || '');
                    const previewUrl = String(bestFilePreviewUrl(file) || file.url || '');
                    this.items[itemIdx][subKey + '_file_id']     = fileId;
                    this.items[itemIdx][subKey + '_url']         = canonicalUrl;
                    this.items[itemIdx][subKey + '_preview_url'] = previewUrl;
                },
            });
        },
    };
};
