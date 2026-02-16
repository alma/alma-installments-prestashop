<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Helper;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Helper\EncryptionHelper;

class EncryptionHelperTest extends TestCase
{
    public function testCheckIfIsEncryptionValueWithValueReturnTrue(): void
    {
        $this->assertTrue(EncryptionHelper::isEncryptionValue(true, 'value'));
    }

    public function testCheckIfIsEncryptionValueWithoutValueReturnFalse(): void
    {
        $this->assertFalse(EncryptionHelper::isEncryptionValue(true, ''));
    }

    public function testCheckIfIsNotEncryptionValueWithValueReturnFalse(): void
    {
        $this->assertFalse(EncryptionHelper::isEncryptionValue(false, 'value'));
    }

    public function testCheckIfIsNotEncryptionValueWithoutValueReturnFalse(): void
    {
        $this->assertFalse(EncryptionHelper::isEncryptionValue(false, ''));
    }
}
