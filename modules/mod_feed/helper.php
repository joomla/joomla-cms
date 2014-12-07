<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_feed
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

class modFeedHelper
{
	static function getFeed($params)
	{
		// module params
		$rssurl	= $params->get('rssurl', '');

		// get RSS parsed object
		$cache_time = 0;
		if ($params->get('cache'))
		{
			/*
			 * The cache_time will get fed into JCache to initiate the feed_parser cache group and eventually
			 * JCacheStorage will multiply the value by 60 and use that for its lifetime. The only way to sync
			 * the feed_parser cache (which caches with an empty dataset anyway) with the module cache is to
			 * first divide the module's cache time by 60 then inject that forward, which once stored into the
			 * JCacheStorage object, will be the correct value in minutes.
			 */
			$cache_time  = $params->get('cache_time', 15) / 60;
		}

		$rssDoc = JFactory::getFeedParser($rssurl, $cache_time);

		$feed = new stdClass;

		if ($rssDoc != false)
		{
			// channel header and link
			$feed->title = $rssDoc->get_title();
			$feed->link = $rssDoc->get_link();
			$feed->description = $rssDoc->get_description();

			// channel image if exists
			$feed->image = new stdClass;
			$feed->image->url = $rssDoc->get_image_url();
			$feed->image->title = $rssDoc->get_image_title();

			// items
			$items = $rssDoc->get_items();

			// feed elements
			$feed->items = array_slice($items, 0, $params->get('rssitems', 5));
		} else {
			$feed = false;
		}

		return $feed;
	}
}
