<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

/**
 * DateTime field
 *
 * @since   __DEPLOY_VERSION__
 */
class DatetimeField extends FormField
{
    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected $type = 'DateTime';

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected $layout = 'joomla.form.field.datetime';

    /**
     * Min value Y-m-dTH:i
     *
     * @var string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected $dateMin = '';

    /**
     * Max value Y-m-dTH:i
     *
     * @var string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected $dateMax = '';

    /**
     * The filter.
     *
     * @var string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected $filter = '';

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

        foreach (['min', 'max', 'filter'] as $attr) {
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

            case 'filter':
                return $this->filter;

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
                    $this->value = $value->format('Y-m-d H:i:s');
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

            case 'filter':
                $this->filter = (string) $value;
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

        $data['min']    = $this->dateMin;
        $data['max']    = $this->dateMax;
        $data['filter'] = $this->filter;

        return $data;
    }

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   __DEPLOY_VERSION__
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
                    $data['value'] = $value->format('Y-m-d\TH:i:s', true, false);
                    break;

                case 'USER_UTC':
                    // Convert a date to UTC based on the user timezone.
                    $value->setTimezone(new \DateTimeZone($app->getIdentity()->getParam('timezone', $app->get('offset'))));

                    // Transform the date string.
                    $data['value'] = $value->format('Y-m-d\TH:i:s', true, false);
                    break;

                default:
                    $data['value'] = $value->format('Y-m-d\TH:i:s', false, false);
            }
        }

        return $this->getRenderer($this->layout)->render($data);
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
