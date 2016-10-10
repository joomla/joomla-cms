<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 * Provides a one line text box with up-down handles to set a number in the field.
 *
 * @link   http://www.w3.org/TR/html-markup/input.text.html#input.text
 * @since  3.2
 */
class JFormFieldNumber extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $type = 'Number';

	/**
	 * The allowable maximum value of the field.
	 *
	 * @var    float
	 * @since  3.2
	 */
	protected $max = null;

	/**
	 * The allowable minimum value of the field.
	 *
	 * @var    float
	 * @since  3.2
	 */
	protected $min = null;

	/**
	 * The step by which value of the field increased or decreased.
	 *
	 * @var    float
	 * @since  3.2
	 */
	protected $step = 0;

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
			case 'max':
			case 'min':
			case 'step':
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
			case 'step':
			case 'min':
			case 'max':
				$this->$name = (float) $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
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
			// It is better not to force any default limits if none is specified
			$this->max  = isset($this->element['max']) ? (float) $this->element['max'] : null;
			$this->min  = isset($this->element['min']) ? (float) $this->element['min'] : null;
			$this->step = isset($this->element['step']) ? (float) $this->element['step'] : 1;
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
		// Translate placeholder text
		$hint = $this->translateHint ? JText::_($this->hint) : $this->hint;

		// Initialize some field attributes.
		$size     = !empty($this->size) ? ' size="' . $this->size . '"' : '';

		// Must use isset instead of !empty for max/min because "zero" boundaries are always acceptable
		$max      = isset($this->max) ? ' max="' . $this->max . '"' : '';
		$min      = isset($this->min) ? ' min="' . $this->min . '"' : '';

		$step     = !empty($this->step) ? ' step="' . $this->step . '"' : '';
		$class    = !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$readonly = $this->readonly ? ' readonly' : '';
		$disabled = $this->disabled ? ' disabled' : '';
		$required = $this->required ? ' required aria-required="true"' : '';
		$hint     = strlen($hint) ? ' placeholder="' . $hint . '"' : '';

		$autocomplete = !$this->autocomplete ? ' autocomplete="off"' : ' autocomplete="' . $this->autocomplete . '"';
		$autocomplete = $autocomplete == ' autocomplete="on"' ? '' : $autocomplete;

		$autofocus = $this->autofocus ? ' autofocus' : '';

		if (is_numeric($this->value))
		{
			$value = (float) $this->value;
		}
		else
		{
			$value = "";
			$value = (isset($this->min)) ? $this->min : $value;
		}

		// Initialize JavaScript field attributes.
		$onchange = !empty($this->onchange) ? ' onchange="' . $this->onchange . '"' : '';

		// Including fallback code for HTML5 non supported browsers.
		JHtml::_('jquery.framework');
		JHtml::_('script', 'system/html5fallback.js', false, true);

		return '<input type="number" name="' . $this->name . '" id="' . $this->id . '"' . ' value="'
			. htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"' . $class . $size . $disabled . $readonly
			. $hint . $onchange . $max . $step . $min . $required . $autocomplete . $autofocus . ' />';
	}
}
