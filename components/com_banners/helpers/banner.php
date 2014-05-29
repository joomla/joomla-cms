<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Banner Helper Class
 *
 * @package     Joomla.Site
 * @subpackage  com_banners
 * @since       1.6
 */
abstract class BannerHelper
{
	/**
	 * Checks if a URL is an image
	 *
	 * @param   string  $url  The URL path to the potential image
	 *
	 * @return   boolean  True if an image of type bmp, gif, jp(e)g or png, false otherwise
	 */
	public static function isImage($url)
	{
		$result = preg_match('#\.(?:bmp|gif|jpe?g|png)$#i', $url);

		return $result;
	}

	/**
	 * Checks if a URL is a Flash file
	 *
	 * @param   string  $url  The URL path to the potential flash file 
	 *
	 * @return   boolean  True if an image of type bmp, gif, jp(e)g or png, false otherwise
	 */
	public static function isFlash($url)
	{
		$result = preg_match('#\.swf$#i', $url);

		return $result;
	}
}
