<?php

declare(strict_types=1);

namespace App\Modules\Cms\Support;

/**
 * Resolves labels, routes, and URLs that vary by block-owner type ('page' vs 'entry').
 *
 * Pure/stateless — no API calls, no controller state. Extracted from
 * BlockInstanceController, which owns the actual API calls (fetchOwner()) and
 * uses this class only for the deterministic label/route lookups.
 */
class BlockOwnerRouting
{
    public const OWNER_PAGE = 'page';
    public const OWNER_ENTRY = 'entry';

    public static function label(string $ownerType): string
    {
        return $ownerType === self::OWNER_ENTRY
            ? lang('Pages.owner_label_entry')
            : lang('Pages.owner_label_page');
    }

    public static function childLabel(string $ownerType): string
    {
        return $ownerType === self::OWNER_ENTRY
            ? lang('Pages.child_label_subblock')
            : lang('Pages.child_label_slide');
    }

    public static function listRoute(string $ownerType): string
    {
        return $ownerType === self::OWNER_ENTRY ? route_to('admin.cms.entries') : route_to('admin.cms.pages');
    }

    public static function showRoute(string $ownerType): string
    {
        return $ownerType === self::OWNER_ENTRY ? 'admin.cms.entries.show' : 'admin.cms.pages.show';
    }

    /**
     * @return array{index:string,create:string,store:string,edit:string,update:string,delete:string,reorder:string,children:string,childrenReorder:string}
     */
    public static function routes(string $ownerType): array
    {
        $prefix = $ownerType === self::OWNER_ENTRY ? 'admin.cms.entries.blocks' : 'admin.cms.pages.blocks';

        return [
            'index'           => $prefix,
            'create'          => $prefix . '.create',
            'store'           => $prefix . '.store',
            'edit'            => $prefix . '.edit',
            'update'          => $prefix . '.update',
            'delete'          => $prefix . '.delete',
            'reorder'         => $prefix . '.reorder',
            'children'        => $prefix . '.children',
            'childrenReorder' => $prefix . '.children.reorder',
        ];
    }

    /** @param array<string, mixed> $owner */
    public static function previewUrl(string $ownerType, array $owner): string
    {
        if ($ownerType !== self::OWNER_PAGE) {
            return '';
        }

        foreach (($owner['translations'] ?? []) as $translation) {
            if (! is_array($translation)) {
                continue;
            }

            $slug = (string) ($translation['slug'] ?? '');
            if ($slug === '') {
                continue;
            }

            $publicSiteUrl = rtrim((string) env('PUBLIC_SITE_URL'), '/');
            if ($publicSiteUrl === '') {
                return '';
            }

            return $publicSiteUrl . '/' . ltrim($slug, '/');
        }

        return '';
    }

    public static function notFoundMessage(string $ownerType): string
    {
        return $ownerType === self::OWNER_ENTRY
            ? lang('Pages.owner_not_found_entry')
            : lang('Pages.pages_not_found');
    }
}
