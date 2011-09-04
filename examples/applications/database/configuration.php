<?php
/**
 * An example configuration file for an application built on the Joomla Platform.
 *
 * This file will be automatically loaded by the command line application.
 *
 * @package    Joomla.Examples
 * @copyright  Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// Prevent direct access to this file outside of a calling application.
defined('_JEXEC') or die;

/**
 * CLI configuration class.
 *
 * @package  Joomla.Examples
 * @since    11.3
 */
final class JConfig
{
	/**
	 * The database driver.
	 *
	 * @var    string
	 * @since  11.3
	 */
	public $dbDriver = 'mysqli';

	/**
	 * Database host.
	 *
	 * @var    string
	 * @since  11.3
	 */
	public $dbHost = 'localhost';

	/**
	 * The database connection user.
	 *
	 * @var    string
	 * @since  11.3
	 */
	public $dbUser = 'root';

	/**
	 * The database connection password.
	 *
	 * @var    string
	 * @since  11.3
	 */
	public $dbPass = 'root';

	/**
	 * The database name.
	 *
	 * @var    string
	 * @since  11.3
	 */
	public $dbName = 'mysql';

	/**
	 * The database table prefix, if necessary.
	 *
	 * @var    string
	 * @since  11.3
	 */
	public $dbPrefix = '';
}