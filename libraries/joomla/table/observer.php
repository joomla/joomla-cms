<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Event\EventInterface;

/**
 * Table class supporting modified pre-order tree traversal behavior.
 *
 * @since  3.1.2
 */
abstract class JTableObserver
{
	/**
	 * The observed table
	 *
	 * @var    JTable
	 * @since  3.1.2
	 */
	protected $table;

	/**
	 * Constructor: Associates to $table $this observer
	 *
	 * @param   JTableInterface  $table  Table to be observed
	 *
	 * @since   3.1.2
	 */
	public function __construct(JTableInterface $table)
	{
		// Set the table to the object
		$this->table = $table;

		// Assign the listeners to the Table's event Dispatcher
		$this->attachListenersToDispatcher();
	}

	/**
	 * Assigns the listeners to the table's event dispatcher.
	 *
	 * If you want to customise the events registered to the dispatcher you should override this method. A standard set
	 * of listeners is supplied with this class and registered by default.
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public function attachListenersToDispatcher()
	{
		// Get the dispatcher
		$dispatcher = $this->table->getDispatcher();

		// Assign the listeners to the dispatcher
		$dispatcher->addListener('onBeforeLoad', [$this, 'onBeforeLoadListener']);
		$dispatcher->addListener('onAfterLoad', [$this, 'onAfterLoadListener']);
		$dispatcher->addListener('onBeforeStore', [$this, 'onBeforeStoreListener']);
		$dispatcher->addListener('onAfterStore', [$this, 'onAfterStoreListener']);

	}

	/**
	 * Event listener for the onBeforeLoad event.
	 *
	 * @param   EventInterface  $event  The event we're handling
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public final function onBeforeLoadListener(EventInterface $event)
	{
		$keys = $event->getArgument('keys', null);
		$reset = $event->getArgument('reset', false);

		$this->onBeforeLoad($keys, $reset);
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
	 * Event listener for the onAfterLoad event.
	 *
	 * @param   EventInterface  $event  The event we're handling
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public final function onAfterLoadListener(EventInterface $event)
	{
		$result = $event->getArgument('result', false);
		$row = $event->getArgument('row', null);

		$this->onAfterLoad($result, $row);

		$event['result'] = $result;
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
	 * Event listener for the onBeforeStore event.
	 *
	 * @param   EventInterface  $event  The event we're handling
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public final function onBeforeStoreListener(EventInterface $event)
	{
		$updateNulls = $event->getArgument('updateNulls', false);
		$tableKey = $event->getArgument('tableKey', null);

		$this->onBeforeStore($updateNulls, $tableKey);
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
	 * Event listener for the onAfterStore event.
	 *
	 * @param   EventInterface  $event  The event we're handling
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public final function onAfterStoreListener(EventInterface $event)
	{
		$result = $event->getArgument('result', false);

		$this->onAfterStore($result);

		$event['result'] = $result;
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
	 * Event listener for the onBeforeDelete event.
	 *
	 * @param   EventInterface  $event  The event we're handling
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public final function onBeforeDeleteListener(EventInterface $event)
	{
		$k = $event->getArgument('k', false);

		$this->onBeforeDelete($k);
	}

	/**
	 * Pre-processor for $table->delete($pk)
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 * @throws  UnexpectedValueException
	 */
	public function onBeforeDelete($pk)
	{
	}

	/**
	 * Event listener for the onAfterDelete event.
	 *
	 * @param   EventInterface  $event  The event we're handling
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public final function onAfterDeleteListener(EventInterface $event)
	{
		$k = $event->getArgument('k', false);

		$this->onBeforeDelete($k);
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
