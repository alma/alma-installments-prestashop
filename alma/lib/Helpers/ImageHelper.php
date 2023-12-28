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

namespace Alma\PrestaShop\Helpers;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ImageHelper
{
    /**
     * @param string $tmpFile
     * @param string $path
     * @param int $idImage
     * @param int $idModel
     * @param string $imageType
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function resizeImages($tmpFile, $path, $idImage, $idModel, $imageType)
    {
        $imagesTypes = \ImageType::getImagesTypes($imageType);
        $watermarkTypes = explode(',', \Configuration::get('WATERMARK_TYPES'));

        foreach ($imagesTypes as $imageType) {
            $this->resizeImage($tmpFile, $path, $imageType, $watermarkTypes, $idImage, $idModel);
        }
    }

    /**
     * @param string $tmpFile
     * @param string $path
     * @param array $imageType
     * @param array $watermarkTypes
     * @param int $idImage
     * @param int $idModel
     * @return void
     * @throws \PrestaShopException
     */
    public function resizeImage($tmpFile, $path, $imageType, $watermarkTypes, $idImage, $idModel)
    {
        \ImageManager::resize(
            $tmpFile,
            sprintf(
                '%s-%s.jpg',
                $path,
                stripslashes($imageType['name'])
            ),
            $imageType['width'],
            $imageType['height']
        );

        if (in_array($imageType['id_image_type'], $watermarkTypes)) {
            \Hook::exec(
                'actionWatermark',
                [
                    'id_image' => $idImage,
                    'id_product' => $idModel
                ]
            );
        }
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getFormattedImageTypeName($name)
    {
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            return \ImageType::getFormattedName($name);
        }

        return \ImageType::getFormatedName($name);
    }
}