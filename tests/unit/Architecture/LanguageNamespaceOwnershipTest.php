<?php

declare(strict_types=1);

namespace Tests\Unit\Architecture;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Keeps language namespaces owned by one source tree.
 *
 * The six namespaces covered here already have module consumers. Keeping a
 * second root catalog makes the result depend on locator order and hides
 * missing keys until a view is rendered in the other locale.
 */
final class LanguageNamespaceOwnershipTest extends CIUnitTestCase
{
    /** @var list<string> */
    private const MODULE_NAMESPACES = [
        'Auth',
        'Collections',
        'FormSubmissions',
        'Forms',
        'Pages',
        'Profile',
    ];

    public function testModuleNamespacesDoNotHaveRootDuplicates(): void
    {
        $rootNamespaces = array_map(
            static fn (string $path): string => pathinfo($path, PATHINFO_FILENAME),
            glob(APPPATH . 'Language/es/*.php') ?: [],
        );
        $moduleNamespaces = array_map(
            static fn (string $path): string => pathinfo($path, PATHINFO_FILENAME),
            glob(APPPATH . 'Modules/*/Language/es/*.php') ?: [],
        );

        $collisions = array_values(array_intersect($rootNamespaces, $moduleNamespaces));
        sort($collisions);

        self::assertSame([], array_values(array_intersect(self::MODULE_NAMESPACES, $collisions)));
    }

    public function testMovedConsumerKeysRemainInModuleCatalogs(): void
    {
        $auth = require APPPATH . 'Modules/Auth/Language/es/Auth.php';
        self::assertArrayHasKey('login_signing_in', $auth);
        self::assertArrayHasKey('login_signing_in_google', $auth);

        $collections = require APPPATH . 'Modules/Cms/Language/es/Collections.php';
        foreach ([
            'collection_type_blog',
            'collection_type_news',
            'collection_type_portfolio',
            'collection_type_services',
            'collection_type_other',
            'wizard_steps_builder_tab',
            'wizard_steps_field_title',
            'wizard_steps_field_og_image',
        ] as $key) {
            self::assertArrayHasKey($key, $collections, "Missing Collections.{$key}.");
        }

        $submissions = require APPPATH . 'Modules/Cms/Language/es/FormSubmissions.php';
        foreach ([
            'field_date',
            'field_email',
            'field_message',
            'field_status',
            'field_form_key',
            'field_page_id',
            'submissions_invalid_status',
        ] as $key) {
            self::assertArrayHasKey($key, $submissions, "Missing FormSubmissions.{$key}.");
        }

        $forms = require APPPATH . 'Modules/Cms/Language/es/Forms.php';
        foreach (['field_key_required', 'save_field_failed', 'confirm_delete_field', 'delete_failed'] as $key) {
            self::assertArrayHasKey($key, $forms, "Missing Forms.{$key}.");
        }
    }
}
