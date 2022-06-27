<?php
/**
 * Joomla! Content Management System
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Image\Filter;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Image\ImageFilter;

/**
 * Image Filter class adjust the contrast of an image.
 *
 * @since  2.5.0
 */
class Contrast extends ImageFilter
{
	/**
	 * Method to apply a filter to an image resource.
	 *
	 * @param   array  $options  An array of options for the filter.
	 *
	 * @return  void
	 *
	 * @since   2.5.0
	 * @throws  \InvalidArgumentException
	 */
	public function execute(array $options = [])
	{
		// Validate that the contrast value exists and is an integer.
		if (!isset($options[IMG_FILTER_CONTRAST]) || !\is_int($options[IMG_FILTER_CONTRAST]))
		{
			throw new \InvalidArgumentException('No valid contrast value was given.  Expected integer.');
		}

		// Perform the contrast filter.
		imagefilter($this->handle, IMG_FILTER_CONTRAST, $options[IMG_FILTER_CONTRAST]);
	}
}
