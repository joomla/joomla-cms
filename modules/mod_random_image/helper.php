<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_random_image
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_random_image
 *
 * @package     Joomla.Site
 * @subpackage  mod_random_image
 * @since       1.5
 */
class ModRandomImageHelper
{
	public static function getRandomImage(&$params, $images)
	{
		$width = $params->get('width');

		$i = count($images);
		$random = mt_rand(0, $i - 1);
		$image = $images[$random];
		$jImage = new JImage(JPATH_BASE . '/' . $image->folder . '/' . $image->name);
		$jImage->relativePath = $image->folder . '/' . $image->name;
		$jImage->name = $image->name;
		$size = getimagesize(JPATH_BASE . '/' . $image->folder . '/' . $image->name);

		if ($width == '' || $width >= $size[0])
		{
			return $jImage;
		}
		else
		{
			return $jImage->resize($width, (int) $width * $size[1] / $size[0], false);
		}
	}

	public static function getImages(&$params, $folder)
	{
		$type = $params->get('type', 'jpg');

		$files = array();
		$images = array();

		$dir = JPATH_BASE . '/' . $folder;

		// Check if directory exists
		if (is_dir($dir))
		{
			if ($handle = opendir($dir))
			{
				while (false !== ($file = readdir($handle)))
				{
					if ($file != '.' && $file != '..' && $file != 'CVS' && $file != 'index.html')
					{
						$files[] = $file;
					}
				}
			}

			closedir($handle);

			$i = 0;

			foreach ($files as $img)
			{
				if (!is_dir($dir . '/' . $img))
				{
					if (preg_match('/' . '(.jpg|.png|.gif)' . '\z/', $img))
					{
						$images[$i] = new stdClass;

						$images[$i]->name = $img;
						$images[$i]->folder = $folder;
						$i++;
					}
				}
			}
		}

		return $images;
	}

	public static function getFolder(&$params)
	{
		$folder = $params->get('folder');

		$LiveSite = JUri::base();

		// If folder includes livesite info, remove
		if (JString::strpos($folder, $LiveSite) === 0)
		{
			$folder = str_replace($LiveSite, '', $folder);
		}

		// If folder includes absolute path, remove
		if (JString::strpos($folder, JPATH_SITE) === 0)
		{
			$folder = str_replace(JPATH_BASE, '', $folder);
		}

		$folder = str_replace('\\', DIRECTORY_SEPARATOR, $folder);
		$folder = str_replace('/', DIRECTORY_SEPARATOR, $folder);

		return $folder;
	}
}
