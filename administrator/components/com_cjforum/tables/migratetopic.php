<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_PLATFORM') or die();
require_once JPATH_ADMINISTRATOR . '/components/com_cjforum/tables/topic.php';

class CjForumTableMigrateTopic extends CjForumTableTopic
{
	public function __construct (JDatabaseDriver $db)
	{
		parent::__construct($db);
	}

	public function store ($updateNulls = false)
	{
		$k = $this->_tbl_keys;

		// Implement JObservableInterface: Pre-processing by observers
		$this->_observers->update('onBeforeStore', array($updateNulls, $k));

		$currentAssetId = 0;

		if (!empty($this->asset_id))
		{
			$currentAssetId = $this->asset_id;
		}

		// The asset id field is managed privately by this class.
		if ($this->_trackAssets)
		{
			unset($this->asset_id);
		}

		// If a primary key exists update the object, otherwise insert it.
		if ($this->hasPrimaryKey())
		{
			$result = $this->_db->updateObject($this->_tbl, $this, $this->_tbl_keys, $updateNulls);
		}
		else
		{
			$result = $this->_db->insertObject($this->_tbl, $this, $this->_tbl_keys[0]);
		}

		// If the table is not set to track assets return true.
		if ($this->_trackAssets)
		{
			if ($this->_locked)
			{
				$this->_unlock();
			}

			/*
			 * Asset Tracking
			 */
			$parentId = $this->_getAssetParentId();
			$name     = $this->_getAssetName();
			$title    = $this->_getAssetTitle();

			$asset = self::getInstance('Asset', 'JTable', array('dbo' => $this->getDbo()));
			$asset->loadByName($name);

			// Re-inject the asset id.
			$this->asset_id = $asset->id;

			// Check for an error.
			$error = $asset->getError();

			if ($error)
			{
				$this->setError($error);

				return false;
			}
			else
			{
				// Specify how a new or moved node asset is inserted into the tree.
				if (empty($this->asset_id) || $asset->parent_id != $parentId)
				{
					$asset->setLocation($parentId, 'last-child');
				}

				// Prepare the asset to be stored.
				$asset->parent_id = $parentId;
				$asset->name      = $name;
				$asset->title     = $title;

				if ($this->_rules instanceof JAccessRules)
				{
					$asset->rules = (string) $this->_rules;
				}

				if (!$asset->check() || !$asset->store($updateNulls))
				{
					$this->setError($asset->getError());

					return false;
				}
				else
				{
					// Create an asset_id or heal one that is corrupted.
					if (empty($this->asset_id) || ($currentAssetId != $this->asset_id && !empty($this->asset_id)))
					{
						// Update the asset_id field in this table.
						$this->asset_id = (int) $asset->id;

						$query = $this->_db->getQuery(true)
							->update($this->_db->quoteName($this->_tbl))
							->set('asset_id = ' . (int) $this->asset_id);
						$this->appendPrimaryKeys($query);
						$this->_db->setQuery($query)->execute();
					}
				}
			}
		}

		// Implement JObservableInterface: Post-processing by observers
		$this->_observers->update('onAfterStore', array(&$result));

		return $result;
	}
	
	public function hasPrimaryKey()
	{
		$query = $this->_db->getQuery(true)
		->select('COUNT(*)')
		->from($this->_tbl);
		$this->appendPrimaryKeys($query);

		$this->_db->setQuery($query);
		$count = $this->_db->loadResult();

		if ($count == 1)
		{
			$empty = false;
		}
		else
		{
			$empty = true;
		}
	
		return !$empty;
	}
}
