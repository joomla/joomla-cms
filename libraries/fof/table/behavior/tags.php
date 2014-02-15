<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  table
 * @copyright   Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('_JEXEC') or die;

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
			$this->checkContentType($table, $options);

			$tagsTable = clone($table);

			$tagsHelper = new JHelperTags();
			$tagsHelper->typeAlias = $table->getAssetKey();

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
			$tagsHelper->typeAlias = $table->getAssetKey();

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
			$tagsHelper->typeAlias = $table->getAssetKey();

			if (!$tagsHelper->deleteTagData($table, $oid))
			{
				$table->setError('Error deleting Tags');
				return false;
			}
		}
	}

	/**
	 * Check if a UCM content type exists for this resource, and
	 * create it if it does not
	 */
	protected function checkContentType(&$table, $options)
	{
		$contentType = new JTableContenttype($table->getDbo());

		$alias = $table->getContentType();

		// Fetch the extension name
		$component = $options['component'];
		$component = JComponentHelper::getComponent($component);

		// Fetch the name using the menu item
		$query = $table->getDbo()->getQuery(true);
		$query->select('title')->from('#__menu')->where('component_id = ' . (int) $component->id);
		$table->getDbo()->setQuery($query);
		$component_name = JText::_($table->getDbo()->loadResult());

		$name = $component_name . ' ' . ucfirst($options['view']);

		// Create a new content type for our resource
		if (!$contentType->load(array('type_alias' => $alias)))
		{
			$contentType->type_title = $name;
			$contentType->type_alias = $alias;
			$contentType->table = json_encode(
				array(
					'special' => array(
						'dbtable' => $table->getTableName(),
						'key'     => $table->getKeyName(),
						'type'    => $name,
						'prefix'  => $options['table_prefix'],
						'config'  => 'array()'
					),
					'common' => array(
						'dbtable' => '#__ucm_content',
						'key' => 'ucm_id',
						'type' => 'CoreContent',
						'prefix' => 'JTable',
						'config' => 'array()'
					)
				)
			);

			$contentType->field_mappings = json_encode(
				array(
					'common' => array(
						0 => array(
							"core_content_item_id" => $table->getKeyName(),
							"core_title"           => $this->getUcmCoreAlias($table, 'title'),
							"core_state"           => $this->getUcmCoreAlias($table, 'enabled'),
							"core_alias"           => $this->getUcmCoreAlias($table, 'alias'),
							"core_created_time"    => $this->getUcmCoreAlias($table, 'created_on'),
							"core_modified_time"   => $this->getUcmCoreAlias($table, 'created_by'),
							"core_body"            => $this->getUcmCoreAlias($table, 'body'),
							"core_hits"            => $this->getUcmCoreAlias($table, 'hits'),
							"core_publish_up"      => $this->getUcmCoreAlias($table, 'publish_up'),
							"core_publish_down"    => $this->getUcmCoreAlias($table, 'publish_down'),
							"core_access"          => $this->getUcmCoreAlias($table, 'access'),
							"core_params"          => $this->getUcmCoreAlias($table, 'params'),
							"core_featured"        => $this->getUcmCoreAlias($table, 'featured'),
							"core_metadata"        => $this->getUcmCoreAlias($table, 'metadata'),
							"core_language"        => $this->getUcmCoreAlias($table, 'language'),
							"core_images"          => $this->getUcmCoreAlias($table, 'images'),
							"core_urls"            => $this->getUcmCoreAlias($table, 'urls'),
							"core_version"         => $this->getUcmCoreAlias($table, 'version'),
							"core_ordering"        => $this->getUcmCoreAlias($table, 'ordering'),
							"core_metakey"         => $this->getUcmCoreAlias($table, 'metakey'),
							"core_metadesc"        => $this->getUcmCoreAlias($table, 'metadesc'),
							"core_catid"           => $this->getUcmCoreAlias($table, 'cat_id'),
							"core_xreference"      => $this->getUcmCoreAlias($table, 'xreference'),
							"asset_id"             => $this->getUcmCoreAlias($table, 'asset_id')
						)
					),
					'special' => array(
						0 => array(
						)
					)
				)
			);

			$contentType->router = '';

			$contentType->store();
		}
	}

	/**
	 * Utility methods that fetches the column name for the field.
	 * If it does not exists, returns a "null" string
	 *
	 * @return string The column name
	 */
	protected function getUcmCoreAlias($table, $alias)
	{
		$alias = $table->getColumnAlias($alias);

		if (in_array($alias, $table->getKnownFields()))
		{
			return $alias;
		}

		return "null";
	}
}