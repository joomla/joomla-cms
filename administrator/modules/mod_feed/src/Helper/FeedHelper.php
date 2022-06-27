<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_feed
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Feed\Administrator\Helper;

use Joomla\CMS\Feed\FeedFactory;
use Joomla\CMS\Language\Text;

/**
 * Helper for mod_feed
 *
 * @since  1.5
 */
class FeedHelper
{
    /**
     * Method to load a feed.
     *
     * @param   \Joomla\Registry\Registry  $params  The parameters object.
     *
     * @return  \Joomla\CMS\Feed\Feed|string  Return a JFeedReader object or a string message if error.
     *
     * @since   1.5
     */
    public static function getFeed($params)
    {
        // Module params
        $rssurl = $params->get('rssurl', '');

        // Get RSS parsed object
        try {
            $feed   = new FeedFactory();
            $rssDoc = $feed->getFeed($rssurl);
        } catch (\Exception $e) {
            return Text::_('MOD_FEED_ERR_FEED_NOT_RETRIEVED');
        }

        if (empty($rssDoc)) {
            return Text::_('MOD_FEED_ERR_FEED_NOT_RETRIEVED');
        }

        return $rssDoc;
    }
}
