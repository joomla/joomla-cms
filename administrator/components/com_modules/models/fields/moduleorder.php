<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_modules
 * @since		1.6
 */
class JFormFieldModuleOrder extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'ModuleOrder';

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

		$html[] = '<script type="text/javascript">';

		$ordering = $this->form->getValue('ordering');
		$position = $this->form->getValue('position');
		$clientId = $this->form->getValue('client_id');

		$html[] = 'var originalOrder = "'.$ordering.'";';
		$html[] = 'var originalPos = "'.$position.'";';
		$html[] = 'var orders = new Array();';

		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$query->select('position, ordering, title');
		$query->from('#__modules');
		$query->where('client_id = '.(int) $clientId);
		$query->order('ordering');

		$db->setQuery($query);
		$orders = $db->loadObjectList();
		if ($error = $db->getErrorMsg()) {
			JError::raiseWarning(500, $error);
			return false;
		}

		$orders2 = array();
		for ($i = 0, $n = count($orders); $i < $n; $i++) {
			if (!isset($orders2[$orders[$i]->position])) {
				$orders2[$orders[$i]->position] = 0;
			}
			$orders2[$orders[$i]->position]++;
			$ord = $orders2[$orders[$i]->position];
			$title = JText::sprintf('COM_MODULES_OPTION_ORDER_POSITION', $ord, addslashes($orders[$i]->title));

			$html[] = 'orders['.$i.'] =  new Array("'.$orders[$i]->position.'","'.$ord.'","'.$title.'");';
		}

		$html[] = 'writeDynaList(\'name="'.$this->name.'" id="'.$this->id.'"'.$attr.'\', orders, originalPos, originalPos, originalOrder);';
		$html[] = '</script>';

		return implode("\n", $html);
	}
}
