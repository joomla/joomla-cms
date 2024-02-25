<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormField;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The Field to load the form inside current form
 *
 * @Example with all attributes:
 *  <field name="field-name" type="subform"
 *      formsource="path/to/form.xml" min="1" max="3" multiple="true" buttons="add,remove,move"
 *      layout="joomla.form.field.subform.repeatable-table" groupByFieldset="false" component="com_example" client="site"
 *      label="Field Label" description="Field Description" />
 *
 * @since  3.6
 */
class SubformField extends FormField
{
    /**
     * The form field type.
     * @var    string
     */
    protected $type = 'Subform';

    /**
     * Form source
     * @var string
     */
    protected $formsource;

    /**
     * Minimum items in repeat mode
     * @var integer
     */
    protected $min = 0;

    /**
     * Maximum items in repeat mode
     * @var integer
     */
    protected $max = 1000;

    /**
     * Layout to render the form
     * @var  string
     */
    protected $layout = 'joomla.form.field.subform.default';

    /**
     * Whether group subform fields by it`s fieldset
     * @var boolean
     */
    protected $groupByFieldset = false;

    /**
     * Which buttons to show in multiple mode
     * @var boolean[] $buttons
     */
    protected $buttons = ['add' => true, 'remove' => true, 'move' => true];

    /**
     * Method to get certain otherwise inaccessible properties from the form field object.
     *
     * @param   string  $name  The property name for which to get the value.
     *
     * @return  mixed  The property value or null.
     *
     * @since   3.6
     */
    public function __get($name)
    {
        switch ($name) {
            case 'formsource':
            case 'min':
            case 'max':
            case 'layout':
            case 'groupByFieldset':
            case 'buttons':
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
     * @since   3.6
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'formsource':
                $this->formsource = (string) $value;

                // Add root path if we have a path to XML file
                if (strrpos($this->formsource, '.xml') === \strlen($this->formsource) - 4) {
                    $this->formsource = Path::clean(JPATH_ROOT . '/' . $this->formsource);
                }

                break;

            case 'min':
                $this->min = (int) $value;
                break;

            case 'max':
                if ($value) {
                    $this->max = max(1, (int) $value);
                }
                break;

            case 'groupByFieldset':
                if ($value !== null) {
                    $value                 = (string) $value;
                    $this->groupByFieldset = !($value === 'false' || $value === 'off' || $value === '0');
                }
                break;

            case 'layout':
                $this->layout = (string) $value;

                // Make sure the layout is not empty.
                if (!$this->layout) {
                    // Set default value depend from "multiple" mode
                    $this->layout = !$this->multiple ? 'joomla.form.field.subform.default' : 'joomla.form.field.subform.repeatable';
                }

                break;

            case 'buttons':
                if (!$this->multiple) {
                    $this->buttons = [];
                    break;
                }

                if ($value && !\is_array($value)) {
                    $value = explode(',', (string) $value);
                    $value = array_fill_keys(array_filter($value), true);
                }

                if ($value) {
                    $value         = array_merge(['add' => false, 'remove' => false, 'move' => false], $value);
                    $this->buttons = $value;
                }

                break;

            case 'value':
                // We allow a json encoded string or an array
                if (is_string($value)) {
                    $value = json_decode($value, true);
                }

                $this->value = $value !== null ? (array) $value : null;

                break;

            default:
                parent::__set($name, $value);
        }
    }

    /**
     * Method to attach a Form object to the field.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value.
     *
     * @return  boolean  True on success.
     *
     * @since   3.6
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        if (!parent::setup($element, $value, $group)) {
            return false;
        }

        foreach (['formsource', 'min', 'max', 'layout', 'groupByFieldset', 'buttons'] as $attributeName) {
            $this->__set($attributeName, $element[$attributeName]);
        }

        if ((string) $element['fieldname']) {
            $this->__set('fieldname', $element['fieldname']);
        }

        if ($this->value && \is_string($this->value)) {
            // Guess here is the JSON string from 'default' attribute
            $this->value = json_decode($this->value, true);
        }

        if (!$this->formsource && $element->form) {
            // Set the formsource parameter from the content of the node
            $this->formsource = $element->form->saveXML();
        }

        return true;
    }

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   3.6
     */
    protected function getInput()
    {
        // Prepare data for renderer
        $data    = $this->getLayoutData();
        $tmpl    = null;
        $control = $this->name;

        try {
            $tmpl  = $this->loadSubForm();
            $forms = $this->loadSubFormData($tmpl);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        $data['tmpl']            = $tmpl;
        $data['forms']           = $forms;
        $data['min']             = $this->min;
        $data['max']             = $this->max;
        $data['control']         = $control;
        $data['buttons']         = $this->buttons;
        $data['fieldname']       = $this->fieldname;
        $data['fieldId']         = $this->id;
        $data['groupByFieldset'] = $this->groupByFieldset;

        /**
         * For each rendering process of a subform element, we want to have a
         * separate unique subform id present to could distinguish the eventhandlers
         * regarding adding/moving/removing rows from nested subforms from their parents.
         */
        static $unique_subform_id  = 0;
        $data['unique_subform_id'] = ('sr-' . ($unique_subform_id++));

        // Prepare renderer
        $renderer = $this->getRenderer($this->layout);

        // Allow to define some Layout options as attribute of the element
        if ($this->element['component']) {
            $renderer->setComponent((string) $this->element['component']);
        }

        if ($this->element['client']) {
            $renderer->setClient((string) $this->element['client']);
        }

        // Render
        $html = $renderer->render($data);

        // Add hidden input on front of the subform inputs, in multiple mode
        // for allow to submit an empty value
        if ($this->multiple) {
            $html = '<input name="' . $this->name . '" type="hidden" value="">' . $html;
        }

        return $html;
    }

    /**
     * Method to get the name used for the field input tag.
     *
     * @param   string  $fieldName  The field element name.
     *
     * @return  string  The name to be used for the field input tag.
     *
     * @since   3.6
     */
    protected function getName($fieldName)
    {
        $name = '';

        // If there is a form control set for the attached form add it first.
        if ($this->formControl) {
            $name .= $this->formControl;
        }

        // If the field is in a group add the group control to the field name.
        if ($this->group) {
            // If we already have a name segment add the group control as another level.
            $groups = explode('.', $this->group);

            if ($name) {
                foreach ($groups as $group) {
                    $name .= '[' . $group . ']';
                }
            } else {
                $name .= array_shift($groups);

                foreach ($groups as $group) {
                    $name .= '[' . $group . ']';
                }
            }
        }

        // If we already have a name segment add the field name as another level.
        if ($name) {
            $name .= '[' . $fieldName . ']';
        } else {
            $name .= $fieldName;
        }

        return $name;
    }

    /**
     * Loads the form instance for the subform.
     *
     * @return  Form  The form instance.
     *
     * @throws  \InvalidArgumentException if no form provided.
     * @throws  \RuntimeException if the form could not be loaded.
     *
     * @since   3.9.7
     */
    public function loadSubForm()
    {
        $control = $this->name;

        if ($this->multiple) {
            $control .= '[' . $this->fieldname . 'X]';
        }

        // Prepare the form template
        $formname = 'subform.' . str_replace(['jform[', '[', ']'], ['', '.', ''], $this->name);
        $tmpl     = Form::getInstance($formname, $this->formsource, ['control' => $control]);

        return $tmpl;
    }

    /**
     * Binds given data to the subform and its elements.
     *
     * @param   Form  $subForm  Form instance of the subform.
     *
     * @return  Form[]  Array of Form instances for the rows.
     *
     * @since   3.9.7
     */
    protected function loadSubFormData(Form $subForm)
    {
        $value = $this->value ? (array) $this->value : [];

        // Simple form, just bind the data and return one row.
        if (!$this->multiple) {
            $subForm->bind($value);

            return [$subForm];
        }

        // Multiple rows possible: Construct array and bind values to their respective forms.
        $forms = [];
        $value = array_values($value);

        // Show as many rows as we have values, but at least min and at most max.
        $c = max($this->min, min(\count($value), $this->max));

        for ($i = 0; $i < $c; $i++) {
            $control  = $this->name . '[' . $this->fieldname . $i . ']';
            $itemForm = Form::getInstance($subForm->getName() . $i, $this->formsource, ['control' => $control]);

            if (!empty($value[$i])) {
                $itemForm->bind($value[$i]);
            }

            $forms[] = $itemForm;
        }

        return $forms;
    }

    /**
     * Method to filter a field value.
     *
     * @param   mixed      $value  The optional value to use as the default for the field.
     * @param   string     $group  The optional dot-separated form group path on which to find the field.
     * @param   ?Registry  $input  An optional Registry object with the entire data set to filter
     *                            against the entire form.
     *
     * @return  mixed   The filtered value.
     *
     * @since   4.0.0
     * @throws  \UnexpectedValueException
     */
    public function filter($value, $group = null, Registry $input = null)
    {
        // Make sure there is a valid SimpleXMLElement.
        if (!($this->element instanceof \SimpleXMLElement)) {
            throw new \UnexpectedValueException(sprintf('%s::filter `element` is not an instance of SimpleXMLElement', \get_class($this)));
        }

        // Get the field filter type.
        $filter = (string) $this->element['filter'];

        if ($filter !== '') {
            return parent::filter($value, $group, $input);
        }

        // Dirty way of ensuring required fields in subforms are submitted and filtered the way other fields are
        $subForm = $this->loadSubForm();

        // Subform field may have a default value, that is a JSON string
        if ($value && is_string($value)) {
            $value = json_decode($value, true);

            // The string is invalid json
            if (!$value) {
                return null;
            }
        }

        if ($this->multiple) {
            $return = [];

            if ($value) {
                foreach ($value as $key => $val) {
                    $return[$key] = $subForm->filter($val);
                }
            }
        } else {
            $return = $subForm->filter($value);
        }

        return $return;
    }
}
