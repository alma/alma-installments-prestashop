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

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class HttpHelper.
 *
 * Provides portable HTTP request header access across different server SAPIs.
 * Some environments (Nginx/FPM with certain proxy configurations) may not populate
 * $_SERVER entries for all headers, while getallheaders() is reliable on PHP 7.3+.
 */
class HttpHelper
{
    /**
     * Retrieves an HTTP request header value.
     *
     * Uses getallheaders() with case-insensitive matching as the primary method,
     * then falls back to $_SERVER using PHP's header normalization convention
     * (e.g. "X-Alma-Signature" → "HTTP_X_ALMA_SIGNATURE").
     *
     * @param string $headerName The header name (e.g., 'X-Alma-Signature')
     *
     * @return string|null The header value, or null if not found
     */
    public function getHeader($headerName)
    {
        foreach ($this->getAllHeaders() as $name => $value) {
            if (strcasecmp($name, $headerName) === 0) {
                return $value;
            }
        }

        // PHP normalizes HTTP headers in $_SERVER: uppercased, hyphens replaced by underscores, prefixed with HTTP_
        $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $headerName));

        return isset($_SERVER[$serverKey]) ? $_SERVER[$serverKey] : null;
    }

    /**
     * Returns all HTTP request headers.
     * Wraps getallheaders() for testability. Available in all PHP SAPIs since PHP 7.3.
     *
     * @return array<string, string>
     */
    protected function getAllHeaders()
    {
        if (function_exists('getallheaders')) {
            return getallheaders() ?: [];
        }

        return [];
    }
}
