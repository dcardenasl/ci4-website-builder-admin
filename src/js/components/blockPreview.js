export const blockPreview = () => ({
    isOpen: false,
    loading: false,
    error: '',
    html: '',
    blockKey: '',

    openWithEvent(event) {
        const { blockKey, blockConfig, blockData } = event.detail || {};
        this.open(blockKey || '', blockConfig || {}, blockData || {});
    },

    open(blockKey, blockConfig, blockData) {
        this.isOpen  = true;
        this.loading = true;
        this.error   = '';
        this.html    = '';
        this.blockKey = blockKey;

        const previewUrl = document.querySelector('meta[name="block-preview-url"]')?.getAttribute('content') || '/admin/cms/blocks/preview';
        const csrfInput  = document.querySelector('input[name^="ci4_"][name$="_csrf_token"]') || document.querySelector('input[name="csrf_token"]');
        const csrfName   = csrfInput?.name  || 'csrf_token';
        const csrfToken  = csrfInput?.value || '';

        const body = new URLSearchParams({
            block_key:    blockKey,
            block_config: JSON.stringify(blockConfig),
            block_data:   JSON.stringify(blockData),
            [csrfName]:   csrfToken,
        });

        fetch(previewUrl, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body })
            .then((r) => r.json())
            .then((json) => { this.html = json.html || ''; this.loading = false; })
            .catch((err) => {
                this.error = 'Error al cargar el preview: ' + (err instanceof Error ? err.message : String(err));
                this.loading = false;
            });
    },

    close() { this.isOpen = false; },
});
