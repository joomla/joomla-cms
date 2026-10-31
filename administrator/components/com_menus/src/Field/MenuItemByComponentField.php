<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Administrator\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

/**
 * MenuItem by Component field.
 *
 * @since 4.3.0
 */
class MenuItemByComponentField extends ListField
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   4.3.0
     */
    protected $type = 'MenuItemByComponent';

    /**
     * Method to get a list of options for a list input.
     *
     * @return    array  An array of JHtml options.
     *
     * @since   4.3.0
     */
    protected function getOptions()
    {
        // Initialise variable.
        $db      = $this->getDatabase();
        $options = [];

        $query = $db->createQuery();
        $query->select('DISTINCT ' . $db->quoteName('extensions.element'))
            ->from($db->quoteName('#__menu', 'menu'))
            ->join(
                'INNER',
                $db->quoteName('#__extensions', 'extensions'),
                $db->quoteName('extensions.extension_id') . ' = ' . $db->quoteName('menu.component_id')
            )
            ->where($db->quoteName('menu.client_id') . ' = 0')
            ->where($db->quoteName('menu.type') . ' = ' . $db->quote('component'))
            ->where($db->quoteName('extensions.extension_id') . ' IS NOT NULL');

        $app             = Factory::getApplication();
        $currentMenuType = $app->getInput()->getString('menutype', $app->getUserState($this->context . '.menutype', ''));

        if ($currentMenuType) {
            $query->where($db->quoteName('menu.menutype') . ' = :currentMenuType')
                ->bind(':currentMenuType', $currentMenuType);
        }

        $db->setQuery($query);
        $components = $db->loadColumn();

        foreach ($components as $component) {
            // Load component language files
            $lang = $app->getLanguage();
            $lang->load($component, JPATH_BASE)
            || $lang->load($component, JPATH_ADMINISTRATOR . '/components/' . $component);

            $option        = new \stdClass();
            $option->value = $component;
            $option->text  = Text::_(strtoupper($component));
            $options[]     = $option;
        }

        // Sort by name
        $options = ArrayHelper::sortObjects($options, 'text', 1, true, true);

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
