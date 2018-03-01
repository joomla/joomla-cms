<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Extension;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Categories\Categories;

/**
 * Access to component specific services.
 *
 * @since  __DEPLOY_VERSION__
 */
class Component implements ComponentInterface
{
	/**
	 * An array of categories.
	 *
	 * @var array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $categories;

	/**
	 * Returns the category service. If the service is not available
	 * null is returned.
	 *
	 * @param   array   $options  The options
	 * @param   string  $section  The section
	 *
	 * @return  Categories|null
	 *
	 * @see Categories::setOptions()
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getCategories(array $options = [], $section = '')
	{
		if (!array_key_exists($section, $this->categories))
		{
			return null;
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
