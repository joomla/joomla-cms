<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Fields\Administrator\Field;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Joomla\CMS\Fields\FieldsServiceInterface;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Components Fieldgroup field.
 *
 * @since  1.6
 */
class ComponentsFieldgroupField extends ListField
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   3.7.0
     */
    protected $type = 'ComponentsFieldgroup';

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array  An array of JHtml options.
     *
     * @since   3.7.0
     */
    protected function getOptions()
    {
        // Initialise variable.
        $db = $this->getDatabase();

        $query = $db->getQuery(true)
            ->select('DISTINCT a.name AS text, a.element AS value')
            ->from('#__extensions as a')
            ->where('a.enabled >= 1')
            ->where('a.type =' . $db->quote('component'));

        $items = $db->setQuery($query)->loadObjectList();

        $options = [];

        if (count($items)) {
            $lang = Factory::getLanguage();

            $components = [];

            // Search for components supporting Fieldgroups - suppose that these components support fields as well
            foreach ($items as &$item) {
                $availableActions = Access::getActionsFromFile(
                    JPATH_ADMINISTRATOR . '/components/' . $item->value . '/access.xml',
                    "/access/section[@name='fieldgroup']/"
                );

                if (!empty($availableActions)) {
                    // Load language
                    $source = JPATH_ADMINISTRATOR . '/components/' . $item->value;
                    $lang->load($item->value . 'sys', JPATH_ADMINISTRATOR)
                        || $lang->load($item->value . 'sys', $source);

                    // Translate component name
                    $item->text = Text::_($item->text);

                    $components[]  = $item;
                }
            }

            if (empty($components)) {
                return [];
            }

            foreach ($components as $component) {
                // Search for different contexts
                $c = Factory::getApplication()->bootComponent($component->value);

                if ($c instanceof FieldsServiceInterface) {
                    $contexts = $c->getContexts();

                    foreach ($contexts as $context) {
                        $newOption        = new \stdClass();
                        $newOption->value = strtolower($component->value . '.' . $context);
                        $newOption->text  = $component->text . ' - ' . Text::_($context);
                        $options[]        = $newOption;
                    }
                } else {
                    $options[] = $component;
                }
            }

            // Sort by name
            $items = ArrayHelper::sortObjects($options, 'text', 1, true, true);
        }

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $items);

        return $options;
    }
}
