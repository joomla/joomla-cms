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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Field class for the Joomla Platform.
 * Supports a one line text field.
 *
 * @link   https://html.spec.whatwg.org/multipage/input.html#text-(type=text)-state-and-search-state-(type=search)
 * @since  1.7.0
 */
class TextField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $type = 'Text';

    /**
     * The allowable maxlength of the field.
     *
     * @var    integer
     * @since  3.2
     */
    protected $maxLength;

    /**
     * Does this field support a character counter?
     *
     * @var    boolean
     * @since  4.3.0
     */
    protected $charcounter = false;

    /**
     * The mode of input associated with the field.
     *
     * @var    mixed
     * @since  3.2
     */
    protected $inputmode;

    /**
     * The name of the form field direction (ltr or rtl).
     *
     * @var    string
     * @since  3.2
     */
    protected $dirname;

    /**
     * Input addon before
     *
     * @var    string
     * @since  4.0.0
     */
    protected $addonBefore;

    /**
     * Input addon after
     *
     * @var    string
     * @since  4.0.0
     */
    protected $addonAfter;

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     * @since  3.7
     */
    protected $layout = 'joomla.form.field.text';

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
        switch ($name) {
            case 'maxLength':
            case 'dirname':
            case 'addonBefore':
            case 'addonAfter':
            case 'inputmode':
            case 'charcounter':
                return $this->$name;
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
            case 'maxLength':
                $this->maxLength = (int) $value;
                break;

            case 'dirname':
                $value         = (string) $value;
                $this->dirname = ($value == $name || $value === 'true' || $value === '1');
                break;

            case 'inputmode':
                $this->inputmode = (string) $value;
                break;

            case 'addonBefore':
                $this->addonBefore = (string) $value;
                break;

            case 'addonAfter':
                $this->addonAfter = (string) $value;
                break;

            case 'charcounter':
                $this->charcounter = strtolower($value) === 'true';
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
        $result = parent::setup($element, $value, $group);

        if ($result == true) {
            $inputmode = (string) $this->element['inputmode'];
            $dirname   = (string) $this->element['dirname'];

            $this->inputmode = '';
            $inputmode       = preg_replace('/\s+/', ' ', trim($inputmode));
            $inputmode       = explode(' ', $inputmode);

            if (!empty($inputmode)) {
                $defaultInputmode = \in_array('default', $inputmode) ? Text::_('JLIB_FORM_INPUTMODE') . ' ' : '';

                foreach (array_keys($inputmode, 'default') as $key) {
                    unset($inputmode[$key]);
                }

                $this->inputmode = $defaultInputmode . implode(' ', $inputmode);
            }

            // Set the dirname.
            $dirname       = ($dirname === 'dirname' || $dirname === 'true' || $dirname === '1');
            $this->dirname = $dirname ? $this->getName($this->fieldname . '_dir') : false;

            $this->maxLength   = (int) $this->element['maxlength'];
            $this->charcounter = isset($this->element['charcounter']) ? strtolower($this->element['charcounter']) === 'true' : false;

            $this->addonBefore = (string) $this->element['addonBefore'];
            $this->addonAfter  = (string) $this->element['addonAfter'];
        }

        return $result;
    }

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   1.7.0
     */
    protected function getInput()
    {
        if ($this->element['useglobal']) {
            $component = Factory::getApplication()->getInput()->getCmd('option');

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

                $this->hint = Text::sprintf('JGLOBAL_USE_GLOBAL_VALUE', $value);
            }
        }

        return $this->getRenderer($this->layout)->render($this->getLayoutData());
    }

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     *
     * @since   3.4
     */
    protected function getOptions()
    {
        $options = [];

        foreach ($this->element->children() as $option) {
            // Only add <option /> elements.
            if ($option->getName() !== 'option') {
                continue;
            }

            // Create a new option object based on the <option /> element.
            $options[] = HTMLHelper::_(
                'select.option',
                (string) $option['value'],
                Text::alt(trim((string) $option), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)),
                'value',
                'text'
            );
        }

        return $options;
    }

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return  array
     *
     * @since 3.7
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        // Initialize some field attributes.
        $maxLength    = !empty($this->maxLength) ? ' maxlength="' . $this->maxLength . '"' : '';
        $inputmode    = !empty($this->inputmode) ? ' inputmode="' . $this->inputmode . '"' : '';
        $dirname      = !empty($this->dirname) ? ' dirname="' . $this->dirname . '"' : '';

        // Get the field options for the datalist.
        $options  = (array) $this->getOptions();

        $extraData = [
            'maxLength'   => $maxLength,
            'pattern'     => $this->pattern,
            'inputmode'   => $inputmode,
            'dirname'     => $dirname,
            'addonBefore' => $this->addonBefore,
            'addonAfter'  => $this->addonAfter,
            'options'     => $options,
            'charcounter' => $this->charcounter,
        ];

        return array_merge($data, $extraData);
    }
}
