<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_feed
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Feed\Site\Helper;

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
     * Retrieve feed information
     *
     * @param   \Joomla\Registry\Registry  $params  module parameters
     *
     * @return  \Joomla\CMS\Feed\Feed|string
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

        if ($rssDoc) {
            return $rssDoc;
        }
    }
}
