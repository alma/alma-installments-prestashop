<?php
/**
 * 2018-2024 Alma SAS.
 *
 * THE MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and
 * to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @author    Alma SAS <contact@getalma.eu>
 * @copyright 2018-2024 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Helpers;

use Alma\PrestaShop\Exceptions\EncryptionException;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class EncryptionHelper.
 *
 * Use for method date
 */
class EncryptionHelper
{
    /** @var string */
    private $cookieKey;

    /**
     * Encryption Helper construct
     */
    public function __construct()
    {
        $this->cookieKey = _COOKIE_KEY_;

        if (defined('_NEW_COOKIE_KEY_')) {
            $this->cookieKey = _NEW_COOKIE_KEY_;
        }
    }

    /**
     * @param $plaintext
     *
     * @return mixed|string
     */
    public function encrypt($plaintext)
    {
        if (class_exists('\PhpEncryption')) {
            $phpEncrypt = new \PhpEncryption($this->cookieKey);

            return $phpEncrypt->encrypt($plaintext);
        }

        if (class_exists('\Rijndael')) {
            $rijndael = new \Rijndael(_RIJNDAEL_KEY_, _RIJNDAEL_IV_);

            return $rijndael->encrypt($plaintext);
        }

        return $plaintext;
    }

    /**
     * @param string $cipherApiKey
     *
     * @return string
     *
     * @throws EncryptionException
     */
    public function decrypt($cipherApiKey)
    {
        if (class_exists('\PhpEncryption')) {
            $phpEncrypt = new \PhpEncryption($this->cookieKey);
            try {
                $decryptedKey = $phpEncrypt->decrypt($cipherApiKey);
                if ($decryptedKey) {
                    return $decryptedKey;
                }
            } catch (\Exception $e) {
                throw new EncryptionException('Exception in API Key decrypt with PhpEncryption', 0, $e);
            }
        }

        if (class_exists('\Rijndael')) {
            $rijndael = new \Rijndael(_RIJNDAEL_KEY_, _RIJNDAEL_IV_);
            $decryptedKey = $rijndael->decrypt($cipherApiKey);
            if ($decryptedKey) {
                return $decryptedKey;
            }
        }

        throw new EncryptionException('Impossible to decrypt Key - Libs not exist');
    }
}
