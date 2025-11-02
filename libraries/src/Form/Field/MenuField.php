<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\Language\Text;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Supports an HTML select list of menus
 *
 * @since  1.6
 */
class MenuField extends GroupedlistField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.6
     */
    public $type = 'Menu';

    /**
     * Method to get the field option groups.
     *
     * @return  array[]  The field option objects as a nested array in groups.
     *
     * @since   1.7.0
     * @throws  \UnexpectedValueException
     */
    protected function getGroups()
    {
        $clientId   = (string) $this->element['clientid'];
        $accessType = (string) $this->element['accesstype'];
        $showAll    = (string) $this->element['showAll'] === 'true';

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('id'),
                    $db->quoteName('menutype', 'value'),
                    $db->quoteName('title', 'text'),
                    $db->quoteName('client_id'),
                ]
            )
            ->from($db->quoteName('#__menu_types'))
            ->order(
                [
                    $db->quoteName('client_id'),
                    $db->quoteName('title'),
                ]
            );

        if (\strlen($clientId)) {
            $client = (int) $clientId;
            $query->where($db->quoteName('client_id') . ' = :client')
                ->bind(':client', $client, ParameterType::INTEGER);
        }

        $menus = $db->setQuery($query)->loadObjectList();

        if ($accessType) {
            $user = $this->getCurrentUser();

            foreach ($menus as $key => $menu) {
                switch ($accessType) {
                    case 'create':
                    case 'manage':
                        if (!$user->authorise('core.' . $accessType, 'com_menus.menu.' . (int) $menu->id)) {
                            unset($menus[$key]);
                        }
                        break;

                    case 'edit':
                        // Editing a menu item is a bit tricky, we have to check the current menutype for core.edit and all others for core.create
                        $check = $this->value == $menu->value ? 'edit' : 'create';

                        if (!$user->authorise('core.' . $check, 'com_menus.menu.' . (int) $menu->id)) {
                            unset($menus[$key]);
                        }
                        break;
                }
            }
        }

        $opts = [];

        // Protected menutypes can be shown if requested
        if ($clientId == 1 && $showAll) {
            $opts[] = (object) [
                'value'     => 'main',
                'text'      => Text::_('COM_MENUS_MENU_TYPE_PROTECTED_MAIN_LABEL'),
                'client_id' => 1,
            ];
        }

        $options = array_merge($opts, $menus);
        $groups  = [];

        if (\strlen($clientId)) {
            $groups[0] = $options;
        } else {
            foreach ($options as $option) {
                // If client id is not specified we group the items.
                $label = ($option->client_id == 1 ? Text::_('JADMINISTRATOR') : Text::_('JSITE'));

                $groups[$label][] = $option;
            }
        }

        // Merge any additional options in the XML definition.
        return array_merge(parent::getGroups(), $groups);
    }
}
