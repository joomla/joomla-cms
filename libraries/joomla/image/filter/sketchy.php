<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Image
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JLog::add('JImageFilterSketchy is deprecated, use Joomla\Image\Filter\Sketchy instead.', JLog::WARNING, 'deprecated');

/**
 * Image Filter class to make an image appear "sketchy".
 *
 * @since       11.3
 * @deprecated  5.0  Use Joomla\Image\Filter\Sketchy instead
 */
class JImageFilterSketchy extends JImageFilter
{
	/**
	 * Method to apply a filter to an image resource.
	 *
	 * @param   array  $options  An array of options for the filter.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 * @deprecated  5.0  Use Joomla\Image\Filter\Sketchy::execute() instead
	 */
	public function execute(array $options = array())
	{
		// Perform the sketchy filter.
		imagefilter($this->handle, IMG_FILTER_MEAN_REMOVAL);
	}
}
