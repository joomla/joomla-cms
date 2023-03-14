<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\HTML\HTMLHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Field class for the Joomla Platform.
 *
 * @since  1.7.0
 */
class TimezoneField extends GroupedlistField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $type = 'Timezone';

    /**
     * The list of available timezone groups to use.
     *
     * @var    array
     * @since  1.7.0
     */
    protected static $zones = ['Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific'];

    /**
     * The keyField of timezone field.
     *
     * @var    integer
     * @since  3.2
     */
    protected $keyField;

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
        if ($name === 'keyField') {
            return $this->keyField;
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
            case 'keyField':
                $this->keyField = (string) $value;
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
        $return = parent::setup($element, $value, $group);

        if ($return) {
            $this->keyField = (string) $this->element['key_field'];
        }

        return $return;
    }

    /**
     * Method to get the time zone field option groups.
     *
     * @return  array  The field option objects as a nested array in groups.
     *
     * @since   1.7.0
     */
    protected function getGroups()
    {
        $groups = [];

        // Get the list of time zones from the server.
        $zones = \DateTimeZone::listIdentifiers();

        // Build the group lists.
        foreach ($zones as $zone) {
            // Time zones not in a group we will ignore.
            if (strpos($zone, '/') === false) {
                continue;
            }

            // Get the group/locale from the timezone.
            list ($group, $locale) = explode('/', $zone, 2);

            // Only use known groups.
            if (\in_array($group, self::$zones)) {
                // Initialize the group if necessary.
                if (!isset($groups[$group])) {
                    $groups[$group] = [];
                }

                // Only add options where a locale exists.
                if (!empty($locale)) {
                    $groups[$group][$zone] = HTMLHelper::_('select.option', $zone, str_replace('_', ' ', $locale), 'value', 'text', false);
                }
            }
        }

        // Sort the group lists.
        ksort($groups);

        foreach ($groups as &$location) {
            sort($location);
        }

        // Merge any additional groups in the XML definition.
        $groups = array_merge(parent::getGroups(), $groups);

        return $groups;
    }
}
