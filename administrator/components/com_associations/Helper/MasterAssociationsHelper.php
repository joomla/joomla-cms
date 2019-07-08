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
use Joomla\CMS\Router\Route;
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
	 * Method to create a link for a child item that has no master item
	 *
	 * @param   string   $globalMasterLang  The global master language
	 * @param   integer  $itemId            The item id
	 * @param   string   $itemType          The item type
	 *
	 * @return  string  the link for the not associated master item
	 */
	public static function addNotAssociatedMasterLink($globalMasterLang, $itemId, $itemType)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('title, sef')
			->from('#__languages')
			->where($db->quoteName('lang_code') . ' = ' . $db->quote($globalMasterLang));
		$db->setQuery($query);
		$globalMasterLangInfos = $db->loadAssoc();

		$classes    = 'badge badge-secondary';
		$masterInfo = '<br><br>' . Text::_('JGLOBAL_ASSOCIATIONS_MASTER_ITEM');
		$text       = $globalMasterLangInfos['sef'] ? strtoupper($globalMasterLangInfos['sef']) : 'XX';
		$title      = Text::_('JGLOBAL_ASSOCIATIONS_STATE_NOT_ASSOCIATED_DESC');
		$url        = Route::_(self::getAssociationUrl($itemId, $globalMasterLang, $itemType));

		$tooltip = '<strong>' . htmlspecialchars($globalMasterLangInfos['title'], ENT_QUOTES, 'UTF-8') . '</strong><br>'
			. htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . $masterInfo;

		$link = '<a href="' . $url . '" title="' . $globalMasterLangInfos['title'] . '" class="' . $classes . '">' . $text . '</a>'
			. '<div role="tooltip" id="tip_no_Master">' . $tooltip . '</div>';

		return $link;
	}

	/**
	 * Method to get master dates of each item of an association.
	 *
	 * @param   array   $associations  the associations to be saved.
	 * @param   string  $context       the association context
	 *
	 * @return  array  association with master dates
	 */
	public static function getMasterDates($associations, $context)
	{
		$db = Factory::getDbo();

		foreach ($associations as $langCode => $id)
		{
			if (is_array($id))
			{
				$id = $id['id'];
			}

			$query = $db->getQuery(true)
				->select($db->quoteName('master_date'))
				->from($db->quoteName('#__associations'))
				->where($db->quoteName('id') . ' = ' . $db->quote($id))
				->where($db->quoteName('context') . ' = ' . $db->quote($context));
			$db->setQuery($query);
			$masterDates[$id] = $db->loadResult();
		}

		return $masterDates;
	}

	/**
	 * Method to get master_id and master_date for an association going to be saved.
	 *
	 * @param   integer  $id                Item id
	 * @param   integer  $dataId            Item id of an item that is going to be saved
	 * @param   integer  $masterId          Id of the associated master item
	 * @param   string   $masterModified    The latest modified date of the master
	 * @param   array    $assocMasterDates  Masters modified date of an associated item
	 * @param   string   $old_key           The old association key to check if it is a new association
	 *
	 * @return  array    master id and master dates for an associated item
	 */
	public static function getMasterValues($id, $dataId, $masterId, $masterModified, $assocMasterDates, $old_key)
	{
		if ($masterId)
		{
			// For the master item
			if ($masterId === $id)
			{
				$masterIdValue = 0;

				// Set always the last modified date
				$masterDateValue = $masterModified ?? 'NULL';
			}

			// For the children
			else
			{
				$masterIdValue = $masterId;

				// If modified date isn't set to the child item, set current modified date from master.
				$masterDateValue = empty($assocMasterDates[$id])
					? $masterModified
					: $assocMasterDates[$id];

				if (!$old_key && ($dataId === $id))
				{
					// Add modified date from master to new associated item
					$masterDateValue = $masterModified ?? 'NULL';
				}
			}
		}
		else
		{
			// Default values when there is no associated master item.
			$masterIdValue   = -1;
			$masterDateValue = 'NULL';
		}

		return [(int) $masterIdValue, $masterDateValue];
	}

	/**
	 * Method to get the latest modified date of a master item
	 *
	 * @param   integer  $masterId   Id of the associated master item
	 * @param   string   $tableName  The name of the table.
	 * @param   string   $typeAlias  Alias for the content type
	 *
	 * @return  string   The modified date of the master item
	 */
	public static function getMasterModifiedDate($masterId, $tableName, $typeAlias)
	{
		// Check if the content version is enabled
		$aliasParts         = explode('.', $typeAlias);
		$saveHistory        = ComponentHelper::getParams($aliasParts[0])->get('save_history', 0);
		$contentTypeTable   = Table::getInstance('ContentType');
		$contentTypeTblName = $contentTypeTable->getTableName();
		$typeId             = $contentTypeTable->getTypeId($typeAlias);

		$db = Factory::getDbo();
		$fieldMapsQuery = $db->getQuery(true)
			->select($db->quoteName('field_mappings'))
			->from($db->quoteName($contentTypeTblName))
			->where($db->quoteName('type_id') . ' = ' . $db->quote($typeId));
		$db->setQuery($fieldMapsQuery);
		$fieldMaps = $db->loadResult();

		$modifiedColumn = json_decode($fieldMaps)->common->core_modified_time;

		if ($masterId)
		{
			// If versions are enabled get the save_date of the master item from history table
			if ($saveHistory)
			{
				$masterHistory = ContentHistoryHelper::getHistory($typeId, $masterId);

				// Latest saved date of the master item
				$masterModified = $masterHistory[0]->save_date;
			}
			else
			{
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

	/**
	 * Method to set class name and information about the association state or the master item.
	 *
	 * @param   integer  $itemId            Item id
	 * @param   array    $items             The associated items for the item with the itemId
	 * @param   integer  $key               The current key from $items that is currently going through the foreach loop.
	 * @param   array    $item              The current value from $items that is currently going through the foreach loop.
	 * @param   boolean  $isMaster          If the item with $itemId is a master item.
	 * @param   integer  $masterId          Id of the associated master item.
	 * @param   array    $assocMasterDates  Master Dates of each associated item.
	 * @param   boolean  $saveHistory       If Versions are enabled or not.
	 *
	 * @return  array  the className and masterInfo for the association state, the array $items back and boolean if item needs update.
	 */
	public static function setMasterAndChildInfos($itemId, $items, $key, $item, $globalMasterLang, $isMaster, $masterId, $assocMasterDates, $saveHistory)
	{

		$addClass   = 'badge-success';
		$masterInfo = '';
		$update     = false;

		// Don't display other children if the current item is a child of the master language.
		if (($key !== $itemId) && ($globalMasterLang !== $item->lang_code) && !$isMaster)
		{
			unset($items[$key]);
		}

		if ($key === $masterId)
		{
			$addClass   .= ' master-item';
			$masterInfo  = '<br><br>' . Text::_('JGLOBAL_ASSOCIATIONS_MASTER_ITEM');
		}
		else
		{
			// Get association state of child when a master exists
			if ($masterId && (array_key_exists($key, $assocMasterDates)) && (array_key_exists($masterId, $assocMasterDates)))
			{
				$associatedModifiedMaster = $assocMasterDates[$key];
				$lastModifiedMaster       = $assocMasterDates[$masterId];

				if ($associatedModifiedMaster < $lastModifiedMaster)
				{
					$update     = true;
					$addClass   = 'badge-warning';
					$masterInfo = $saveHistory
						? '<br><br>' . Text::_('JGLOBAL_ASSOCIATIONS_STATE_OUT_OF_DATE_DESC')
						: '<br><br>' . Text::_('JGLOBAL_ASSOCIATIONS_STATE_MIGHT_BE_OUT_OF_DATE_DESC');
				}
				else
				{
					$addClass   = 'badge-success';
					$masterInfo = '<br><br>' . Text::_('JGLOBAL_ASSOCIATIONS_STATE_UP_TO_DATE_DESC');
				}
			}
		}

		return [$addClass, $masterInfo, $items, $update];
	}

	/**
	 * Method to get the association url for an item
	 *
	 * @param   integer  $itemId            The item id
	 * @param   string   $globalMasterLang  The global master language
	 * @param   string   $itemType          The item type
	 * @param   string   $itemLang          The current value from $items that is currently going through the foreach loop.
	 * @param   integer  $key               The current key from $items that is currently going through the foreach loop.
	 * @param   integer  $masterId          Id of the associated master item.
	 * @param   boolean  $needsUpdate       If the item needs an update or not.
	 *
	 * @return string
	 */
	public static function getAssociationUrl($itemId, $globalMasterLang, $itemType, $itemLang = '', $key = '', $masterId = '', $needsUpdate = false)
	{
		$target = '';

		if (empty($masterId))
		{
			$target = $globalMasterLang . ':0:add';
		}
		elseif ($key !== $masterId)
		{
			$target = $itemLang . ':' . $itemId . ':edit';
		}

		// Generate item Html.
		$options   = array(
			'option'   => 'com_associations',
			'view'     => 'association',
			'layout'   => $needsUpdate ? 'update' : 'edit',
			'itemtype' => $itemType,
			'task'     => 'association.edit',
			'id'       => empty($masterId) ? $itemId : $masterId,
			'target'   => $target,
		);

		$url = 'index.php?' . http_build_query($options);

		return $url;
	}
}
