<?php

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('plugins');

class JFormFieldSearchPlugins extends JFormFieldPlugins
{
    /**
     * The form field type.
     *
     * @var string
     *
     * @since  11.1
     */
    protected $type = 'SearchPlugins';

    /**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as an array container for the field.
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
		if (is_string($value) && strpos($value, ',') !== false)
		{
			$value = explode(',', $value);
		}
		
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
            $this->folder = 'search';
            $this->useaccess = true;
		}

		return $return;
	}
}
