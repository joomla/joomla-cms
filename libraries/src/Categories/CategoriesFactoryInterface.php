<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Categories;

defined('_JEXEC') or die;

/**
 * Categories factory interface
 *
 * @since  __DEPLOY_VERSION__
 */
interface CategoriesFactoryInterface
{
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
	public function createCategory(string $section): CategoryInterface;
}
