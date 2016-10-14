<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Media Component File Type Image Model
 */
class MediaModelFileTypeImage extends MediaModelFileTypeAbstract implements MediaModelFileTypeInterface
{
	/**
	 * File extensions supported by this file type
	 */
	protected $extensions = array(
		'jpg',
		'png',
		'gif',
		'xcf',
		'odg',
		'bmp',
		'jpeg',
		'ico',
	);

	/**
	 * MIME types supported by this file type
	 */
	protected $mimeTypes = array(
		'image/png',
		'image/gif'.
		'image/x-icon',
		'image/jpeg',
		'image/bmp',
		'image/xcf',
		'image/odg',
		'image/x-windows-bmp',
	);

	/**
	 * Return the file properties of a specific file
	 *
	 * @param string $filePath
	 *
	 * @return array
	 */
	public function getProperties($filePath)
	{
		$properties = array();

		$info = @getimagesize($filePath);
		$properties['width'] = @$info[0];
		$properties['height'] = @$info[1];
		$properties['type'] = @$info[2];
		$properties['mime']  = @$info['mime'];

		if (($info[0] > 60) || ($info[1] > 60))
		{
			$dimensions = MediaHelper::imageResize($info[0], $info[1], 60);
			$properties['width_60'] = $dimensions[0];
			$properties['height_60'] = $dimensions[1];
		}
		else
		{
			$properties['width_60'] = $properties['width'];
			$properties['height_60'] = $properties['height'];
		}

		if (($info[0] > 16) || ($info[1] > 16))
		{
			$dimensions = MediaHelper::imageResize($info[0], $info[1], 16);
			$properties['width_16'] = $dimensions[0];
			$properties['height_16'] = $dimensions[1];
		}
		else
		{
			$properties['width_16'] = $properties['width'];
			$properties['height_16'] = $properties['height'];
		}

		return $properties;
	}
}