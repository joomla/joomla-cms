<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Model;

\defined('JPATH_PLATFORM') or die;

use Joomla\Database\DatabaseInterface;

/**
 * Database aware trait.
 *
 * @since  4.0.0
 */
trait DatabaseAwareTrait
{
	/**
	 * The database driver.
	 *
	 * @var    DatabaseInterface
	 * @since  4.0.0
	 */
	protected $_db;

	/**
	 * Get the database driver.
	 *
	 * @return  DatabaseInterface  The database driver.
	 *
	 * @since   4.0.0
	 * @throws  \UnexpectedValueException
	 */
	public function getDbo()
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
	 * @param   DatabaseInterface  $db  The database driver.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function setDbo(DatabaseInterface $db = null)
	{
		$this->_db = $db;
	}
}
