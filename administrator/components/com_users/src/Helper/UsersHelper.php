<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Helper\UserGroupsHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Users component helper.
 *
 * @since  1.6
 */
class UsersHelper extends ContentHelper
{
    /**
     * @var    CMSObject  A cache for the available actions.
     * @since  1.6
     * @deprecated 5.3 will be removed in 7.0 without replacement
     */
    protected static $actions;

    /**
     * Get a list of filter options for the blocked state of a user.
     *
     * @return  array  An array of \JHtmlOption elements.
     *
     * @since   1.6
     * @deprecated 5.3 will be removed in 7.0
     *             Use Form Fields instead
     */
    public static function getStateOptions()
    {
        // Build the filter options.
        $options   = [];
        $options[] = HTMLHelper::_('select.option', '0', Text::_('JENABLED'));
        $options[] = HTMLHelper::_('select.option', '1', Text::_('JDISABLED'));

        return $options;
    }

    /**
     * Get a list of filter options for the activated state of a user.
     *
     * @return  array  An array of \JHtmlOption elements.
     *
     * @since   1.6
     * @deprecated 5.3 will be removed in 7.0
     *             Use Form Fields instead
     */
    public static function getActiveOptions()
    {
        // Build the filter options.
        $options   = [];
        $options[] = HTMLHelper::_('select.option', '0', Text::_('COM_USERS_ACTIVATED'));
        $options[] = HTMLHelper::_('select.option', '1', Text::_('COM_USERS_UNACTIVATED'));

        return $options;
    }

    /**
     * Get a list of the user groups for filtering.
     *
     * @return  array  An array of \JHtmlOption elements.
     *
     * @since   1.6
     */
    public static function getGroups()
    {
        $options = UserGroupsHelper::getInstance()->getAll();

        foreach ($options as &$option) {
            $option->value = $option->id;
            $option->text  = str_repeat('- ', $option->level) . $option->title;
        }

        return $options;
    }

    /**
     * Creates a list of range options used in filter select list
     * used in com_users on users view
     *
     * @return  array
     *
     * @since   2.5
     * @deprecated 5.3 will be removed in 7.0
     *             Use Form Fields instead
     */
    public static function getRangeOptions()
    {
        $options = [
            HTMLHelper::_('select.option', 'today', Text::_('COM_USERS_OPTION_RANGE_TODAY')),
            HTMLHelper::_('select.option', 'past_week', Text::_('COM_USERS_OPTION_RANGE_PAST_WEEK')),
            HTMLHelper::_('select.option', 'past_1month', Text::_('COM_USERS_OPTION_RANGE_PAST_1MONTH')),
            HTMLHelper::_('select.option', 'past_3month', Text::_('COM_USERS_OPTION_RANGE_PAST_3MONTH')),
            HTMLHelper::_('select.option', 'past_6month', Text::_('COM_USERS_OPTION_RANGE_PAST_6MONTH')),
            HTMLHelper::_('select.option', 'past_year', Text::_('COM_USERS_OPTION_RANGE_PAST_YEAR')),
            HTMLHelper::_('select.option', 'post_year', Text::_('COM_USERS_OPTION_RANGE_POST_YEAR')),
        ];

        return $options;
    }

    /**
     * No longer used.
     *
     * @return  array
     *
     * @since   3.2.0
     * @throws  \Exception
     *
     * @deprecated  4.2 will be removed in 6.0
     *              No longer used, will be removed without replacement
     */
    public static function getTwoFactorMethods()
    {
        return [];
    }

    /**
     * Get a list of the User Groups for Viewing Access Levels
     *
     * @param   string  $rules  User Groups in JSON format
     *
     * @return  string  $groups  Comma separated list of User Groups
     *
     * @since   3.6
     */
    public static function getVisibleByGroups($rules)
    {
        $rules = json_decode($rules);

        if (!$rules) {
            return false;
        }

        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName('title', 'text'))
            ->from($db->quoteName('#__usergroups'))
            ->whereIn($db->quoteName('id'), $rules);
        $db->setQuery($query);

        $groups = $db->loadColumn();
        $groups = implode(', ', $groups);

        return $groups;
    }

    /**
     * Returns a valid section for users. If it is not valid then null
     * is returned.
     *
     * @param   string  $section  The section to get the mapping for
     *
     * @return  string|null  The new section
     *
     * @since       3.7.0
     * @throws      \Exception
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Use \Joomla\Component\Users\Administrator\Extension\UsersComponent::validateSection() instead.
     */
    public static function validateSection($section)
    {
        return Factory::getApplication()->bootComponent('com_users')->validateSection($section, null);
    }

    /**
     * Returns valid contexts
     *
     * @return  array
     *
     * @since       3.7.0
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Use \Joomla\Component\Users\Administrator\Extension\UsersComponent::getContexts() instead.
     */
    public static function getContexts()
    {
        return Factory::getApplication()->bootComponent('com_users')->getContexts();
    }
}
