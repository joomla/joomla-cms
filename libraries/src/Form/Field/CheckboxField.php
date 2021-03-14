<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Form\FormField;

/**
 * Form Field class for the Joomla Platform.
 * Single checkbox field.
 * This is a boolean field with null for false and the specified option for true
 *
 * @link   https://html.spec.whatwg.org/multipage/input.html#checkbox-state-(type=checkbox)
 * @see    CheckboxField
 * @since  1.7.0
 */
class CheckboxField extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	protected $type = 'Checkbox';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $layout = 'joomla.form.field.checkbox';

	/**
	 * The checked state of checkbox field.
	 *
	 * @var    boolean
	 * @since  3.2
	 */
	protected $checked = false;

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
		switch ($name)
		{
			case 'checked':
				return $this->checked;
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
		switch ($name)
		{
			case 'checked':
				$value = (string) $value;
				$this->checked = ($value === 'true' || $value == $name || $value === '1');
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
		// Handle the default attribute
		$default = (string) $element['default'];

		if ($default)
		{
			$test = $this->form->getValue((string) $element['name'], $group);

			$value = ($test == $default) ? $default : null;
		}

		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$checked = (string) $this->element['checked'];
			$this->checked = ($checked === 'true' || $checked === 'checked' || $checked === '1');

			empty($this->value) || $this->checked ? null : $this->checked = true;
		}

		return $return;
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	protected function getLayoutData()
	{
		$data            = parent::getLayoutData();
		$data['value']   = $this->default ?: '1';
		$data['checked'] = $this->checked || $this->value;

		return $data;
	}
}
