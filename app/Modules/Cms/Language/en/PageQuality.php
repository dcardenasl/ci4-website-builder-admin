<?php

declare(strict_types=1);

return [
    'title' => 'Page quality',
    'description' => 'Editorial and SEO readiness checks from the Domain.',
    'unavailable' => 'The Domain quality report is temporarily unavailable. The page remains editable.',
    'status_ready' => 'Ready',
    'status_warning' => 'Needs review',
    'status_blocked' => 'Blocked',
    'score' => 'Score',
    'errors' => 'Errors',
    'warnings' => 'Warnings',
    'passed' => 'Passed',
    'unknown_issue' => 'A quality check needs attention.',
    'check_default_title_required' => 'The default language needs a title.',
    'check_default_slug_required' => 'The default language needs a slug.',
    'check_translation_title_missing' => 'A translation is missing its title.',
    'check_translation_slug_missing' => 'A translation is missing its slug.',
    'check_meta_title_missing' => 'A meta title is missing.',
    'check_meta_description_missing' => 'A meta description is missing.',
    'check_meta_title_too_long' => 'A meta title is longer than recommended.',
    'check_meta_description_too_long' => 'A meta description is longer than recommended.',
    'check_schema_data_invalid' => 'Structured data contains invalid JSON.',
    'check_og_image_missing' => 'An Open Graph image is missing.',
    'check_page_heading_missing' => 'The page has no heading block.',
    'check_page_heading_multiple' => 'More than one block owns the page heading.',
    'check_active_blocks_missing' => 'The page has no active content blocks.',
    'check_sitemap_robots_inconsistent' => 'Sitemap inclusion conflicts with noindex.',
];
