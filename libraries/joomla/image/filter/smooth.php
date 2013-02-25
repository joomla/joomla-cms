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
 * Image Filter class adjust the smoothness of an image.
 *
 * @package     Joomla.Platform
 * @subpackage  Image
 * @since       11.3
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
	 * @throws  RuntimeException
	 */
	public function execute(array $options = array())
	{
		// Verify that image filter support for PHP is available.
		if (!function_exists('imagefilter'))
		{
			JLog::add('The imagefilter function for PHP is not available.', JLog::ERROR);
			throw new RuntimeException('The imagefilter function for PHP is not available.');
		}

		// Validate that the smoothing value exists and is an integer.
		if (!isset($options[IMG_FILTER_SMOOTH]) || !is_int($options[IMG_FILTER_SMOOTH]))
		{
			throw new InvalidArgumentException('No valid smoothing value was given.  Expected integer.');
		}

		// Perform the smoothing filter.
		imagefilter($this->handle, IMG_FILTER_SMOOTH, $options[IMG_FILTER_SMOOTH]);
	}
}
