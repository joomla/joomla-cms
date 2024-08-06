<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form;

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Form\Field\SubformField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\Exception\DatabaseNotFoundException;
use Joomla\Registry\Registry;
use Joomla\String\Normalise;
use Joomla\String\StringHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Abstract Form Field class for the Joomla Platform.
 *
 * @since  1.7.0
 */
abstract class FormField implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * The description text for the form field. Usually used in tooltips.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $description;

    /**
     * The hint text for the form field used to display hint inside the field.
     *
     * @var    string
     * @since  3.2
     */
    protected $hint;

    /**
     * The autocomplete state for the form field.  If 'off' element will not be automatically
     * completed by browser.
     *
     * @var    mixed
     * @since  3.2
     */
    protected $autocomplete = 'on';

    /**
     * The spellcheck state for the form field.
     *
     * @var    boolean
     * @since  3.2
     */
    protected $spellcheck = true;

    /**
     * The autofocus request for the form field.  If true element will be automatically
     * focused on document load.
     *
     * @var    boolean
     * @since  3.2
     */
    protected $autofocus = false;

    /**
     * The SimpleXMLElement object of the `<field>` XML element that describes the form field.
     *
     * @var    \SimpleXMLElement
     * @since  1.7.0
     */
    protected $element;

    /**
     * The Form object of the form attached to the form field.
     *
     * @var    Form
     * @since  1.7.0
     */
    protected $form;

    /**
     * The form control prefix for field names from the Form object attached to the form field.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $formControl;

    /**
     * The hidden state for the form field.
     *
     * @var    boolean
     * @since  1.7.0
     */
    protected $hidden = false;

    /**
     * Should the label be hidden when rendering the form field? This may be useful if you have the
     * label rendering in a legend in your form field itself for radio buttons in a fieldset etc.
     * If you use this flag you should ensure you display the label in your form (for a11y etc.)
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $hiddenLabel = false;

    /**
     * Should the description be hidden when rendering the form field? This may be useful if you have the
     * description rendering in your form field itself for e.g. note fields.
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $hiddenDescription = false;

    /**
     * True to translate the field label string.
     *
     * @var    boolean
     * @since  1.7.0
     */
    protected $translateLabel = true;

    /**
     * True to translate the field description string.
     *
     * @var    boolean
     * @since  1.7.0
     */
    protected $translateDescription = true;

    /**
     * True to translate the field hint string.
     *
     * @var    boolean
     * @since  3.2
     */
    protected $translateHint = true;

    /**
     * The document id for the form field.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $id;

    /**
     * The input for the form field.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $input;

    /**
     * The label for the form field.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $label;

    /**
     * The multiple state for the form field.  If true then multiple values are allowed for the
     * field.  Most often used for list field types.
     *
     * @var    boolean
     * @since  1.7.0
     */
    protected $multiple = false;

    /**
     * Allows extensions to create repeat elements
     *
     * @var    mixed
     * @since  3.2
     */
    public $repeat = false;

    /**
     * The pattern (Reg Ex) of value of the form field.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $pattern;

    /**
     * The validation text of invalid value of the form field.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $validationtext;

    /**
     * The name of the form field.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $name;

    /**
     * The name of the field.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $fieldname;

    /**
     * The group of the field.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $group;

    /**
     * The required state for the form field.  If true then there must be a value for the field to
     * be considered valid.
     *
     * @var    boolean
     * @since  1.7.0
     */
    protected $required = false;

    /**
     * The disabled state for the form field.  If true then the field will be disabled and user can't
     * interact with the field.
     *
     * @var    boolean
     * @since  3.2
     */
    protected $disabled = false;

    /**
     * The readonly state for the form field.  If true then the field will be readonly.
     *
     * @var    boolean
     * @since  3.2
     */
    protected $readonly = false;

    /**
     * The form field type.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $type;

    /**
     * The validation method for the form field.  This value will determine which method is used
     * to validate the value for a field.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $validate;

    /**
     * The value of the form field.
     *
     * @var    mixed
     * @since  1.7.0
     */
    protected $value;

    /**
     * The default value of the form field.
     *
     * @var    mixed
     * @since  1.7.0
     */
    protected $default;

    /**
     * The size of the form field.
     *
     * @var    integer
     * @since  3.2
     */
    protected $size;

    /**
     * The class of the form field
     *
     * @var    mixed
     * @since  3.2
     */
    protected $class;

    /**
     * The label's CSS class of the form field
     *
     * @var    mixed
     * @since  1.7.0
     */
    protected $labelclass;

    /**
     * The javascript onchange of the form field.
     *
     * @var    string
     * @since  3.2
     */
    protected $onchange;

    /**
     * The javascript onclick of the form field.
     *
     * @var    string
     * @since  3.2
     */
    protected $onclick;

    /**
     * The conditions to show/hide the field.
     *
     * @var    string
     * @since  3.7.0
     */
    protected $showon;

    /**
     * The parent class of the field
     *
     * @var  string
     * @since 4.0.0
     */
    protected $parentclass;

    /**
     * The count value for generated name field
     *
     * @var    integer
     * @since  1.7.0
     */
    protected static $count = 0;

    /**
     * The string used for generated fields names
     *
     * @var    string
     * @since  1.7.0
     */
    protected static $generated_fieldname = '__field';

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     * @since  3.5
     */
    protected $layout;

    /**
     * Layout to render the form field
     *
     * @var  string
     */
    protected $renderLayout = 'joomla.form.renderfield';

    /**
     * Layout to render the label
     *
     * @var  string
     */
    protected $renderLabelLayout = 'joomla.form.renderlabel';

    /**
     * The data-attribute name and values of the form field.
     * For example, data-action-type="click" data-action-type="change"
     *
     * @var  array
     *
     * @since 4.0.0
     */
    protected $dataAttributes = [];

    /**
     * Method to instantiate the form field object.
     *
     * @param   Form  $form  The form to attach to the form field object.
     *
     * @since   1.7.0
     */
    public function __construct($form = null)
    {
        // If there is a form passed into the constructor set the form and form control properties.
        if ($form instanceof Form) {
            $this->form        = $form;
            $this->formControl = $form->getFormControl();
        }

        // Detect the field type if not set
        if (!isset($this->type)) {
            $parts = Normalise::fromCamelCase(\get_called_class(), true);

            if ($parts[0] === 'J') {
                $this->type = StringHelper::ucfirst($parts[\count($parts) - 1], '_');
            } else {
                $this->type = StringHelper::ucfirst($parts[0], '_') . StringHelper::ucfirst($parts[\count($parts) - 1], '_');
            }
        }
    }

    /**
     * Method to get certain otherwise inaccessible properties from the form field object.
     *
     * @param   string  $name  The property name for which to get the value.
     *
     * @return  mixed  The property value or null.
     *
     * @since   1.7.0
     */
    public function __get($name)
    {
        switch ($name) {
            case 'description':
            case 'hint':
            case 'formControl':
            case 'hidden':
            case 'id':
            case 'multiple':
            case 'name':
            case 'required':
            case 'type':
            case 'validate':
            case 'value':
            case 'class':
            case 'layout':
            case 'labelclass':
            case 'size':
            case 'onchange':
            case 'onclick':
            case 'fieldname':
            case 'group':
            case 'disabled':
            case 'readonly':
            case 'autofocus':
            case 'autocomplete':
            case 'spellcheck':
            case 'validationtext':
            case 'showon':
            case 'parentclass':
                return $this->$name;

            case 'input':
                // If the input hasn't yet been generated, generate it.
                if (empty($this->input)) {
                    $this->input = $this->getInput();
                }

                return $this->input;

            case 'label':
                // If the label hasn't yet been generated, generate it.
                if (empty($this->label)) {
                    $this->label = $this->getLabel();
                }

                return $this->label;

            case 'title':
                return $this->getTitle();

            default:
                // Check for data attribute
                if (strpos($name, 'data-') === 0 && array_key_exists($name, $this->dataAttributes)) {
                    return $this->dataAttributes[$name];
                }
        }
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
            case 'class':
                // Removes spaces from left & right and extra spaces from middle
                $value = preg_replace('/\s+/', ' ', trim((string) $value));

                // No break

            case 'description':
            case 'hint':
            case 'value':
            case 'labelclass':
            case 'layout':
            case 'onchange':
            case 'onclick':
            case 'validate':
            case 'pattern':
            case 'validationtext':
            case 'group':
            case 'showon':
            case 'parentclass':
            case 'default':
            case 'autocomplete':
                $this->$name = (string) $value;
                break;

            case 'id':
                $this->id = $this->getId((string) $value, $this->fieldname);
                break;

            case 'fieldname':
                $this->fieldname = $this->getFieldName((string) $value);
                break;

            case 'name':
                $this->fieldname = $this->getFieldName((string) $value);
                $this->name      = $this->getName($this->fieldname);
                break;

            case 'multiple':
                // Allow for field classes to force the multiple values option.
                $value = (string) $value;
                $value = $value === '' && isset($this->forceMultiple) ? (string) $this->forceMultiple : $value;

                // No break

            case 'required':
            case 'disabled':
            case 'readonly':
            case 'autofocus':
            case 'hidden':
                $value       = (string) $value;
                $this->$name = ($value === 'true' || $value === $name || $value === '1');
                break;

            case 'spellcheck':
            case 'translateLabel':
            case 'translateDescription':
            case 'translateHint':
                $value       = (string) $value;
                $this->$name = !($value === 'false' || $value === 'off' || $value === '0');
                break;

            case 'translate_label':
                $value                = (string) $value;
                $this->translateLabel = $this->translateLabel && !($value === 'false' || $value === 'off' || $value === '0');
                break;

            case 'translate_description':
                $value                      = (string) $value;
                $this->translateDescription = $this->translateDescription && !($value === 'false' || $value === 'off' || $value === '0');
                break;

            case 'size':
                $this->$name = (int) $value;
                break;

            default:
                // Detect data attribute(s)
                if (strpos($name, 'data-') === 0) {
                    $this->dataAttributes[$name] = $value;
                } else {
                    if (property_exists(__CLASS__, $name)) {
                        Log::add("Cannot access protected / private property $name of " . __CLASS__);
                    } else {
                        $this->$name = $value;
                    }
                }
        }
    }

    /**
     * Method to attach a Form object to the field.
     *
     * @param   Form  $form  The Form object to attach to the form field.
     *
     * @return  FormField  The form field object so that the method can be used in a chain.
     *
     * @since   1.7.0
     */
    public function setForm(Form $form)
    {
        $this->form        = $form;
        $this->formControl = $form->getFormControl();

        return $this;
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
     * @since   1.7.0
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        // Make sure there is a valid FormField XML element.
        if ((string) $element->getName() !== 'field') {
            return false;
        }

        // Reset the input and label values.
        $this->input = null;
        $this->label = null;

        // Set the XML element object.
        $this->element = $element;

        // Set the group of the field.
        $this->group = $group;

        $attributes = [
            'multiple', 'name', 'id', 'hint', 'class', 'description', 'labelclass', 'onchange', 'onclick', 'validate', 'pattern', 'validationtext',
            'default', 'required', 'disabled', 'readonly', 'autofocus', 'hidden', 'autocomplete', 'spellcheck', 'translateHint', 'translateLabel',
            'translate_label', 'translateDescription', 'translate_description', 'size', 'showon', ];

        $this->default = isset($element['value']) ? (string) $element['value'] : $this->default;

        // Set the field default value.
        if ($element['multiple'] && \is_string($value) && \is_array(json_decode($value, true))) {
            $this->value = (array) json_decode($value);
        } else {
            $this->value = $value;
        }

        // Lets detect miscellaneous data attribute. For eg, data-*
        foreach ($this->element->attributes() as $key => $value) {
            if (strpos($key, 'data-') === 0) {
                // Data attribute key value pair
                $this->dataAttributes[$key] = $value;
            }
        }

        foreach ($attributes as $attributeName) {
            $this->__set($attributeName, $element[$attributeName]);
        }

        // Allow for repeatable elements
        $repeat       = (string) $element['repeat'];
        $this->repeat = ($repeat === 'true' || $repeat === 'multiple' || (!empty($this->form->repeat) && $this->form->repeat == 1));

        // Set the visibility.
        $this->hidden = ($this->hidden || strtolower((string) $this->element['type']) === 'hidden');

        $this->layout = !empty($this->element['layout']) ? (string) $this->element['layout'] : $this->layout;

        $this->parentclass = isset($this->element['parentclass']) ? (string) $this->element['parentclass'] : $this->parentclass;

        // Add required to class list if field is required.
        if ($this->required) {
            $this->class = trim($this->class . ' required');
        }

        return true;
    }

    /**
     * Simple method to set the value
     *
     * @param   mixed  $value  Value to set
     *
     * @return  void
     *
     * @since   3.2
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Method to get the id used for the field input tag.
     *
     * @param   string  $fieldId    The field element id.
     * @param   string  $fieldName  The field element name.
     *
     * @return  string  The id to be used for the field input tag.
     *
     * @since   1.7.0
     */
    protected function getId($fieldId, $fieldName)
    {
        $id = '';

        // If there is a form control set for the attached form add it first.
        if ($this->formControl) {
            $id .= $this->formControl;
        }

        // If the field is in a group add the group control to the field id.
        if ($this->group) {
            // If we already have an id segment add the group control as another level.
            if ($id) {
                $id .= '_' . str_replace('.', '_', $this->group);
            } else {
                $id .= str_replace('.', '_', $this->group);
            }
        }

        // If we already have an id segment add the field id/name as another level.
        if ($id) {
            $id .= '_' . ($fieldId ?: $fieldName);
        } else {
            $id .= ($fieldId ?: $fieldName);
        }

        // Clean up any invalid characters.
        $id = preg_replace('#\W#', '_', $id);

        // If this is a repeatable element, add the repeat count to the ID
        if ($this->repeat) {
            $repeatCounter = empty($this->form->repeatCounter) ? 0 : $this->form->repeatCounter;
            $id .= '-' . $repeatCounter;

            if (strtolower($this->type) === 'radio') {
                $id .= '-';
            }
        }

        return $id;
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
        if (empty($this->layout)) {
            throw new \UnexpectedValueException(sprintf('%s has no layout assigned.', $this->name));
        }

        return $this->getRenderer($this->layout)->render($this->getLayoutData());
    }

    /**
     * Method to get the field title.
     *
     * @return  string  The field title.
     *
     * @since   1.7.0
     */
    protected function getTitle()
    {
        $title = '';

        if ($this->hidden) {
            return $title;
        }

        // Get the label text from the XML element, defaulting to the element name.
        $title = $this->element['label'] ? (string) $this->element['label'] : (string) $this->element['name'];
        $title = $this->translateLabel ? Text::_($title) : $title;

        return $title;
    }

    /**
     * Method to get the field label markup.
     *
     * @return  string  The field label markup.
     *
     * @since   1.7.0
     */
    protected function getLabel()
    {
        if ($this->hidden) {
            return '';
        }

        $data = $this->getLayoutData();

        // Forcing the Alias field to display the tip below
        $position = ((string) $this->element['name']) === 'alias' ? ' data-bs-placement="bottom" ' : '';

        // Here mainly for B/C with old layouts. This can be done in the layouts directly
        $extraData = [
            'text'     => $data['label'],
            'for'      => $this->id,
            'classes'  => explode(' ', $data['labelclass']),
            'position' => $position,
        ];

        return $this->getRenderer($this->renderLabelLayout)->render(array_merge($data, $extraData));
    }

    /**
     * Method to get the name used for the field input tag.
     *
     * @param   string  $fieldName  The field element name.
     *
     * @return  string  The name to be used for the field input tag.
     *
     * @since   1.7.0
     */
    protected function getName($fieldName)
    {
        // To support repeated element, extensions can set this in plugin->onRenderSettings

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

        // If the field should support multiple values add the final array segment.
        if ($this->multiple) {
            switch (strtolower((string) $this->element['type'])) {
                case 'text':
                case 'textarea':
                case 'email':
                case 'password':
                case 'radio':
                case 'calendar':
                case 'editor':
                case 'hidden':
                    break;
                default:
                    $name .= '[]';
            }
        }

        return $name;
    }

    /**
     * Method to get the field name used.
     *
     * @param   string  $fieldName  The field element name.
     *
     * @return  string  The field name
     *
     * @since   1.7.0
     */
    protected function getFieldName($fieldName)
    {
        if ($fieldName) {
            return $fieldName;
        } else {
            self::$count = self::$count + 1;

            return self::$generated_fieldname . self::$count;
        }
    }

    /**
     * Method to get an attribute of the field
     *
     * @param   string  $name     Name of the attribute to get
     * @param   mixed   $default  Optional value to return if attribute not found
     *
     * @return  mixed             Value of the attribute / default
     *
     * @since   3.2
     */
    public function getAttribute($name, $default = null)
    {
        if ($this->element instanceof \SimpleXMLElement) {
            $attributes = $this->element->attributes();

            // Ensure that the attribute exists
            if ($attributes->$name !== null) {
                return (string) $attributes->$name;
            }
        }

        return $default;
    }

    /**
     * Method to get data attributes. For example, data-user-type
     *
     * @return  array list of data attribute(s)
     *
     * @since  4.0.0
     */
    public function getDataAttributes()
    {
        return $this->dataAttributes;
    }

    /**
     * Method to render data attributes to html.
     *
     * @return  string  A HTML Tag Attribute string of data attribute(s)
     *
     * @since  4.0.0
     */
    public function renderDataAttributes()
    {
        $dataAttribute  = '';
        $dataAttributes = $this->getDataAttributes();

        if (!empty($dataAttributes)) {
            foreach ($dataAttributes as $key => $attrValue) {
                $dataAttribute .= ' ' . $key . '="' . htmlspecialchars($attrValue, ENT_COMPAT, 'UTF-8') . '"';
            }
        }

        return $dataAttribute;
    }

    /**
     * Render a layout of this field
     *
     * @param   string  $layoutId  Layout identifier
     * @param   array   $data      Optional data for the layout
     *
     * @return  string
     *
     * @since   3.5
     */
    public function render($layoutId, $data = [])
    {
        $data = array_merge($this->getLayoutData(), $data);

        return $this->getRenderer($layoutId)->render($data);
    }

    /**
     * Method to get a control group with label and input.
     *
     * @param   array  $options  Options to be passed into the rendering of the field
     *
     * @return  string  A string containing the html for the control group
     *
     * @since   3.2
     */
    public function renderField($options = [])
    {
        if ($this->hidden) {
            return $this->getInput();
        }

        if (!isset($options['class'])) {
            $options['class'] = '';
        }

        $options['rel'] = '';

        if (empty($options['hiddenLabel'])) {
            if ($this->getAttribute('hiddenLabel')) {
                $options['hiddenLabel'] = $this->getAttribute('hiddenLabel') == 'true';
            } else {
                $options['hiddenLabel'] = $this->hiddenLabel;
            }
        }

        if (empty($options['hiddenDescription'])) {
            if ($this->getAttribute('hiddenDescription')) {
                $options['hiddenDescription'] = $this->getAttribute('hiddenDescription') == 'true';
            } else {
                $options['hiddenDescription'] = $this->hiddenDescription;
            }
        }

        $options['inlineHelp'] = isset($this->form, $this->form->getXml()->config->inlinehelp['button'])
            ? ((string) $this->form->getXml()->config->inlinehelp['button'] == 'show' ?: false)
            : false;

        // Check if the field has showon in nested option
        $hasOptionShowOn = false;

        if (!empty((array) $this->element->xpath('option'))) {
            foreach ($this->element->xpath('option') as $option) {
                if ((string) $option['showon']) {
                    $hasOptionShowOn = true;

                    break;
                }
            }
        }

        if ($this->showon || $hasOptionShowOn) {
            $options['rel']           = ' data-showon=\'' .
                json_encode(FormHelper::parseShowOnConditions($this->showon, $this->formControl, $this->group)) . '\'';
            $options['showonEnabled'] = true;
        }

        $data = [
            'input'   => $this->getInput(),
            'label'   => $this->getLabel(),
            'options' => $options,
        ];

        $data = array_merge($this->getLayoutData(), $data);

        return $this->getRenderer($this->renderLayout)->render($data);
    }

    /**
     * Method to filter a field value.
     *
     * @param   mixed      $value  The optional value to use as the default for the field.
     * @param   string     $group  The optional dot-separated form group path on which to find the field.
     * @param   ?Registry  $input  An optional Registry object with the entire data set to filter
     *                             against the entire form.
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
            $required = ((string) $this->element['required'] === 'true' || (string) $this->element['required'] === 'required');

            if (($value === '' || $value === null) && !$required) {
                return '';
            }

            // Check for a callback filter
            if (strpos($filter, '::') !== false && \is_callable(explode('::', $filter))) {
                return \call_user_func(explode('::', $filter), $value);
            }

            // Load the FormRule object for the field. FormRule objects take precedence over PHP functions
            $obj = FormHelper::loadFilterType($filter);

            // Run the filter rule.
            if ($obj) {
                return $obj->filter($this->element, $value, $group, $input, $this->form);
            }

            if (\function_exists($filter)) {
                return \call_user_func($filter, $value);
            }

            if ($this instanceof SubformField) {
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

        return InputFilter::getInstance()->clean($value, $filter);
    }

    /**
     * Method to validate a FormField object based on field data.
     *
     * @param   mixed      $value  The optional value to use as the default for the field.
     * @param   string     $group  The optional dot-separated form group path on which to find the field.
     * @param   ?Registry  $input  An optional Registry object with the entire data set to validate
     *                             against the entire form.
     *
     * @return  boolean|\Exception  Boolean true if field value is valid, Exception on failure.
     *
     * @since   4.0.0
     * @throws  \InvalidArgumentException
     * @throws  \UnexpectedValueException
     */
    public function validate($value, $group = null, Registry $input = null)
    {
        // Make sure there is a valid SimpleXMLElement.
        if (!($this->element instanceof \SimpleXMLElement)) {
            throw new \UnexpectedValueException(sprintf('%s::validate `element` is not an instance of SimpleXMLElement', \get_class($this)));
        }

        $valid = true;

        // Check if the field is required.
        $required = ((string) $this->element['required'] === 'true' || (string) $this->element['required'] === 'required');

        if ($this->element['label']) {
            $fieldLabel = $this->element['label'];

            // Try to translate label if not set to false
            $translate = (string) $this->element['translateLabel'];

            if (!($translate === 'false' || $translate === 'off' || $translate === '0')) {
                $fieldLabel = Text::_($fieldLabel);
            }
        } else {
            $fieldLabel = Text::_($this->element['name']);
        }

        // If the field is required and the value is empty return an error message.
        if ($required && (($value === '') || ($value === null))) {
            $message = Text::sprintf('JLIB_FORM_VALIDATE_FIELD_REQUIRED', $fieldLabel);

            return new \RuntimeException($message);
        }

        // Get the field validation rule.
        if ($type = (string) $this->element['validate']) {
            // Load the FormRule object for the field.
            $rule = FormHelper::loadRuleType($type);

            // If the object could not be loaded return an error message.
            if ($rule === false) {
                throw new \UnexpectedValueException(sprintf('%s::validate() rule `%s` missing.', \get_class($this), $type));
            }

            if ($rule instanceof DatabaseAwareInterface) {
                try {
                    $rule->setDatabase($this->getDatabase());
                } catch (DatabaseNotFoundException $e) {
                    @trigger_error(sprintf('Database must be set, this will not be caught anymore in 5.0.'), E_USER_DEPRECATED);
                    $rule->setDatabase(Factory::getContainer()->get(DatabaseInterface::class));
                }
            }

            try {
                // Run the field validation rule test.
                $valid = $rule->test($this->element, $value, $group, $input, $this->form);
            } catch (\Exception $e) {
                return $e;
            }
        }

        if ($valid !== false && $this instanceof SubformField) {
            // Load the subform validation rule.
            $rule = FormHelper::loadRuleType('Subform');

            if ($rule instanceof DatabaseAwareInterface) {
                try {
                    $rule->setDatabase($this->getDatabase());
                } catch (DatabaseNotFoundException $e) {
                    @trigger_error(sprintf('Database must be set, this will not be caught anymore in 5.0.'), E_USER_DEPRECATED);
                    $rule->setDatabase(Factory::getContainer()->get(DatabaseInterface::class));
                }
            }

            try {
                // Run the field validation rule test.
                $valid = $rule->test($this->element, $value, $group, $input, $this->form);
            } catch (\Exception $e) {
                return $e;
            }
        }

        // Check if the field is valid.
        if ($valid === false) {
            // Does the field have a defined error message?
            $message = (string) $this->element['message'];

            if ($message) {
                $message = Text::_($this->element['message']);
            } else {
                $message = Text::sprintf('JLIB_FORM_VALIDATE_FIELD_INVALID', $fieldLabel);
            }

            return new \UnexpectedValueException($message);
        }

        return $valid;
    }

    /**
     * Method to post-process a field value.
     *
     * @param   mixed      $value  The optional value to use as the default for the field.
     * @param   string     $group  The optional dot-separated form group path on which to find the field.
     * @param   ?Registry  $input  An optional Registry object with the entire data set to filter
     *                             against the entire form.
     *
     * @return  mixed   The processed value.
     *
     * @since   4.0.0
     */
    public function postProcess($value, $group = null, Registry $input = null)
    {
        return $value;
    }

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return  array
     *
     * @since 3.5
     */
    protected function getLayoutData()
    {
        $label       = !empty($this->element['label']) ? (string) $this->element['label'] : null;
        $label       = $label && $this->translateLabel ? Text::_($label) : $label;
        $description = !empty($this->description) ? $this->description : null;
        $description = !empty($description) && $this->translateDescription ? Text::_($description) : $description;
        $alt         = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname);
        $options     = [
            'autocomplete'   => $this->autocomplete,
            'autofocus'      => $this->autofocus,
            'class'          => $this->class,
            'description'    => $description,
            'disabled'       => $this->disabled,
            'field'          => $this,
            'group'          => $this->group,
            'hidden'         => $this->hidden,
            'hint'           => $this->translateHint ? Text::alt($this->hint, $alt) : $this->hint,
            'id'             => $this->id,
            'label'          => $label,
            'labelclass'     => $this->labelclass,
            'multiple'       => $this->multiple,
            'name'           => $this->name,
            'onchange'       => $this->onchange,
            'onclick'        => $this->onclick,
            'pattern'        => $this->pattern,
            'validationtext' => $this->validationtext,
            'readonly'       => $this->readonly,
            'repeat'         => $this->repeat,
            'required'       => (bool) $this->required,
            'size'           => $this->size,
            'spellcheck'     => $this->spellcheck,
            'validate'       => $this->validate,
            'value'          => $this->value,
            'dataAttribute'  => $this->renderDataAttributes(),
            'dataAttributes' => $this->dataAttributes,
            'parentclass'    => $this->parentclass,
        ];

        return $options;
    }

    /**
     * Allow to override renderer include paths in child fields
     *
     * @return  array
     *
     * @since   3.5
     */
    protected function getLayoutPaths()
    {
        $renderer = new FileLayout('default');

        return $renderer->getDefaultIncludePaths();
    }

    /**
     * Get the renderer
     *
     * @param   string  $layoutId  Id to load
     *
     * @return  FileLayout
     *
     * @since   3.5
     */
    protected function getRenderer($layoutId = 'default')
    {
        $renderer = new FileLayout($layoutId);

        $renderer->setDebug($this->isDebugEnabled());

        $layoutPaths = $this->getLayoutPaths();

        if ($layoutPaths) {
            $renderer->setIncludePaths($layoutPaths);
        }

        return $renderer;
    }

    /**
     * Is debug enabled for this field
     *
     * @return  boolean
     *
     * @since   3.5
     */
    protected function isDebugEnabled()
    {
        return $this->getAttribute('debug', 'false') === 'true';
    }
}
