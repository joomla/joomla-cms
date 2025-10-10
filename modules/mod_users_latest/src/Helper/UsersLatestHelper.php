<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_users_latest
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\UsersLatest\Site\Helper;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_users_latest
 *
 * @since  1.6
 */
class UsersLatestHelper implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * Get users sorted by activation date
     *
     * @param   Registry         $params  Object holding the models parameters
     * @param   SiteApplication  $app     The app
     *
     * @return  array  The array of users
     *
     * @since   4.4.0
     */
    public function getLatestUsers(Registry $params, SiteApplication $app): array
    {
        // Get the Dbo and User object
        $db   = $this->getDatabase();
        $user = $app->getIdentity();

        $query = $db->createQuery()
            ->select($db->quoteName(['a.id', 'a.name', 'a.username', 'a.registerDate']))
            ->order($db->quoteName('a.registerDate') . ' DESC')
            ->from($db->quoteName('#__users', 'a'));

        if (!$user->authorise('core.admin') && $params->get('filter_groups', 0) == 1) {
            $groups = $user->getAuthorisedGroups();

            if (empty($groups)) {
                return [];
            }

            $query->leftJoin($db->quoteName('#__user_usergroup_map', 'm'), $db->quoteName('m.user_id') . ' = ' . $db->quoteName('a.id'))
                ->leftJoin($db->quoteName('#__usergroups', 'ug'), $db->quoteName('ug.id') . ' = ' . $db->quoteName('m.group_id'))
                ->whereIn($db->quoteName('ug.id'), $groups)
                ->where($db->quoteName('ug.id') . ' <> 1');
        }

        $query->setLimit((int) $params->get('shownumber', 5));
        $db->setQuery($query);

        try {
            return (array) $db->loadObjectList();
        } catch (\RuntimeException) {
            $app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

            return [];
        }
    }

    /**
     * Get users sorted by activation date
     *
     * @param   \Joomla\Registry\Registry  $params  module parameters
     *
     * @return  array  The array of users
     *
     * @since   1.6
     *
     * @deprecated 4.4.0 will be removed in 6.0
     *             Use the non-static method getLatestUsers
     *             Example: Factory::getApplication()->bootModule('mod_users_latest', 'site')
     *                          ->getHelper('UsersLatestHelper')
     *                          ->getLatestUsers($params, Factory::getApplication())
     */
    public static function getUsers($params)
    {
        return (new self())->getLatestUsers($params, Factory::getApplication());
    }
}
