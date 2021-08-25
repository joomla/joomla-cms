<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Categories;

\defined('_JEXEC') or die;

/**
 * Option based categories factory.
 *
 * @since  3.10.0
 */
class CategoryFactory implements CategoryFactoryInterface
{
	/**
	 * The namespace to create the categories from.
	 *
	 * @var    string
	 * @since  3.10.0
	 */
	private $namespace;

	/**
	 * The namespace must be like:
	 * Joomla\Component\Content
	 *
	 * @param   string  $namespace  The namespace
	 *
	 * @since   3.10.0
	 */
	public function __construct($namespace)
	{
		$this->namespace = $namespace;
	}

	/**
	 * Creates a category.
	 *
	 * @param   array   $options  The options
	 * @param   string  $section  The section
	 *
	 * @return  CategoryInterface
	 *
	 * @since   3.10.0
	 *
	 * @throws  SectionNotFoundException
	 */
	public function createCategory(array $options = array(), $section = '')
	{
		$className = trim($this->namespace, '\\') . '\\Site\\Service\\' . ucfirst($section) . 'Category';

		if (!class_exists($className))
		{
			throw new SectionNotFoundException;
		}

		return new $className($options);
	}
}
