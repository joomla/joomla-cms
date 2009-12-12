<?php
/**
 * @version		$Id: banners.php 13611 2009-11-29 14:31:00Z chdemko $
 * @package		Joomla.Site
 * @subpackage	com_banners
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

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
		$result = preg_match('#(\.bmp|\.gif|\.jpg|\.jpeg|\.png)$#i', $url);
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

