<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_users_latest
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\UsersLatest\Site\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Helper for mod_users_latest
 *
 * @since  1.6
 */
class UsersLatestHelper
{
    /**
     * Get users sorted by activation date
     *
     * @param   \Joomla\Registry\Registry  $params  module parameters
     *
     * @return  array  The array of users
     *
     * @since   1.6
     */
    public static function getUsers($params)
    {
        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['a.id', 'a.name', 'a.username', 'a.registerDate']))
            ->order($db->quoteName('a.registerDate') . ' DESC')
            ->from($db->quoteName('#__users', 'a'));
        $user = Factory::getUser();

        if (!$user->authorise('core.admin') && $params->get('filter_groups', 0) == 1) {
            $groups = $user->getAuthorisedGroups();

            if (empty($groups)) {
                return array();
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
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

            return array();
        }
    }
}
