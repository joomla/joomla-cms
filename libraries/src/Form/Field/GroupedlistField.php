<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Field class for the Joomla Platform.
 * Provides a grouped list select field.
 *
 * @since  1.7.0
 */
class GroupedlistField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $type = 'Groupedlist';

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     * @since  4.0.0
     */
    protected $layout = 'joomla.form.field.groupedlist';

    /**
     * Whether the field should submit an empty value when nothing is selected in the <select multiple>.
     * Because browser does not submit anything when <select multiple> is empty.
     *
     * @var    boolean
     *
     * @since  __DEPLOY_VERSION__
     */
    protected $emptyValueWhenUnselected = false;

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
     * @since   __DEPLOY_VERSION__
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $return = parent::setup($element, $value, $group);

        if ($return && $element['emptyValueWhenUnselected']) {
            $this->__set('emptyValueWhenUnselected', $element['emptyValueWhenUnselected']);
        }

        return $return;
    }

    /**
     * Method to get certain otherwise inaccessible properties from the form field object.
     *
     * @param   string  $name  The property name for which to get the value.
     *
     * @return  mixed  The property value or null.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __get($name)
    {
        switch ($name) {
            case 'emptyValueWhenUnselected':
                return $this->emptyValueWhenUnselected;

            default:
                return parent::__get($name);
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
     * @since   __DEPLOY_VERSION__
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'emptyValueWhenUnselected':
                $this->emptyValueWhenUnselected = \is_bool($value) ? $value : ($value == 'true' || $value == '1');
                break;
            default:
                parent::__set($name, $value);
        }
    }

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
        $groups = [];
        $label  = $this->layout === 'joomla.form.field.groupedlist-fancy-select' ? '' : 0;
        // To be able to display an out-of-group option when using grouped list with fancy-select,
        // this one should be in an empty group. This allows you to have a placeholder option with a non-empty value.
        // Choices.js issue about mixed options with optgroup: https://github.com/Choices-js/Choices/pull/1110

        foreach ($this->element->children() as $element) {
            switch ($element->getName()) {
                case 'option':
                    // The element is an <option />
                    // Initialize the group if necessary.
                    if (!isset($groups[$label])) {
                        $groups[$label] = [];
                    }

                    $disabled = (string) $element['disabled'];
                    $disabled = ($disabled === 'true' || $disabled === 'disabled' || $disabled === '1');

                    // Create a new option object based on the <option /> element.
                    $tmp = HTMLHelper::_(
                        'select.option',
                        ($element['value']) ? (string) $element['value'] : trim((string) $element),
                        Text::alt(trim((string) $element), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)),
                        'value',
                        'text',
                        $disabled
                    );

                    // Set some option attributes.
                    $tmp->class = (string) $element['class'];

                    // Set some JavaScript option attributes.
                    $tmp->onclick = (string) $element['onclick'];

                    // Add the option.
                    $groups[$label][] = $tmp;
                    break;

                case 'group':
                    // The element is a <group />
                    // Get the group label.
                    if ($groupLabel = (string) $element['label']) {
                        $label = Text::_($groupLabel);
                    }

                    // Initialize the group if necessary.
                    if (!isset($groups[$label])) {
                        $groups[$label] = [];
                    }

                    // Iterate through the children and build an array of options.
                    foreach ($element->children() as $option) {
                        // Only add <option /> elements.
                        if ($option->getName() !== 'option') {
                            continue;
                        }

                        $disabled = (string) $option['disabled'];
                        $disabled = ($disabled === 'true' || $disabled === 'disabled' || $disabled === '1');

                        // Create a new option object based on the <option /> element.
                        $tmp = HTMLHelper::_(
                            'select.option',
                            ($option['value']) ? (string) $option['value'] : Text::_(trim((string) $option)),
                            Text::_(trim((string) $option)),
                            'value',
                            'text',
                            $disabled
                        );

                        // Set some option attributes.
                        $tmp->class = (string) $option['class'];

                        // Set some JavaScript option attributes.
                        $tmp->onclick = (string) $option['onclick'];

                        // Add the option.
                        $groups[$label][] = $tmp;
                    }

                    if ($groupLabel) {
                        $label = \count($groups);
                    }
                    break;

                default:
                    // Unknown element type.
                    throw new \UnexpectedValueException(\sprintf('Unsupported element %s in GroupedlistField', $element->getName()), 500);
            }
        }

        return $groups;
    }

    /**
     * Method to get the field input markup for a grouped list.
     * Multiselect is enabled by using the multiple attribute.
     *
     * @return  string  The field input markup.
     *
     * @since   1.7.0
     */
    protected function getInput()
    {
        $data = $this->collectLayoutData();

        // Get the field groups.
        $data['groups'] = (array) $this->getGroups();

        return $this->getRenderer($this->layout)->render($data);
    }

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return  array
     *
     * @since __DEPLOY_VERSION__
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        $data['emptyValueWhenUnselected'] = $this->emptyValueWhenUnselected;

        return $data;
    }
}
