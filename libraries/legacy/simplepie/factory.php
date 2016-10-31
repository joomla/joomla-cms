<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Simplepie
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('simplepie.simplepie');

/**
 * Class to maintain a pathway.
 *
 * The user's navigated path within the application.
 *
 * @since       3.0
 * @deprecated  3.0 Use JFeed or supply your own methods
 */
class JSimplepieFactory
{
	/**
	 * Get a parsed XML Feed Source
	 *
	 * @param   string   $url         Url for feed source.
	 * @param   integer  $cache_time  Time to cache feed for (using internal cache mechanism).
	 *
	 * @return  SimplePie|boolean  SimplePie parsed object on success, false on failure.
	 *
	 * @since   3.0
	 * @deprecated  3.0  Use JFeedFactory($url) instead.
	 */
	public static function getFeedParser($url, $cache_time = 0)
	{
		JLog::add(__METHOD__ . ' is deprecated.   Use JFeedFactory() or supply Simple Pie instead.', JLog::WARNING, 'deprecated');

		$cache = JFactory::getCache('feed_parser', 'callback');

		if ($cache_time > 0)
		{
			$cache->setLifeTime($cache_time);
		}

		$simplepie = new SimplePie(null, null, 0);

		$simplepie->enable_cache(false);
		$simplepie->set_feed_url($url);
		$simplepie->force_feed(true);

		$contents = $cache->get(array($simplepie, 'init'), null, false, false);

		if ($contents)
		{
			return $simplepie;
		}

		JLog::add(JText::_('JLIB_UTIL_ERROR_LOADING_FEED_DATA'), JLog::WARNING, 'jerror');

		return false;
	}
}
