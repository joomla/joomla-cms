<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 * Displays options as a list of check boxes.
 * Multiselect may be forced to be true.
 *
 * @see    JFormFieldCheckbox
 * @since  11.1
 */
class JFormFieldCheckboxes extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Checkboxes';

	/**
	 * Flag to tell the field to always be in multiple values mode.
	 *
	 * @var    boolean
	 * @since  11.1
	 */
	protected $forceMultiple = true;

	/**
	 * The comma seprated list of checked checkboxes value.
	 *
	 * @var    mixed
	 * @since  3.2
	 */
	public $checkedOptions;

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
			case 'forceMultiple':
			case 'checkedOptions':
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
			case 'checkedOptions':
				$this->checkedOptions = (string) $value;
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
			$this->checkedOptions = (string) $this->element['checked'];
		}

		return $return;
	}

	/**
	 * Method to get the field input markup for check boxes.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		// Including fallback code for HTML5 non supported browsers.
		JHtml::_('jquery.framework');
		JHtml::_('script', 'system/html5fallback.js', false, true);

		$class = !empty($this->class) ? 'checkboxes '. $this->class : 'checkboxes';

		$fieldset = new JHtmlElement('fieldset', array('class' => $class));

		if($this->required)
		{
			$fieldset->addAttribute('required aria-required', 'true');
		}

		if($this->autofocus)
		{
			$fieldset->addAttribute('autofocus');
		}

		// Build the checkbox field output.
		$ul = $fieldset->addChild('ul');

		// Get the field options.
		$checkedOptions = explode(',', (string) $this->checkedOptions);
		$options = $this->getOptions();


		foreach ($options as $i => $option)
		{
			$inputId = $this->id . $i;
			$cleanValue = htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8');

			$li = $ul->addChild('li');
			$input = $li->addChild('input', array('type' => 'checkbox', 'id' => $inputId, 'name' => $this->name, 'value' => $cleanValue, 'class' => $option->class));
			$li->addChild('label', array('for' =>  $inputId, 'class' => $option->class), JText::_($option->text));

			// Initialize value checks.
			$value = !is_array($this->value) ? explode(',', $this->value) : $this->value;
			$valueEmpty = (empty($value));
			$inClassValue = (in_array((string) $option->value, (array) $value) ? ' checked' : '');
			$inCheckedOptions = (in_array((string) $option->value, (array) $checkedOptions));

			if ($inClassValue || ($valueEmpty && $inCheckedOptions) || $option->checked)
			{
				$input->addAttribute('checked', 'checked');
			}

			if(!empty($option->disable) || $this->disabled)
			{
				$input->addAttribute('disabled', 'disabled');
			}

			if(!empty($option->onclick))
			{
				$input->addAttribute('onclick', $option->onclick);
			}

			if(!empty($option->onchange))
			{
				$input->addAttribute('onchange', $option->onchange);
			}
		}

		return $fieldset->__toString();
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$options = array();

		foreach ($this->element->children() as $option)
		{
			// Only add <option /> elements.
			if ($option->getName() != 'option')
			{
				continue;
			}

			$disabled = (string) $option['disabled'];
			$disabled = ($disabled == 'true' || $disabled == 'disabled' || $disabled == '1');

			$checked = (string) $option['checked'];
			$checked = ($checked == 'true' || $checked == 'checked' || $checked == '1');

			// Create a new option object based on the <option /> element.
			$tmp = JHtml::_('select.option', (string) $option['value'], trim((string) $option), 'value', 'text', $disabled);

			// Set some option attributes.
			$tmp->class = (string) $option['class'];
			$tmp->checked = $checked;

			// Set some JavaScript option attributes.
			$tmp->onclick = (string) $option['onclick'];
			$tmp->onchange = (string) $option['onchange'];

			// Add the option object to the result set.
			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}
}
