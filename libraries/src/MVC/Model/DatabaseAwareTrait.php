<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Model;

use Joomla\Database\DatabaseInterface;

/**
 * Database aware trait.
 *
 * @since  __DEPLOY_VERSION__
 */
trait DatabaseAwareTrait
{
	/**
	 * The database driver.
	 *
	 * @var    DatabaseInterface
	 * @since  __DEPLOY_VERSION__
	 */
	protected $_db;

	/**
	 * Get the database driver.
	 *
	 * @return  DatabaseInterface  The database driver.
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \UnexpectedValueException
	 */
	public function getDb()
	{
		if ($this->_db)
		{
			return $this->_db;
		}

		throw new \UnexpectedValueException('Database driver not set in ' . __CLASS__);
	}

	/**
	 * Set the database driver.
	 *
	 * @param   DatabaseInterface $_db The database driver.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setDb(DatabaseInterface $db)
	{
		$this->_db = $db;
	}
}
