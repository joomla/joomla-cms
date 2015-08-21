<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla! CMS.
 *
 * @since  1.6
 */
class JFormFieldModuleOrder extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since   1.6
	 */
	protected $type = 'ModuleOrder';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string	The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$attr .= $this->disabled ? ' disabled' : '';
		$attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';

		// Initialize JavaScript field attributes.
		$attr .= !empty($this->onchange) ? ' onchange="' . $this->onchange . '"' : '';

		$html[] = '<script type="text/javascript">';

		$ordering = $this->form->getValue('ordering');
		$position = $this->form->getValue('position');
		$clientId = $this->form->getValue('client_id');

		$html[] = 'var originalOrder = "' . $ordering . '";';
		$html[] = 'var originalPos = "' . $position . '";';
		$html[] = 'var orders = new Array();';

		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true)
			->select('position, ordering, title')
			->from('#__modules')
			->where('client_id = ' . (int) $clientId)
			->order('ordering');

		$db->setQuery($query);

		try
		{
			$orders = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());

			return false;
		}

		$orders2 = array();

		for ($i = 0, $n = count($orders); $i < $n; $i++)
		{
			if (!isset($orders2[$orders[$i]->position]))
			{
				$orders2[$orders[$i]->position] = 0;
			}

			$orders2[$orders[$i]->position]++;
			$ord = $orders2[$orders[$i]->position];
			$title = JText::sprintf('COM_MODULES_OPTION_ORDER_POSITION', $ord, addslashes($orders[$i]->title));

			$html[] = 'orders[' . $i . '] =  new Array("' . $orders[$i]->position . '","' . $ord . '","' . $title . '");';
		}

		$html[] = 'writeDynaList(\'name="' . $this->name . '" id="' . $this->id . '"' . $attr . '\', orders, originalPos, originalPos, originalOrder);';
		$html[] = '</script>';

		return implode("\n", $html);
	}
}
