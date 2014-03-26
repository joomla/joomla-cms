<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Platform.
 * Implements a combo box field.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
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
		$attr .= !empty($this->class) ? ' class=combobox"' . $this->class . '"' : ' class="combobox"';
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

		$html[] = '<div class="combobox input-append">';

		// Build the input for the combo box.
		$html[] = '<input type="text" name="' . $this->name . '" id="' . $this->id . '" value="'
			. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $attr . ' autocomplete="off" />';

		$html[] = '<div class="btn-group">';
		$html[] = '<button type="button" class="btn dropdown-toggle">';
		$html[] = '		<span class="caret"></span>';
		$html[] = '</button>';

		// Build the list for the combo box.
		$html[] = '<ul class="dropdown-menu">';

		foreach ($options as $option)
		{
			$html[] = '<li><a href="#">' . $option->text . '</a></li>';
		}

		$html[] = '</ul>';

		$html[] = '</div></div>';

		return implode($html);
	}
}
