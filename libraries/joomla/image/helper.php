<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Image
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Image helper class, provides static methods to perform various tasks relevant
 * to the Joomla image routines.
 *
 * @package     Joomla.Platform
 * @subpackage  Image
 *
 * @since       __DEPLOY_VERSION__
 */
abstract class JImageHelper
{
	/**
	 * @const  string
	 * @since  __DEPLOY_VERSION__
	 */
	const ORIENTATION_LANDSCAPE = 'landscape';

	/**
	 * @const  string
	 * @since  __DEPLOY_VERSION__
	 */
	const ORIENTATION_PORTRAIT = 'portrait';

	/**
	 * @const  string
	 * @since  __DEPLOY_VERSION__
	 */
	const ORIENTATION_SQUARE = 'square';

	/**
	 * Method to return a properties object for an image given a filesystem path.
	 * The result object has values for image width, height, type, attributes,
	 * bits, channels, mime type, filesize and orientation.
	 *
	 * @param   string  $path  The filesystem path to the image for which to get properties.
	 *
	 * @return  stdClass
	 *
	 * @throws  InvalidArgumentException
	 * @throws  RuntimeException
	 *
	 * @since   3.4
	 */
	public static function getImageFileProperties($path)
	{
		// Make sure the file exists.
		if (!file_exists($path))
		{
			throw new InvalidArgumentException('The image file does not exist.');
		}

		// Get the image file information.
		$info = getimagesize($path);

		if (!$info)
		{
			// @codeCoverageIgnoreStart
			throw new RuntimeException('Unable to get properties for the image.');

			// @codeCoverageIgnoreEnd
		}

		// Build the response object.
		$properties = (object) array(
			'width' => $info[0],
			'height' => $info[1],
			'type' => $info[2],
			'attributes' => $info[3],
			'bits' => isset($info['bits']) ? $info['bits'] : null,
			'channels' => isset($info['channels']) ? $info['channels'] : null,
			'mime' => $info['mime'],
			'filesize' => filesize($path),
			'orientation' => self::getOrientation((int) $info[0], (int) $info[1])
		);

		return $properties;
	}

	/**
	 * Compare width and height integers to determine image orientation.
	 *
	 * @param   integer  $width   The width value to use for calculation
	 * @param   integer  $height  The height value to use for calculation
	 *
	 * @return  mixed    Orientation string or null.
	 *
	 * @since   3.4
	 */
	public static function getOrientation($width, $height)
	{
		switch (true)
		{
		case ($width > $height) :
			return self::ORIENTATION_LANDSCAPE;

		case ($width < $height) :
			return self::ORIENTATION_PORTRAIT;

		case ($width == $height) :
			return self::ORIENTATION_SQUARE;

		default :
			return null;
		}
	}
}
