<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_banners
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * @package		Joomla.Site
 * @subpackage	com_banners
 */
abstract class BannerHelper
{
	/**
	 * Checks if a URL is an image
	 *
	 * @param string
	 * @return URL
	 */
	public static function isImage($url)
	{
		$result = preg_match('#\.(?:bmp|gif|jpe?g|png)$#i', $url);
		return $result;
	}

	/**
	 * Checks if a URL is a Flash file
	 *
	 * @param string
	 * @return URL
	 */
	public static function isFlash($url)
	{
		$result = preg_match('#\.swf$#i', $url);
		return $result;
	}
}
