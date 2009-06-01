<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Banners
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * @package		Joomla.Administrator
 * @subpackage	Banners
 */
class BannerHelper
{
	/**
	 * Returns a list of valid keywords based on the prefix in banner
	 * configuration
	 * @param mixed An array of keywords, or comma delimited string
	 * @return array
	 * @static
	 */
	function &getKeywords($keywords)
	{
		static $instance;

		if (!$instance)
		{
			$config = &JComponentHelper::getParams('com_banners');
			$prefix = $config->get('tag_prefix');

			$instance = array();

			if (!is_array($keywords))
			{
				$keywords = explode(',', $keywords);
			}

			foreach ($keywords as $keyword)
			{
				$keyword = trim($keyword);
				$regex = '#^' . $prefix . '#';
				if (preg_match($regex, $keyword))
				{
					$instance[] = $keyword;
				}
			}
		}
		return $instance;
	}

	/**
	 * Checks if a URL is an image
	 *
	 * @param string
	 * @return URL
	 */
	function isImage($url)
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
	function isFlash($url)
	{
		$result = preg_match('#\.swf$#i', $url);
		return $result;
	}
}
?>