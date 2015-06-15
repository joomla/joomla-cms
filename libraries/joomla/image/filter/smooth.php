<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Image
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Image Filter class adjust the smoothness of an image.
 *
 * @since  11.3
 */
class JImageFilterSmooth extends JImageFilter
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
		// Validate that the smoothing value exists and is an integer.
		if (!isset($options[IMG_FILTER_SMOOTH]) || !is_int($options[IMG_FILTER_SMOOTH]))
		{
			throw new InvalidArgumentException('No valid smoothing value was given.  Expected integer.');
		}

		// Perform the smoothing filter.
		imagefilter($this->handle, IMG_FILTER_SMOOTH, $options[IMG_FILTER_SMOOTH]);
	}
}
