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
	protected Table $table;

	/**
	 * Initialize the DatabaseStorage class
	 *
	 * @param   DatabaseDriver $db
	 * @param   integer $extensionId
	 */
	public function __construct(DatabaseDriver $db, int $extensionId)
	{
		$this->table = new Tuf($db);

		$this->table->load($extensionId);
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetExists(mixed $offset): bool
	{
		$column = $this->getCleanColumn($offset);

		return substr($offset, -5) === '_json' && $this->table->hasField($column) && strlen($this->table->$column);
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetGet($offset): mixed
	{
		if (!$this->offsetExists($offset))
		{
			throw new RoleNotFoundException;
		}

		$column = $this->getCleanColumn($offset);

		return $this->table->$column;
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetSet($offset, $value): void
	{
		if (!$this->offsetExists($offset))
		{
			throw new RoleNotFoundException;
		}

		$this->table->$offset = $value;

		$this->table->store();
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetUnset($offset): void
	{
		if (!$this->offsetExists($offset))
		{
			throw new RoleNotFoundException;
		}

		$this->table->$offset = '';

		$this->table->store();
	}

	/**
	 * Convert file names to table columns
	 *
	 * @param   string $name
	 *
	 * @return string
	 */
	protected function getCleanColumn($name): string
	{
		return str_replace('.', '_', $name);
	}
}
