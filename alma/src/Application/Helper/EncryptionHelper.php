<?php

namespace PrestaShop\Module\Alma\Application\Helper;

class EncryptionHelper
{
    public const OBSCURE_VALUE = '********************************';
    /**
     * @var \PhpEncryption
     */
    private \PhpEncryption $phpEncryption;

    public function __construct(
        \PhpEncryption $phpEncryption
    ) {
        $this->phpEncryption = $phpEncryption;
    }

    /**
     * Check if the value should be encrypted based on the 'encrypted' param and if the value is not empty.
     * @param bool $isEncryptedInput
     * @param string $value
     * @return bool
     */
    public static function isEncryptionValue(bool $isEncryptedInput, string $value): bool
    {
        if (!$isEncryptedInput || empty($value)) {
            return false;
        }

        return true;
    }

    /**
     * Encrypt the value if the PhpEncryption class exists, otherwise return the value as is.
     * @param string $value
     * @return string
     */
    public function encrypt(string $value): string
    {
        if (class_exists('\PhpEncryption')) {
            return $this->phpEncryption->encrypt($value);
        }

        return $value;
    }

    /**
     * Decrypt the value if the PhpEncryption class exists, otherwise return the value as is.
     * @param string $value
     * @return string
     */
    public function decrypt(string $value): string
    {
        if (class_exists('\PhpEncryption')) {
            try {
                return $this->phpEncryption->decrypt($value);
            } catch (\Exception $e) {
                // TODO: Add logging for decryption failure
                return $value;
            }
        }

        // TODO: Add logging for decryption librairy missing
        return $value;
    }
}
