<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\Form\FormField;

/**
 * Date field
 *
 * @since   __DEPLOY_VERSION__
 */
class DateField extends FormField
{
    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected $type = 'Date';

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected $layout = 'joomla.form.field.date';

    /**
     * Min value Y-m-d
     *
     * @var string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected $dateMin = '';

    /**
     * Max value Y-m-d
     *
     * @var string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected $dateMax = '';

    /**
     * Method to attach a Form object to the field.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as as an array container for the field.
     *                                       For example if the field has name="foo" and the group value is set to "bar" then the
     *                                       full field name would end up being "bar[foo]".
     *
     * @return  boolean  True on success.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        if (!parent::setup($element, $value, $group)) {
            return false;
        }

        foreach (['min', 'max'] as $attr) {
            $this->__set($attr, (string) $element[$attr]);
        }

        return true;
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
            case 'min':
                return $this->dateMin;
            case 'max':
                return $this->dateMax;
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
            case 'value':
                if ($value instanceof \DateTimeInterface) {
                    $this->value = $value->format('Y-m-d');
                } else {
                    $this->value = (string) $value;
                }
                break;
            case 'min':
                $this->dateMin = (string) $value;
                break;
            case 'max':
                $this->dateMax = (string) $value;
                break;
            default:
                parent::__set($name, $value);
        }
    }

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        $data['min'] = $this->dateMin;
        $data['max'] = $this->dateMax;

        return $data;
    }
}
