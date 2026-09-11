<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Locally-owned CMS field and menu enum values consumed by Admin.
 *
 * Admin validates these values before sending them to the API, but it must
 * not require the Domain repository's source tree just to load a controller.
 * Keep these lists aligned manually with the API contract when it changes.
 */
final class CmsFieldEnums
{
    /** @var list<string> */
    public const MENU_LINK_TYPES = ['page', 'entry', 'collection_listing', 'event_listing', 'custom_url', 'no_link'];

    /** @var list<string> */
    public const NON_TRANSLATABLE_TYPES = ['media_reference', 'repeater', 'boolean', 'integer', 'select', 'number'];

    /** @param array<string> $values */
    public static function inListRule(array $values): string
    {
        return 'in_list[' . implode(',', $values) . ']';
    }
}
