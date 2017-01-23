<?php
/**
 * Part of the Joomla Framework Image Package
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Image\Filter;

use Joomla\Image\ImageFilter;
use InvalidArgumentException;

/**
 * Image Filter class adjust the contrast of an image.
 *
 * @since  1.0
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
	 * @since   1.0
	 * @throws  InvalidArgumentException
	 */
	public function execute(array $options = array())
	{
		// Validate that the contrast value exists and is an integer.
		if (!isset($options[IMG_FILTER_CONTRAST]) || !is_int($options[IMG_FILTER_CONTRAST]))
		{
			throw new InvalidArgumentException('No valid contrast value was given.  Expected integer.');
		}

		// Perform the contrast filter.
		imagefilter($this->handle, IMG_FILTER_CONTRAST, $options[IMG_FILTER_CONTRAST]);
	}
}
