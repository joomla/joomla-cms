<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Media
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.media.mediaexception');

/**
 * Class to manipulate an image.
 *
 * @package     Joomla.Platform
 * @subpackage  Media
 * @since       11.3
 */
abstract class JImageFilter
{
	/**
	 * Class constructor.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 * @throws  MediaException
	 */
	public function __construct()
	{
		// Verify that image filter support for PHP is available.
		if (!function_exists('imagefilter')) {
			JLog::add('The imagefilter function for PHP is not available.', JLog::ERROR);
			throw new MediaException();
		}
	}

	/**
	 * Method to apply a filter to an image resource.
	 *
	 * @param   resource  $handle   The image resource on which to apply the filter.
	 * @param   array     $options  An array of options
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	abstract public function execute($handle, $options=array());
}
