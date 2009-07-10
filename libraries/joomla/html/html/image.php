<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	HTML
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Utility class working with images
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
abstract class JHtmlImage
{
	/**
	* Checks to see if an image exists in the current templates image directory
 	* if it does it loads this image.  Otherwise the default image is loaded.
	* Also can be used in conjunction with the menulist param to create the chosen image
	* load the default or use no image
	*
	* @param	string	The file name, eg foobar.png
	* @param	string	The path to the image
	* @param	int		empty: use $file and $folder, -1: show no image, not-empty: use $altFile and $altFolder
	* @param	string	Another path.  Only used for the contact us form based on the value of the imagelist parm
	* @param	string	Alt text
	* @param	array	An associative array of attributes to add
	* @param	boolean	True (default) to display full tag, false to return just the path
	*/
	public static function site($file, $folder='/images/joomla/', $altFile=NULL, $altFolder='/images/joomla/', $alt=NULL, $attribs = null, $asTag = 1)
	{
		static $paths;
		$appl = &JFactory::getApplication();

		if (!$paths) {
			$paths = array();
		}

		if (is_array($attribs)) {
			$attribs = JArrayHelper::toString($attribs);
		}

		$cur_template = $appl->getTemplate();

		if ($altFile)
		{
			// $param allows for an alternative file to be used
			$src = $altFolder . $altFile;
		}
		else if ($altFile == -1)
		{
			// Comes from an image list param field with 'Do not use' selected
			return '';
		} else {
			$path = JPATH_SITE .'/templates/'. $cur_template .'/images/'. $file;
			if (!isset($paths[$path]))
			{
				if (file_exists(JPATH_SITE .'/templates/'. $cur_template .'/images/'. $file)) {
					$paths[$path] = 'templates/'. $cur_template .'/images/'. $file;
				} else {
					// outputs only path to image
					$paths[$path] = $folder . $file;
				}
			}
			$src = $paths[$path];
		}

		if (substr($src, 0, 1) == "/") {
			$src = substr_replace($src, '', 0, 1);
		}

		// Prepend the base path
		$src = JURI::base(true).'/'.$src;

		// outputs actual html <img> tag
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
	* @param	string	The file name, eg foobar.png
	* @param	string	The path to the image
	* @param	int		empty: use $file and $folder, -1: show no image, not-empty: use $altFile and $altFolder
	* @param	string	Another path.  Only used for the contact us form based on the value of the imagelist parm
	* @param	string	Alt text
	* @param	array	An associative array of attributes to add
	* @param	boolean	True (default) to display full tag, false to return just the path
	*/
	public static function administrator($file, $directory='/images/', $param=NULL, $param_directory='/images/', $alt = NULL, $attribs = null, $type = 1)
	{
		$appl = &JFactory::getApplication();

		if (is_array($attribs)) {
			$attribs = JArrayHelper::toString($attribs);
		}

		$cur_template = $appl->getTemplate();

		// strip html
		$alt	= html_entity_decode($alt);

		if ($param) {
			$image = $param_directory . $param;
		} else if ($param == -1) {
			$image = '';
		} else {
			if (file_exists(JPATH_ADMINISTRATOR .'/templates/'. $cur_template .'/images/'. $file)) {
				$image = 'templates/'. $cur_template .'/images/'. $file;
			} else {
				// compability with previous versions
				if (substr($directory, 0, 14)== "/administrator") {
					$image = substr($directory,15) . $file;
				} else {
					$image = $directory . $file;
				}
			}
		}

		if (substr($image, 0, 1) == "/") {
			$image = substr_replace($image, '', 0, 1);
		}

		// Prepend the base path
		$image = JURI::base(true).'/'.$image;

		// outputs actual html <img> tag
		if ($type) {
			$image = '<img src="'. $image .'" alt="'. $alt .'" '.$attribs.' />';
		}

		return $image;
	}
}