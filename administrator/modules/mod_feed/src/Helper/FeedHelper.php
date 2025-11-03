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
use Joomla\Registry\Registry;

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
     * Method to load a feed.
     *
     * @param   \Joomla\Registry\Registry  $params  The parameters object.
     * @param   \Joomla\CMS\Feed\FeedFactory  FeedFactory object
     *
     * @return  \Joomla\CMS\Feed\Feed|string  Return a Feed object or a string message if error.
     *
     * @since   5.3.0
     */
    public function getFeedData(Registry $params, FeedFactory $feed): \Joomla\CMS\Feed\Feed|string
    {
        // Module params
        $rssurl = $params->get('rssurl', '');

        // Get RSS parsed object
        try {
            $rssDoc = $feed->getFeed($rssurl);
        } catch (\Exception) {
            return Text::_('MOD_FEED_ERR_FEED_NOT_RETRIEVED');
        }

        if (empty($rssDoc)) {
            return Text::_('MOD_FEED_ERR_FEED_NOT_RETRIEVED');
        }

        return $rssDoc;
    }

    /**
     * Method to load a feed.
     *
     * @param   \Joomla\Registry\Registry  $params  The parameters object.
     *
     * @return  \Joomla\CMS\Feed\Feed|string  Return a Feed object or a string message if error.
     *
     * @since   1.5
     *
     * @deprecated 5.3.0 will be removed in 7.0
     *             Use the non-static method getFeedData
     *             Example: Factory::getApplication()->bootModule('mod_feed', 'administrator')
     *                          ->getHelper('FeedHelper')
     *                          ->getFeedData($params, new FeedFactory())
     */
    public static function getFeed($params)
    {
        return (new self())->getFeedData($params, new FeedFactory());
    }
}
