<?php
/**
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Banners component helper.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_banners
 * @since		1.6
 */
class BannersHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	The name of the active view.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public static function addSubmenu($vName)
	{
		JSubMenuHelper::addEntry(
			JText::_('COM_BANNERS_SUBMENU_BANNERS'),
			'index.php?option=com_banners&view=banners',
			$vName == 'banners'
		);

		JSubMenuHelper::addEntry(
			JText::_('COM_BANNERS_SUBMENU_CATEGORIES'),
			'index.php?option=com_categories&extension=com_banners',
			$vName == 'categories'
		);
		if ($vName=='categories') {
			JToolBarHelper::title(
				JText::sprintf('COM_CATEGORIES_CATEGORIES_TITLE', JText::_('com_banners')),
				'banners-categories');
		}

		JSubMenuHelper::addEntry(
			JText::_('COM_BANNERS_SUBMENU_CLIENTS'),
			'index.php?option=com_banners&view=clients',
			$vName == 'clients'
		);

		JSubMenuHelper::addEntry(
			JText::_('COM_BANNERS_SUBMENU_TRACKS'),
			'index.php?option=com_banners&view=tracks',
			$vName == 'tracks'
		);
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param	int		The category ID.
	 *
	 * @return	JObject
	 * @since	1.6
	 */
	public static function getActions($categoryId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		if (empty($categoryId)) {
			$assetName = 'com_banners';
			$level = 'component';
		} else {
			$assetName = 'com_banners.category.'.(int) $categoryId;
			$level = 'category';
		}

		$actions = JAccess::getActions('com_banners', $level);

		foreach ($actions as $action) {
			$result->set($action->name,	$user->authorise($action->name, $assetName));
		}

		return $result;
	}

	/**
	 * @return	boolean
	 * @since	1.6
	 */
	public static function updateReset()
	{
		$user = JFactory::getUser();
		$db = JFactory::getDBO();
		$nullDate = $db->getNullDate();
		$now = JFactory::getDate();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__banners');
		$query->where("'".$now."' >= ".$db->quoteName('reset'));
		$query->where($db->quoteName('reset').' != '.$db->quote($nullDate).' AND '.$db->quoteName('reset').'!=NULL');
		$query->where('('.$db->quoteName('checked_out').' = 0 OR '.$db->quoteName('checked_out').' = '.(int) $db->Quote($user->id).')');
		$db->setQuery((string)$query);
		$rows = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseWarning(500, $db->getErrorMsg());
			return false;
		}

		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');

		foreach ($rows as $row) {
			$purchase_type = $row->purchase_type;

			if ($purchase_type < 0 && $row->cid) {
				$client = JTable::getInstance('Client', 'BannersTable');
				$client->load($row->cid);
				$purchase_type = $client->purchase_type;
			}

			if ($purchase_type < 0) {
				$params = JComponentHelper::getParams('com_banners');
				$purchase_type = $params->get('purchase_type');
			}

			switch($purchase_type) {
				case 1:
					$reset = $nullDate;
					break;
				case 2:
					$date = JFactory::getDate('+1 year '.date('Y-m-d', strtotime('now')));
					$reset = $db->Quote($date->toSql());
					break;
				case 3:
					$date = JFactory::getDate('+1 month '.date('Y-m-d', strtotime('now')));
					$reset = $db->Quote($date->toSql());
					break;
				case 4:
					$date = JFactory::getDate('+7 day '.date('Y-m-d', strtotime('now')));
					$reset = $db->Quote($date->toSql());
					break;
				case 5:
					$date = JFactory::getDate('+1 day '.date('Y-m-d', strtotime('now')));
					$reset = $db->Quote($date->toSql());
					break;
			}

			// Update the row ordering field.
			$query->clear();
			$query->update($db->quoteName('#__banners'));
			$query->set($db->quoteName('reset').' = '.$db->quote($reset));
			$query->set($db->quoteName('impmade').' = '.$db->quote(0));
			$query->set($db->quoteName('clicks').' = '.$db->quote(0));
			$query->where($db->quoteName('id').' = '.$db->quote($row->id));
			$db->setQuery((string)$query);
			$db->query();

			// Check for a database error.
			if ($db->getErrorNum()) {
				JError::raiseWarning(500, $db->getErrorMsg());
				return false;
			}
		}

		return true;
	}

	public static function getClientOptions()
	{
		// Initialize variables.
		$options = array();

		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);

		$query->select('id As value, name As text');
		$query->from('#__banner_clients AS a');
		$query->order('a.name');

		// Get the options.
		$db->setQuery($query);

		$options = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseWarning(500, $db->getErrorMsg());
		}

		// Merge any additional options in the XML definition.
		//$options = array_merge(parent::getOptions(), $options);

		array_unshift($options, JHtml::_('select.option', '0', JText::_('COM_BANNERS_NO_CLIENT')));

		return $options;
	}
}
