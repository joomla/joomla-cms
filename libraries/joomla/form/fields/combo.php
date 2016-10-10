<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Platform.
 * Implements a combo box field.
 *
 * @since  11.1
 */
class JFormFieldCombo extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Combo';

	/**
	 * Method to get the field input markup for a combo box field.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= !empty($this->class) ? ' class="combobox form-control ' . $this->class . '"' : ' class="combobox form-control"';
		$attr .= $this->readonly ? ' readonly' : '';
		$attr .= $this->disabled ? ' disabled' : '';
		$attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$attr .= $this->required ? ' required aria-required="true"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->onchange ? ' onchange="' . $this->onchange . '"' : '';

		// Get the field options.
		$options = $this->getOptions();

		// Load the combobox behavior.
		JHtml::_('behavior.combobox');

		$html[] = '<div class="combobox input-group">';

		// Build the input for the combo box.
		$html[] = '<input type="text" name="' . $this->name . '" id="' . $this->id . '" value="'
			. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $attr . ' autocomplete="off" />';

		$html[] = '<div class="input-group-btn">';
		$html[] = '<button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown">';
		$html[] = '		<span class="caret"></span>';
		$html[] = '</button>';

		// Build the list for the combo box.
		$html[] = '<div class="dropdown-menu dropdown-menu-right">';

		foreach ($options as $option)
		{
			$html[] = '<a href="#" class="dropdown-item">' . $option->text . '</a>';
		}

		$html[] = '</div></div></div>';

		return implode($html);
	}
}
