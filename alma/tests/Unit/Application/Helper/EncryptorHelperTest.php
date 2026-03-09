<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Helper;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Helper\EncryptorHelper;

class EncryptorHelperTest extends TestCase
{
    /**
     * @var EncryptorHelper
     */
    private EncryptorHelper $encryptorHelper;

    public function setUp(): void
    {
        $this->phpEncryption = $this->createMock(\PhpEncryption::class);
        $this->encryptorHelper = new EncryptorHelper(
            $this->phpEncryption
        );
    }

    public function testCheckIfIsEncryptionValueWithValueReturnTrue(): void
    {
        $this->assertTrue(EncryptorHelper::isEncryptionValue(true, 'value'));
    }

    public function testCheckIfIsEncryptionValueWithoutValueReturnFalse(): void
    {
        $this->assertFalse(EncryptorHelper::isEncryptionValue(true, ''));
    }

    public function testCheckIfIsNotEncryptionValueWithValueReturnFalse(): void
    {
        $this->assertFalse(EncryptorHelper::isEncryptionValue(false, 'value'));
    }

    public function testCheckIfIsNotEncryptionValueWithoutValueReturnFalse(): void
    {
        $this->assertFalse(EncryptorHelper::isEncryptionValue(false, ''));
    }

    public function testEncryptionValue(): void
    {
        $value = 'test';
        $encryptedValue = 'encrypted_test';

        $this->phpEncryption->expects($this->once())
            ->method('encrypt')
            ->with($value)
            ->willReturn($encryptedValue);

        $this->assertEquals($encryptedValue, $this->encryptorHelper->encrypt($value));
    }
}
