<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  table
 * @copyright   Copyright (C) 2010 - 2015 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

/**
 * FrameworkOnFramework table behavior class for assets
 *
 * @package  FrameworkOnFramework
 * @since    2.1
 */
class FOFTableBehaviorAssets extends FOFTableBehavior
{
	/**
	 * The event which runs after storing (saving) data to the database
	 *
	 * @param   FOFTable  &$table       The table which calls this event
	 *
	 * @return  boolean  True to allow saving
	 */
	public function onAfterStore(&$table)
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

		// Asset Tracking
		if (in_array($asset_id_field, $table->getKnownFields()) && $table->isAssetsTracked())
		{
			$parentId = $table->getAssetParentId();

            try{
                $name     = $table->getAssetName();
            }
            catch(Exception $e)
            {
                $table->setError($e->getMessage());
                return false;
            }

			$title    = $table->getAssetTitle();

			$asset = JTable::getInstance('Asset');
			$asset->loadByName($name);

			// Re-inject the asset id.
			$this->$asset_id_field = $asset->id;

			// Check for an error.
			$error = $asset->getError();

            // Since we are using JTable, there is no way to mock it and test for failures :(
            // @codeCoverageIgnoreStart
			if ($error)
			{
				$table->setError($error);

				return false;
			}
            // @codeCoverageIgnoreEnd

			// Specify how a new or moved node asset is inserted into the tree.
            // Since we're unsetting the table field before, this statement is always true...
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

            // Since we are using JTable, there is no way to mock it and test for failures :(
            // @codeCoverageIgnoreStart
			if (!$asset->check() || !$asset->store())
			{
				$table->setError($asset->getError());

				return false;
			}
            // @codeCoverageIgnoreEnd

			// Create an asset_id or heal one that is corrupted.
			if (empty($table->$asset_id_field) || (($currentAssetId != $table->$asset_id_field) && !empty($table->$asset_id_field)))
			{
				// Update the asset_id field in this table.
				$table->$asset_id_field = (int) $asset->id;

				$k = $table->getKeyName();

                $db = $table->getDbo();

				$query = $db->getQuery(true)
				            ->update($db->qn($table->getTableName()))
				            ->set($db->qn($asset_id_field).' = ' . (int) $table->$asset_id_field)
				            ->where($db->qn($k) . ' = ' . (int) $table->$k);

				$db->setQuery($query)->execute();
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
                // We have to manually remove any empty value, since they will be converted to int,
                // and "Inherited" values will become "Denied". Joomla is doing this manually, too.
                // @todo Should we move this logic inside the setRules method?
                $rules = array();

                foreach ($src['rules'] as $action => $ids)
                {
                    // Build the rules array.
                    $rules[$action] = array();

                    foreach ($ids as $id => $p)
                    {
                        if ($p !== '')
                        {
                            $rules[$action][$id] = ($p == '1' || $p == 'true') ? true : false;
                        }
                    }
                }

				$table->setRules($rules);
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
            $k = $table->getKeyName();

            // If the table is not loaded, let's try to load it with the id
            if(!$table->$k)
            {
                $table->load($oid);
            }

            // If I have an invalid assetName I have to stop
            try
            {
                $name = $table->getAssetName();
            }
            catch(Exception $e)
            {
                $table->setError($e->getMessage());
                return false;
            }

			// Do NOT touch JTable here -- we are loading the core asset table which is a JTable, not a FOFTable
			$asset = JTable::getInstance('Asset');

			if ($asset->loadByName($name))
			{
                // Since we are using JTable, there is no way to mock it and test for failures :(
                // @codeCoverageIgnoreStart
				if (!$asset->delete())
				{
					$table->setError($asset->getError());

					return false;
				}
                // @codeCoverageIgnoreEnd
			}
			else
			{
                // I'll simply return true even if I couldn't load the asset. In this way I can still
                // delete a broken record
				return true;
			}
		}

		return true;
	}
}
