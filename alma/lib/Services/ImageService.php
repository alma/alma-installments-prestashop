<?php
/**
 * 2018-2023 Alma SAS.
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
 * @copyright 2018-2023 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Services;

use Alma\PrestaShop\Helpers\ImageHelper;

class ImageService
{

    /**
     * @var \Image
     */
    private $image;
    /**
     * @var ImageHelper
     */
    private $imageHelper;

    public function __construct()
    {
        $this->image = new \Image();
        $this->imageHelper = new ImageHelper();
    }
    /**
     * @param int $idProduct
     * @param []ShopCore $shops
     * @param string $urlImage
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function associateImageToProduct($idProduct, $shops, $urlImage)
    {
        $this->image->id_product = $idProduct;
        $this->image->cover = true;

        if (
            $this->image->validateFields(false, true) === true
            && $this->image->validateFieldsLang(false, true) === true
            && $this->image->add()
        ) {
            $this->image->associateTo($shops);

            if (!$this->uploadImage($this->image, $idProduct, $urlImage, 'products')) {
                $this->image->delete();
            }
        }
    }

    /**
     * @param \ImageCore $image
     * @param int $idModel
     * @param string $imgUrl
     * @param string $imageType
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    protected function uploadImage($image, $idModel, $imgUrl, $imageType)
    {
        $tmpFile = tempnam(_PS_TMP_IMG_DIR_, 'ps_import');
        $path = $image->getPathForCreation();
        $imgUrl = str_replace(' ', '%20', trim($imgUrl));

        // Evaluate the memory required to resize the image: if it's too big we can't resize it.
        if (!\ImageManager::checkImageMemoryLimit($imgUrl)) {
            return false;
        }

        if (@copy($imgUrl, $tmpFile)) {
            \ImageManager::resize($tmpFile, $path . '.jpg');

            $this->imageHelper->resizeImages($tmpFile, $path, $image->id, $idModel, $imageType);

            unlink($tmpFile);
            return true;
        }

        unlink($tmpFile);
        return false;
    }
}