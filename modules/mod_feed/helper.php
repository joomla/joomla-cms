<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_feed
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
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
	/**
	 * Retrieve feed information
	 *
	 * @param   JRegistry  $params  module parameters
	 *
	 * @return  JFeedReader|string
	 */
	public static function getFeed($params)
	{
		// Module params
		$rssurl	= $params->get('rssurl', '');

		// Get RSS parsed object
		try
		{
			if ($params->get('cache', 1) == 2)
			{
				$cache = JFactory::getCache('mod_feed', '');
				$cache->setLifeTime($params->get('cache_time', 900));
				$cache->setCaching(true);
				
				$rssDoc = $cache->get($rssurl);
				
				if ($rssDoc === false)
				{
					$feed = new JFeedFactory;
					$rssDoc = $feed->getFeed($rssurl);
					
					$cache->store($rssDoc, $rssurl);
				}
			}
			else
			{
				$feed = new JFeedFactory;
				$rssDoc = $feed->getFeed($rssurl);
			}
		}
		catch (InvalidArgumentException $e)
		{
			return JText::_('MOD_FEED_ERR_FEED_NOT_RETRIEVED');
		}
		catch (RunTimeException $e)
		{
			return JText::_('MOD_FEED_ERR_FEED_NOT_RETRIEVED');
		}
		catch (LogicException $e)
		{
			return JText::_('MOD_FEED_ERR_FEED_NOT_RETRIEVED');
		}

		if (empty($rssDoc))
		{
			return JText::_('MOD_FEED_ERR_FEED_NOT_RETRIEVED');
		}

		if ($rssDoc)
		{
			return $rssDoc;
		}
	}
}
