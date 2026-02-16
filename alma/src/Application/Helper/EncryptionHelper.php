<?php

namespace PrestaShop\Module\Alma\Application\Helper;

class EncryptionHelper
{
    public const OBSCURE_VALUE = '********************************';

    public static function isEncryptionValue(bool $isEncryptedInput, string $value): bool
    {
        if (!$isEncryptedInput || empty($value)) {
            return false;
        }

        return true;
    }
}
