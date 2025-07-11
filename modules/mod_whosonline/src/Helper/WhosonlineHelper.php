<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_whosonline
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Whosonline\Site\Helper;

use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_whosonline
 *
 * @since  1.5
 */
class WhosonlineHelper
{
    /**
     * Show online count
     *
     * @return  array  The number of Users and Guests online.
     *
     * @since   1.5
     **/
    public static function getOnlineCount()
    {
        $db = Factory::getDbo();

        // Calculate number of guests and users
        $result      = [];
        $user_array  = 0;
        $guest_array = 0;

        $whereCondition = Factory::getApplication()->get('shared_session', '0') ? 'IS NULL' : '= 0';

        $query = $db->getQuery(true)
            ->select('guest, client_id')
            ->from('#__session')
            ->where('client_id ' . $whereCondition);
        $db->setQuery($query);

        try {
            $sessions = (array) $db->loadObjectList();
        } catch (\RuntimeException) {
            $sessions = [];
        }

        if (\count($sessions)) {
            foreach ($sessions as $session) {
                // If guest increase guest count by 1
                if ($session->guest == 1) {
                    $guest_array++;
                }

                // If member increase member count by 1
                if ($session->guest == 0) {
                    $user_array++;
                }
            }
        }

        $result['user']  = $user_array;
        $result['guest'] = $guest_array;

        return $result;
    }

    /**
     * Show online member names
     *
     * @param   mixed  $params  The parameters
     *
     * @return  array   (array) $db->loadObjectList()  The names of the online users.
     *
     * @since   1.5
     **/
    public static function getOnlineUserNames($params)
    {
        $whereCondition = Factory::getApplication()->get('shared_session', '0') ? 'IS NULL' : '= 0';

        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['a.username', 'a.userid', 'a.client_id']))
            ->from($db->quoteName('#__session', 'a'))
            ->where($db->quoteName('a.userid') . ' != 0')
            ->where($db->quoteName('a.client_id') . ' ' . $whereCondition)
            ->group($db->quoteName(['a.username', 'a.userid', 'a.client_id']));

        $user = Factory::getUser();

        if (!$user->authorise('core.admin') && $params->get('filter_groups', 0) == 1) {
            $groups = $user->getAuthorisedGroups();

            if (empty($groups)) {
                return [];
            }

            $query->leftJoin($db->quoteName('#__user_usergroup_map', 'm'), $db->quoteName('m.user_id') . ' = ' . $db->quoteName('a.userid'))
                ->leftJoin($db->quoteName('#__usergroups', 'ug'), $db->quoteName('ug.id') . ' = ' . $db->quoteName('m.group_id'))
                ->whereIn($db->quoteName('ug.id'), $groups)
                ->where($db->quoteName('ug.id') . ' <> 1');
        }

        $db->setQuery($query);

        try {
            return (array) $db->loadObjectList();
        } catch (\RuntimeException) {
            return [];
        }
    }
}
