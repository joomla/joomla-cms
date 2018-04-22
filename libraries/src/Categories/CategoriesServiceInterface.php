<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Categories;

defined('JPATH_PLATFORM') or die;

/**
 * Access to component specific categories.
 *
 * @since  __DEPLOY_VERSION__
 */
interface CategoriesServiceInterface
{
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
	 * @throws  CategoriesNotFoundException
	 */
	public function getCategories(array $options = [], $section = ''): Categories;
}
