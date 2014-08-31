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
 * @since       3.4
 */
abstract class JImageHelper
{
	/**
	 * @const  string
	 * @since  3.4
	 */
	const ORIENTATION_LANDSCAPE = 'landscape';

	/**
	 * @const  string
	 * @since  3.4
	 */
	const ORIENTATION_PORTRAIT = 'portrait';

	/**
	 * @const  string
	 * @since  3.4
	 */
	const ORIENTATION_SQUARE = 'square';

	/**
	 * Method to return a properties object for an image given a filesystem path.  The
	 * result object has values for image width, height, type, attributes, mime type, bits,
	 * and channels.
	 *
	 * @param   string  $path  The filesystem path to the image for which to get properties.
	 *
	 * @return  stdClass
	 *
	 * @since   3.4
	 * @throws  InvalidArgumentException
	 * @throws  RuntimeException
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
			'width'       => $info[0],
			'height'      => $info[1],
			'type'        => $info[2],
			'attributes'  => $info[3],
			'bits'        => isset($info['bits']) ? $info['bits'] : null,
			'channels'    => isset($info['channels']) ? $info['channels'] : null,
			'mime'        => $info['mime'],
			'filesize'    => filesize($path),
			'orientation' => JImageHelper::getOrientation((int) $info[0], (int) $info[1])
		);

		return $properties;
	}

	/**
	 * Compare width and height integers to determine image orientation.
	 *
	 * @param   integer  $width
	 * @param   integer  $height
	 *
	 * @return  mixed   Orientation string or null.
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

	/**
	 * Create image file from image file data stream.
	 *
	 * @param   string   $data      The base64 encoded input stream including the header portion 'data:image/png;base64'.
	 * @param   boolean  $store     Flag indicating whether to store the data into a file.
	 * @param   string   $filename  The file name to use when data should be stored.
	 * @param   string   $filepath  The file path to use when data should be stored. Note, this must be an absolute system path.
	 *
	 * @return	boolean
	 *
	 * @since   3.4
	 * @throws	RuntimeException
	 */
	public static function fromBase64($data, $store = false, $filename = null, $filepath = null)
	{
		list($type, $stream) = explode(';', $data);

		$stream = JString::str_ireplace('base64,', '', $stream);
		$stream = base64_decode($stream);
		$type   = explode('/', $type);
		$type   = end($type);

		if ($store)
		{
			if (!$filename)
			{
				// TODO - translate
				throw new RuntimeException(JText::_('You selected to store the image file. However, you did not specify a file name.'));
			}

			if (!$filepath)
			{
				// TODO - translate
				throw new RuntimeException(JText::_('You selected to store the image file. However, you did not specify a target path.'));
			}
			else
			{
				// Fix slashes.
				$filepath = JPath::clean($filepath);

				// It might be the file already includes the absolute path. Remove it.
				$filepath = JString::str_ireplace(JPATH_BASE, '', $filepath);

				// The file path must be an absolute system path.
				$filepath = JPATH_BASE . DIRECTORY_SEPARATOR . trim($filepath, DIRECTORY_SEPARATOR);

				// Check whether the target path exists.
				if (!JFolder::exists($filepath))
				{
					// TODO - translate
					throw new RuntimeException(JText::sprintf('You selected to store the image file. However, the specified target %s path does not exist.', $filepath));
				}

				// Check whether the target path is writable.
				if (!is_writable($filepath))
				{
					// TODO - translate
					throw new RuntimeException(JText::sprintf('You selected to store the image file. However, the specified target %s path is not writable.', $filepath));
				}
			}

			// Build save path.
			$filename = trim($filename, DIRECTORY_SEPARATOR);
			$filename = JFile::stripext(basename($filename));
			$filepath = trim($filepath, DIRECTORY_SEPARATOR);
			$fullpath = DIRECTORY_SEPARATOR . $filepath . DIRECTORY_SEPARATOR . "{$filename}.{$type}";

			// Attempt to save the file.
			try
			{
				file_put_contents($fullpath, $stream);
			}
			catch (Exception $e)
			{
				throw new RuntimeException($e->getMessage());
			}
		}

		return true;
	}

	/**
	 * Create base64 encoded data stream from image file.
	 *
	 * @param   string  $filepath  The input file to use. Note, this must be an absolute system path.
	 *
	 * @return  string  base64 image string
	 *
	 * @since   3.4
	 */
	public static function toBase64($filepath)
	{
		if (!$filepath)
		{
			return '';
		}

		// Sanitize input path.
		$filepath = JUri::getInstance($filepath);

		// We accept internal files only.
		$filepath = $filepath->getScheme() ? $filepath->getPath() : $filepath->toString();

		// Fix slashes.
		$filepath = JPath::clean($filepath);

		// It might be the file already includes the absolute path. Remove it.
		$filepath = JString::str_ireplace(JPATH_BASE, '', $filepath);

		// The file path must be an absolute system path.
		$filepath = JPATH_BASE . DIRECTORY_SEPARATOR . ltrim($filepath, DIRECTORY_SEPARATOR);

		// Validate whether the file exists on site.
		if (!JFile::exists($filepath))
		{
			return '';
		}

		// We don't trust the file extension. So we read the image type from its mime header.
		if (!($props = static::getImageFileProperties($filepath)))
		{
			return '';
		}

		$type = isset($props->mime) ? $props->mime : null;

		// The file appears to be no image.
		if (!$type)
		{
			return '';
		}

		// Extract the image type.
		$type = explode('/', $type);
		$type = end($type);

		// Load and convert the file.
		$data = base64_encode(file_get_contents($filepath));

		// Excape slashes as they break CSS parsing.
		$data = htmlentities($data);

		/*
		 * Return escaped string.
		 * NOTE:   Escaping is important as otherwise parsing the data might fail when it contains slashes.
		 */
		return "data:image/{$type};base64,{$data}";
	}
}
