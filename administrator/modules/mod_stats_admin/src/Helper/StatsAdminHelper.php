<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_stats_admin
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\StatsAdmin\Administrator\Helper;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper class for admin stats module
 *
 * @since  3.0
 */
class StatsAdminHelper
{
    /**
     * Method to retrieve information about the site
     *
     * @param   Registry           $params  The module parameters
     * @param   CMSApplication     $app     The application
     * @param   DatabaseInterface  $db      The database
     *
     * @return  array  Array containing site information
     *
     * @since   5.1.0
     */
    public function getStatsData(Registry $params, CMSApplication $app, DatabaseInterface $db)
    {
        $user = $app->getIdentity();

        $rows  = [];
        $query = $db->getQuery(true);

        $serverinfo = $params->get('serverinfo', 0);
        $siteinfo   = $params->get('siteinfo', 0);

        $i = 0;

        if ($serverinfo) {
            $rows[$i]        = new \stdClass();
            $rows[$i]->title = Text::_('MOD_STATS_PHP');
            $rows[$i]->icon  = 'cogs';
            $rows[$i]->data  = PHP_VERSION;
            $i++;

            $rows[$i]        = new \stdClass();
            $rows[$i]->title = Text::_($db->name);
            $rows[$i]->icon  = 'database';
            $rows[$i]->data  = $db->getVersion();
            $i++;

            $rows[$i]        = new \stdClass();
            $rows[$i]->title = Text::_('MOD_STATS_CACHING');
            $rows[$i]->icon  = 'tachometer-alt';
            $rows[$i]->data  = $app->get('caching') ? Text::_('JENABLED') : Text::_('JDISABLED');
            $i++;

            $rows[$i]        = new \stdClass();
            $rows[$i]->title = Text::_('MOD_STATS_GZIP');
            $rows[$i]->icon  = 'bolt';
            $rows[$i]->data  = $app->get('gzip') ? Text::_('JENABLED') : Text::_('JDISABLED');
            $i++;
        }

        if ($siteinfo) {
            $query->select('COUNT(id) AS count_users')
                ->from('#__users');
            $db->setQuery($query);

            try {
                $users = $db->loadResult();
            } catch (\RuntimeException $e) {
                $users = false;
            }

            $query->clear()
                ->select('COUNT(id) AS count_items')
                ->from('#__content')
                ->where('state = 1');
            $db->setQuery($query);

            try {
                $items = $db->loadResult();
            } catch (\RuntimeException) {
                $items = false;
            }

            if ($users) {
                $rows[$i]        = new \stdClass();
                $rows[$i]->title = Text::_('MOD_STATS_USERS');
                $rows[$i]->icon  = 'users';
                $rows[$i]->data  = $users;

                if ($user->authorise('core.manage', 'com_users')) {
                    $rows[$i]->link = Route::_('index.php?option=com_users');
                }

                $i++;
            }

            if ($items) {
                $rows[$i]        = new \stdClass();
                $rows[$i]->title = Text::_('MOD_STATS_ARTICLES');
                $rows[$i]->icon  = 'file';
                $rows[$i]->data  = $items;
                $rows[$i]->link  = Route::_('index.php?option=com_content&view=articles&filter[published]=1');
                $i++;
            }
        }

        // Include additional data defined by published system plugins
        PluginHelper::importPlugin('system');

        $arrays = (array) $app->triggerEvent('onGetStats', ['mod_stats_admin']);

        foreach ($arrays as $response) {
            foreach ($response as $row) {
                // We only add a row if the title and data are given
                if (isset($row['title'], $row['data'])) {
                    $rows[$i]        = new \stdClass();
                    $rows[$i]->title = $row['title'];
                    $rows[$i]->icon  = $row['icon'] ?? 'info';
                    $rows[$i]->data  = $row['data'];
                    $rows[$i]->link  = $row['link'] ?? null;
                    $i++;
                }
            }
        }

        return $rows;
    }

    /**
     * Method to retrieve information about the site
     *
     * @param   Registry           $params  The module parameters
     * @param   CMSApplication     $app     The application
     * @param   DatabaseInterface  $db      The database
     *
     * @return  array  Array containing site information
     *
     * @since   3.0
     *
     * @deprecated 5.1.0 will be removed in 7.0
     *             Use the non-static method getStatsData
     *             Example: Factory::getApplication()->bootModule('mod_stats_admin', 'administrator')
     *                          ->getHelper('StatsAdminHelper')
     *                          ->getStatsData($params, Factory::getApplication(), Factory::getContainer()->get(DatabaseInterface::class))
     */
    public static function getStats(Registry $params, CMSApplication $app, DatabaseInterface $db)
    {
        return (new self())->getStatsData($params, $app, $db);
    }
}
