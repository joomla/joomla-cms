<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Field class for the Joomla Platform.
 * Supports a generic list of options.
 *
 * @since  1.7.0
 */
class ListField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $type = 'List';

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     * @since  4.0.0
     */
    protected $layout = 'joomla.form.field.list';

    /**
     * Method to get the field input markup for a generic list.
     * Use the multiple attribute to enable multiselect.
     *
     * @return  string  The field input markup.
     *
     * @since   3.7.0
     */
    protected function getInput()
    {
        $data = $this->getLayoutData();

        $data['options'] = (array) $this->getOptions();

        return $this->getRenderer($this->layout)->render($data);
    }

    /**
     * Method to get the field options.
     *
     * @return  object[]  The field option objects.
     *
     * @since   3.7.0
     */
    protected function getOptions()
    {
        $fieldname = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname);
        $options   = [];

        foreach ($this->element->xpath('option') as $option) {
            // Filter requirements
            $requires = explode(',', (string) $option['requires']);

            // Requires multilanguage
            if (\in_array('multilanguage', $requires) && !Multilanguage::isEnabled()) {
                continue;
            }

            // Requires associations
            if (\in_array('associations', $requires) && !Associations::isEnabled()) {
                continue;
            }

            // Requires adminlanguage
            if (\in_array('adminlanguage', $requires) && !ModuleHelper::isAdminMultilang()) {
                continue;
            }

            // Requires vote plugin
            if (\in_array('vote', $requires) && !PluginHelper::isEnabled('content', 'vote')) {
                continue;
            }

            // Requires record hits
            if (\in_array('hits', $requires) && !ComponentHelper::getParams('com_content')->get('record_hits', 1)) {
                continue;
            }

            $value = (string) $option['value'];
            $text  = trim((string) $option) != '' ? trim((string) $option) : $value;

            $disabled = (string) $option['disabled'];
            $disabled = ($disabled === 'true' || $disabled === 'disabled' || $disabled === '1');
            $disabled = $disabled || ($this->readonly && $value != $this->value);

            $checked = (string) $option['checked'];
            $checked = ($checked === 'true' || $checked === 'checked' || $checked === '1');

            $selected = (string) $option['selected'];
            $selected = ($selected === 'true' || $selected === 'selected' || $selected === '1');

            $tmp = [
                    'value'    => $value,
                    'text'     => Text::alt($text, $fieldname),
                    'disable'  => $disabled,
                    'class'    => (string) $option['class'],
                    'selected' => ($checked || $selected),
                    'checked'  => ($checked || $selected),
            ];

            // Set some event handler attributes. But really, should be using unobtrusive js.
            $tmp['onclick']  = (string) $option['onclick'];
            $tmp['onchange'] = (string) $option['onchange'];

            if ((string) $option['showon']) {
                $encodedConditions = json_encode(
                    FormHelper::parseShowOnConditions((string) $option['showon'], $this->formControl, $this->group)
                );

                $tmp['optionattr'] = " data-showon='" . $encodedConditions . "'";
            }

            // Add the option object to the result set.
            $options[] = (object) $tmp;
        }

        if ($this->element['useglobal']) {
            $tmp        = new \stdClass();
            $tmp->value = '';
            $tmp->text  = Text::_('JGLOBAL_USE_GLOBAL');
            $component  = Factory::getApplication()->getInput()->getCmd('option');

            // Get correct component for menu items
            if ($component === 'com_menus') {
                $link      = $this->form->getData()->get('link');
                $uri       = new Uri($link);
                $component = $uri->getVar('option', 'com_menus');
            }

            $params = ComponentHelper::getParams($component);
            $value  = $params->get($this->fieldname);

            // Try with global configuration
            if (\is_null($value)) {
                $value = Factory::getApplication()->get($this->fieldname);
            }

            // Try with menu configuration
            if (\is_null($value) && Factory::getApplication()->getInput()->getCmd('option') === 'com_menus') {
                $value = ComponentHelper::getParams('com_menus')->get($this->fieldname);
            }

            if (!\is_null($value)) {
                $value = (string) $value;

                foreach ($options as $option) {
                    if ($option->value === $value) {
                        $value = $option->text;

                        break;
                    }
                }

                $tmp->text = Text::sprintf('JGLOBAL_USE_GLOBAL_VALUE', $value);
            }

            array_unshift($options, $tmp);
        }

        reset($options);

        return $options;
    }

    /**
     * Method to add an option to the list field.
     *
     * @param   string    $text        Text/Language variable of the option.
     * @param   string[]  $attributes  Array of attributes ('name' => 'value') format
     *
     * @return  ListField  For chaining.
     *
     * @since   3.7.0
     */
    public function addOption($text, $attributes = [])
    {
        if ($text && $this->element instanceof \SimpleXMLElement) {
            $child = $this->element->addChild('option', $text);

            foreach ($attributes as $name => $value) {
                $child->addAttribute($name, $value);
            }
        }

        return $this;
    }

    /**
     * Method to get certain otherwise inaccessible properties from the form field object.
     *
     * @param   string  $name  The property name for which to get the value.
     *
     * @return  mixed  The property value or null.
     *
     * @since   3.7.0
     */
    public function __get($name)
    {
        if ($name === 'options') {
            return $this->getOptions();
        }

        return parent::__get($name);
    }
}
