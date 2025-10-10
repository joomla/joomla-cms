<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Messages\Administrator\Field;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\UserField;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Supports a modal select of users that have access to com_messages
 *
 * @since  1.6
 */
class UserMessagesField extends UserField
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   1.6
     */
    public $type = 'UserMessages';

    /**
     * Method to get the filtering groups (null means no filtering)
     *
     * @return  array|null  array of filtering groups or null.
     *
     * @since   1.6
     */
    protected function getGroups()
    {
        // Compute usergroups
        $db    = $this->getDatabase();
        $query = $db->createQuery()
            ->select('id')
            ->from('#__usergroups');
        $db->setQuery($query);

        try {
            $groups = $db->loadColumn();
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'notice');

            return null;
        }

        foreach ($groups as $i => $group) {
            if (Access::checkGroup($group, 'core.admin')) {
                continue;
            }

            if (!Access::checkGroup($group, 'core.manage', 'com_messages')) {
                unset($groups[$i]);
                continue;
            }

            if (!Access::checkGroup($group, 'core.login.admin')) {
                unset($groups[$i]);
            }
        }

        return array_values($groups);
    }

    /**
     * Method to get the users to exclude from the list of users
     *
     * @return  array|null array of users to exclude or null to not exclude them
     *
     * @since   1.6
     */
    protected function getExcluded()
    {
        return [$this->getCurrentUser()->id];
    }
}
