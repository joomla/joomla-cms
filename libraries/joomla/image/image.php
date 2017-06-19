<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Image
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Log\Log;

/**
 * Class to manipulate an image.
 *
 * @since       11.3
 * @deprecated  5.0 Use the class \Joomla\Image\Image instead
 */
class JImage extends \Joomla\Image\Image
{
	/**
	 * Method to get an image filter instance of a specified type.
	 *
	 * @param   string  $type  The image filter type to get.
	 *
	 * @return  JImageFilter
	 *
	 * @since   11.3
	 * @throws  RuntimeException
	 */
	protected function getFilterInstance($type)
	{
		try
		{
			return parent::getFilterInstance($type);
		}
		catch (RuntimeException $e)
		{
			// Ignore, filter is probably not namespaced
		}

		// Sanitize the filter type.
		$type = strtolower(preg_replace('#[^A-Z0-9_]#i', '', $type));

		// Verify that the filter type exists.
		$className = 'JImageFilter' . ucfirst($type);

		if (!class_exists($className))
		{
			Log::add('The ' . ucfirst($type) . ' image filter is not available.', Log::ERROR);
			throw new RuntimeException('The ' . ucfirst($type) . ' image filter is not available.');
		}

		// Instantiate the filter object.
		$instance = new $className($this->getHandle());

		// Verify that the filter type is valid.
		if (!($instance instanceof JImageFilter))
		{
			// @codeCoverageIgnoreStart
			Log::add('The ' . ucfirst($type) . ' image filter is not valid.', Log::ERROR);
			throw new RuntimeException('The ' . ucfirst($type) . ' image filter is not valid.');

			// @codeCoverageIgnoreEnd
		}

		return $instance;
	}
}
