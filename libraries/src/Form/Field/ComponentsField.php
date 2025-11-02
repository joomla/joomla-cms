<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Field class for the Joomla Framework.
 *
 * @since  3.7.0
 */
class ComponentsField extends ListField
{
    /**
     * The form field type.
     *
     * @var     string
     * @since  3.7.0
     */
    protected $type = 'Components';

    /**
     * Method to get a list of options for a list input.
     *
     * @return  object[]  An array of JHtml options.
     *
     * @since   2.5.0
     */
    protected function getOptions()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('name', 'text'),
                    $db->quoteName('element', 'value'),
                ]
            )
            ->from($db->quoteName('#__extensions'))
            ->where(
                [
                    $db->quoteName('enabled') . ' >= 1',
                    $db->quoteName('type') . ' = ' . $db->quote('component'),
                ]
            );

        $items = $db->setQuery($query)->loadObjectList();

        if ($items) {
            $lang = Factory::getLanguage();

            foreach ($items as &$item) {
                // Load language
                $extension = $item->value;

                $lang->load("$extension.sys", JPATH_ADMINISTRATOR)
                    || $lang->load("$extension.sys", JPATH_ADMINISTRATOR . '/components/' . $extension);

                // Translate component name
                $item->text = Text::_($item->text);
            }

            // Sort by component name
            $items = ArrayHelper::sortObjects($items, 'text', 1, true, true);
        }

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $items);

        return $options;
    }
}
