<?php
/**
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Image;

use Joomla\CMS\Image\Image as Image;

/**
 * Inspector for the Image class.
 *
 * @since  4.0.0
 */
class ImageInspector extends Image
{
	/**
	 * @var    ImageFilter  A mock image filter to be returned from getFilterInstance().
	 * @since  4.0.0
	 */
	public $mockFilter;

	/**
	 * Allows public access to protected method.
	 *
	 * @param   string  $type  The image filter type to get.
	 *
	 * @return  ImageFilter
	 *
	 * @since   4.0.0
	 * @throws  \RuntimeException
	 */
	public function getFilterInstance($type)
	{
		if ($this->mockFilter)
		{
			return $this->mockFilter;
		}
		else
		{
			return parent::getFilterInstance($type);
		}
	}
}
