<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\Language\Text;

/**
 * Form Field to load a list of predefined values
 *
 * @since  3.2
 */
abstract class PredefinedlistField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  3.2
     */
    protected $type = 'Predefinedlist';

    /**
     * Cached array of the category items.
     *
     * @var    array
     * @since  3.2
     */
    protected static $options = array();

    /**
     * Available predefined options
     *
     * @var  array
     * @since  3.2
     */
    protected $predefinedOptions = array();

    /**
     * Translate options labels ?
     *
     * @var  boolean
     * @since  3.2
     */
    protected $translate = true;

    /**
     * Allows to use only specific values of the predefined list
     *
     * @var  array
     * @since  4.0.0
     */
    protected $optionsFilter = [];

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
     * @see     \Joomla\CMS\Form\FormField::setup()
     * @since   4.0.0
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $return = parent::setup($element, $value, $group);

        if ($return) {
            // Note: $this->element['optionsFilter'] is not cast to string here to allow empty string value.
            $this->optionsFilter = $this->element['optionsFilter'] ? explode(',', (string) $this->element['optionsFilter']) : [];
        }

        return $return;
    }

    /**
     * Method to get the options to populate list
     *
     * @return  array  The field option objects.
     *
     * @since   3.2
     */
    protected function getOptions()
    {
        // Hash for caching
        $hash = md5($this->element);
        $type = strtolower($this->type);

        if (!isset(static::$options[$type][$hash]) && !empty($this->predefinedOptions)) {
            static::$options[$type][$hash] = parent::getOptions();

            $options = array();

            foreach ($this->predefinedOptions as $value => $text) {
                $val = (string) $value;

                if (empty($this->optionsFilter) || in_array($val, $this->optionsFilter, true)) {
                    $text = $this->translate ? Text::_($text) : $text;

                    $options[] = (object) array(
                        'value' => $value,
                        'text'  => $text,
                    );
                }
            }

            static::$options[$type][$hash] = array_merge(static::$options[$type][$hash], $options);
        }

        return static::$options[$type][$hash];
    }
}
