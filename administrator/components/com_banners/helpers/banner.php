<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Banners
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * @package		Joomla
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
			$config =& JComponentHelper::getParams('com_banners');
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
		$result = preg_match('#(\.bmp|\.gif|\.jpg|\.jpeg|\.png)$#', $url);
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
		$result = preg_match('#\.swf$#', $url);
		return $result;
	}
}
?>