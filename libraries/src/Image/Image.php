<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Image;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Log\Log;

/**
 * Class to manipulate an image.
 *
 * @since       11.3
 * @deprecated  5.0 Use the class \Joomla\Image\Image instead
 */
class Image extends \Joomla\Image\Image
{
	/**
	 * Class constructor.
	 *
	 * @param   mixed  $source  Either a file path for a source image or a GD resource handler for an image.
	 *
	 * @since   11.3
	 * @throws  \RuntimeException
	 */
	public function __construct($source = null)
	{
		Log::add('Joomla\CMS\Image\Image is deprecated, use Joomla\Image\Image instead.', Log::WARNING, 'deprecated');

		// Inject the PSR-3 compatible logger in for forward compatibility
		$this->setLogger(Log::createDelegatedLogger());

		parent::__construct($source);
	}

	/**
	 * Method to get an image filter instance of a specified type.
	 *
	 * @param   string  $type  The image filter type to get.
	 *
	 * @return  ImageFilter
	 *
	 * @since   11.3
	 * @throws  \RuntimeException
	 */
	protected function getFilterInstance($type)
	{
		try
		{
			return parent::getFilterInstance($type);
		}
		catch (\RuntimeException $e)
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
			throw new \RuntimeException('The ' . ucfirst($type) . ' image filter is not available.');
		}

		// Instantiate the filter object.
		$instance = new $className($this->getHandle());

		// Verify that the filter type is valid.
		if (!($instance instanceof ImageFilter))
		{
			// @codeCoverageIgnoreStart
			Log::add('The ' . ucfirst($type) . ' image filter is not valid.', Log::ERROR);
			throw new \RuntimeException('The ' . ucfirst($type) . ' image filter is not valid.');

			// @codeCoverageIgnoreEnd
		}

		return $instance;
	}
}
