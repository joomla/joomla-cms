<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  table
 * @copyright   Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

/**
 * FrameworkOnFramework table behavior class for tags
 *
 * @package  FrameworkOnFramework
 * @since    2.1
 */
class FOFTableBehaviorTags extends FOFTableBehavior
{
	/**
	 * The event which runs after binding data to the table
	 *
	 * @param   FOFTable  		&$table  	The table which calls this event
	 * @param   object|array  	&$src  		The data to bind
	 * @param  	array 			$options 	The options of the table
	 *
	 * @return  boolean  True on success
	 */
	public function onAfterBind(&$table, &$src, $options = array())
	{
		// Bind tags
		if ($table->hasTags())
		{
			if ((!empty($src['tags']) && $src['tags'][0] != ''))
			{
				$table->newTags = $src['tags'];
			}

			// Check if the content type exists, and create it if it does not
			$table->checkContentType();

			$tagsTable = clone($table);

			$tagsHelper = new JHelperTags();
			$tagsHelper->typeAlias = $table->getContentType();

			// TODO: This little guy here fails because JHelperTags
			// need a JTable object to work, while our is FOFTable
			// Need probably to write our own FOFHelperTags
			// Thank you com_tags
			if (!$tagsHelper->postStoreProcess($tagsTable))
			{
				$table->setError('Error storing tags');
				return false;
			}
		}

		return true;
	}

	/**
	 * The event which runs before storing (saving) data to the database
	 *
	 * @param   FOFTable  &$table  The table which calls this event
	 * @param   boolean  $updateNulls  Should nulls be saved as nulls (true) or just skipped over (false)?
	 *
	 * @return  boolean  True to allow saving
	 */
	public function onBeforeStore(&$table, $updateNulls)
	{
		if ($table->hasTags())
		{
			$tagsHelper = new JHelperTags();
			$tagsHelper->typeAlias = $table->getContentType();

			// TODO: JHelperTags sucks in Joomla! 3.1, it requires that tags are
			// stored in the metadata property. Not our case, therefore we need
			// to add it in a fake object. We sent a PR to Joomla! CMS to fix
			// that. Once it's accepted, we'll have to remove the attrocity
			// here...
			$tagsTable = clone($table);
			$tagsHelper->preStoreProcess($tagsTable);
		}
	}

	/**
	 * The event which runs after deleting a record
	 *
	 * @param   FOFTable &$table  The table which calls this event
	 * @param   integer  $oid  The PK value of the record which was deleted
	 *
	 * @return  boolean  True to allow the deletion without errors
	 */
	public function onAfterDelete(&$table, $oid)
	{
		// If this resource has tags, delete the tags first
		if ($table->hasTags())
		{
			$tagsHelper = new JHelperTags();
			$tagsHelper->typeAlias = $table->getContentType();

			if (!$tagsHelper->deleteTagData($table, $oid))
			{
				$table->setError('Error deleting Tags');
				return false;
			}
		}
	}
}
