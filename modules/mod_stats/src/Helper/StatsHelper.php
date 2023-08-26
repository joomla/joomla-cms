<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_stats
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Stats\Site\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_stats
 *
 * @since  1.5
 */
class StatsHelper
{
    /**
     * Get list of stats
     *
     * @param   \Joomla\Registry\Registry  &$params  module parameters
     *
     * @return  array
     */
    public static function &getList(&$params)
    {
        $app        = Factory::getApplication();
        $db         = Factory::getDbo();
        $rows       = [];
        $query      = $db->getQuery(true);
        $serverinfo = $params->get('serverinfo', 0);
        $siteinfo   = $params->get('siteinfo', 0);
        $counter    = $params->get('counter', 0);
        $increase   = $params->get('increase', 0);

        $i = 0;

        if ($serverinfo) {
            $rows[$i]        = new \stdClass();
            $rows[$i]->title = Text::_('MOD_STATS_OS');
            $rows[$i]->data  = substr(php_uname(), 0, 7);
            $i++;

            $rows[$i]        = new \stdClass();
            $rows[$i]->title = Text::_('MOD_STATS_PHP');
            $rows[$i]->data  = PHP_VERSION;
            $i++;

            $rows[$i]        = new \stdClass();
            $rows[$i]->title = Text::_($db->name);
            $rows[$i]->data  = $db->getVersion();
            $i++;

            $rows[$i]        = new \stdClass();
            $rows[$i]->title = Text::_('MOD_STATS_TIME');
            $rows[$i]->data  = HTMLHelper::_('date', 'now', 'H:i');
            $i++;

            $rows[$i]        = new \stdClass();
            $rows[$i]->title = Text::_('MOD_STATS_CACHING');
            $rows[$i]->data  = $app->get('caching') ? Text::_('JENABLED') : Text::_('JDISABLED');
            $i++;

            $rows[$i]        = new \stdClass();
            $rows[$i]->title = Text::_('MOD_STATS_GZIP');
            $rows[$i]->data  = $app->get('gzip') ? Text::_('JENABLED') : Text::_('JDISABLED');
            $i++;
        }

        if ($siteinfo) {
            $query->select('COUNT(' . $db->quoteName('id') . ') AS count_users')
                ->from($db->quoteName('#__users'));
            $db->setQuery($query);

            try {
                $users = $db->loadResult();
            } catch (\RuntimeException $e) {
                $users = false;
            }

            $query->clear()
                ->select('COUNT(' . $db->quoteName('c.id') . ') AS count_items')
                ->from($db->quoteName('#__content', 'c'))
                ->where($db->quoteName('c.state') . ' = ' . ContentComponent::CONDITION_PUBLISHED);
            $db->setQuery($query);

            try {
                $items = $db->loadResult();
            } catch (\RuntimeException $e) {
                $items = false;
            }

            if ($users) {
                $rows[$i]        = new \stdClass();
                $rows[$i]->title = Text::_('MOD_STATS_USERS');
                $rows[$i]->data  = $users;
                $i++;
            }

            if ($items) {
                $rows[$i]        = new \stdClass();
                $rows[$i]->title = Text::_('MOD_STATS_ARTICLES');
                $rows[$i]->data  = $items;
                $i++;
            }
        }

        if ($counter) {
            $query->clear()
                ->select('SUM(' . $db->quoteName('hits') . ') AS count_hits')
                ->from($db->quoteName('#__content'))
                ->where($db->quoteName('state') . ' = ' . ContentComponent::CONDITION_PUBLISHED);
            $db->setQuery($query);

            try {
                $hits = $db->loadResult();
            } catch (\RuntimeException $e) {
                $hits = false;
            }

            if ($hits) {
                $rows[$i]        = new \stdClass();
                $rows[$i]->title = Text::_('MOD_STATS_ARTICLES_VIEW_HITS');
                $rows[$i]->data  = $hits + $increase;
                $i++;
            }
        }

        // Include additional data defined by published system plugins
        PluginHelper::importPlugin('system');

        $arrays = (array) $app->triggerEvent('onGetStats', ['mod_stats']);

        foreach ($arrays as $response) {
            foreach ($response as $row) {
                // We only add a row if the title and data are given
                if (isset($row['title']) && isset($row['data'])) {
                    $rows[$i]        = new \stdClass();
                    $rows[$i]->title = $row['title'];
                    $rows[$i]->icon  = $row['icon'] ?? 'info';
                    $rows[$i]->data  = $row['data'];
                    $i++;
                }
            }
        }

        return $rows;
    }
}
