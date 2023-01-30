<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\Form\FormField;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Field class for the Joomla Platform.
 * Provides a meter to show value in a range.
 *
 * @link   https://html.spec.whatwg.org/multipage/input.html#text-(type=text)-state-and-search-state-(type=search)
 * @since  3.2
 */
class MeterField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  3.2
     */
    protected $type = 'Meter';

    /**
     * Whether the field is active or not.
     *
     * @var    boolean
     * @since  3.2
     */
    protected $active = false;

    /**
     * Whether the field is animated or not.
     *
     * @var    boolean
     * @since  3.2
     */
    protected $animated = true;

    /**
     * The max value of the progress bar
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $max = 100;

    /**
     * The striped class for the progress bar
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $striped;

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     * @since  3.7
     */
    protected $layout = 'joomla.form.field.meter';

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
            case 'active':
            case 'width':
            case 'animated':
            case 'color':
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
            case 'width':
            case 'color':
                $this->$name = (string) $value;
                break;

            case 'active':
                $value = (string) $value;
                $this->active = ($value === 'true' || $value === $name || $value === '1');
                break;

            case 'animated':
                $value = (string) $value;
                $this->animated = !($value === 'false' || $value === 'off' || $value === '0');
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
     *                                      For example if the field has name="foo" and the group value is set to "bar" then the
     *                                      full field name would end up being "bar[foo]".
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
            $this->width = isset($this->element['width']) ? (string) $this->element['width'] : '';
            $this->color = isset($this->element['color']) ? (string) $this->element['color'] : '';

            $active       = (string) $this->element['active'];
            $this->active = ($active === 'true' || $active === 'on' || $active === '1');

            $animated       = (string) $this->element['animated'];
            $this->animated = !($animated === 'false' || $animated === 'off' || $animated === '0');
        }

        return $return;
    }

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   3.2
     */
    protected function getInput()
    {
        // Trim the trailing line in the layout file
        return rtrim($this->getRenderer($this->layout)->render($this->getLayoutData()), PHP_EOL);
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
        $data = parent::getLayoutData();

        // Initialize some field attributes.
        $extraData = [
            'width'    => $this->width,
            'color'    => $this->color,
            'animated' => $this->animated,
            'active'   => $this->active,
            'max'      => $this->max,
            'min'      => $this->min,
        ];

        return array_merge($data, $extraData);
    }
}
