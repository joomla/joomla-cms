<?php

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

class JFormFieldCustomList extends JFormFieldList
{
    /**
     * The form field type.
     *
     * @var string
     *
     * @since  11.1
     */
    protected $type = 'CustomList';

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
        
        $this->class = trim($this->class.' com_jce_select_custom');

        return $return;
    }

    protected function getOptions()
    {
        $options = parent::getOptions();

        $this->value = is_array($this->value) ? $this->value : explode(',', $this->value);

        $custom = array();

        foreach ($this->value as $value) {
            $tmp = array(
                'value' => $value,
                'text'  => $value,
                'selected' => true,
            );

            $found = false;

            foreach($options as $option) {
                if ($option->value === $value) {
                    $found = true;
                }
            }

            if (!$found) {
                $custom[] = (object) $tmp;
            }
        }

        // Merge any additional options in the XML definition.
		$options = array_merge($options, $custom);

        return $options;
    }
}
