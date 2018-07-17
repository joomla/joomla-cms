<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Categories;

defined('_JEXEC') or die;

/**
 * Option based categories factory.
 *
 * @since  __DEPLOY_VERSION__
 */
class CategoriesFactory implements CategoriesFactoryInterface
{
	/**
	 * The options
	 *
	 * @var  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private $options;

	/**
	 * CategoriesFactory constructor.
	 *
	 * @param   array  $options  The options
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(array $options)
	{
		$this->options = $options;
	}

	/**
	 * Creates a category.
	 *
	 * @param   string  $section  The section
	 *
	 * @return  CategoryInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @throws  SectionNotFoundException
	 */
	public function createCategory(string $section): CategoryInterface
	{
		if (!array_key_exists($section, $this->options))
		{
			throw new SectionNotFoundException;
		}

		return new Categories($this->options[$section]);
	}
}
