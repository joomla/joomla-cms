<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\User;

\defined('_JEXEC') or die;

/**
 * Interface defining a factory which can create User objects
 *
 * @since  4.0.0
 */
interface UserFactoryInterface
{
	/**
	 * Method to get an instance of a user for the given id.
	 *
	 * @param   int  $id  The id
	 *
	 * @return  User
	 *
	 * @since   4.0.0
	 */
	public function loadUserById(int $id): User;

	/**
	 * Method to get an instance of a user for the given username.
	 *
	 * @param   string  $username  The username
	 *
	 * @return  User
	 *
	 * @since   4.0.0
	 */
	public function loadUserByUsername(string $username): User;
}
