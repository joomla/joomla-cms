<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Associations\Administrator\Helper;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Helper\ContentHistoryHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;

defined('_JEXEC') or die;

/**
 * Master Associations component helper.
 *
 * @since  4.0
 */
class MasterAssociationsHelper extends ContentHelper
{
	/**
	 * @param $globalMasterLanguage
	 *
	 * @return string
	 */
	public static function addNotAssociatedMasterLink($globalMasterLanguage)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('title, sef')
			->from('#__languages')
			->where($db->quoteName('lang_code') . ' = '
				. $db->quote($globalMasterLanguage));
		$db->setQuery($query);
		$globalMasterLanguageInfos = $db->loadAssoc();

		$classes       = 'hasPopover badge badge-secondary';
		$languageTitle = $globalMasterLanguageInfos['title'] . ' - ' . Text::_('JGLOBAL_ASSOCIATIONS_MASTER_LANGUAGE');
		$text          = $globalMasterLanguageInfos['sef']
			? strtoupper($globalMasterLanguageInfos['sef'])
			: 'XX';
		$title         = Text::_('JGLOBAL_ASSOCIATIONS_STATE_NOT_ASSOCIATED_DESC');
		$tooltip       = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
		$url           = '';

		$link = '<a href="' . $url . '" title="' . $languageTitle . '" class="'
			. $classes . '" data-content="'
			. $tooltip . '" data-placement="top">' . $text . '</a>';

		return $link;
	}

	/**
	 * @param   integer  $itemId   Item id
	 * @param   string   $context  Context of the association
	 *
	 * @return   boolean  True if the item is a master item, false otherwise
	 */
	public static function isMaster($itemId, $context)
	{
		$parentId = self::getMasterId($itemId, $context);

		$isMaster = ($parentId === 0) ? true : false;

		return $isMaster;

	}

	/**
	 * Get the associated master item id from a child element.
	 *
	 * @param   integer  $itemId   Item id
	 * @param   string   $context  context of the association
	 *
	 * @return  integer  The id of the associated master item
	 *
	 * @since  4.0
	 */
	public static function getMasterId($itemId, $context)
	{
		$db = Factory::getDbo();

		$parentQuery = $db->getQuery(true)
			->select($db->quoteName('parent_id'))
			->from($db->quoteName('#__associations'))
			->where($db->quoteName('id') . ' = ' . $db->quote($itemId))
			->where($db->quoteName('context') . ' = ' . $db->quote($context));
		$masterId    = $db->setQuery($parentQuery)->loadResult();

		return (int) $masterId;

	}

	/**
	 * Method to get associated params of the master item.
	 *
	 * @param   array   $associations  the associations to be saved.
	 *
	 * @param   string  $context       the association context
	 *
	 * @return   array  associations with params
	 *
	 */
	public static function getAssociationsParams($associations, $context)
	{
		$db = Factory::getDbo();

		foreach ($associations as $langCode => $id)
		{
			if (is_array($id))
			{
				$id = $id['id'];
			}
			$query = $db->getQuery(true)
				->select($db->quoteName('assocParams'))
				->from($db->quoteName('#__associations'))
				->where($db->quoteName('id') . ' = ' . $db->quote($id))
				->where($db->quoteName('context') . ' = ' . $db->quote($context));
			$db->setQuery($query);
			$param            = $db->loadResult();
			$assocParams[$id] = $param;
		}

		return $assocParams;
	}

	/**
	 * @param   integer   $id                  Item id
	 * @param   integer   $dataId              Item id of an item that is going to be saved
	 * @param   integer   $masterId            Id of the associated master item
	 * @param   string    $masterModified      the latest modified date of the master
	 * @param   array     $associationsParams  modified date to associated items
	 * @param   string    $old_key             the old association key
	 *
	 * @return   array     parent id and modified date
	 */
	public static function getMasterLanguageValues($id, $dataId, $masterId, $masterModified, $associationsParams, $old_key) {

		if ($masterId)
		{
			// For the master item
			if ($masterId === $id)
			{
				$parentId = 0;
				// set always the last modified date
				$parentModified = $masterModified ?? null;
			}
			// For the children
			else
			{
				$parentId = $masterId;

				// if modified date isn't set to the child item set it with current modified date from master
				$parentModified = empty($associationsParams[$id])
					? $masterModified
					: $associationsParams[$id];

				if (!$old_key && ($dataId === $id))
				{
					// Add modified date from master to new associated item
					$parentModified = $masterModified ?? null;
				}
			}
		}
		// default values when there is no associated master item
		else
		{
			$parentId       = -1;
			$parentModified = null;
		}

		return [$parentId, $parentModified];
	}

	/**
	 * Get the latest modified date of an master item
	 *
	 * @param   integer   $masterId   Id of the associated master item
	 * @param   string    $tableName  The name of the table.
	 * @param   string    $typeAlias  Alias for the content type
	 *
	 * @return  string    The modified date of the master item
	 */
	public static function getMasterModifiedDate($masterId, $tableName, $typeAlias)
	{
		// check if the content version is enabled
		$option = Factory::getApplication()->input->get('option');
		$saveHistory = ComponentHelper::getParams($option)->get('save_history', 0);

		if ($masterId)
		{
			// if versions are enabled get the save_data of the master item from history table
			if ($saveHistory)
			{
				$typeId        = Table::getInstance('ContentType')->getTypeId($typeAlias);
				$masterHistory = ContentHistoryHelper::getHistory($typeId, $masterId);

				// latest saved date of the master item
				$masterModified = $masterHistory[0]->save_date;
			}
			else
			{
				$db = Factory::getDbo();

				if ($tableName === '#__categories')
				{
					$modifiedColumn = 'modified_time';
				}
				else
				{
					$modifiedColumn = 'modified';
				}

				$masterDateQuery = $db->getQuery(true)
					->select($db->quoteName($modifiedColumn))
					->from($db->quoteName($tableName))
					->where($db->quoteName('id') . ' = ' . $db->quote($masterId));
				$db->setQuery($masterDateQuery);
				$masterModified = $db->loadResult();
			}
		}

		return $masterModified ?? '';
	}
}
