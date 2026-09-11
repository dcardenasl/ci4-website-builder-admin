<?php

declare(strict_types=1);

namespace Tests\Unit\Architecture;

use App\Support\CmsFieldEnums;
use CodeIgniter\Test\CIUnitTestCase;
use ReflectionClass;

final class LocalCmsFieldEnumsTest extends CIUnitTestCase
{
    public function testCmsFieldEnumsIsOwnedByAdmin(): void
    {
        $filename = (new ReflectionClass(CmsFieldEnums::class))->getFileName();

        $this->assertNotFalse($filename);
        $this->assertStringStartsWith(APPPATH, (string) $filename);
        $this->assertSame(
            'in_list[media_reference,repeater,boolean,integer,select,number]',
            CmsFieldEnums::inListRule(CmsFieldEnums::NON_TRANSLATABLE_TYPES),
        );
    }
}
