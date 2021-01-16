<?php
/**
 * Part of the Joomla Framework Image Package
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Image\Filter;

use InvalidArgumentException;
use Joomla\Image\ImageFilter;

/**
 * Image Filter class adjust the smoothness of an image.
 *
 * @since       1.0
 * @deprecated  The joomla/image package is deprecated
 */
class Smooth extends ImageFilter
{
	/**
	 * Method to apply a filter to an image resource.
	 *
	 * @param   array  $options  An array of options for the filter.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  InvalidArgumentException
	 */
	public function execute(array $options = array())
	{
		// Validate that the smoothing value exists and is an integer.
		if (!isset($options[IMG_FILTER_SMOOTH]) || !\is_int($options[IMG_FILTER_SMOOTH]))
		{
			throw new InvalidArgumentException('No valid smoothing value was given.  Expected integer.');
		}

		// Perform the smoothing filter.
		imagefilter($this->handle, IMG_FILTER_SMOOTH, $options[IMG_FILTER_SMOOTH]);
	}
}
