<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Categories;

defined('JPATH_PLATFORM') or die;

/**
 * The category interface.
 *
 * @since  __DEPLOY_VERSION__
 */
interface CategoryInterface
{
	/**
	 * Loads a specific category and all its children in a CategoryNode object.
	 *
	 * @param   mixed    $id         an optional id integer or equal to 'root'
	 * @param   boolean  $forceload  True to force  the _load method to execute
	 *
	 * @return  CategoryNode  CategoryNode object
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @throws  CategoryNotFoundException
	 */
	public function get($id = 'root', $forceload = false): CategoryNode;

	/**
	 * Allows to set some optional options, eg. if the access level should be considered.
	 * Also clears the internal children cache.
	 *
	 * @param   array  $options  The new options
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function setOptions(array $options);
}
