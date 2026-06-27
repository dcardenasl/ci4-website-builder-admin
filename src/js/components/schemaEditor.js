export const schemaEditor = (initialSchema = {}, initialIsContainer = false) => ({
    schemaFields: [],
    configFields: [],
    schemaJson: '{}',
    isContainer: initialIsContainer,
    allowedChildren: [],

    init() {
        this.schemaFields = this._schemaToRows(initialSchema.fields || {});
        this.configFields = this._schemaToRows(initialSchema.config_fields || {});
        this.allowedChildren = initialSchema.allowed_children || [];
        this.rebuildJson();
    },

    _schemaToRows(fieldsObj) {
        return Object.entries(fieldsObj).map(([key, def]) => ({
            key,
            type:     def.type    || 'string',
            label:    def.label   || key,
            required: def.required === true || def.required === 1,
            options:  Array.isArray(def.options) ? def.options.join(', ') : '',
            default:  def.default || '',
            item_fields: def.item_fields ? this._schemaToRows(def.item_fields) : [],
        }));
    },

    addField(section, parentRow = null) {
        const row = { key: '', type: 'string', label: '', required: false, options: '', default: '', item_fields: [] };
        if (parentRow) { if (!parentRow.item_fields) parentRow.item_fields = []; parentRow.item_fields.push(row); }
        else if (section === 'config') { this.configFields.push(row); }
        else { this.schemaFields.push(row); }
        this.rebuildJson();
    },

    removeField(section, index, parentRow = null) {
        if (parentRow) { parentRow.item_fields.splice(index, 1); }
        else if (section === 'config') { this.configFields.splice(index, 1); }
        else { this.schemaFields.splice(index, 1); }
        this.rebuildJson();
    },

    rebuildJson() {
        const buildObj = (rows) => {
            const obj = {};
            for (const row of rows) {
                if (!row.key) continue;
                const def = { type: row.type, label: row.label, required: row.required };
                if (row.type === 'select' && row.options) def.options = row.options.split(',').map((s) => s.trim()).filter(Boolean);
                if (row.type === 'repeater') def.item_fields = buildObj(row.item_fields || []);
                if (row.default !== '') def.default = row.default;
                obj[row.key] = def;
            }
            return obj;
        };
        const schema = { fields: buildObj(this.schemaFields), config_fields: buildObj(this.configFields) };
        if (this.isContainer) schema.allowed_children = this.allowedChildren || [];
        this.schemaJson = JSON.stringify(schema, null, 2);
    },
});
