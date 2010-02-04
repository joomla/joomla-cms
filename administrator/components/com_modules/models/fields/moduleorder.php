<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Administrator
 * @subpackage	Modules
 * @since		1.6
 */
class JFormFieldModuleOrder extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'ModuleOrder';

	/**
	 * Method to get the field input.
	 *
	 * @return	string		The field input.
	 */
	protected function _getInput()
	{
		$html		= '';
		$attribs	= ($v = $this->_element->attributes('size')) ? ' size="'.$v.'"' : '';
		$attribs	.= ($v = $this->_element->attributes('class')) ? ' class="'.$v.'"' : 'class="inputbox"';
		$attribs	.= $this->_element->attributes('readonly') == 'true' ? ' readonly="readonly"' : '';

		$html .= '<script language="javascript" type="text/javascript">';
		$html .= '<!-- ';

		$ordering = $this->_form->getValue('ordering');
		$position = $this->_form->getValue('position');
		$clientId = $this->_form->getValue('client_id');

		$html	.= "\nvar originalOrder = '".$ordering."'";
		$html	.= "\nvar originalPos = '".$position."'";
		$html	.= "\nvar orders = new Array();";

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
			$title = JText::sprintf('Modules_Option_Order_Position', $ord, addslashes($orders[$i]->title));

			$html .= "\norders[".$i."] =  new Array('".$orders[$i]->position."','".$ord."','".$title."')";
		}

		$html .= "\n".'writeDynaList(\'name="'.$this->inputName.'" id="'.$this->inputId.'"'.$attribs.'\', orders, originalPos, originalPos, originalOrder);';
		$html .= ' //-->';
		$html .= '</script>';

		return $html;
	}
}
