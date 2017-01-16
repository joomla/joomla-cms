<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Tags helper class, provides methods to perform various tasks relevant
 * tagging of content.
 *
 * @since  3.1
 */
class JHelperTags extends JHelper
{
	/**
	 * Helper object for storing and deleting tag information.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $tagsChanged = false;

	/**
	 * Whether up replace all tags or just add tags
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $replaceTags = false;

	/**
	 * Alias for querying mapping and content type table.
	 *
	 * @var    string
	 * @since  3.1
	 */
	public $typeAlias = null;

	/**
	 * Method to add tag rows to mapping table.
	 *
	 * @param   integer          $ucmId  ID of the #__ucm_content item being tagged
	 * @param   JTableInterface  $table  JTable object being tagged
	 * @param   array            $tags   Array of tags to be applied.
	 *
	 * @return  boolean  true on success, otherwise false.
	 *
	 * @since   3.1
	 */
	public function addTagMapping($ucmId, JTableInterface $table, $tags = array())
	{
		$db = $table->getDbo();
		$key = $table->getKeyName();
		$item = $table->$key;
		$typeId = $this->getTypeId($this->typeAlias);

		// Insert the new tag maps
		if (strpos('#', implode(',', $tags)) === false)
		{
			$tags = self::createTagsFromField($tags);
		}

		// Prevent saving duplicate tags
		$tags = array_unique($tags);

		$query = $db->getQuery(true);
		$query->insert('#__contentitem_tag_map');
		$query->columns(
			array(
				$db->quoteName('type_alias'),
				$db->quoteName('core_content_id'),
				$db->quoteName('content_item_id'),
				$db->quoteName('tag_id'),
				$db->quoteName('tag_date'),
				$db->quoteName('type_id'),
			)
		);

		foreach ($tags as $tag)
		{
			$query->values(
				$db->quote($this->typeAlias)
				. ', ' . (int) $ucmId
				. ', ' . (int) $item
				. ', ' . $db->quote($tag)
				. ', ' . $query->currentTimestamp()
				. ', ' . (int) $typeId
			);
		}

		$db->setQuery($query);

		return (boolean) $db->execute();
	}

	/**
	 * Function that converts tags paths into paths of names
	 *
	 * @param   array  $tags  Array of tags
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public static function convertPathsToNames($tags)
	{
		// We will replace path aliases with tag names
		if ($tags)
		{
			// Create an array with all the aliases of the results
			$aliases = array();

			foreach ($tags as $tag)
			{
				if (!empty($tag->path))
				{
					if ($pathParts = explode('/', $tag->path))
					{
						$aliases = array_merge($aliases, $pathParts);
					}
				}
			}

			// Get the aliases titles in one single query and map the results
			if ($aliases)
			{
				// Remove duplicates
				$aliases = array_unique($aliases);

				$db = JFactory::getDbo();

				$query = $db->getQuery(true)
					->select('alias, title')
					->from('#__tags')
					->where('alias IN (' . implode(',', array_map(array($db, 'quote'), $aliases)) . ')');
				$db->setQuery($query);

				try
				{
					$aliasesMapper = $db->loadAssocList('alias');
				}
				catch (RuntimeException $e)
				{
					return false;
				}

				// Rebuild the items path
				if ($aliasesMapper)
				{
					foreach ($tags as $tag)
					{
						$namesPath = array();

						if (!empty($tag->path))
						{
							if ($pathParts = explode('/', $tag->path))
							{
								foreach ($pathParts as $alias)
								{
									if (isset($aliasesMapper[$alias]))
									{
										$namesPath[] = $aliasesMapper[$alias]['title'];
									}
									else
									{
										$namesPath[] = $alias;
									}
								}

								$tag->text = implode('/', $namesPath);
							}
						}
					}
				}
			}
		}

		return $tags;
	}

	/**
	 * Create any new tags by looking for #new# in the strings
	 *
	 * @param   array  $tags  Tags text array from the field
	 *
	 * @return  mixed   If successful, metadata with new tag titles replaced by tag ids. Otherwise false.
	 *
	 * @since   3.1
	 */
	public function createTagsFromField($tags)
	{
		if (empty($tags) || $tags[0] == '')
		{
			return;
		}
		else
		{
			// We will use the tags table to store them
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tags/tables');
			$tagTable  = JTable::getInstance('Tag', 'TagsTable');
			$newTags   = array();
			$canCreate = JFactory::getUser()->authorise('core.create', 'com_tags');

			foreach ($tags as $key => $tag)
			{
				// User is not allowed to create tags, so don't create.
				if (strpos($tag, '#new#') !== false && !$canCreate)
				{
					continue;
				}

				// Remove the #new# prefix that identifies new tags
				$tagText = str_replace('#new#', '', $tag);

				if ($tagText == $tag)
				{
					$newTags[] = (int) $tag;
				}
				else
				{
					// Clear old data if exist
					$tagTable->reset();

					// Try to load the selected tag
					if ($tagTable->load(array('title' => $tagText)))
					{
						$newTags[] = (int) $tagTable->id;
					}
					else
					{
						// Prepare tag data
						$tagTable->id = 0;
						$tagTable->title = $tagText;
						$tagTable->published = 1;

						// $tagTable->language = property_exists ($item, 'language') ? $item->language : '*';
						$tagTable->language = '*';
						$tagTable->access = 1;

						// Make this item a child of the root tag
						$tagTable->setLocation($tagTable->getRootId(), 'last-child');

						// Try to store tag
						if ($tagTable->check())
						{
							// Assign the alias as path (autogenerated tags have always level 1)
							$tagTable->path = $tagTable->alias;

							if ($tagTable->store())
							{
								$newTags[] = (int) $tagTable->id;
							}
						}
					}
				}
			}

			// At this point $tags is an array of all tag ids
			$this->tags = $newTags;
			$result = $newTags;
		}

		return $result;
	}

	/**
	 * Create any new tags by looking for #new# in the metadata
	 *
	 * @param   string  $metadata  Metadata JSON string
	 *
	 * @return  mixed   If successful, metadata with new tag titles replaced by tag ids. Otherwise false.
	 *
	 * @since   3.1
	 * @deprecated  4.0  This method is no longer used in the CMS and will not be replaced.
	 */
	public function createTagsFromMetadata($metadata)
	{
		$metaObject = json_decode($metadata);

		if (empty($metaObject->tags))
		{
			return $metadata;
		}

		$tags = $metaObject->tags;

		if (empty($tags) || !is_array($tags))
		{
			$result = $metadata;
		}
		else
		{
			// We will use the tags table to store them
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tags/tables');
			$tagTable = JTable::getInstance('Tag', 'TagsTable');
			$newTags = array();

			foreach ($tags as $tag)
			{
				// Remove the #new# prefix that identifies new tags
				$tagText = str_replace('#new#', '', $tag);

				if ($tagText == $tag)
				{
					$newTags[] = (int) $tag;
				}
				else
				{
					// Clear old data if exist
					$tagTable->reset();

					// Try to load the selected tag
					if ($tagTable->load(array('title' => $tagText)))
					{
						$newTags[] = (int) $tagTable->id;
					}
					else
					{
						// Prepare tag data
						$tagTable->id = 0;
						$tagTable->title = $tagText;
						$tagTable->published = 1;

						// $tagTable->language = property_exists ($item, 'language') ? $item->language : '*';
						$tagTable->language = '*';
						$tagTable->access = 1;

						// Make this item a child of the root tag
						$tagTable->setLocation($tagTable->getRootId(), 'last-child');

						// Try to store tag
						if ($tagTable->check())
						{
							// Assign the alias as path (autogenerated tags have always level 1)
							$tagTable->path = $tagTable->alias;

							if ($tagTable->store())
							{
								$newTags[] = (int) $tagTable->id;
							}
						}
					}
				}
			}

			// At this point $tags is an array of all tag ids
			$metaObject->tags = $newTags;
			$result = json_encode($metaObject);
		}

		return $result;
	}

	/**
	 * Method to delete the tag mappings and #__ucm_content record for for an item
	 *
	 * @param   JTableInterface  $table          JTable object of content table where delete occurred
	 * @param   integer|array    $contentItemId  ID of the content item. Or an array of key/value pairs with array key
	 *                                           being a primary key name and value being the content item ID. Note
	 *                                           multiple primary keys are not supported
	 *
	 * @return  boolean  true on success, false on failure
	 *
	 * @since   3.1
	 * @throws  InvalidArgumentException
	 */
	public function deleteTagData(JTableInterface $table, $contentItemId)
	{
		$key = $table->getKeyName();

		if (!is_array($contentItemId))
		{
			$contentItemId = array($key => $contentItemId);
		}

		// If we have multiple items for the content item primary key we currently don't support this so
		// throw an InvalidArgumentException for now
		if (count($contentItemId) != 1)
		{
			throw new InvalidArgumentException('Multiple primary keys are not supported as a content item id');
		}

		$result = $this->unTagItem($contentItemId[$key], $table);

		/** @var JTableCorecontent $ucmContentTable */
		$ucmContentTable = JTable::getInstance('Corecontent');

		return $result && $ucmContentTable->deleteByContentId($contentItemId[$key], $this->typeAlias);
	}

	/**
	 * Method to get a list of tags for an item, optionally with the tag data.
	 *
	 * @param   string   $contentType  Content type alias. Dot separated.
	 * @param   integer  $id           Id of the item to retrieve tags for.
	 * @param   boolean  $getTagData   If true, data from the tags table will be included, defaults to true.
	 *
	 * @return  array    Array of of tag objects
	 *
	 * @since   3.1
	 */
	public function getItemTags($contentType, $id, $getTagData = true)
	{
		// Initialize some variables.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('m.tag_id'))
			->from($db->quoteName('#__contentitem_tag_map') . ' AS m ')
			->where(
				array(
					$db->quoteName('m.type_alias') . ' = ' . $db->quote($contentType),
					$db->quoteName('m.content_item_id') . ' = ' . (int) $id,
					$db->quoteName('t.published') . ' = 1',
				)
			);

		$user = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());

		$query->where('t.access IN (' . $groups . ')');

		// Optionally filter on language
		$language = JComponentHelper::getParams('com_tags')->get('tag_list_language_filter', 'all');

		if ($language != 'all')
		{
			if ($language == 'current_language')
			{
				$language = $this->getCurrentLanguage();
			}

			$query->where($db->quoteName('language') . ' IN (' . $db->quote($language) . ', ' . $db->quote('*') . ')');
		}

		if ($getTagData)
		{
			$query->select($db->quoteName('t') . '.*');
		}

		$query->join('INNER', $db->quoteName('#__tags') . ' AS t ' . ' ON ' . $db->quoteName('m.tag_id') . ' = ' . $db->quoteName('t.id'));

		$db->setQuery($query);
		$this->itemTags = $db->loadObjectList();

		return $this->itemTags;
	}

	/**
	 * Method to get a list of tags for a given item.
	 * Normally used for displaying a list of tags within a layout
	 *
	 * @param   mixed   $ids     The id or array of ids (primary key) of the item to be tagged.
	 * @param   string  $prefix  Dot separated string with the option and view to be used for a url.
	 *
	 * @return  string   Comma separated list of tag Ids.
	 *
	 * @since   3.1
	 */
	public function getTagIds($ids, $prefix)
	{
		if (empty($ids))
		{
			return;
		}

		/**
		 * Ids possible formats:
		 * ---------------------
		 * 	$id = 1;
		 *  $id = array(1,2);
		 *  $id = array('1,3,4,19');
		 *  $id = '1,3';
		 */
		$ids = (array) $ids;
		$ids = implode(',', $ids);
		$ids = explode(',', $ids);
		$ids = ArrayHelper::toInteger($ids);

		$db = JFactory::getDbo();

		// Load the tags.
		$query = $db->getQuery(true)
			->select($db->quoteName('t.id'))
			->from($db->quoteName('#__tags') . ' AS t ')
			->join(
				'INNER', $db->quoteName('#__contentitem_tag_map') . ' AS m'
				. ' ON ' . $db->quoteName('m.tag_id') . ' = ' . $db->quoteName('t.id')
				. ' AND ' . $db->quoteName('m.type_alias') . ' = ' . $db->quote($prefix)
				. ' AND ' . $db->quoteName('m.content_item_id') . ' IN ( ' . implode(',', $ids) . ')'
			);

		$db->setQuery($query);

		// Add the tags to the content data.
		$tagsList = $db->loadColumn();
		$this->tags = implode(',', $tagsList);

		return $this->tags;
	}

	/**
	 * Method to get a query to retrieve a detailed list of items for a tag.
	 *
	 * @param   mixed    $tagId            Tag or array of tags to be matched
	 * @param   mixed    $typesr           Null, type or array of type aliases for content types to be included in the results
	 * @param   boolean  $includeChildren  True to include the results from child tags
	 * @param   string   $orderByOption    Column to order the results by
	 * @param   string   $orderDir         Direction to sort the results in
	 * @param   boolean  $anyOrAll         True to include items matching at least one tag, false to include
	 *                                     items all tags in the array.
	 * @param   string   $languageFilter   Optional filter on language. Options are 'all', 'current' or any string.
	 * @param   string   $stateFilter      Optional filtering on publication state, defaults to published or unpublished.
	 *
	 * @return  JDatabaseQuery  Query to retrieve a list of tags
	 *
	 * @since   3.1
	 */
	public function getTagItemsQuery($tagId, $typesr = null, $includeChildren = false, $orderByOption = 'c.core_title', $orderDir = 'ASC',
		$anyOrAll = true, $languageFilter = 'all', $stateFilter = '0,1')
	{
		// Create a new query object.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$user = JFactory::getUser();
		$nullDate = $db->quote($db->getNullDate());
		$nowDate = $db->quote(JFactory::getDate()->toSql());

		$ntagsr = substr_count($tagId, ',') + 1;

		// Force ids to array and sanitize
		$tagIds = (array) $tagId;
		$tagIds = implode(',', $tagIds);
		$tagIds = explode(',', $tagIds);
		$tagIds = ArrayHelper::toInteger($tagIds);

		// If we want to include children we have to adjust the list of tags.
		// We do not search child tags when the match all option is selected.
		if ($includeChildren)
		{
			$tagTreeArray = array();

			foreach ($tagIds as $tag)
			{
				$this->getTagTreeArray($tag, $tagTreeArray);
			}

			$tagIds = array_unique(array_merge($tagIds, $tagTreeArray));
		}

		// Sanitize filter states
		$stateFilters = explode(',', $stateFilter);
		$stateFilters = ArrayHelper::toInteger($stateFilters);

		// M is the mapping table. C is the core_content table. Ct is the content_types table.
		$query
			->select(
				'm.type_alias'
				. ', ' . 'm.content_item_id'
				. ', ' . 'm.core_content_id'
				. ', ' . 'count(m.tag_id) AS match_count'
				. ', ' . 'MAX(m.tag_date) as tag_date'
				. ', ' . 'MAX(c.core_title) AS core_title'
				. ', ' . 'MAX(c.core_params) AS core_params'
			)
			->select('MAX(c.core_alias) AS core_alias, MAX(c.core_body) AS core_body, MAX(c.core_state) AS core_state, MAX(c.core_access) AS core_access')
			->select(
				'MAX(c.core_metadata) AS core_metadata'
				. ', ' . 'MAX(c.core_created_user_id) AS core_created_user_id'
				. ', ' . 'MAX(c.core_created_by_alias) AS core_created_by_alias'
			)
			->select('MAX(c.core_created_time) as core_created_time, MAX(c.core_images) as core_images')
			->select('CASE WHEN c.core_modified_time = ' . $nullDate . ' THEN c.core_created_time ELSE c.core_modified_time END as core_modified_time')
			->select('MAX(c.core_language) AS core_language, MAX(c.core_catid) AS core_catid')
			->select('MAX(c.core_publish_up) AS core_publish_up, MAX(c.core_publish_down) as core_publish_down')
			->select('MAX(ct.type_title) AS content_type_title, MAX(ct.router) AS router')

			->from('#__contentitem_tag_map AS m')
			->join(
				'INNER',
				'#__ucm_content AS c ON m.type_alias = c.core_type_alias AND m.core_content_id = c.core_content_id AND c.core_state IN ('
					. implode(',', $stateFilters) . ')'
					. (in_array('0', $stateFilters) ? '' : ' AND (c.core_publish_up = ' . $nullDate
					. ' OR c.core_publish_up <= ' . $nowDate . ') '
					. ' AND (c.core_publish_down = ' . $nullDate . ' OR  c.core_publish_down >= ' . $nowDate . ')')
			)
			->join('INNER', '#__content_types AS ct ON ct.type_alias = m.type_alias')

			// Join over categoris for get only published
			->join('INNER', '#__categories AS tc ON tc.id = c.core_catid AND tc.published = 1')

			// Join over the users for the author and email
			->select("CASE WHEN c.core_created_by_alias > ' ' THEN c.core_created_by_alias ELSE ua.name END AS author")
			->select('ua.email AS author_email')

			->join('LEFT', '#__users AS ua ON ua.id = c.core_created_user_id')

			->where('m.tag_id IN (' . implode(',', $tagIds) . ')');

		// Optionally filter on language
		if (empty($language))
		{
			$language = $languageFilter;
		}

		if ($language != 'all')
		{
			if ($language == 'current_language')
			{
				$language = $this->getCurrentLanguage();
			}

			$query->where($db->quoteName('c.core_language') . ' IN (' . $db->quote($language) . ', ' . $db->quote('*') . ')');
		}

		// Get the type data, limited to types in the request if there are any specified.
		$typesarray = self::getTypes('assocList', $typesr, false);

		$typeAliases = array();

		foreach ($typesarray as $type)
		{
			$typeAliases[] = $db->quote($type['type_alias']);
		}

		$query->where('m.type_alias IN (' . implode(',', $typeAliases) . ')');

		$groups = '0,' . implode(',', array_unique($user->getAuthorisedViewLevels()));
		$query->where('c.core_access IN (' . $groups . ')')
			->group('m.type_alias, m.content_item_id, m.core_content_id, core_modified_time, core_created_time, core_created_by_alias, name, author_email');

		// Use HAVING if matching all tags and we are matching more than one tag.
		if ($ntagsr > 1 && $anyOrAll != 1 && $includeChildren != 1)
		{
			// The number of results should equal the number of tags requested.
			$query->having("COUNT('m.tag_id') = " . (int) $ntagsr);
		}

		// Set up the order by using the option chosen
		if ($orderByOption == 'match_count')
		{
			$orderBy = 'COUNT(m.tag_id)';
		}
		else
		{
			$orderBy = 'MAX(' . $db->quoteName($orderByOption) . ')';
		}

		$query->order($orderBy . ' ' . $orderDir);

		return $query;
	}

	/**
	 * Function that converts tag ids to their tag names
	 *
	 * @param   array  $tagIds  Array of integer tag ids.
	 *
	 * @return  array  An array of tag names.
	 *
	 * @since   3.1
	 */
	public function getTagNames($tagIds)
	{
		$tagNames = array();

		if (is_array($tagIds) && count($tagIds) > 0)
		{
			$tagIds = ArrayHelper::toInteger($tagIds);

			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('title'))
				->from($db->quoteName('#__tags'))
				->where($db->quoteName('id') . ' IN (' . implode(',', $tagIds) . ')');
			$query->order($db->quoteName('title'));

			$db->setQuery($query);
			$tagNames = $db->loadColumn();
		}

		return $tagNames;
	}

	/**
	 * Method to get an array of tag ids for the current tag and its children
	 *
	 * @param   integer  $id             An optional ID
	 * @param   array    &$tagTreeArray  Array containing the tag tree
	 *
	 * @return  mixed
	 *
	 * @since   3.1
	 */
	public function getTagTreeArray($id, &$tagTreeArray = array())
	{
		// Get a level row instance.
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tags/tables');
		$table = JTable::getInstance('Tag', 'TagsTable');

		if ($table->isLeaf($id))
		{
			$tagTreeArray[] = $id;

			return $tagTreeArray;
		}

		$tagTree = $table->getTree($id);

		// Attempt to load the tree
		if ($tagTree)
		{
			foreach ($tagTree as $tag)
			{
				$tagTreeArray[] = $tag->id;
			}

			return $tagTreeArray;
		}
	}

	/**
	 * Method to get the type id for a type alias.
	 *
	 * @param   string  $typeAlias  A type alias.
	 *
	 * @return  string  Name of the table for a type
	 *
	 * @since   3.1
	 * @deprecated  4.0  Use JUcmType::getTypeId() instead
	 */
	public function getTypeId($typeAlias)
	{
		$contentType = new JUcmType;

		return $contentType->getTypeId($typeAlias);
	}

	/**
	 * Method to get a list of types with associated data.
	 *
	 * @param   string   $arrayType    Optionally specify that the returned list consist of objects, associative arrays, or arrays.
	 *                                 Options are: rowList, assocList, and objectList
	 * @param   array    $selectTypes  Optional array of type ids to limit the results to. Often from a request.
	 * @param   boolean  $useAlias     If true, the alias is used to match, if false the type_id is used.
	 *
	 * @return  array   Array of of types
	 *
	 * @since   3.1
	 */
	public static function getTypes($arrayType = 'objectList', $selectTypes = null, $useAlias = true)
	{
		// Initialize some variables.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('*');

		if (!empty($selectTypes))
		{
			$selectTypes = (array) $selectTypes;

			if ($useAlias)
			{
				$selectTypes = array_map(array($db, 'quote'), $selectTypes);

				$query->where($db->quoteName('type_alias') . ' IN (' . implode(',', $selectTypes) . ')');
			}
			else
			{
				$selectTypes = ArrayHelper::toInteger($selectTypes);

				$query->where($db->quoteName('type_id') . ' IN (' . implode(',', $selectTypes) . ')');
			}
		}

		$query->from($db->quoteName('#__content_types'));

		$db->setQuery($query);

		switch ($arrayType)
		{
			case 'assocList':
				$types = $db->loadAssocList();
				break;

			case 'rowList':
				$types = $db->loadRowList();
				break;

			case 'objectList':
			default:
				$types = $db->loadObjectList();
				break;
		}

		return $types;
	}

	/**
	 * Function that handles saving tags used in a table class after a store()
	 *
	 * @param   JTableInterface  $table    JTable being processed
	 * @param   array            $newTags  Array of new tags
	 * @param   boolean          $replace  Flag indicating if all exising tags should be replaced
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	public function postStoreProcess(JTableInterface $table, $newTags = array(), $replace = true)
	{
		if (!empty($table->newTags) && empty($newTags))
		{
			$newTags = $table->newTags;
		}

		// If existing row, check to see if tags have changed.
		$newTable = clone $table;
		$newTable->reset();

		$result = true;

		// Process ucm_content and ucm_base if either tags have changed or we have some tags.
		if ($this->tagsChanged || (!empty($newTags) && $newTags[0] != ''))
		{
			if (!$newTags && $replace == true)
			{
				// Delete all tags data
				$key = $table->getKeyName();
				$result = $this->deleteTagData($table, $table->$key);
			}
			else
			{
				// Process the tags
				$data = $this->getRowData($table);
				$ucmContentTable = JTable::getInstance('Corecontent');

				$ucm = new JUcmContent($table, $this->typeAlias);
				$ucmData = $data ? $ucm->mapData($data) : $ucm->ucmData;

				$primaryId = $ucm->getPrimaryKey($ucmData['common']['core_type_id'], $ucmData['common']['core_content_item_id']);
				$result = $ucmContentTable->load($primaryId);
				$result = $result && $ucmContentTable->bind($ucmData['common']);
				$result = $result && $ucmContentTable->check();
				$result = $result && $ucmContentTable->store();
				$ucmId = $ucmContentTable->core_content_id;

				// Store the tag data if the article data was saved and run related methods.
				$result = $result && $this->tagItem($ucmId, $table, $newTags, $replace);
			}
		}

		return $result;
	}

	/**
	 * Function that preProcesses data from a table prior to a store() to ensure proper tag handling
	 *
	 * @param   JTableInterface  $table    JTable being processed
	 * @param   array            $newTags  Array of new tags
	 *
	 * @return  null
	 *
	 * @since   3.1
	 */
	public function preStoreProcess(JTableInterface $table, $newTags = array())
	{
		if ($newTags != array())
		{
			$this->newTags = $newTags;
		}

		// If existing row, check to see if tags have changed.
		$oldTable = clone $table;
		$oldTable->reset();
		$key = $oldTable->getKeyName();
		$typeAlias = $this->typeAlias;

		if ($oldTable->$key && $oldTable->load())
		{
			$this->oldTags = $this->getTagIds($oldTable->$key, $typeAlias);
		}

		// New items with no tags bypass this step.
		if ((!empty($newTags) && is_string($newTags) || (isset($newTags[0]) && $newTags[0] != '')) || isset($this->oldTags))
		{
			if (is_array($newTags))
			{
				$newTags = implode(',', $newTags);
			}
			// We need to process tags if the tags have changed or if we have a new row
			$this->tagsChanged = (empty($this->oldTags) && !empty($newTags)) ||(!empty($this->oldTags) && $this->oldTags != $newTags) || !$table->$key;
		}
	}

	/**
	 * Function to search tags
	 *
	 * @param   array  $filters  Filter to apply to the search
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public static function searchTags($filters = array())
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('a.id AS value')
			->select('a.path AS text')
			->select('a.path')
			->from('#__tags AS a')
			->join('LEFT', $db->quoteName('#__tags', 'b') . ' ON a.lft > b.lft AND a.rgt < b.rgt');

		// Filter language
		if (!empty($filters['flanguage']))
		{
			$query->where('a.language IN (' . $db->quote($filters['flanguage']) . ',' . $db->quote('*') . ') ');
		}

		// Do not return root
		$query->where($db->quoteName('a.alias') . ' <> ' . $db->quote('root'));

		// Search in title or path
		if (!empty($filters['like']))
		{
			$query->where(
				'(' . $db->quoteName('a.title') . ' LIKE ' . $db->quote('%' . $filters['like'] . '%')
					. ' OR ' . $db->quoteName('a.path') . ' LIKE ' . $db->quote('%' . $filters['like'] . '%') . ')'
			);
		}

		// Filter title
		if (!empty($filters['title']))
		{
			$query->where($db->quoteName('a.title') . ' = ' . $db->quote($filters['title']));
		}

		// Filter on the published state
		if (isset($filters['published']) && is_numeric($filters['published']))
		{
			$query->where('a.published = ' . (int) $filters['published']);
		}

		// Filter by parent_id
		if (!empty($filters['parent_id']))
		{
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tags/tables');
			$tagTable = JTable::getInstance('Tag', 'TagsTable');

			if ($children = $tagTable->getTree($filters['parent_id']))
			{
				foreach ($children as $child)
				{
					$childrenIds[] = $child->id;
				}

				$query->where('a.id IN (' . implode(',', $childrenIds) . ')');
			}
		}

		$query->group('a.id, a.title, a.level, a.lft, a.rgt, a.parent_id, a.published, a.path')
			->order('a.lft ASC');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$results = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			return array();
		}

		// We will replace path aliases with tag names
		return self::convertPathsToNames($results);
	}

	/**
	 * Method to delete all instances of a tag from the mapping table. Generally used when a tag is deleted.
	 *
	 * @param   integer  $tag_id  The tag_id (primary key) for the deleted tag.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function tagDeleteInstances($tag_id)
	{
		// Delete the old tag maps.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__contentitem_tag_map'))
			->where($db->quoteName('tag_id') . ' = ' . (int) $tag_id);
		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * Method to add or update tags associated with an item.
	 *
	 * @param   integer          $ucmId    Id of the #__ucm_content item being tagged
	 * @param   JTableInterface  $table    JTable object being tagged
	 * @param   array            $tags     Array of tags to be applied.
	 * @param   boolean          $replace  Flag indicating if all exising tags should be replaced
	 *
	 * @return  boolean  true on success, otherwise false.
	 *
	 * @since   3.1
	 */
	public function tagItem($ucmId, JTableInterface $table, $tags = array(), $replace = true)
	{
		$key = $table->get('_tbl_key');
		$oldTags = $this->getTagIds((int) $table->$key, $this->typeAlias);
		$oldTags = explode(',', $oldTags);
		$result = $this->unTagItem($ucmId, $table);

		if ($replace)
		{
			$newTags = $tags;
		}
		else
		{
			if ($tags == array())
			{
				$newTags = $table->newTags;
			}
			else
			{
				$newTags = $tags;
			}

			if ($oldTags[0] != '')
			{
				$newTags = array_unique(array_merge($newTags, $oldTags));
			}
		}

		if (is_array($newTags) && count($newTags) > 0 && $newTags[0] != '')
		{
			$result = $result && $this->addTagMapping($ucmId, $table, $newTags);
		}

		return $result;
	}

	/**
	 * Method to untag an item
	 *
	 * @param   integer          $contentId  ID of the content item being untagged
	 * @param   JTableInterface  $table      JTable object being untagged
	 * @param   array            $tags       Array of tags to be untagged. Use an empty array to untag all existing tags.
	 *
	 * @return  boolean  true on success, otherwise false.
	 *
	 * @since   3.1
	 */
	public function unTagItem($contentId, JTableInterface $table, $tags = array())
	{
		$key = $table->getKeyName();
		$id = $table->$key;
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->delete('#__contentitem_tag_map')
			->where($db->quoteName('type_alias') . ' = ' . $db->quote($this->typeAlias))
			->where($db->quoteName('content_item_id') . ' = ' . (int) $id);

		if (is_array($tags) && count($tags) > 0)
		{
			$tags = ArrayHelper::toInteger($tags);

			$query->where($db->quoteName('tag_id') . ' IN (' . implode(',', $tags) . ')');
		}

		$db->setQuery($query);

		return (boolean) $db->execute();
	}
}
