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

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

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
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getFeedData($params)
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

    /**
     * Retrieve feed information
     *
     * @param   \Joomla\Registry\Registry  $params  module parameters
     *
     * @return  \Joomla\CMS\Feed\Feed|string
     *
     * @since   1.5
     *
     * @deprecated __DEPLOY_VERSION__ will be removed in 6.0
     *             Use the non-static method getFeedData
     *             Example: Factory::getApplication()->bootModule('mod_feed', 'site')
     *                          ->getHelper('FeedHelper')
     *                          ->getFeedData($params)
     */
    public static function getFeed($params)
    {
        return (new self())->getFeedData($params);
    }
}
