<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Form Field class for the Joomla Platform.
 *
 * @package     Joomla.Libraries
 * @subpackage  Form
 * @since       3.2
 */
class JFormFieldOrdering extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since   3.2
	 */
	protected $type = 'Ordering';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string	The field input markup.
	 *
	 * @since   3.2
	 */
	protected function getInput()
	{
		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		$itemId = (int) $this->getItemId();

		$query = $this->getQuery();

		// Create a read-only list (no name) with a hidden input to store the value.
		if ((string) $this->element['readonly'] == 'true')
		{
			$html[] = JHtml::_('list.ordering', '', $query, trim($attr), $this->value, $itemId ? 0 : 1);
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . $this->value . '"/>';
		}
		else
		{
			// Create a regular list.
			$html[] = JHtml::_('list.ordering', $this->name, $query, trim($attr), $this->value, $itemId ? 0 : 1);
		}

		return implode($html);
	}

	/**
	 * Builds the query for the ordering list.
	 *
	 * @return  JDatabaseQuery  The query for the ordering form field
	 */
	protected function getQuery()
	{
		$categoryId	= (int) $this->form->getValue('catid');
		$table = (string) $this->element['table'];

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select(array($db->quoteName('ordering', 'value'), $db->quoteName('name', 'text')))
			->from($db->quoteName($table))
			->where($db->quoteName('catid') . ' = ' . (int) $categoryId)
			->order('ordering');

		return $query;
	}

	/**
	 * Retrieves the current Item's Id.
	 *
	 * @return  integer  The current item ID
	 */
	protected function getItemId()
	{
		return (int) $this->form->getValue('id');
	}
}
