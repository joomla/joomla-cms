<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Table class supporting modified pre-order tree traversal behavior.
 *
 * @package     Joomla
 * @subpackage  Table
 * @link        http://docs.joomla.org/JTableObserver
 * @since       3.1.2
 */
class JTableObserverTags extends JTableObserver
{
	/**
	 * Helper object for storing and deleting tag information associated with this table observer
	 *
	 * @var  JHelperTags
	 */
	protected $tagsHelper;

	/**
	 * Pre-processor for $table->store($updateNulls)
	 *
	 * @param   boolean   $updateNulls   The result of the load
	 * @param   string    $tableKey      The key of the table
	 *
	 * @return  void
	 */
	public function onBeforeStore($updateNulls, $tableKey)
	{
		$this->tagsHelper->preStoreProcess($this->table);
	}

	/**
	 * Post-processor for $table->store($updateNulls)
	 *
	 * @param   boolean   $result   The result of the load
	 */
	public function onAfterStore(&$result)
	{
		if ($result)
		{
			$this->tagsHelper->postStoreProcess($this->table);
		}
	}

	/**
	 * Pre-processor for $table->delete($pk)
	 *
	 * @param   mixed    $pk         An optional primary key value to delete.  If not set the instance property value is used.
	 * @param   string   $tableKey   The normal key of the table
	 *
	 * @return  void
	 *
	 * @throws  UnexpectedValueException
	 */
	public function onBeforeDelete($pk, $tableKey)
	{
		$this->tagsHelper->deleteTagData($this->table, $pk);
	}

	/**
	 * Creates the associated tags helper class instance
	 *
	 * @param   JTable   $table
	 * @param   string   $typeAlias   The type alias (null if set after initial binding like in categories)
	 *
	 * @return  JTableObserverTags
	 */
	public static function observeTableWithTagsHelperOfTypeAlias($table, $typeAlias)
	{
		$observer = new self($table);
		$observer->tagsHelper = new JHelperTags;
		$observer->tagsHelper->typeAlias = $typeAlias;
		return $observer;
	}
}
