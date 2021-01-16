<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 * Display a JSON loaded window with a repeatable set of sub fields
 *
 * @since       3.2
 *
 * @deprecated  4.0  Use JFormFieldSubform
 */
class JFormFieldRepeatable extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $type = 'Repeatable';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.2
	 */
	protected function getInput()
	{
		JLog::add('JFormFieldRepeatable is deprecated. Use JFormFieldSubform instead.', JLog::WARNING, 'deprecated');

		// Initialize variables.
		$subForm = new JForm($this->name, array('control' => 'jform'));
		$xml = $this->element->children()->asXml();
		$subForm->load($xml);

		// Needed for repeating modals in gmaps
		// @TODO: what and where???
		$subForm->repeatCounter = (int) @$this->form->repeatCounter;

		$children = $this->element->children();
		$subForm->setFields($children);

		// If a maximum value isn't set then we'll make the maximum amount of cells a large number
		$maximum = $this->element['maximum'] ? (int) $this->element['maximum'] : '999';

		// Build a Table
		$head_row_str = array();
		$body_row_str = array();
		$head_row_str[] = '<th></th>';
		$body_row_str[] = '<td><span class="sortable-handler " style="cursor: move;"><span class="icon-menu" aria-hidden="true"></span></span></td>';
		foreach ($subForm->getFieldset() as $field)
		{
			// Reset name to simple
			$field->name = (string) $field->element['name'];

			// Build heading
			$head_row_str[] = '<th>' . strip_tags($field->getLabel($field->name));
			$head_row_str[] = '<br /><small style="font-weight:normal">' . JText::_($field->description) . '</small>';
			$head_row_str[] = '</th>';

			// Build body
			$body_row_str[] = '<td>' . $field->getInput() . '</td>';
		}

		// Append buttons
		$head_row_str[] = '<th><div class="btn-group"><a href="#" class="add btn button btn-success" aria-label="' . JText::_('JGLOBAL_FIELD_ADD') . '">';
		$head_row_str[] = '<span class="icon-plus" aria-hidden="true"></span> </a></div></th>';
		$body_row_str[] = '<td><div class="btn-group">';
		$body_row_str[] = '<a class="add btn button btn-success" aria-label="' . JText::_('JGLOBAL_FIELD_ADD') . '">';
		$body_row_str[] = '<span class="icon-plus" aria-hidden="true"></span> </a>';
		$body_row_str[] = '<a class="remove btn button btn-danger" aria-label="' . JText::_('JGLOBAL_FIELD_REMOVE') . '">';
		$body_row_str[] = '<span class="icon-minus" aria-hidden="true"></span> </a>';
		$body_row_str[] = '</div></td>';

		// Put all table parts together
		$table = '<table id="' . $this->id . '_table" class="adminlist ' . $this->element['class'] . ' table table-striped">'
					. '<thead><tr>' . implode("\n", $head_row_str) . '</tr></thead>'
					. '<tbody><tr>' . implode("\n", $body_row_str) . '</tr></tbody>'
				. '</table>';

		// And finally build a main container
		$str = array();
		$str[] = '<div id="' . $this->id . '_container">';

		// Add the table to modal
		$str[] = '<div id="' . $this->id . '_modal" class="modal hide">';
		$str[] = $table;
		$str[] = '<div class="modal-footer">';
		$str[] = '<button class="close-modal btn button btn-link">' . JText::_('JCANCEL') . '</button>';
		$str[] = '<button class="save-modal-data btn button btn-primary">' . JText::_('JAPPLY') . '</button>';
		$str[] = '</div>';

		// Close modal container
		$str[] = '</div>';

		// Close main container
		$str[] = '</div>';

		// Button for display the modal window
		$select = (string) $this->element['select'] ? JText::_((string) $this->element['select']) : JText::_('JLIB_FORM_BUTTON_SELECT');
		$icon = $this->element['icon'] ? '<span class="icon-' . $this->element['icon'] . '"></span> ' : '';
		$str[] = '<button class="open-modal btn" id="' . $this->id . '_button" >' . $icon . $select . '</button>';

		if (is_array($this->value))
		{
			$this->value = array_shift($this->value);
		}

		// Script params
		$data = array();
		$data[] = 'data-container="#' . $this->id . '_container"';
		$data[] = 'data-modal-element="#' . $this->id . '_modal"';
		$data[] = 'data-repeatable-element="table tbody tr"';
		$data[] = 'data-bt-add="a.add"';
		$data[] = 'data-bt-remove="a.remove"';
		$data[] = 'data-bt-modal-open="#' . $this->id . '_button"';
		$data[] = 'data-bt-modal-close="button.close-modal"';
		$data[] = 'data-bt-modal-save-data="button.save-modal-data"';
		$data[] = 'data-maximum="' . $maximum . '"';
		$data[] = 'data-input="#' . $this->id . '"';

		// Hidden input, where the main value is
		$value = htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8');
		$str[] = '<input type="hidden" name="' . $this->name . '" id="' . $this->id . '" value="' . $value
				. '"  class="form-field-repeatable" ' . implode(' ', $data) . ' />';

		// Add scripts
		JHtml::_('bootstrap.framework');

		// Depends on jQuery UI
		JHtml::_('jquery.ui', array('core', 'sortable'));

		JHtml::_('script', 'jui/sortablelist.js', array('version' => 'auto', 'relative' => true));
		JHtml::_('stylesheet', 'jui/sortablelist.css', array('version' => 'auto', 'relative' => true));
		JHtml::_('script', 'system/repeatable.js', array('framework' => true, 'version' => 'auto', 'relative' => true));

		$javascript = 'jQuery(document).ready(function($) { $("#' . $this->id . '_table tbody").sortable(); });';

		JFactory::getDocument()->addScriptDeclaration($javascript);

		return implode("\n", $str);
	}
}
