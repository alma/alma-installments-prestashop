<?php
/**
 * 2018-2020 Alma SAS
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
 * @copyright 2018-2020 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Model;

use Exception;
use Category;
use Validate;

if (!defined('_PS_VERSION_')) {
	exit;
}

class CategoryAdapter
{
	/**
	 * @var int
	 */
	private $idCategory;

	/**
	 * @var Category
	 */
	private $category;

	/**
	 * @param $idCategory int ID of the PrestaShop Category to load.
	 * @return CategoryAdapter
	 */
	public static function fromCategory($idCategory)
	{
		try {
			return new CategoryAdapter($idCategory);
		} catch (Exception $e) {
			return null;
		}

	}

	/**
	 * AlmaCategory constructor.
	 *
	 * @param $idCategory int ID of the PrestaShop Category to load.
	 * @throws Exception
	 */
	public function __construct($idCategory)
	{
		$this->idCategory = $idCategory;
		$this->category = new Category($idCategory);

		if (!Validate::isLoadedObject($this->category)) {
			throw new Exception("Could not load Category with id $idCategory");
		}
	}

	private function map_category_ids($category) {
		return (int) $category->id;
	}

	public function getAllChildrenIds()
	{
		if (version_compare(_PS_VERSION_, '1.5.0.1', '<')) {
			// We don't support PrestaShop versions that old, so don't even try to find an alternative
			return [];
		}

		return array_map([$this, 'map_category_ids'], $this->category->getAllChildren()->getResults());
	}
}
