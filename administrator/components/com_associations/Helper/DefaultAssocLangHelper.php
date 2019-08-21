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
use Joomla\Database\ParameterType;

defined('_JEXEC') or die;

/**
 * Associations component helper with default association language.
 *
 * @since  4.0
 */
class DefaultAssocLangHelper extends ContentHelper
{
	/**
	 * Method to create a link for a child item that has no parent
	 *
	 * @param   string   $defaultAssocLang  The default association language
	 * @param   integer  $itemId            The item id
	 * @param   string   $itemType          The item type
	 *
	 * @return  string  the link for the not associated parent
	 */
	public static function addNotAssociatedParentLink($defaultAssocLang, $itemId, $itemType)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName(['title', 'sef']))
			->from($db->quoteName('#__languages'))
			->where($db->quoteName('lang_code') . ' = :lang_code')
			->bind(':lang_code', $defaultAssocLang);
		$db->setQuery($query);
		$defaultAssocLangInfos = $db->loadAssoc();

		$classes         = 'badge badge-secondary';
		$parentChildInfo = '<br><br>' . Text::_('JGLOBAL_ASSOCIATIONS_DEFAULT_ASSOC_LANG_ITEM');
		$text            = $defaultAssocLangInfos['sef'] ? strtoupper($defaultAssocLangInfos['sef']) : 'XX';
		$title           = Text::_('JGLOBAL_ASSOCIATIONS_STATE_NOT_ASSOCIATED_DESC');
		$url             = Route::_(self::getAssociationUrl($itemId, $defaultAssocLang, $itemType));

		$tooltip = '<strong>' . htmlspecialchars($defaultAssocLangInfos['title'], ENT_QUOTES, 'UTF-8') . '</strong><br>'
			. htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . $parentChildInfo;

		$link = '<a href="' . $url . '" title="' . $defaultAssocLangInfos['title'] . '" class="' . $classes . '">' . $text . '</a>'
			. '<div role="tooltip" id="tip_no_parent">' . $tooltip . '</div>';

		return $link;
	}

	/**
	 * Method to get parent dates of each item of an association
	 *
	 * @param   array   $associations  the associations to be saved
	 * @param   string  $context       the association context
	 *
	 * @return  array  association with parent dates
	 */
	public static function getParentDates($associations, $context)
	{
		$db = Factory::getDbo();

		foreach ($associations as $langCode => $id)
		{
			if (is_array($id))
			{
				$id = $id['id'];
			}

			$query = $db->getQuery(true)
				->select($db->quoteName(['parent_date', 'key']))
				->from($db->quoteName('#__associations'))
				->where(
					[
						$db->quoteName('id') . ' = :id',
						$db->quoteName('context') . ' = :context'
					]
				)
				->bind(':id', $id, ParameterType::INTEGER)
				->bind(':context', $context);
			$db->setQuery($query);
			$parentDates[$id] = $db->loadRow();
		}

		return $parentDates;
	}

	/**
	 * Method to get parent_id and parent_date for an association going to be saved.
	 *
	 * @param   integer  $id                Item id
	 * @param   integer  $dataId            Item id of an item that is going to be saved
	 * @param   integer  $parentId          Id of the associated parent
	 * @param   string   $parentModified    The latest modified date of the parent
	 * @param   array    $assocParentDates  Parents modified date of an associated item
	 * @param   string   $old_key           The old association key to check if it is a new association
	 *
	 * @return  array    parent id and parent dates for an associated item
	 */
	public static function getParentValues($id, $dataId, $parentId, $parentModified, $assocParentDates, $old_key)
	{
		if ($parentId)
		{
			// For the parent
			if ($parentId === $id)
			{
				$parentIdValue = 0;

				// Set always the last modified date
				$parentDateValue = $parentModified ?? 'NULL';
			}

			// For the children
			else
			{
				$parentIdValue = $parentId;

				// If modified date isn't set to the child item, set current modified date from parent OR if child is added from another association
				$parentDateValue = (
					empty($assocParentDates[$id][0])
					|| ($assocParentDates[$id][1] !== $old_key)
					|| ($assocParentDates[$parentId][1] !== $assocParentDates[$id][1])
				)
					? $parentModified
					: $assocParentDates[$id][0];

				if (!$old_key && ($dataId !== $id))
				{
					// Add modified date from parent to new associated item
					$parentDateValue = $parentModified ?? 'NULL';
				}
			}
		}
		else
		{
			// Default values when there is no associated parent.
			$parentIdValue   = -1;
			$parentDateValue = 'NULL';
		}

		return [(int) $parentIdValue, $parentDateValue];
	}

	/**
	 * Method to get the latest modified date of a parent
	 *
	 * @param   integer  $parentId   Id of the associated parent
	 * @param   string   $tableName  The name of the table
	 * @param   string   $typeAlias  Alias for the content type
	 *
	 * @return  string   The modified date of the parent
	 */
	public static function getParentModifiedDate($parentId, $tableName, $typeAlias)
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
			->where($db->quoteName('type_id') . ' = :type_id')
			->bind(':type_id', $typeId, ParameterType::INTEGER);
		$db->setQuery($fieldMapsQuery);
		$fieldMaps = $db->loadResult();

		$modifiedColumn = json_decode($fieldMaps)->common->core_modified_time;

		if ($parentId)
		{
			// If versions are enabled get the save_date of the parent from history table
			if ($saveHistory)
			{
				$parentHistory = ContentHistoryHelper::getHistory($typeId, $parentId);

				// Latest saved date of the parent
				$parentModified = $parentHistory[0]->save_date;
			}
			else
			{
				$parentDateQuery = $db->getQuery(true)
					->select($db->quoteName($modifiedColumn))
					->from($db->quoteName($tableName))
					->where($db->quoteName('id') . ' = :id')
					->bind(':id', $parentId, ParameterType::INTEGER);
				$db->setQuery($parentDateQuery);
				$parentModified = $db->loadResult();
			}
		}

		return $parentModified ?? '';
	}

	/**
	 * Method to set class name and information about the association state or the parent.
	 *
	 * @param   integer  $itemId            Item id
	 * @param   array    $items             The associated items for the item with the itemId
	 * @param   integer  $key               The current key from $items that is currently going through the foreach loop
	 * @param   array    $item              The current value from $items that is currently going through the foreach loop
	 * @param   string   $defaultAssocLang  The default association language
	 * @param   boolean  $isParent          If the item with $itemId is a parent
	 * @param   integer  $parentId          Id of the associated parent
	 * @param   array    $assocParentDates  Parent Dates of each associated item
	 * @param   boolean  $saveHistory       If Versions are enabled or not
	 *
	 * @return  array  the className and parentInfo for the association state, the array $items back and boolean if item needs update.
	 */
	public static function setParentAndChildInfos($itemId, $items, $key, $item, $defaultAssocLang, $isParent, $parentId, $assocParentDates, $saveHistory)
	{

		$addClass   = 'badge-success';
		$parentInfo = '';
		$update     = false;

		// Don't display other children if the current item is a child.
		if (($key !== $itemId) && ($defaultAssocLang !== $item->lang_code) && !$isParent)
		{
			unset($items[$key]);
		}

		if ($key === $parentId)
		{
			$addClass   .= ' parent-item';
			$parentInfo  = '<br><br>' . Text::_('JGLOBAL_ASSOCIATIONS_DEFAULT_ASSOC_LANG_ITEM');
		}
		else
		{
			// Get association state of child when a parent item exists
			if ($parentId && (array_key_exists($key, $assocParentDates)) && (array_key_exists($parentId, $assocParentDates)))
			{
				$associatedModifiedParent = $assocParentDates[$key][0];
				$lastModifiedParent       = $assocParentDates[$parentId][0];

				if ($associatedModifiedParent < $lastModifiedParent)
				{
					$update     = true;
					$addClass   = 'badge-warning';
					$parentInfo = $saveHistory
						? '<br><br>' . Text::_('JGLOBAL_ASSOCIATIONS_STATE_OUT_OF_DATE_DESC')
						: '<br><br>' . Text::_('JGLOBAL_ASSOCIATIONS_STATE_MIGHT_BE_OUT_OF_DATE_DESC');
				}
				else
				{
					$addClass   = 'badge-success';
					$parentInfo = '<br><br>' . Text::_('JGLOBAL_ASSOCIATIONS_STATE_UP_TO_DATE_DESC');
				}
			}
		}

		return [$addClass, $parentInfo, $items, $update];
	}

	/**
	 * Method to get the association url for an item
	 *
	 * @param   integer  $itemId            The item id
	 * @param   string   $defaultAssocLang  The default association language
	 * @param   string   $itemType          The item type
	 * @param   string   $itemLang          The current value from $items that is currently going through the foreach loop
	 * @param   integer  $key               The current key from $items that is currently going through the foreach loop
	 * @param   integer  $parentId          Id of the associated parent
	 * @param   boolean  $needsUpdate       If the item needs an update or not
	 *
	 * @return string
	 */
	public static function getAssociationUrl($itemId, $defaultAssocLang, $itemType, $itemLang = '', $key = '', $parentId = '', $needsUpdate = false)
	{
		$target = '';

		if (empty($parentId))
		{
			$target = $defaultAssocLang . ':0:add';
		}
		elseif ($key !== $parentId)
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
			'id'       => empty($parentId) ? $itemId : $parentId,
			'target'   => $target,
		);

		$url = 'index.php?' . http_build_query($options);

		return $url;
	}
}
