<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_feed
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_feed
 *
 * @package     Joomla.Site
 * @subpackage  mod_feed
 * @since       1.5
 */
class ModFeedHelper
{
	public static function getFeed($params)
	{
		// module params
		$rssurl	= $params->get('rssurl', '');

		// get RSS parsed object
		$cache_time = 0;
		if ($params->get('cache'))
		{
			$cache_time  = $params->get('cache_time', 15) * 60;
		}

		try
		{
			$feed = new JFeedFactory;
			$rssDoc = $feed->getFeed($rssurl);
		}
		catch (InvalidArgumentException $e)
		{
			$msg = JText::_('MOD_NEWSFEEDS_ERRORS_FEED_NOT_RETRIEVED');
		}
		catch (RunTimeException $e)
		{
			$msg = JText::_('MOD_FEED_ERR_FEED_NOT_RETRIEVED');
		}

		if (empty($rssDoc))
		{
			$msg = JText::_('MOD_FEED_ERR_FEED_NOT_RETRIEVED');
			return $msg;
		}

		if ($rssDoc)
		{
			return $rssDoc;
		}
	}
}
