<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML\Helpers;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\UserGroupsHelper;
use Joomla\CMS\HTML\HTMLHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Utility class working with users
 *
 * @since  2.5
 */
abstract class User
{
    /**
     * Displays a list of user groups.
     *
     * @param   boolean  $includeSuperAdmin  true to include super admin groups, false to exclude them
     *
     * @return  array  An array containing a list of user groups.
     *
     * @since   2.5
     */
    public static function groups($includeSuperAdmin = false)
    {
        $options = array_values(UserGroupsHelper::getInstance()->getAll());

        foreach ($options as $option) {
            $option->value = $option->id;
            $option->text  = str_repeat('- ', $option->level) . $option->title;
            $groups[]      = HTMLHelper::_('select.option', $option->value, $option->text);
        }

        // Exclude super admin groups if requested
        if (!$includeSuperAdmin) {
            $filteredGroups = [];

            foreach ($groups as $group) {
                if (!Access::checkGroup($group->value, 'core.admin')) {
                    $filteredGroups[] = $group;
                }
            }

            $groups = $filteredGroups;
        }

        return $groups;
    }

    /**
     * Get a list of users.
     *
     * @return  string
     *
     * @since   2.5
     */
    public static function userlist()
    {
        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('a.id', 'value'),
                    $db->quoteName('a.name', 'text'),
                ]
            )
            ->from($db->quoteName('#__users', 'a'))
            ->where($db->quoteName('a.block') . ' = 0')
            ->order($db->quoteName('a.name'));
        $db->setQuery($query);

        return $db->loadObjectList();
    }
}
