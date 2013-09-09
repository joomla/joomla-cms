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
 * @package     Joomla.Platform
 * @subpackage  Table
 * @link        http://docs.joomla.org/JTableObserver
 * @since       3.1.2
 */
class JTableObserverTags extends JTableObserver
{
	/**
	 * Helper object for storing and deleting tag information associated with this table observer
	 *
	 * @var    JHelperTags
	 * @since  3.1.2
	 */
	protected $tagsHelper;

	/**
	 * The pattern for this tag's TypeAlias
	 *
	 * @var    string
	 * @since  3.1.2
	 */
	protected $typeAliasPattern = null;

	/**
	 * Override for postStoreProcess param newTags, Set by setNewTagsToAdd, used by onAfterStore
	 *
	 * @var    array
	 * @since  3.1.2
	 */
	protected $newTags = array();

	/**
	 * Override for postStoreProcess param replaceTags. Set by setNewTagsToAdd, used by onAfterStore
	 *
	 * @var    boolean
	 * @since  3.1.2
	 */
	protected $replaceTags = true;

	/**
	 * Not public, so marking private and deprecated, but needed internally in parseTypeAlias for
	 * PHP < 5.4.0 as it's not passing context $this to closure function.
	 *
	 * @var         JTableObserverTags
	 * @since       3.1.2
	 * @deprecated  Never use this
	 * @private
	 */
	public static $_myTableForPregreplaceOnly;

	/**
	 * Creates the associated observer instance and attaches it to the $observableObject
	 * Creates the associated tags helper class instance
	 * $typeAlias can be of the form "{variableName}.type", automatically replacing {variableName} with table-instance variables variableName
	 *
	 * @param   JObservableInterface  $observableObject  The subject object to be observed
	 * @param   array                 $params            ( 'typeAlias' => $typeAlias )
	 *
	 * @return  JTableObserverTags
	 *
	 * @since   3.1.2
	 */
	public static function createObserver(JObservableInterface $observableObject, $params = array())
	{
		$typeAlias = $params['typeAlias'];

		$observer = new self($observableObject);

		$observer->tagsHelper = new JHelperTags;
		$observer->typeAliasPattern = $typeAlias;

		return $observer;
	}

	/**
	 * Pre-processor for $table->store($updateNulls)
	 *
	 * @param   boolean  $updateNulls  The result of the load
	 * @param   string   $tableKey     The key of the table
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public function onBeforeStore($updateNulls, $tableKey)
	{
		$this->parseTypeAlias();
		$this->tagsHelper->preStoreProcess($this->table);
	}

	/**
	 * Post-processor for $table->store($updateNulls)
	 * You can change optional params newTags and replaceTags of tagsHelper with method setNewTagsToAdd
	 *
	 * @param   boolean  &$result  The result of the load
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public function onAfterStore(&$result)
	{
		if ($result)
		{
			$result = $this->tagsHelper->postStoreProcess($this->table);

			// Restore default values for the optional params:
			$this->newTags = array();
			$this->replaceTags = true;
		}
	}

	/**
	 * Pre-processor for $table->delete($pk)
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 * @throws  UnexpectedValueException
	 */
	public function onBeforeDelete($pk)
	{
		$this->parseTypeAlias();
		$this->tagsHelper->deleteTagData($this->table, $pk);
	}

	/**
	 * Sets the new tags to be added/replaced to the table row
	 *
	 * @param   array    $newTags      New tags to be added or replaced
	 * @param   boolean  $replaceTags  Replace tags (true) or add them (false)
	 *
	 * @return  boolean
	 *
	 * @since   3.1.2
	 */
	public function setNewTags($newTags, $replaceTags)
	{
		$this->parseTypeAlias();

		return $this->tagsHelper->postStoreProcess($this->table, $newTags, $replaceTags);
	}

	/**
	 * Internal method
	 * Parses a TypeAlias of the form "{variableName}.type", replacing {variableName} with table-instance variables variableName
	 * Storing result into $this->tagsHelper->typeAlias
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	protected function parseTypeAlias()
	{
		// Needed for PHP < 5.4.0 as it's not passing context $this to closure function
		static::$_myTableForPregreplaceOnly = $this->table;

		$this->tagsHelper->typeAlias = preg_replace_callback('/{([^}]+)}/',
			function($matches)
			{
				return JTableObserverTags::$_myTableForPregreplaceOnly->{$matches[1]};
			},
			$this->typeAliasPattern
		);
	}
}
