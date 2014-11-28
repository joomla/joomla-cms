<?php
/**
 * @package    FrameworkOnFramework
 * @subpackage form
 * @copyright  Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

/**
 * Generic field header, with text input (search) filter
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class FOFFormHeaderFieldsearchable extends FOFFormHeaderField
{
	/**
	 * Get the filter field
	 *
	 * @return  string  The HTML
	 */
	protected function getFilter()
	{
		// Initialize some field attributes.
		$size        = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$maxLength   = $this->element['maxlength'] ? ' maxlength="' . (int) $this->element['maxlength'] . '"' : '';
		$filterclass = $this->element['filterclass'] ? ' class="' . (string) $this->element['filterclass'] . '"' : '';
		$placeholder = $this->element['placeholder'] ? $this->element['placeholder'] : $this->getLabel();
		$name        = $this->element['searchfieldname'] ? $this->element['searchfieldname'] : $this->name;
		$placeholder = ' placeholder="' . JText::_($placeholder) . '"';

		if ($this->element['searchfieldname'])
		{
			$model       = $this->form->getModel();
			$searchvalue = $model->getState((string) $this->element['searchfieldname']);
		}
		else
		{
			$searchvalue = $this->value;
		}

		// Initialize JavaScript field attributes.
		if ($this->element['onchange'])
		{
			$onchange = ' onchange="' . (string) $this->element['onchange'] . '"';
		}
		else
		{
			$onchange = ' onchange="document.adminForm.submit();"';
		}

		return '<input type="text" name="' . $name . '" id="' . $this->id . '"' . ' value="'
			. htmlspecialchars($searchvalue, ENT_COMPAT, 'UTF-8') . '"' . $filterclass . $size . $placeholder . $onchange . $maxLength . '/>';
	}

	/**
	 * Get the buttons HTML code
	 *
	 * @return  string  The HTML
	 */
	protected function getButtons()
	{
		$buttonclass = $this->element['buttonclass'] ? (string) $this->element['buttonclass'] : 'btn hasTip hasTooltip';
		$buttonsState = strtolower($this->element['buttons']);
		$show_buttons = !in_array($buttonsState, array('no', 'false', '0'));

		if (!$show_buttons)
		{
			return '';
		}

		$html = '';

		$html .= '<button class="' . $buttonclass . '" onclick="this.form.submit();" title="' . JText::_('JSEARCH_FILTER') . '" >' . "\n";
		$html .= '<i class="icon-search"></i>';
		$html .= '</button>' . "\n";
		$html .= '<button class="' . $buttonclass . '" onclick="document.adminForm.' . $this->id . '.value=\'\';this.form.submit();" title="' . JText::_('JSEARCH_RESET') . '">' . "\n";
		$html .= '<i class="icon-remove"></i>';
		$html .= '</button>' . "\n";

		return $html;
	}
}
