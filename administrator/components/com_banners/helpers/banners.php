<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Banners component helper.
 *
 * @since  1.6
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
		$db       = JFactory::getDbo();
		$nullDate = $db->getNullDate();
		$query    = $db->getQuery(true)
			->select('*')
			->from('#__banners')
			->where($db->quote(JFactory::getDate()) . ' >= ' . $db->quote('reset'))
			->where($db->quoteName('reset') . ' != ' . $db->quote($nullDate) . ' AND ' . $db->quoteName('reset') . '!= NULL')
			->where(
				'(' . $db->quoteName('checked_out') . ' = 0 OR ' . $db->quoteName('checked_out') . ' = '
				. (int) $db->quote(JFactory::getUser()->id) . ')'
			);
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
			$purchaseType = $row->purchase_type;

			if ($purchaseType < 0 && $row->cid)
			{
				/** @var BannersTableClient $client */
				$client = JTable::getInstance('Client', 'BannersTable');
				$client->load($row->cid);
				$purchaseType = $client->purchase_type;
			}

			if ($purchaseType < 0)
			{
				$params = JComponentHelper::getParams('com_banners');
				$purchaseType = $params->get('purchase_type');
			}

			switch ($purchaseType)
			{
				case 1:
					$reset = $nullDate;
					break;
				case 2:
					$date = JFactory::getDate('+1 year ' . date('Y-m-d'));
					$reset = $db->quote($date->toSql());
					break;
				case 3:
					$date = JFactory::getDate('+1 month ' . date('Y-m-d'));
					$reset = $db->quote($date->toSql());
					break;
				case 4:
					$date = JFactory::getDate('+7 day ' . date('Y-m-d'));
					$reset = $db->quote($date->toSql());
					break;
				case 5:
					$date = JFactory::getDate('+1 day ' . date('Y-m-d'));
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
			->select('id AS value, name AS text')
			->from('#__banner_clients AS a')
			->where('a.state = 1')
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

		array_unshift($options, JHtml::_('select.option', '0', JText::_('COM_BANNERS_NO_CLIENT')));

		return $options;
	}

	/**
	 * Adds Count Items for Category Manager.
	 *
	 * @param   stdClass[]  &$items  The banner category objects
	 *
	 * @return  stdClass[]
	 *
	 * @since   3.5
	 */
	public static function countItems(&$items)
	{
		$db = JFactory::getDbo();

		foreach ($items as $item)
		{
			$item->count_trashed = 0;
			$item->count_archived = 0;
			$item->count_unpublished = 0;
			$item->count_published = 0;
			$query = $db->getQuery(true);
			$query->select('state, count(*) AS count')
				->from($db->qn('#__banners'))
				->where('catid = ' . (int) $item->id)
				->group('state');
			$db->setQuery($query);
			$banners = $db->loadObjectList();

			foreach ($banners as $banner)
			{
				if ($banner->state == 1)
				{
					$item->count_published = $banner->count;
				}

				if ($banner->state == 0)
				{
					$item->count_unpublished = $banner->count;
				}

				if ($banner->state == 2)
				{
					$item->count_archived = $banner->count;
				}

				if ($banner->state == -2)
				{
					$item->count_trashed = $banner->count;
				}
			}
		}

		return $items;
	}

	/**
	 * Adds Count Items for Tag Manager.
	 *
	 * @param   stdClass[]  &$items     The banner tag objects
	 * @param   string      $extension  The name of the active view.
	 *
	 * @return  stdClass[]
	 *
	 * @since   3.6
	 */
	public static function countTagItems(&$items, $extension)
	{
		$db = JFactory::getDbo();

		foreach ($items as $item)
		{
			$item->count_trashed = 0;
			$item->count_archived = 0;
			$item->count_unpublished = 0;
			$item->count_published = 0;
			$query = $db->getQuery(true);
			$query->select('published as state, count(*) AS count')
				->from($db->qn('#__contentitem_tag_map') . 'AS ct ')
				->where('ct.tag_id = ' . (int) $item->id)
				->where('ct.type_alias =' . $db->q($extension))
				->join('LEFT', $db->qn('#__categories') . ' AS c ON ct.content_item_id=c.id')
				->group('state');

			$db->setQuery($query);
			$banners = $db->loadObjectList();

			foreach ($banners as $banner)
			{
				if ($banner->state == 1)
				{
					$item->count_published = $banner->count;
				}

				if ($banner->state == 0)
				{
					$item->count_unpublished = $banner->count;
				}

				if ($banner->state == 2)
				{
					$item->count_archived = $banner->count;
				}

				if ($banner->state == -2)
				{
					$item->count_trashed = $banner->count;
				}
			}
		}

		return $items;
	}
}
