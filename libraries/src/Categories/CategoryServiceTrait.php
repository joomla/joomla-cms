<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Categories;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;

/**
 * Trait for component categories service.
 *
 * @since  4.0.0
 */
trait CategoryServiceTrait
{
	/**
	 * The categories factory
	 *
	 * @var  CategoryFactoryInterface
	 *
	 * @since  4.0.0
	 */
	private $categoryFactory;

	/**
	 * Returns the category service.
	 *
	 * @param   array   $options  The options
	 * @param   string  $section  The section
	 *
	 * @return  CategoryInterface
	 *
	 * @since   4.0.0
	 * @throws  SectionNotFoundException
	 */
	public function getCategory(array $options = [], $section = ''): CategoryInterface
	{
		return $this->categoryFactory->createCategory($options, $section);
	}

	/**
	 * Sets the internal category factory.
	 *
	 * @param   CategoryFactoryInterface  $categoryFactory  The categories factory
	 *
	 * @return  void
	 *
	 * @since  4.0.0
	 */
	public function setCategoryFactory(CategoryFactoryInterface $categoryFactory)
	{
		$this->categoryFactory = $categoryFactory;
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

		$db    = Factory::getDbo();
		$state = $this->getStateColumnForSection($section);

		foreach ($items as $item)
		{
			$item->count_trashed = 0;
			$item->count_archived = 0;
			$item->count_unpublished = 0;
			$item->count_published = 0;
			$query = $db->getQuery(true);
			$query->select($state . ' as state, count(*) AS count')
				->from($db->quoteName($sectionTable))
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

		$db    = Factory::getDbo();
		$join  = $db->quoteName($sectionTable) . ' AS c ON ct.content_item_id=c.id';
		$state = $this->getStateColumnForSection($section);

		if ($section === 'category')
		{
			$join = $db->quoteName('#__categories') . ' AS c ON ct.content_item_id=c.id';
			$state = 'published as state';
		}

		foreach ($items as $item)
		{
			$item->count_trashed = 0;
			$item->count_archived = 0;
			$item->count_unpublished = 0;
			$item->count_published = 0;
			$query = $db->getQuery(true);
			$query->select($state . ' as state, count(*) AS count')
				->from($db->quoteName('#__contentitem_tag_map') . 'AS ct ')
				->where('ct.tag_id = ' . (int) $item->id)
				->where('ct.type_alias =' . $db->quote($extension))
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
	 * Prepares the category form
	 *
	 * @param   Form          $form  The form to change
	 * @param   array|object  $data  The form data
	 *
	 * @return void
	 */
	public function prepareForm(Form $form, $data)
	{
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
