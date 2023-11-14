<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Field class for the Joomla Framework.
 *
 * @since  2.5.0
 */
class PluginsField extends ListField
{
    /**
     * The field type.
     *
     * @var    string
     * @since  2.5.0
     */
    protected $type = 'Plugins';

    /**
     * The path to folder for plugins.
     *
     * @var    string
     * @since  3.2
     */
    protected $folder;

    /**
     * Method to get certain otherwise inaccessible properties from the form field object.
     *
     * @param   string  $name  The property name for which to get the value.
     *
     * @return  mixed  The property value or null.
     *
     * @since   3.2
     */
    public function __get($name)
    {
        if ($name === 'folder') {
            return $this->folder;
        }

        return parent::__get($name);
    }

    /**
     * Method to set certain otherwise inaccessible properties of the form field object.
     *
     * @param   string  $name   The property name for which to set the value.
     * @param   mixed   $value  The value of the property.
     *
     * @return  void
     *
     * @since   3.2
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'folder':
                $this->folder = (string) $value;
                break;

            default:
                parent::__set($name, $value);
        }
    }

    /**
     * Method to attach a Form object to the field.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as an array container for the field.
     *                                       For example if the field has name="foo" and the group value is set to "bar" then the
     *                                       full field name would end up being "bar[foo]".
     *
     * @return  boolean  True on success.
     *
     * @see     FormField::setup()
     * @since   3.2
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $return = parent::setup($element, $value, $group);

        if ($return) {
            $this->folder = (string) $this->element['folder'];
        }

        return $return;
    }

    /**
     * Method to get a list of options for a list input.
     *
     * @return  object[]  An array of JHtml options.
     *
     * @since   2.5.0
     */
    protected function getOptions()
    {
        $folder        = $this->folder;
        $parentOptions = parent::getOptions();

        if (empty($folder)) {
            Log::add(Text::_('JFRAMEWORK_FORM_FIELDS_PLUGINS_ERROR_FOLDER_EMPTY'), Log::WARNING, 'jerror');

            return $parentOptions;
        }

        // Get list of plugins
        $db    = $this->getDatabase();
        $query = $db->createQuery()
            ->select(
                [
                    $db->quoteName('element', 'value'),
                    $db->quoteName('name', 'text'),
                ]
            )
            ->from($db->quoteName('#__extensions'))
            ->where(
                [
                    $db->quoteName('folder') . ' = :folder',
                    $db->quoteName('enabled') . ' = 1',
                ]
            )
            ->bind(':folder', $folder)
            ->order(
                [
                    $db->quoteName('ordering'),
                    $db->quoteName('name'),
                ]
            );

        if ((string) $this->element['useaccess'] === 'true') {
            $query->whereIn($db->quoteName('access'), $this->getCurrentUser()->getAuthorisedViewLevels());
        }

        $options   = $db->setQuery($query)->loadObjectList();
        $lang      = Factory::getLanguage();
        $useGlobal = $this->element['useglobal'];

        if ($useGlobal) {
            $globalValue = Factory::getApplication()->get($this->fieldname);
        }

        foreach ($options as $i => $item) {
            $source    = JPATH_PLUGINS . '/' . $folder . '/' . $item->value;
            $extension = 'plg_' . $folder . '_' . $item->value;
            $lang->load($extension . '.sys', JPATH_ADMINISTRATOR) || $lang->load($extension . '.sys', $source);
            $item->text = Text::_($item->text);

            // If we are using useglobal update the use global value text with the plugin text.
            if ($useGlobal && isset($parentOptions[0]) && $item->value === $globalValue) {
                $text                   = Text::_($extension);
                $parentOptions[0]->text = Text::sprintf('JGLOBAL_USE_GLOBAL_VALUE', ($text === '' || $text === $extension ? $item->value : $text));
            }
        }

        return array_merge($parentOptions, $options);
    }
}
