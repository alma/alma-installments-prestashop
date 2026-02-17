<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Helper;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Helper\EncryptionHelper;

class EncryptionHelperTest extends TestCase
{
    /**
     * @var EncryptionHelper
     */
    private EncryptionHelper $encryptionHelper;

    public function setUp(): void
    {
        $this->phpEncryption = $this->createMock(\PhpEncryption::class);
        $this->encryptionHelper = new EncryptionHelper(
            $this->phpEncryption
        );
    }

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

    public function testEncryptionValue(): void
    {
        $value = 'test';
        $encryptedValue = 'encrypted_test';

        $this->phpEncryption->expects($this->once())
            ->method('encrypt')
            ->with($value)
            ->willReturn($encryptedValue);

        $this->assertEquals($encryptedValue, $this->encryptionHelper->encrypt($value));
    }
}
