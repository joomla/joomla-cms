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
}
