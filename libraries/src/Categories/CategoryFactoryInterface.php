<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Categories;

\defined('_JEXEC') or die;

/**
 * Category factory interface
 *
 * @since  3.10.0
 */
interface CategoryFactoryInterface
{
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
	public function createCategory(array $options = array(), $section = '');
}
