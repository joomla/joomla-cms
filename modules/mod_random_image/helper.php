<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_random_image
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\String\StringHelper;

/**
 * Helper for mod_random_image
 *
 * @package     Joomla.Site
 * @subpackage  mod_random_image
 * @since       1.5
 */
class ModRandomImageHelper
{
	/**
	 * Retrieves a random image
	 *
	 * @param   \Joomla\Registry\Registry  &$params  module parameters object
	 * @param   array                      $images   list of images
	 *
	 * @return  mixed
	 */
	public static function getRandomImage(&$params, $images)
	{
		$width  = $params->get('width');
		$height = $params->get('height');

		$i      = count($images);
		$random = mt_rand(0, $i - 1);
		$image  = $images[$random];
		$size   = getimagesize(JPATH_BASE . '/' . $image->folder . '/' . $image->name);

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

		$image->width  = $width;
		$image->height = $height;
		$image->folder = str_replace('\\', '/', $image->folder);

		return $image;
	}

	/**
	 * Retrieves images from a specific folder
	 *
	 * @param   \Joomla\Registry\Registry  &$params  module params
	 * @param   string                     $folder   folder to get the images from
	 *
	 * @return array
	 */
	public static function getImages(&$params, $folder)
	{
		$type   = $params->get('type', 'jpg');
		$files  = array();
		$images = array();

		$dir = JPATH_BASE . '/' . $folder;

		// Check if directory exists
		if (is_dir($dir))
		{
			if ($handle = opendir($dir))
			{
				while (false !== ($file = readdir($handle)))
				{
					if ($file !== '.' && $file !== '..' && $file !== 'CVS' && $file !== 'index.html')
					{
						$files[] = $file;
					}
				}
			}

			closedir($handle);

			$i = 0;

			foreach ($files as $img)
			{
				if (!is_dir($dir . '/' . $img) && preg_match('/' . $type . '/', $img))
				{
					$images[$i] = new stdClass;

					$images[$i]->name   = $img;
					$images[$i]->folder = $folder;
					$i++;
				}
			}
		}

		return $images;
	}

	/**
	 * Get sanitized folder
	 *
	 * @param   \Joomla\Registry\Registry  &$params  module params objects
	 *
	 * @return  mixed
	 */
	public static function getFolder(&$params)
	{
		$folder   = $params->get('folder');
		$LiveSite = JUri::base();

		// If folder includes livesite info, remove
		if (StringHelper::strpos($folder, $LiveSite) === 0)
		{
			$folder = str_replace($LiveSite, '', $folder);
		}

		// If folder includes absolute path, remove
		if (StringHelper::strpos($folder, JPATH_SITE) === 0)
		{
			$folder = str_replace(JPATH_BASE, '', $folder);
		}

		return str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $folder);
	}
}
