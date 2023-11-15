<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\Form\FormField;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Field class for the Joomla Platform.
 * Provides a select list of integers with specified first, last and step values.
 *
 * @since  4.0.0
 */
class TimeField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $type = 'Time';

    /**
     * The allowable minimal value of the field.
     *
     * @var    integer
     * @since  4.0.0
     */
    protected $min;

    /**
     * The allowable maximal value of the field.
     *
     * @var    integer
     * @since  4.0.0
     */
    protected $max;

    /**
     * Steps between different values
     *
     * @var    integer
     * @since  4.0.0
     */
    protected $step;

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     * @since  4.0.0
     */
    protected $layout = 'joomla.form.field.time';

    /**
     * Method to set certain otherwise inaccessible properties of the form field object.
     *
     * @param   string  $name   The property name for which to set the value.
     * @param   mixed   $value  The value of the property.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'max':
            case 'min':
            case 'step':
                $this->$name = (int) $value;
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
     * @see     FormField::setup()
     * @return  boolean  True on success.
     *
     * @since   4.0.0
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $return = parent::setup($element, $value, $group);

        if ($return) {
            // It is better not to force any default limits if none is specified
            $this->max  = isset($this->element['max']) ? (int) $this->element['max'] : null;
            $this->min  = isset($this->element['min']) ? (int) $this->element['min'] : null;
            $this->step = isset($this->element['step']) ? (int) $this->element['step'] : null;
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
     * @since   4.0.0
     */
    public function __get($name)
    {
        switch ($name) {
            case 'min':
            case 'max':
            case 'step':
                return $this->$name;
        }

        return parent::__get($name);
    }

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   4.0.0
     */
    protected function getInput()
    {
        return $this->getRenderer($this->layout)->render($this->getLayoutData());
    }

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return  array
     *
     * @since 4.0.0
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        $extraData = [
            'min'  => $this->min,
            'max'  => $this->max,
            'step' => $this->step,
        ];

        return array_merge($data, $extraData);
    }
}
