<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Banners component helper.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 * @since       1.6
 */
class BannersHelper extends JHelperContent
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_BANNERS_SUBMENU_BANNERS'),
			'index.php?option=com_banners&view=banners',
			$vName == 'banners'
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_BANNERS_SUBMENU_CATEGORIES'),
			'index.php?option=com_categories&extension=com_banners',
			$vName == 'categories'
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_BANNERS_SUBMENU_CLIENTS'),
			'index.php?option=com_banners&view=clients',
			$vName == 'clients'
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_BANNERS_SUBMENU_TRACKS'),
			'index.php?option=com_banners&view=tracks',
			$vName == 'tracks'
		);
	}

	/**
	 * Update / reset the banners
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public static function updateReset()
	{
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		$nullDate = $db->getNullDate();
		$now = JFactory::getDate();
		$query = $db->getQuery(true)
			->select('*')
			->from('#__banners')
			->where($db->quote($now) . ' >= ' . $db->quote('reset'))
			->where($db->quoteName('reset') . ' != ' . $db->quote($nullDate) . ' AND ' . $db->quoteName('reset') . '!=NULL')
			->where('(' . $db->quoteName('checked_out') . ' = 0 OR ' . $db->quoteName('checked_out') . ' = ' . (int) $db->quote($user->id) . ')');
		$db->setQuery($query);

		try
		{
			$rows = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());

			return false;
		}

		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');

		foreach ($rows as $row)
		{
			$purchase_type = $row->purchase_type;

			if ($purchase_type < 0 && $row->cid)
			{
				$client = JTable::getInstance('Client', 'BannersTable');
				$client->load($row->cid);
				$purchase_type = $client->purchase_type;
			}

			if ($purchase_type < 0)
			{
				$params = JComponentHelper::getParams('com_banners');
				$purchase_type = $params->get('purchase_type');
			}

			switch ($purchase_type)
			{
				case 1:
					$reset = $nullDate;
					break;
				case 2:
					$date = JFactory::getDate('+1 year ' . date('Y-m-d', strtotime('now')));
					$reset = $db->quote($date->toSql());
					break;
				case 3:
					$date = JFactory::getDate('+1 month ' . date('Y-m-d', strtotime('now')));
					$reset = $db->quote($date->toSql());
					break;
				case 4:
					$date = JFactory::getDate('+7 day ' . date('Y-m-d', strtotime('now')));
					$reset = $db->quote($date->toSql());
					break;
				case 5:
					$date = JFactory::getDate('+1 day ' . date('Y-m-d', strtotime('now')));
					$reset = $db->quote($date->toSql());
					break;
			}

			// Update the row ordering field.
			$query->clear()
				->update($db->quoteName('#__banners'))
				->set($db->quoteName('reset') . ' = ' . $db->quote($reset))
				->set($db->quoteName('impmade') . ' = ' . $db->quote(0))
				->set($db->quoteName('clicks') . ' = ' . $db->quote(0))
				->where($db->quoteName('id') . ' = ' . $db->quote($row->id));
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (RuntimeException $e)
			{
				JError::raiseWarning(500, $db->getMessage());

				return false;
			}
		}

		return true;
	}

	/**
	 * Get client list in text/value format for a select field
	 *
	 * @return  array
	 */
	public static function getClientOptions()
	{
		$options = array();

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('id As value, name As text')
			->from('#__banner_clients AS a')
			->order('a.name');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}

		// Merge any additional options in the XML definition.
		// $options = array_merge(parent::getOptions(), $options);

		array_unshift($options, JHtml::_('select.option', '0', JText::_('COM_BANNERS_NO_CLIENT')));

		return $options;
	}
}
