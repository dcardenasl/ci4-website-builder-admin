const DEFAULT_LABELS = {
    slot_title: 'Title', slot_title_hint: 'Primary',
    slot_subtitle: 'Subtitle', slot_subtitle_hint: 'Secondary',
    slot_summary: 'Summary', slot_summary_hint: 'Quick read',
    slot_date: 'Date or time value', slot_date_hint: 'Metadata',
    slot_image: 'Image', slot_image_hint: 'Visual',
};

const normalizeInitial = (value) => {
    if (value && typeof value === 'object') return value;
    if (typeof value !== 'string' || value.trim() === '') return {};
    try {
        const parsed = JSON.parse(value);
        return parsed && typeof parsed === 'object' ? parsed : {};
    } catch {
        return {};
    }
};

const normalizeItems = (items) => Array.isArray(items)
    ? items.map((item) => ({
        id: item?.id || `${Date.now()}-${Math.random().toString(36).slice(2)}`,
        source: String(item?.source || ''),
        label: String(item?.label || ''),
        operator: String(item?.operator || 'equals'),
    }))
    : [];

export function listingProjectionEditor(catalog, initial = {}, initialCollection = '', labels = {}) {
    const raw = normalizeInitial(initial);
    const direction = String(raw.order?.direction || 'desc').toLowerCase();
    const projection = {
        version: Number(raw.version) > 0 ? Number(raw.version) : 1,
        slots: {
            title: String(raw.slots?.title || 'entry.title'),
            subtitle: String(raw.slots?.subtitle || ''),
            summary: String(raw.slots?.summary || 'entry.excerpt'),
            date: String(raw.slots?.date || ''),
            image: String(raw.slots?.image || ''),
        },
        extras: normalizeItems(raw.extras),
        order: {
            field: String(raw.order?.field || ''),
            direction: ['asc', 'desc'].includes(direction) ? direction : 'desc',
            public: raw.order?.public === true || raw.order?.public === '1' || raw.order?.public === 1,
        },
        filters: normalizeItems(raw.filters),
    };

    return {
        catalog: catalog && typeof catalog === 'object' ? catalog : {},
        projection,
        labels: { ...DEFAULT_LABELS, ...(labels && typeof labels === 'object' ? labels : {}) },
        initialCollection: String(initialCollection || ''),
        collection: '',
        slots: [],

        init() {
            this.slots = [
                { key: 'title', label: this.labels.slot_title, hint: this.labels.slot_title_hint, types: ['text', 'string'] },
                { key: 'subtitle', label: this.labels.slot_subtitle, hint: this.labels.slot_subtitle_hint, types: ['text', 'string', 'taxonomy'] },
                { key: 'summary', label: this.labels.slot_summary, hint: this.labels.slot_summary_hint, types: ['text', 'string'] },
                { key: 'date', label: this.labels.slot_date, hint: this.labels.slot_date_hint, types: ['date', 'datetime', 'text', 'string'] },
                { key: 'image', label: this.labels.slot_image, hint: this.labels.slot_image_hint, types: ['media_reference'] },
            ];
            this.syncContext(this.initialCollection);
        },

        syncContext(value) {
            this.collection = String(value || '');
            const valid = new Set(this.fields().map((field) => field.value));
            Object.keys(this.projection.slots).forEach((key) => {
                if (this.projection.slots[key] && !valid.has(this.projection.slots[key])) {
                    this.projection.slots[key] = key === 'title' && valid.has('entry.title') ? 'entry.title' : '';
                }
            });
            if (this.projection.order.field && !valid.has(this.projection.order.field)) this.projection.order.field = '';
            this.projection.extras = this.projection.extras.filter((item) => !item.source || valid.has(item.source));
            this.projection.filters = this.projection.filters.filter((item) => !item.source || valid.has(item.source));
        },

        fields() {
            return Array.isArray(this.catalog[this.collection]) ? this.catalog[this.collection] : [];
        },

        availableFields(criteria = {}) {
            return this.fields().filter((field) => {
                if (criteria.sortable === true && field.sortable !== true) return false;
                if (criteria.filterable === true && field.filterable !== true) return false;
                if (Array.isArray(criteria.types) && !criteria.types.includes(field.type)) return false;
                return true;
            });
        },

        setSlot(key, value) { this.projection.slots[key] = String(value || ''); },
        addExtra() { this.projection.extras.push({ id: `${Date.now()}-${Math.random()}`, source: '', label: '', operator: 'equals' }); },
        removeExtra(index) { this.projection.extras.splice(index, 1); },
        addFilter() { this.projection.filters.push({ id: `${Date.now()}-${Math.random()}`, source: '', label: '', operator: 'equals' }); },
        removeFilter(index) { this.projection.filters.splice(index, 1); },
        serialized() {
            return JSON.stringify({
                version: this.projection.version,
                slots: this.projection.slots,
                extras: this.projection.extras.map(({ source, label, operator }) => ({ source, label, operator })),
                order: this.projection.order,
                filters: this.projection.filters.map(({ source, label, operator }) => ({ source, label, operator })),
            });
        },
    };
}
