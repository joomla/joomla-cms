<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 *
 * Provides a popup date picker linked to a button.
 * Optionally may be extended to include the time picker.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldCalpicker extends JFormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  11.1
     */
    protected $type = 'Calpicker';

    /**
     * The allowable maxlength of calendar field.
     *
     * @var    integer
     * @since  3.2
     */
    protected $maxlength;

    /**
     * The format of date and time.
     *
     * @var    integer
     * @since  3.2
     */
    protected $format;

    /**
     * The filter.
     *
     * @var    integer
     * @since  3.2
     */
    protected $filter;


    /**
     * Method to get certain otherwise inaccessible properties from the form field object.
     *
     * @param   string  $name  The property name for which to the the value.
     *
     * @return  mixed  The property value or null.
     *
     * @since   3.2
     */
    public function __get($name)
    {
        switch ($name)
        {
            case 'maxlength':
            case 'format':
            case 'filter':
                return $this->$name;
        }

        return parent::__get($name);
    }

    /**
     * Method to set certain otherwise inaccessible properties of the form field object.
     *
     * @param   string  $name   The property name for which to the the value.
     * @param   mixed   $value  The value of the property.
     *
     * @return  void
     *
     * @since   3.2
     */
    public function __set($name, $value)
    {
        switch ($name)
        {
            case 'maxlength':
                $value = (int) $value;

            case 'format':
            case 'filter':
                $this->$name = (string) $value;
                break;

            default:
                parent::__set($name, $value);
        }
    }

    /**
     * Method to attach a JForm object to the field.
     *
     * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
     * @param   mixed             $value    The form field value to validate.
     * @param   string            $group    The field name group control value. This acts as as an array container for the field.
     *                                      For example if the field has name="foo" and the group value is set to "bar" then the
     *                                      full field name would end up being "bar[foo]".
     *
     * @return  boolean  True on success.
     *
     * @see     JFormField::setup()
     * @since   3.2
     */
    public function setup(SimpleXMLElement $element, $value, $group = null)
    {
        $return = parent::setup($element, $value, $group);

        if ($return)
        {
            $this->maxlength = (int) $this->element['maxlength'] ? (int) $this->element['maxlength'] : 45;
            $this->format    = (string) $this->element['format'] ? (string) $this->element['format'] : '%Y-%m-%d';
            $this->filter    = (string) $this->element['filter'] ? (string) $this->element['filter'] : 'USER_UTC';
        }

        return $return;
    }

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   11.1
     */
    protected function getInput()
    {
        // Translate placeholder text
        $hint = $this->translateHint ? JText::_($this->hint) : $this->hint;

        // Initialize some field attributes.
        $format = $this->format;

        // Handle the special case for "now".
        if (strtoupper($this->value) == 'NOW')
        {
            $this->value = strftime($format);
        }

        // Get some system objects.
        $config = JFactory::getConfig();
        $user = JFactory::getUser();

        // If a known filter is given use it.
        switch (strtoupper($this->filter))
        {
            case 'SERVER_UTC':
                // Convert a date to UTC based on the server timezone.
                if ((int) $this->value)
                {
                    // Get a date object based on the correct timezone.
                    $date = JFactory::getDate($this->value, 'UTC');
                    $date->setTimezone(new DateTimeZone($config->get('offset')));

                    // Transform the date string.
                    $this->value = $date->format('Y-m-d H:i:s', true, false);
                }

                break;

            case 'USER_UTC':
                // Convert a date to UTC based on the user timezone.
                if ((int) $this->value)
                {
                    // Get a date object based on the correct timezone.
                    $date = JFactory::getDate($this->value, 'UTC');

                    $date->setTimezone(new DateTimeZone($user->getParam('timezone', $config->get('offset'))));

                    // Transform the date string.
                    $this->value = $date->format('Y-m-d H:i:s', true, false);
                }

                break;
        }

        // Including fallback code for HTML5 non supported browsers.
        JHtml::_('jquery.framework');
        JHtml::_('script', 'system/html5fallback.js', false, true);

        // Inject the scripts into the document
        JHtml::_('script','calendars/jquery.calendars.js', true, true);
        JHtml::_('script','calendars/jquery.calendars.plus.js', true, true);
        JHtml::_('script','calendars/jquery.plugin.js', true, true);
        JHtml::_('script','calendars/jquery.calendars.picker.js', true, true);
        JHtml::_('stylesheet','calendars/jquery.calendars.picker.css',null, true);
        JHtml::_('stylesheet','calendars/redmond.calendars.picker.css',null, true);
        JHtml::_('stylesheet','calendars/joomla-css-fixes.css',null, true);

        // Setup the calendar
        $doc = JFactory::getDocument();
        $doc->addScriptDeclaration('jQuery(document).ready(function(){
                jQuery("#' . $this->id . '").calendarsPicker({
                            dateFormat:"' . $this->format . '",
                            showTrigger: "<button type=\"button\" class=\"btn trigger\"><i class=\"icon-calendar\"></i></button>"
                            }
                        );
        });');

        // Including fallback code for HTML5 non supported browsers.
        JHtml::_('jquery.framework');
        JHtml::_('script', 'system/html5fallback.js', false, true);



        $html = '<div class="input-append"><input type="text" name="' . $this->name . '" id="' . $this->id . '"' . ' value="'
            . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" class="input-medium"/>
            </div>';

        return $html;
    }
}
