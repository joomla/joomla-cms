<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.form.formfield');

/**
 * Color Form Field class for the Joomla Framework.
 * This implementation is designed to be compatible with HTML5's <input type="color">
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.3
 * @link		http://www.w3.org/TR/html-markup/input.color.html
 */
class JFormFieldColor extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.3
	 */
	protected $type = 'Color';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 * @since   11.3
	 */
	protected function getInput()
	{
		// Initialize some field attributes.
		$size		= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		$classes	= (string) $this->element['class'];
		$disabled	= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';

		if (!$disabled) {
			JHtml::_('behavior.colorpicker');
			$classes .= ' input-colorpicker';
		}
		
		if (empty($this->value)) {
			// A color field can't be empty, we default to black. This is the same as the HTML5 spec.
			$this->value = '#000000';
		}
		
		// Initialize JavaScript field attributes.
		$onchange	= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';

		$class		= $classes ? ' class="'.trim($classes).'"' : '';
		
		return '<input type="text" name="'.$this->name.'" id="'.$this->id.'"' .
				' value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'"' .
				$class.$size.$disabled.$onchange.'/>';
	}
}
