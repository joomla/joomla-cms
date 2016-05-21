<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_feed
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
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
	 * @param   \Joomla\Registry\Registry  $params  module parameters
	 *
	 * @return  JFeedReader|string
	 */
	public static function getFeed($params)
	{
		// Module params
		$rssurl = $params->get('rssurl', '');

		// Get RSS parsed object
		try
		{
			$feed   = new JFeedFactory;
			$rssDoc = $feed->getFeed($rssurl);
		}
		catch (Exception $e)
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
