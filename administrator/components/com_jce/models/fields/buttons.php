<?php

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('checkboxes');

class JFormFieldButtons extends JFormFieldCheckboxes
{
    /**
     * The form field type.
     *
     * @var string
     *
     * @since  11.1
     */
    protected $type = 'Buttons';

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     * @since  3.5
     */
    protected $layout = 'form.field.buttons';

    /**
     * Method to attach a JForm object to the field.
     *
     * @param SimpleXMLElement $element The SimpleXMLElement object representing the <field /> tag for the form field object
     * @param mixed            $value   The form field value to validate
     * @param string           $group   The field name group control value. This acts as as an array container for the field.
     *                                  For example if the field has name="foo" and the group value is set to "bar" then the
     *                                  full field name would end up being "bar[foo]"
     *
     * @return bool True on success
     *
     * @since   11.1
     */
    public function setup(SimpleXMLElement $element, $value, $group = null)
    {
        $return = parent::setup($element, $value, $group);

        $this->class = trim($this->class . ' defaultSkin');

        return $return;
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
        return array(JPATH_ADMINISTRATOR . '/components/com_jce/layouts', JPATH_SITE . '/layouts');
    }
}
