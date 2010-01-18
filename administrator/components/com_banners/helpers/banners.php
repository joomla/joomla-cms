<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * @package		Joomla.Administrator
 * @subpackage	com_banners
 */
class BannersHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	The name of the active view.
	 */
	public static function addSubmenu($vName)
	{
		JSubMenuHelper::addEntry(
			JText::_('Banners_Submenu_Banners'),
			'index.php?option=com_banners&view=banners',
			$vName == 'banners'
		);
		JSubMenuHelper::addEntry(
			JText::_('Banners_Submenu_Clients'),
			'index.php?option=com_banners&view=clients',
			$vName == 'clients'
		);
		JSubMenuHelper::addEntry(
			JText::_('Banners_Submenu_Tracks'),
			'index.php?option=com_banners&view=tracks',
			$vName == 'tracks'
		);
		JSubMenuHelper::addEntry(
			JText::_('Banners_Submenu_Categories'),
			'index.php?option=com_categories&extension=com_banners',
			$vName == 'categories'
		);
		if ($vName=='categories') {
			JToolBarHelper::title(JText::_('Banners_Manager_Categories'), 'categories');
		}
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param	int		The category ID.
	 *
	 * @return	JObject
	 */
	public static function getActions($categoryId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		if (empty($categoryId)) {
			$assetName = 'com_banners';
		}
		else {
			$assetName = 'com_banners.category.'.(int) $categoryId;
		}

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}

		return $result;
	}
	public static function updateReset()
	{
		$user = JFactory::getUser();
		$db = JFactory::getDBO();
		$query = new JQuery;
		$query->select('*');
		$query->from("#__banners");
		$query->where("NOW()>=`reset`");
		$query->where("`reset`!='0000-00-00 00:00:00' AND `reset`!=NULL");
		$query->where("(`checked_out`=0 OR `checked_out`=".$db->Quote($user->id).")");
		$db->setQuery((string)$query);
		$rows = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseWarning(500, $db->getErrorMsg());
			return false;
		}

		JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_banners/tables');
		foreach ($rows as $row)
		{
			$purchase_type = $row->purchase_type;
			if ($purchase_type < 0 && $row->cid)
			{
				$client = JTable::getInstance('Client','BannersTable');
				$client->load($row->cid);
				$purchase_type = $client->purchase_type;
			}
			if ($purchase_type < 0)
			{
				$params = JComponentHelper::getParams('com_banners');
				$purchase_type = $params->get('purchase_type');
			}

			switch($purchase_type)
			{
			case 1:
				$reset='0000-00-00 00:00:00';
			break;
			case 2:
				$reset = JFactory::getDate('+1 year '.date('Y-m-d',strtotime('now')))->toMySQL();
			break;
			case 3:
				$reset = JFactory::getDate('+1 month '.date('Y-m-d',strtotime('now')))->toMySQL();
			break;
			case 4:
				$reset = JFactory::getDate('+7 day '.date('Y-m-d',strtotime('now')))->toMySQL();
			break;
			case 5:
				$reset = JFactory::getDate('+1 day '.date('Y-m-d',strtotime('now')))->toMySQL();
			break;
			}

			// Update the row ordering field.
			$query = new JQuery;
			$query->update('`#__banners`');
			$query->set('`reset` = '.$db->quote($reset));
			$query->set('`impmade` = '.$db->quote(0));
			$query->set('`clicks` = '.$db->quote(0));
			$query->where('`id` = '.$db->quote($row->id));
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
}
