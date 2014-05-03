<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_plugins
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Supports an HTML select list of plugins
 *
 * @package		Joomla.Administrator
 * @subpackage	com_plugins
 * @since		1.6
 */
class JFormFieldOrdering extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Ordering';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';

		// Get some field values from the form.
		$pluginId	= (int) $this->form->getValue('extension_id');
		$folder	=  $this->form->getValue('folder');
		$db = JFactory::getDbo();

		// Build the query for the ordering list.
		$query = 'SELECT ordering AS value, name AS text, type AS type, folder AS folder, extension_id AS extension_id' .
				' FROM #__extensions' .
				' WHERE (type =' .$db->Quote('plugin'). 'AND folder='. $db->Quote($folder) . ')'.
				' ORDER BY ordering';

		// Create a read-only list (no name) with a hidden input to store the value.
		if ((string) $this->element['readonly'] == 'true') {
			$html[] = JHtml::_('list.ordering', '', $query, trim($attr), $this->value, $pluginId ? 0 : 1);
			$html[] = '<input type="hidden" name="'.$this->name.'" value="'.$this->value.'"/>';
		}
		// Create a regular list.
		else {
			$html[] = JHtml::_('list.ordering', $this->name, $query, trim($attr), $this->value, $pluginId ? 0 : 1);
		}

		return implode($html);
	}
}
