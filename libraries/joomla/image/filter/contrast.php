<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Image
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Image Filter class adjust the contrast of an image.
 *
 * @package     Joomla.Platform
 * @subpackage  Image
 * @since       11.3
 */
class JImageFilterContrast extends JImageFilter
{
	/**
	 * Method to apply a filter to an image resource.
	 *
	 * @param   array  $options  An array of options for the filter.
	 *
	 * @return  void
	 *
	 * @since   11.3
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
