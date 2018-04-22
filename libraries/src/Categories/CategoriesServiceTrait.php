<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Categories;

defined('JPATH_PLATFORM') or die;

/**
 * Trait for component categories service.
 *
 * @since  __DEPLOY_VERSION__
 */
trait CategoriesServiceTrait
{
	/**
	 * An array of categories.
	 *
	 * @var  Categories[]
	 *
	 * @since  __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
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
	 * @since  __DEPLOY_VERSION__
	 */
	public function setCategories(array $categories)
	{
		$this->categories = $categories;
	}
}
