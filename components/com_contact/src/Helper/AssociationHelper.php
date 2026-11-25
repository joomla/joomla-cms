<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contact\Site\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\Component\Categories\Administrator\Helper\CategoryAssociationHelper;
use Joomla\Database\DatabaseInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Contact Component Association Helper
 *
 * @since  3.0
 */
abstract class AssociationHelper extends CategoryAssociationHelper
{
    /**
     * Method to get the associations for a given item
     *
     * @param   integer  $id    Id of the item
     * @param   string   $view  Name of the view
     *
     * @return  array   Array of associations for the item
     *
     * @since  3.0
     */
    public static function getAssociations($id = 0, $view = null)
    {
        $jinput = Factory::getApplication()->getInput();
        $view   ??= $jinput->get('view');
        $id     = empty($id) ? $jinput->getInt('id') : $id;

        if ($view === 'contact' && $id) {
            $user      = Factory::getApplication()->getIdentity();
            $groups    = implode(',', $user->getAuthorisedViewLevels());
            $db        = Factory::getContainer()->get(DatabaseInterface::class);
            $advClause = [];

            // Filter by user groups
            $advClause[] = 'c2.access IN (' . $groups . ')';

            // Filter by current language
            $advClause[] = 'c2.language != ' . $db->quote(Factory::getApplication()->getLanguage()->getTag());

            if (!$user->authorise('core.edit.state', 'com_contact') && !$user->authorise('core.edit', 'com_contact')) {
                // Filter by start and end dates.
                $date = Factory::getDate();

                $nowDate = $db->quote($date->toSql());

                $advClause[] = '(c2.publish_up IS NULL OR c2.publish_up <= ' . $nowDate . ')';
                $advClause[] = '(c2.publish_down IS NULL OR c2.publish_down >= ' . $nowDate . ')';

                // Filter by published
                $advClause[] = 'c2.published = 1';
            }

            $associations = Associations::getAssociations(
                'com_contact',
                '#__contact_details',
                'com_contact.item',
                (array) $id,
                'id',
                'alias',
                'catid',
                $advClause
            );

            $return = [];

            foreach ($associations as $itemId => $itemAssociations) {
                foreach ($itemAssociations as $tag => $item) {
                    $return[$itemId][$tag] = RouteHelper::getContactRoute($item->id, (int) $item->catid, $item->language);
                }
            }

            return \is_array($id) ? $return : ($return[$id] ?? []);
        }

        if ($view === 'category' || $view === 'categories') {
            return self::getCategoryAssociations($id, 'com_contact');
        }

        return [];
    }
}
