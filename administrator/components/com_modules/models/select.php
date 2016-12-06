<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Module model.
 *
 * @since  1.6
 */
class ModulesModelSelect extends JModelList
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$clientId = $app->getUserState('com_modules.modules.client_id', 0);
		$this->setState('client_id', (int) $clientId);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_modules');
		$this->setState('params', $params);

		// Manually set limits to get all modules.
		$this->setState('list.limit', 0);
		$this->setState('list.start', 0);
		$this->setState('list.ordering', 'a.name');
		$this->setState('list.direction', 'ASC');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string    A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('client_id');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.extension_id, a.name, a.element AS module'
			)
		);
		$query->from($db->quoteName('#__extensions') . ' AS a');

		// Filter by module
		$query->where('a.type = ' . $db->quote('module'));

		// Filter by client.
		$clientId = $this->getState('client_id');
		$query->where('a.client_id = ' . (int) $clientId);

		// Filter by enabled
		$query->where('a.enabled = 1');

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'a.ordering')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

		return $query;
	}

	/**
	 * Method to get a list of items.
	 *
	 * @return  mixed  An array of objects on success, false on failure.
	 */
	public function getItems()
	{
		// Get the list of items from the database.
		$items = parent::getItems();

		$client = JApplicationHelper::getClientInfo($this->getState('client_id', 0));
		$lang = JFactory::getLanguage();

		// Loop through the results to add the XML metadata,
		// and load language support.
		foreach ($items as &$item)
		{
			$path = JPath::clean($client->path . '/modules/' . $item->module . '/' . $item->module . '.xml');

			if (file_exists($path))
			{
				$item->xml = simplexml_load_file($path);
			}
			else
			{
				$item->xml = null;
			}

			// 1.5 Format; Core files or language packs then
			// 1.6 3PD Extension Support
			$lang->load($item->module . '.sys', $client->path, null, false, true)
				|| $lang->load($item->module . '.sys', $client->path . '/modules/' . $item->module, null, false, true);
			$item->name = JText::_($item->name);

			if (isset($item->xml) && $text = trim($item->xml->description))
			{
				$item->desc = JText::_($text);
			}
			else
			{
				$item->desc = JText::_('COM_MODULES_NODESCRIPTION');
			}
		}

		$items = ArrayHelper::sortObjects($items, 'name', 1, true, true);

		// TODO: Use the cached XML from the extensions table?

		return $items;
	}
}
