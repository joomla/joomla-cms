<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Model\DataModel\Behaviour;

defined('_JEXEC') || die;

use Exception;
use FOF30\Event\Observer;
use FOF30\Model\DataModel;
use Joomla\CMS\Access\Rules;
use Joomla\CMS\Table\Table;

/**
 * FOF model behavior class to add Joomla! ACL assets support
 *
 * @since    2.1
 */
class Assets extends Observer
{
	public function onAfterSave(DataModel &$model)
	{
		if (!$model->hasField('asset_id') || !$model->isAssetsTracked())
		{
			return true;
		}

		$assetFieldAlias = $model->getFieldAlias('asset_id');
		$currentAssetId  = $model->getFieldValue('asset_id');

		unset($model->$assetFieldAlias);

		// Create the object used for inserting/updating data to the database
		$fields = $model->getTableFields();

		// Let's remove the asset_id field, since we unset the property above and we would get a PHP notice
		if (isset($fields[$assetFieldAlias]))
		{
			unset($fields[$assetFieldAlias]);
		}

		// Asset Tracking
		$parentId = $model->getAssetParentId();
		$name     = $model->getAssetName();
		$title    = $model->getAssetTitle();

		$asset = Table::getInstance('Asset');
		$asset->loadByName($name);

		// Re-inject the asset id.
		$this->$assetFieldAlias = $asset->id;

		// Check for an error.
		$error = $asset->getError();

		// Since we are using JTable, there is no way to mock it and test for failures :(
		// @codeCoverageIgnoreStart
		if ($error)
		{
			throw new Exception($error);
		}
		// @codeCoverageIgnoreEnd

		// Specify how a new or moved node asset is inserted into the tree.
		// Since we're unsetting the table field before, this statement is always true...
		if (empty($model->$assetFieldAlias) || $asset->parent_id != $parentId)
		{
			$asset->setLocation($parentId, 'last-child');
		}

		// Prepare the asset to be stored.
		$asset->parent_id = $parentId;
		$asset->name      = $name;
		$asset->title     = $title;

		if ($model->getRules() instanceof Rules)
		{
			$asset->rules = (string) $model->getRules();
		}

		// Since we are using JTable, there is no way to mock it and test for failures :(
		// @codeCoverageIgnoreStart
		if (!$asset->check() || !$asset->store())
		{
			throw new Exception($asset->getError());
		}
		// @codeCoverageIgnoreEnd

		// Create an asset_id or heal one that is corrupted.
		if (empty($model->$assetFieldAlias) || (($currentAssetId != $model->$assetFieldAlias) && !empty($model->$assetFieldAlias)))
		{
			// Update the asset_id field in this table.
			$model->$assetFieldAlias = (int) $asset->id;

			$k = $model->getKeyName();

			$db = $model->getDbo();

			$query = $db->getQuery(true)
				->update($db->qn($model->getTableName()))
				->set($db->qn($assetFieldAlias) . ' = ' . (int) $model->$assetFieldAlias)
				->where($db->qn($k) . ' = ' . (int) $model->$k);

			$db->setQuery($query)->execute();
		}

		return true;
	}

	public function onAfterBind(DataModel &$model, &$src)
	{
		if (!$model->isAssetsTracked())
		{
			return true;
		}

		$rawRules = [];

		if (is_array($src) && array_key_exists('rules', $src) && is_array($src['rules']))
		{
			$rawRules = $src['rules'];
		}
		elseif (is_object($src) && isset($src->rules) && is_array($src->rules))
		{
			$rawRules = $src->rules;
		}

		if (empty($rawRules))
		{
			return true;
		}

		// Bind the rules.
		if (isset($rawRules) && is_array($rawRules))
		{
			// We have to manually remove any empty value, since they will be converted to int,
			// and "Inherited" values will become "Denied". Joomla is doing this manually, too.
			$rules = [];

			foreach ($rawRules as $action => $ids)
			{
				// Build the rules array.
				$rules[$action] = [];

				foreach ($ids as $id => $p)
				{
					if ($p !== '')
					{
						$rules[$action][$id] = ($p == '1' || $p == 'true') ? true : false;
					}
				}
			}

			$model->setRules($rules);
		}

		return true;
	}

	public function onBeforeDelete(DataModel &$model, $oid)
	{
		if (!$model->isAssetsTracked())
		{
			return true;
		}

		$k = $model->getKeyName();

		// If the table is not loaded, let's try to load it with the id
		if (!$model->$k)
		{
			$model->load($oid);
		}

		// If I have an invalid assetName I have to stop
		$name = $model->getAssetName();

		// Do NOT touch JTable here -- we are loading the core asset table which is a JTable, not a FOF Table
		$asset = Table::getInstance('Asset');

		if ($asset->loadByName($name))
		{
			// Since we are using JTable, there is no way to mock it and test for failures :(
			// @codeCoverageIgnoreStart
			if (!$asset->delete())
			{
				throw new Exception($asset->getError());
			}
			// @codeCoverageIgnoreEnd
		}

		return true;
	}
}
