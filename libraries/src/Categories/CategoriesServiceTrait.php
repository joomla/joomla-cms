<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Categories;

defined('JPATH_PLATFORM') or die;

/**
 * Trait for component categories service.
 *
 * @since  4.0.0
 */
trait CategoriesServiceTrait
{
	/**
	 * An array of categories.
	 *
	 * @var  Categories[]
	 *
	 * @since  4.0.0
	 */
	private $categories;

	/**
	 * Returns the category service.
	 *
	 * @param   array   $options  The options
	 * @param   string  $section  The section
	 *
	 * @return  Categories
	 *
	 * @see Categories::setOptions()
	 *
	 * @since   4.0.0
	 * @throws  SectionNotFoundException
	 */
	public function getCategories(array $options = [], $section = ''): Categories
	{
		if (!array_key_exists($section, $this->categories))
		{
			throw new SectionNotFoundException;
		}

		$categories = clone $this->categories[$section];
		$categories->setOptions($options);

		return $categories;
	}

	/**
	 * An array of categories where the key is the name of the section.
	 * If the component has no sections then the array must have at least
	 * an empty key.
	 *
	 * @param   array  $categories  The categories
	 *
	 * @return  void
	 *
	 * @since  4.0.0
	 */
	public function setCategories(array $categories)
	{
		$this->categories = $categories;
	}

	/**
	 * Adds Count Items for Category Manager.
	 *
	 * @param   \stdClass[]  $items    The category objects
	 * @param   string       $section  The section
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 * @throws  \Exception
	 */
	public function countItems(array $items, string $section)
	{
		$sectionTable = $this->getTableNameForSection($section);
		if (!$sectionTable)
		{
			return;
		}

		$db = \JFactory::getDbo();

		foreach ($items as $item)
		{
			$item->count_trashed = 0;
			$item->count_archived = 0;
			$item->count_unpublished = 0;
			$item->count_published = 0;
			$query = $db->getQuery(true);
			$query->select('state, count(*) AS count')
				->from($db->qn($sectionTable))
				->where('catid = ' . (int) $item->id)
				->group('state');
			$db->setQuery($query);
			$objects = $db->loadObjectList();

			foreach ($objects as $object)
			{
				if ($object->state == 1)
				{
					$item->count_published = $object->count;
				}

				if ($object->state == 0)
				{
					$item->count_unpublished = $object->count;
				}

				if ($object->state == 2)
				{
					$item->count_archived = $object->count;
				}

				if ($object->state == -2)
				{
					$item->count_trashed = $object->count;
				}
			}
		}
	}

	/**
	 * Adds Count Items for Tag Manager.
	 *
	 * @param   \stdClass[]  $items      The content objects
	 * @param   string       $extension  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function countTagItems(array $items, string $extension)
	{
		$parts     = explode('.', $extension);
		$section   = '';

		if (count($parts) > 1)
		{
			$section = $parts[1];
		}

		$sectionTable = $this->getTableNameForSection($section);
		if (!$sectionTable)
		{
			return;
		}

		$db    = \JFactory::getDbo();
		$join  = $db->qn($sectionTable) . ' AS c ON ct.content_item_id=c.id';
		$state = $this->getStateColumnForSection($section);

		if ($section === 'category')
		{
			$join = $db->qn('#__categories') . ' AS c ON ct.content_item_id=c.id';
			$state = 'published as state';
		}

		foreach ($items as $item)
		{
			$item->count_trashed = 0;
			$item->count_archived = 0;
			$item->count_unpublished = 0;
			$item->count_published = 0;
			$query = $db->getQuery(true);
			$query->select($state . ', count(*) AS count')
				->from($db->qn('#__contentitem_tag_map') . 'AS ct ')
				->where('ct.tag_id = ' . (int) $item->id)
				->where('ct.type_alias =' . $db->q($extension))
				->join('LEFT', $join)
				->group('state');
			$db->setQuery($query);
			$contents = $db->loadObjectList();

			foreach ($contents as $content)
			{
				if ($content->state == 1)
				{
					$item->count_published = $content->count;
				}

				if ($content->state == 0)
				{
					$item->count_unpublished = $content->count;
				}

				if ($content->state == 2)
				{
					$item->count_archived = $content->count;
				}

				if ($content->state == -2)
				{
					$item->count_trashed = $content->count;
				}
			}
		}
	}

	/**
	 * Returns the table for the count items functions for the given section.
	 *
	 * @param   string  $section  The section
	 *
	 * @return  string|null
	 *
	 * @since   4.0.0
	 */
	protected function getTableNameForSection(string $section = null)
	{
		return null;
	}

	/**
	 * Returns the state column for the count items functions for the given section.
	 *
	 * @param   string  $section  The section
	 *
	 * @return  string|null
	 *
	 * @since   4.0.0
	 */
	protected function getStateColumnForSection(string $section = null)
	{
		return 'state';
	}
}
