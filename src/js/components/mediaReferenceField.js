import { bestFileOriginalUrl, bestFilePreviewUrl } from '../utils/fileUrl.js';

const DEFAULT_SOURCE_KIND = 'hub_file';

const normalizeReferenceValue = (value = {}) => {
    const raw = (value && typeof value === 'object' && !Array.isArray(value)) ? value : {};
    const fileId = String(raw.file_id ?? raw.fileId ?? '');
    const url = String(raw.url ?? raw.external_url ?? '');
    let sourceKind = String(raw.source_kind ?? raw.sourceKind ?? '').trim().toLowerCase();

    if (sourceKind === '') {
        sourceKind = fileId !== '' ? 'hub_file' : (url !== '' ? 'external_url' : DEFAULT_SOURCE_KIND);
    }

    if (sourceKind === 'external_url') {
        return {
            source_kind: sourceKind,
            file_id: '',
            url,
            preview_url: String(raw.preview_url ?? raw.previewUrl ?? url ?? ''),
        };
    }

    return {
        source_kind: sourceKind,
        file_id: fileId,
        url,
        preview_url: String(raw.preview_url ?? raw.previewUrl ?? url ?? ''),
    };
};

export const mediaReferenceField = (initialValue = {}, accept = 'image', fieldKey = '') => {
    const normalized = normalizeReferenceValue(initialValue);

    return {
        fieldKey: String(fieldKey || ''),
        sourceKind: normalized.source_kind,
        fileId: normalized.file_id,
        url: normalized.url,
        previewUrl: normalized.preview_url,
        accept: String(accept || 'image'),
        pickerLabels: {
            image:    { select: 'Seleccionar imagen',    change: 'Cambiar imagen' },
            video:    { select: 'Seleccionar video',     change: 'Cambiar video' },
            document: { select: 'Seleccionar documento', change: 'Cambiar documento' },
            audio:    { select: 'Seleccionar audio',     change: 'Cambiar audio' },
            any:      { select: 'Seleccionar archivo',    change: 'Cambiar archivo' },
        },

        init() {
            this._applySourceDefaults();
        },

        _applySourceDefaults() {
            const normalizedValues = normalizeReferenceValue({
                source_kind: this.sourceKind,
                file_id: this.fileId,
                url: this.url,
                preview_url: this.previewUrl,
            });
            this.sourceKind = normalizedValues.source_kind;
            this.fileId = normalizedValues.file_id;
            this.url = normalizedValues.url;
            this.previewUrl = normalizedValues.preview_url;
        },

        isFileSource() {
            return this.sourceKind === 'hub_file';
        },

        isExternalSource() {
            return this.sourceKind === 'external_url';
        },

        sourceKindLabel() {
            return this.isExternalSource() ? 'URL externa' : 'Biblioteca';
        },

        sourceKindHint(kind = this.sourceKind) {
            return String(kind) === 'external_url'
                ? 'Pega un enlace público directo.'
                : 'Selecciona un archivo desde la biblioteca.';
        },

        sourceKindButtonClass(kind) {
            const active = this.sourceKind === kind;
            return active
                ? 'border-brand-200 bg-brand-50 text-brand-800 ring-1 ring-brand-100 shadow-sm'
                : 'border-gray-200 bg-white text-gray-700 hover:border-brand-300 hover:bg-gray-50';
        },

        sourceKindDotClass(kind) {
            return this.sourceKind === kind ? 'bg-brand-500' : 'bg-gray-300';
        },

        pickerButtonLabel() {
            if (this.isExternalSource()) {
                return 'Abrir biblioteca';
            }

            return this.fileId !== ''
                ? (this.pickerLabels[this.accept]?.change || 'Cambiar archivo')
                : (this.pickerLabels[this.accept]?.select || 'Seleccionar archivo');
        },

        setSourceKind(kind) {
            const nextKind = String(kind || DEFAULT_SOURCE_KIND);
            if (nextKind === 'external_url') {
                const externalUrl = this.isExternalSource() ? this.url.trim() : '';
                this.applyReference({
                    source_kind: 'external_url',
                    file_id: '',
                    url: externalUrl,
                    preview_url: externalUrl,
                });
                return;
            }

            const hasPickedFile = String(this.fileId || '') !== '';
            this.applyReference({
                source_kind: 'hub_file',
                file_id: this.fileId,
                url: hasPickedFile ? this.url : '',
                preview_url: hasPickedFile ? this.previewUrl : '',
            });
        },

        applyReference(reference = {}) {
            const normalizedValues = normalizeReferenceValue(reference);
            this.sourceKind = normalizedValues.source_kind;
            this.fileId = normalizedValues.file_id;
            this.url = normalizedValues.url;
            this.previewUrl = normalizedValues.preview_url;
        },

        openPicker() {
            const filterTypeMap = { video: 'video', document: 'document', audio: 'audio' };
            const filterType = filterTypeMap[this.accept] ?? 'image';
            const mimeAccept = this.accept === 'any'
                ? ''
                : this.accept.includes('/')
                    ? this.accept
                    : this.accept + '/*';

            this.sourceKind = 'hub_file';
            Alpine.store('filePicker').show({
                filterType,
                accept: mimeAccept,
                multi: false,
                onSelect: (file) => {
                    this.applyReference({
                        source_kind: 'hub_file',
                        file_id: String(file.id ?? ''),
                        url: String(bestFileOriginalUrl(file) || file.url || ''),
                        preview_url: String(bestFilePreviewUrl(file) || file.url || ''),
                    });
                },
            });
        },

        syncExternalUrl() {
            if (this.isExternalSource()) {
                this.applyReference({
                    source_kind: 'external_url',
                    file_id: '',
                    url: this.url.trim(),
                    preview_url: this.url.trim(),
                });
            }
        },

        clearReference() {
            this.applyReference({
                source_kind: DEFAULT_SOURCE_KIND,
                file_id: '',
                url: '',
                preview_url: '',
            });
        },

        copyToAllLanguages() {
            if (this.fieldKey === '' || typeof window.copyLangTabsMediaReferenceFieldToAll !== 'function') {
                return;
            }

            window.copyLangTabsMediaReferenceFieldToAll(this.fieldKey, {
                source_kind: this.sourceKind,
                file_id: this.fileId,
                url: this.url,
                preview_url: this.previewUrl,
            });
        },
    };
};
