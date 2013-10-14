<?php
/**
 * @package    FrameworkOnFramework
 * @copyright  Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('_JEXEC') or die;

/**
 * FrameworkOnFramework table behavior class for assets
 *
 * @package  FrameworkOnFramework
 * @since    2.1
 */
class FOFTableBehaviorAssets extends FOFTableBehavior
{
	/**
	 * The event which runs before storing (saving) data to the database
	 *
	 * @param   FOFTable  &$table       The table which calls this event
	 * @param   boolean   $updateNulls  Should nulls be saved as nulls (true) or just skipped over (false)?
	 *
	 * @return  boolean  True to allow saving
	 */
	public function onBeforeStore(&$table, $updateNulls)
	{
		$result = true;

		$asset_id_field	= $table->getColumnAlias('asset_id');

		if (in_array($asset_id_field, $table->getKnownFields()))
		{
			if (!empty($table->$asset_id_field))
			{
				$currentAssetId = $table->$asset_id_field;
			}

			// The asset id field is managed privately by this class.
			if ($table->isAssetsTracked())
			{
				unset($table->$asset_id_field);
			}
		}

		// Create the object used for inserting/udpating data to the database
		$fields     = $table->getTableFields();

		// Let's remove the asset_id field, since we unset the property above and we would get a PHP notice
		if (isset($fields[$asset_id_field]))
		{
			unset($fields[$asset_id_field]);
		}

		/*
		 * Asset Tracking
		 */
		if (in_array($asset_id_field, $table->getKnownFields()) && $table->isAssetsTracked())
		{
			$parentId = $table->getAssetParentId();
			$name     = $table->getAssetName();
			$title    = $table->getAssetTitle();

			$asset = JTable::getInstance('Asset', 'JTable', array('dbo' => $table->getDbo()));
			$asset->loadByName($name);

			// Re-inject the asset id.
			$this->$asset_id_field = $asset->id;

			// Check for an error.
			$error = $asset->getError();

			if ($error)
			{
				$table->setError($error);

				return false;
			}

			// Specify how a new or moved node asset is inserted into the tree.
			if (empty($table->$asset_id_field) || $asset->parent_id != $parentId)
			{
				$asset->setLocation($parentId, 'last-child');
			}

			// Prepare the asset to be stored.
			$asset->parent_id = $parentId;
			$asset->name      = $name;
			$asset->title     = $title;

			if ($table->getRules() instanceof JAccessRules)
			{
				$asset->rules = (string) $table->getRules();
			}

			if (!$asset->check() || !$asset->store($updateNulls))
			{
				$table->setError($asset->getError());

				return false;
			}

			// Create an asset_id or heal one that is corrupted.
			if (empty($table->$asset_id_field) || (($currentAssetId != $table->$asset_id_field) && !empty($table->$asset_id_field)))
			{
				// Update the asset_id field in this table.
				$table->$asset_id_field = (int) $asset->id;

				$k = $table->getKeyName();

				$query = $table->getDbo()->getQuery(true);
				$query->update($table->getDbo()->qn($table->getTableName()));
				$query->set('asset_id = ' . (int) $table->$asset_id_field);
				$query->where($table->getDbo()->qn($k) . ' = ' . (int) $table->$k);
				$table->getDbo()->setQuery($query);

				$table->getDbo()->execute();
			}

			$result = true;
		}

		return $result;
	}

	/**
	 * The event which runs after binding data to the table
	 *
	 * @param   FOFTable      &$table  The table which calls this event
	 * @param   object|array  &$src    The data to bind
	 *
	 * @return  boolean  True on success
	 */
	public function onAfterBind(&$table, &$src)
	{
		// Set rules for assets enabled tables
		if ($table->isAssetsTracked())
		{
			// Bind the rules.
			if (isset($src['rules']) && is_array($src['rules']))
			{
				$table->setRules($src['rules']);
			}
		}

		return true;
	}

	/**
	 * The event which runs before deleting a record
	 *
	 * @param   FOFTable  &$table  The table which calls this event
	 * @param   integer   $oid     The PK value of the record to delete
	 *
	 * @return  boolean  True to allow the deletion
	 */
	public function onBeforeDelete(&$table, $oid)
	{
		// If tracking assets, remove the asset first.
		if ($table->isAssetsTracked())
		{
			// Get and the asset name.
			$table->$k 	= $pk;
			$name    	= $table->getAssetName();

			// Do NOT touch JTable here -- we are loading the core asset table which is a JTable, not a FOFTable
			$asset    = JTable::getInstance('Asset');

			if ($asset->loadByName($name))
			{
				if (!$asset->delete())
				{
					$table->setError($asset->getError());

					return false;
				}
			}
			else
			{
				$table->setError($asset->getError());

				return false;
			}
		}

		return true;
	}
}
