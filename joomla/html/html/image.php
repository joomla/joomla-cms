<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Html
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Utility class working with images.
 *
 * @package 	Joomla.Framework
 * @subpackage	Html
 * @since		1.5
 */
abstract class JHtmlImage
{
	/**
	 * Checks to see if an image exists in the current templates image directory.
 	 * If it does it loads this image.  Otherwise the default image is loaded.
	 * Also can be used in conjunction with the menulist param to create the chosen image
	 * load the default or use no image.
	 *
	 * @param	string	$file		The file name, eg foobar.png.
	 * @param	string	$folder		The path to the image.
	 * @param	int		$altFile	Empty: use $file and $folder, -1: show no image, not-empty: use $altFile and $altFolder.
	 * @param	string	$altFolder	Another path.  Only used for the contact us form based on the value of the imagelist param.
	 * @param	string	$alt		Alternative text.
	 * @param	array	$attribs	An associative array of attributes to add.
	 * @param	bool	$asTag		True (default) to display full tag, false to return just the path.
	 */
	public static function site($file, $folder = '/images/system/', $altFile = null, $altFolder = '/images/system/', $alt = null, $attribs = null, $asTag = true)
	{
		static $paths;
		$app = &JFactory::getApplication();

		if (!$paths) {
			$paths = array();
		}

		if (is_array($attribs)) {
			$attribs = JArrayHelper::toString($attribs);
		}

		$cur_template = $app->getTemplate();

		// Strip HTML.
		$alt = html_entity_decode($alt);

		if ($altFile) {
			$src = $altFolder . $altFile;
		}
		else if ($altFile == -1) {
			return '';
		}
		else
		{
			$path = JPATH_SITE .'/templates/'. $cur_template .'/images/'. $file;
			if (!isset($paths[$path]))
			{
				if (file_exists(JPATH_SITE .'/templates/'. $cur_template .'/images/'. $file)) {
					$paths[$path] = 'templates/'. $cur_template .'/images/'. $file;
				}
				else
				{
					// Outputs only path to image.
					$paths[$path] = $folder . $file;
				}
			}
			$src = $paths[$path];
		}

		if (substr($src, 0, 1) == "/") {
			$src = substr_replace($src, '', 0, 1);
		}

		// Prepend the base path.
		$src = JURI::base(true).'/'.$src;

		// Outputs actual html <img> tag.
		if ($asTag) {
			return '<img src="'. $src .'" alt="'. html_entity_decode($alt) .'" '.$attribs.' />';
		}

		return $src;
	}

	/**
	 * Checks to see if an image exists in the current templates image directory
	 * if it does it loads this image.  Otherwise the default image is loaded.
	 * Also can be used in conjunction with the menulist param to create the chosen image
	 * load the default or use no image
	 *
	 * @param	string	$file		The file name, eg foobar.png.
	 * @param	string	$folder		The path to the image.
	 * @param	int		$altFile	Empty: use $file and $folder, -1: show no image, not-empty: use $altFile and $altFolder.
	 * @param	string	$altFolder	Another path.  Only used for the contact us form based on the value of the imagelist param.
	 * @param	string	$alt		Alternative text.
	 * @param	array	$attribs	An associative array of attributes to add.
	 * @param	bool	$asTag		True (default) to display full tag, false to return just the path.
	 */
	public static function administrator($file, $folder = '/images/', $altFile = null, $altFolder = '/images/', $alt = null, $attribs = null, $asTag = true)
	{
		$app = &JFactory::getApplication();

		if (is_array($attribs)) {
			$attribs = JArrayHelper::toString($attribs);
		}

		$cur_template = $app->getTemplate();

		// Strip HTML.
		$alt = html_entity_decode($alt);

		if ($altFile) {
			$image = $altFolder . $altFile;
		}
		else if ($altFile == -1) {
			$image = '';
		}
		else
		{
			if (file_exists(JPATH_ADMINISTRATOR .'/templates/'. $cur_template .'/images/'. $file)) {
				$image = 'templates/'. $cur_template .'/images/'. $file;
			}
			else
			{
				// Compability with previous versions.
				if (substr($folder, 0, 14) == "/administrator") {
					$image = substr($folder, 15) . $file;
				} else {
					$image = $folder . $file;
				}
			}
		}

		if (substr($image, 0, 1) == "/") {
			$image = substr_replace($image, '', 0, 1);
		}

		// Prepend the base path.
		$image = JURI::base(true).'/'.$image;

		// Outputs actual html <img> tag.
		if ($asTag) {
			$image = '<img src="'. $image .'" alt="'. $alt .'" '.$attribs.' />';
		}

		return $image;
	}
}