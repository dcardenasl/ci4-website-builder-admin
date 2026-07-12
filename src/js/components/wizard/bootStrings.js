// Block type → display emoji. Not locale-dependent, so it stays a plain JS
// constant instead of coming from the server-injected boot config.
export const BLOCK_ICONS = {
    hero_slider:          '🎠',
    hero_banner:          '🖼️',
    slide_banner:         '🖼️',
    rich_text:            '📝',
    image:                '🖼️',
    collection_grid:      '▦',
    cta:                  '📢',
    form_embed:           '▣',
    video_player:         '🎬',
    accordion:            '▤',
    accordion_item:       '▤',
    cards_grid:           '▦',
    card_item:            '▢',
    metrics_grid:         '📊',
    metric_item:          '🔢',
    cards_slider:         '↔',
    slide_card:           '▢',
    asset_showcase:       '◇',
    asset_item:           '◇',
    contact_info:         '📍',
    map_embed:            '🗺️',
    social_links:         '🔗',
    container:            '📦',
    page_header:          '📄',
};

export function defaultSteps(strings) {
    return [
        {
            step_title: strings.default_step1_title,
            step_hint:  strings.default_step1_hint,
            fields: [{ key: 'title', label: strings.default_field_title, type: 'text', required: true }],
        },
        {
            step_title: strings.default_step2_title,
            step_hint:  strings.default_step2_hint,
            fields: [{ key: 'featured_image', label: strings.default_field_image, type: 'image', required: false }],
        },
        {
            step_title: strings.default_step3_title,
            step_hint:  strings.default_step3_hint,
            fields: [{ key: 'excerpt', label: strings.default_field_excerpt, type: 'textarea', required: false }],
        },
    ];
}
