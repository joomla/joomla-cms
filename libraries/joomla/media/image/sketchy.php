<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Media
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.media.imagefilter');

/**
 * Image Filter class to make an image appear "sketchy".
 *
 * @package     Joomla.Platform
 * @subpackage  Media
 * @since       11.1
 */
class JImageFilterSketchy extends JImageFilter
{
	/**
	 * Method to apply a filter to an image resource.
	 *
	 * @param   resource  The image resource on which to apply the filter.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	function execute(& $handle)
	{
		// Make sure the file handle is valid.
		if ((!is_resource($handle) || get_resource_type($handle) != 'gd')) {
			JLog::add('The image is invalid.', JLog::ERROR);
			throw new MediaException();
		}

		// Perform sketchy filter.
		imagefilter($handle, IMG_FILTER_MEAN_REMOVAL);
	}
}
