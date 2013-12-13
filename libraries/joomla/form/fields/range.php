<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('number');

/**
 * Form Field class for the Joomla Platform.
 * Provides a horizontal scroll bar to specify a value in a range.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @link        http://www.w3.org/TR/html-markup/input.text.html#input.text
 * @since       3.2
 */
class JFormFieldRange extends JFormFieldNumber
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $type = 'Range';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.2
	 */
	protected function getInput()
	{
		// Initialize field attributes.
		$value = (float) $this->value;

		$displayData = array(
			'autofocus' => (boolean) $this->autofocus,
			'class' => (string) $this->class,
			'disabled' => (boolean) $this->disabled,
			'field' => $this,
			'options' => $this->getOptions(),
			'readonly' => (boolean) $this->readonly,
			'required' => (boolean) $this->required,
			'max' => $this->max,
			'min' => $this->min,
			'step' => $this->step,
			'onchange' => $this->onchange,
			'value' => empty($value) ? $this->min : $value,
			);

		return JLayoutHelper::render('joomla.fields.range', $displayData);
	}
}
