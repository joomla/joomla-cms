<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Joomla Platform Database Model Class
 *
 * @since  12.1
 */
abstract class JModelDatabase extends JModelBase
{
	/**
	 * The database driver.
	 *
	 * @var    JDatabaseDriver
	 * @since  12.1
	 */
	protected $db;

	/**
	 * Instantiate the model.
	 *
	 * @param   Registry         $state  The model state.
	 * @param   JDatabaseDriver  $db     The database adpater.
	 *
	 * @since   12.1
	 */
	public function __construct(Registry $state = null, JDatabaseDriver $db = null)
	{
		parent::__construct($state);

		// Setup the model.
		$this->db = isset($db) ? $db : $this->loadDb();
	}

	/**
	 * Get the database driver.
	 *
	 * @return  JDatabaseDriver  The database driver.
	 *
	 * @since   12.1
	 */
	public function getDb()
	{
		return $this->db;
	}

	/**
	 * Set the database driver.
	 *
	 * @param   JDatabaseDriver  $db  The database driver.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function setDb(JDatabaseDriver $db)
	{
		$this->db = $db;
	}

	/**
	 * Load the database driver.
	 *
	 * @return  JDatabaseDriver  The database driver.
	 *
	 * @since   12.1
	 */
	protected function loadDb()
	{
		return JFactory::getDbo();
	}
}
