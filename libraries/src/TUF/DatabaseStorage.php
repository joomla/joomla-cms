<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\TUF;

use Joomla\CMS\Table\Table;
use Joomla\CMS\Table\Tuf;
use Joomla\CMS\TUF\Exception\RoleNotFoundException;
use Joomla\Database\DatabaseDriver;

\defined('JPATH_PLATFORM') or die;

/**
 * @since  __DEPLOY_VERSION__
 */
class DatabaseStorage implements \ArrayAccess
{
	/**
	 * The Tuf table object
	 *
	 * @var Table
	 */
	protected $table;

	/**
	 * Initialize the DatabaseStorage class
	 *
	 * @param   DatabaseDriver  $db           A database connector object
	 * @param   integer         $extensionId  The extension ID where the storage should be implemented for
	 */
	public function __construct(DatabaseDriver $db, int $extensionId)
	{
		$this->table = new Tuf($db);

		$this->table->load(['extension_id' => $extensionId]);
	}

	/**
	 * Check if an offset/table column exists
	 *
	 * @param   mixed  $offset  The offset/database column to check for
	 *
	 * @return boolean
	 */
	public function offsetExists($offset): bool
	{
		$column = $this->getCleanColumn($offset);

		return substr($column, -5) === '_json' && $this->table->hasField($column) && !is_null($this->table->$column);
	}

	/**
	 * Check if an offset/table column exists
	 *
	 * @param   mixed  $offset  The offset/database column to check for
	 *
	 * @return boolean
	 */
	public function tableColumnExists($offset): bool
	{
		$column = $this->getCleanColumn($offset);

		return substr($column, -5) === '_json' && $this->table->hasField($column);
	}

	/**
	 * Get the value of a table column
	 *
	 * @param   mixed  $offset  The column name to get the value for
	 *
	 * @return  mixed
	 */
	public function offsetGet($offset)
	{
		if (!$this->offsetExists($offset))
		{
			throw new RoleNotFoundException;
		}

		$column = $this->getCleanColumn($offset);

		return $this->table->$column;
	}

	/**
	 * Set a value in a column
	 *
	 * @param   [type] $offset  The table column to set the value
	 * @param   [type] $value   The value to set
	 *
	 * @return void
	 */
	public function offsetSet($offset, $value)
	{
		if (!$this->tableColumnExists($offset))
		{
			throw new RoleNotFoundException;
		}

		$column = $this->getCleanColumn($offset);

		$this->table->$column = $value;

		$this->table->store();
	}

	/**
	 * Reset the value to a
	 *
	 * @param   mixed  $offset  The table column to reset the value to null
	 *
	 * @return void
	 */
	public function offsetUnset($offset): void
	{
		if (!$this->offsetExists($offset))
		{
			throw new RoleNotFoundException;
		}

		$column = $this->getCleanColumn($offset);

		$this->table->$column = null;

		$this->table->store(true);
	}

	/**
	 * Convert file names to table columns
	 *
	 * @param   string  $name  The original file name
	 *
	 * @return string
	 */
	protected function getCleanColumn($name): string
	{
		return str_replace('.', '_', $name);
	}
}
