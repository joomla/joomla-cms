<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Helper;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\CoreContent;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Table\TableInterface;
use Joomla\CMS\UCM\UCMContent;
use Joomla\CMS\UCM\UCMType;
use Joomla\Utilities\ArrayHelper;

/**
 * Tags helper class, provides methods to perform various tasks relevant
 * tagging of content.
 *
 * @since  3.1
 */
class TagsHelper extends CMSHelper
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

				$tag->value = (string) $tag->value;
			}

			// Get the aliases titles in one single query and map the results
			if ($aliases)
			{
				// Remove duplicates
				$aliases = array_unique($aliases);

				$db = Factory::getDbo();

				$query = $db->getQuery(true)
					->select('alias, title')
					->from('#__tags')
					->where('alias IN (' . implode(',', array_map(array($db, 'quote'), $aliases)) . ')');
				$db->setQuery($query);

				try
				{
					$aliasesMapper = $db->loadAssocList('alias');
				}
				catch (\RuntimeException $e)
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
		$db = Factory::getDbo();
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

		$user = Factory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());

		$query->where('t.access IN (' . $groups . ')');

		// Optionally filter on language
		$language = ComponentHelper::getParams('com_tags')->get('tag_list_language_filter', 'all');

		if ($language !== 'all')
		{
			if ($language === 'current_language')
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

		$db = Factory::getDbo();

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

			$db = Factory::getDbo();
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
		$db = Factory::getDbo();
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
		$db = Factory::getDbo();
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

		// Filter on the access level
		if (isset($filters['access']) && is_array($filters['access']) && count($filters['access']))
		{
			$groups = ArrayHelper::toInteger($filters['access']);
			$query->where('a.access IN (' . implode(",", $groups) . ')');
		}

		// Filter by parent_id
		if (isset($filters['parent_id']) && is_numeric($filters['parent_id']))
		{
			Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tags/tables');
			$tagTable = Table::getInstance('Tag', 'TagsTable');

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
		catch (\RuntimeException $e)
		{
			return array();
		}

		// We will replace path aliases with tag names
		return self::convertPathsToNames($results);
	}
}
