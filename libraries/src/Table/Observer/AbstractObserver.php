<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table\Observer;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Table\TableInterface;
use Joomla\CMS\Table\Table;

/**
 * Table class supporting modified pre-order tree traversal behavior.
 *
 * @since  3.1.2
 */
abstract class AbstractObserver implements \JObserverInterface
{
	/**
	 * The observed table
	 *
	 * @var    Table
	 * @since  3.1.2
	 */
	protected $table;

	/**
	 * Constructor: Associates to $table $this observer
	 *
	 * @param   TableInterface  $table  Table to be observed
	 *
	 * @since   3.1.2
	 */
	public function __construct(TableInterface $table)
	{
		$table->attachObserver($this);
		$this->table = $table;
	}

	/**
	 * Pre-processor for $table->load($keys, $reset)
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.  If not
	 *                           set the instance property value is used.
	 * @param   boolean  $reset  True to reset the default values before loading the new row.
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public function onBeforeLoad($keys, $reset)
	{
	}

	/**
	 * Post-processor for $table->load($keys, $reset)
	 *
	 * @param   boolean  &$result  The result of the load
	 * @param   array    $row      The loaded (and already binded to $this->table) row of the database table
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public function onAfterLoad(&$result, $row)
	{
	}

	/**
	 * Pre-processor for $table->store($updateNulls)
	 *
	 * @param   boolean  $updateNulls  The result of the load
	 * @param   string   $tableKey     The key of the table
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public function onBeforeStore($updateNulls, $tableKey)
	{
	}

	/**
	 * Post-processor for $table->store($updateNulls)
	 *
	 * @param   boolean  &$result  The result of the store
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public function onAfterStore(&$result)
	{
	}

	/**
	 * Pre-processor for $table->delete($pk)
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 * @throws  \UnexpectedValueException
	 */
	public function onBeforeDelete($pk)
	{
	}

	/**
	 * Post-processor for $table->delete($pk)
	 *
	 * @param   mixed  $pk  The deleted primary key value.
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public function onAfterDelete($pk)
	{
	}
}
