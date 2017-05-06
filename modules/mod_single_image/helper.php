<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_single_image
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.file');

/**
 * Helper for mod_image
 *
 * @package     Joomla.Site
 * @subpackage  mod_single_image
 * @since       1.5
 */
class ModSingleImageHelper
{
	/**
	 * Retrieves an image
	 *
	 * @param   \Joomla\Registry\Registry  &$params  module parameters object
	 *
	 * @return  mixed
	 */
	public static function getImage(&$params)
	{
		$image 	= new stdClass;
		$path 	= $params->get('image');
		$width	= $params->get('width');
		$height	= $params->get('height');

		if ( !JFile::exists(JPATH_BASE . '/' . $path) )
		{
			return false;
		}

		$size = getimagesize(JPATH_BASE . '/' . $path);

		$proportional = $params->get('proportional');

		if ( $proportional )
		{
			if ($width == '')
			{
				$width = 100;
			}

			if ($size[0] < $width)
			{
				$width = $size[0];
			}

			$coeff = $size[0] / $size[1];

			if ($height == '')
			{
				$height = (int) ($width / $coeff);
			}
			else
			{
				$newheight = min($height, (int) ($width / $coeff));

				if ($newheight < $height)
				{
					$height = $newheight;
				}
				else
				{
					$width = $height * $coeff;
				}
			}
		}
		else
		{
			$width 	= (empty($width)) ? $size[0] : $width;
			$height = (empty($height)) ? $size[1] : $height;
		}

		$alt = $params->get('alt', basename($path));

		$image->width	= $width;
		$image->height	= $height;
		$image->path	= $path;
		$image->alt 	= $alt;

		return $image;
	}
}
