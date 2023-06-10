<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Field class for the Joomla Platform.
 * Provides a Time input.
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
     * The filter.
     *
     * @var string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected $filter = '';

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
            case 'value':
                if ($value instanceof \DateTimeInterface) {
                    $this->value = $value->format('H:i:s');
                } else {
                    $this->value = (string) $value;
                }
                break;

            case 'max':
            case 'min':
            case 'step':
                $this->$name = (int) $value;
                break;

            case 'filter':
                $this->filter = (string) $value;
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

            if ($this->element['filter']) {
                $this->__set('filter', $element['filter']);
            }
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
            case 'filter':
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
        $data = $this->getLayoutData();

        if ($this->value) {
            $app   = Factory::getApplication();
            $value = Factory::getDate($this->value, 'UTC');

            switch (strtoupper($this->filter)) {
                case 'SERVER_UTC':
                    // Convert a date to UTC based on the server timezone.
                    $value->setTimezone(new \DateTimeZone($app->get('offset')));

                    // Transform the date string.
                    $data['value'] = $value->format('H:i:s', true, false);
                    break;

                case 'USER_UTC':
                    // Convert a date to UTC based on the user timezone.
                    $value->setTimezone(new \DateTimeZone($app->getIdentity()->getParam('timezone', $app->get('offset'))));

                    // Transform the date string.
                    $data['value'] = $value->format('H:i:s', true, false);
                    break;

                default:
                    $data['value'] = $value->format('H:i:s', false, false);
            }
        }

        return $this->getRenderer($this->layout)->render($data);
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
            'min'    => $this->min,
            'max'    => $this->max,
            'step'   => $this->step,
            'filter' => $this->filter,
        ];

        return array_merge($data, $extraData);
    }

    /**
     * Method to filter a field value.
     *
     * @param   mixed     $value  The optional value to use as the default for the field.
     * @param   string    $group  The optional dot-separated form group path on which to find the field.
     * @param   Registry  $input  An optional Registry object with the entire data set to filter
     *                            against the entire form.
     *
     * @return  mixed   The filtered value.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function filter($value, $group = null, Registry $input = null)
    {
        // Make sure there is a valid SimpleXMLElement.
        if (!($this->element instanceof \SimpleXMLElement)) {
            throw new \UnexpectedValueException(sprintf('%s `element` is not an instance of SimpleXMLElement', __METHOD__));
        }

        if (!$value) {
            return '';
        }

        $app = Factory::getApplication();

        switch (strtoupper($this->filter)) {
            // Convert a date to UTC based on the server timezone offset.
            case 'SERVER_UTC':
                // Return an SQL formatted datetime string in UTC.
                $return = Factory::getDate($value, $app->get('offset'))->toSql();
                break;

            // Convert a date to UTC based on the user timezone offset.
            case 'USER_UTC':
                // Get the user timezone setting defaulting to the server timezone setting.
                $offset = $app->getIdentity()->getParam('timezone', $app->get('offset'));

                // Return an SQL formatted datetime string in UTC.
                $return = Factory::getDate($value, $offset)->toSql();
                break;

            default:
                $return = parent::filter($value, $group, $input);
        }

        return $return;
    }
}
