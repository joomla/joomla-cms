<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Banners\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Table\Table;

/**
 * Banners component helper.
 *
 * @since  1.6
 */
class BannersHelper extends ContentHelper
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
		\JHtmlSidebar::addEntry(
			\JText::_('COM_BANNERS_SUBMENU_BANNERS'),
			'index.php?option=com_banners&view=banners',
			$vName == 'banners'
		);

		\JHtmlSidebar::addEntry(
			\JText::_('COM_BANNERS_SUBMENU_CATEGORIES'),
			'index.php?option=com_categories&extension=com_banners',
			$vName == 'categories'
		);

		\JHtmlSidebar::addEntry(
			\JText::_('COM_BANNERS_SUBMENU_CLIENTS'),
			'index.php?option=com_banners&view=clients',
			$vName == 'clients'
		);

		\JHtmlSidebar::addEntry(
			\JText::_('COM_BANNERS_SUBMENU_TRACKS'),
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
		$db       = \JFactory::getDbo();
		$nullDate = $db->getNullDate();
		$query    = $db->getQuery(true)
			->select('*')
			->from('#__banners')
			->where($db->quote(\JFactory::getDate()) . ' >= ' . $db->quote('reset'))
			->where($db->quoteName('reset') . ' != ' . $db->quote($nullDate) . ' AND ' . $db->quoteName('reset') . '!= NULL')
			->where(
				'(' . $db->quoteName('checked_out') . ' = 0 OR ' . $db->quoteName('checked_out') . ' = '
				. (int) $db->quote(\JFactory::getUser()->id) . ')'
			);
		$db->setQuery($query);

		try
		{
			$rows = $db->loadObjectList();
		}
		catch (\RuntimeException $e)
		{
			\JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		foreach ($rows as $row)
		{
			$purchaseType = $row->purchase_type;

			if ($purchaseType < 0 && $row->cid)
			{
				/** @var \Joomla\Component\Banners\Administrator\Table\Client $client */
				$client = Table::getInstance('Client', '\\Joomla\\Component\\Banners\\Administrator\\Table\\');
				$client->load($row->cid);
				$purchaseType = $client->purchase_type;
			}

			if ($purchaseType < 0)
			{
				$params = ComponentHelper::getParams('com_banners');
				$purchaseType = $params->get('purchase_type');
			}

			switch ($purchaseType)
			{
				case 1:
					$reset = $nullDate;
					break;
				case 2:
					$date = \JFactory::getDate('+1 year ' . date('Y-m-d'));
					$reset = $db->quote($date->toSql());
					break;
				case 3:
					$date = \JFactory::getDate('+1 month ' . date('Y-m-d'));
					$reset = $db->quote($date->toSql());
					break;
				case 4:
					$date = \JFactory::getDate('+7 day ' . date('Y-m-d'));
					$reset = $db->quote($date->toSql());
					break;
				case 5:
					$date = \JFactory::getDate('+1 day ' . date('Y-m-d'));
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
			catch (\RuntimeException $e)
			{
				\JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');

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

		$db = \JFactory::getDbo();
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
		catch (\RuntimeException $e)
		{
			\JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		array_unshift($options, \JHtml::_('select.option', '0', \JText::_('COM_BANNERS_NO_CLIENT')));

		return $options;
	}
}
