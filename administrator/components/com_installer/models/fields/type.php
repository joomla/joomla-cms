<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Form Field Place class.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 * @since       1.6
 */
class JFormFieldType extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'Type';

	/**
	 * Method to get the field input.
	 *
	 * @return	string		The field input.
	 * @since	1.6
	 */
	protected function getInput()
	{
		// Initialise field variables
		$attr = '';
		$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';
		$options = array();
		foreach ($this->element->children() as $option) {
			$options[] = JHtml::_('select.option', $option->attributes('value'), JText::_(trim((string) $option)));
		}

		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('type')->from('#__extensions');
		$db->setQuery($query);
		$types = array_unique($db->loadColumn());
		foreach($types as $type)
		{
			$options[] = JHtml::_('select.option', $type, JText::_('COM_INSTALLER_TYPE_'. strtoupper($type)));
		}

		$return = JHtml::_('select.genericlist', $options, $this->name, $attr, 'value', 'text', $this->value, $this->id);

		return $return;
	}
}
