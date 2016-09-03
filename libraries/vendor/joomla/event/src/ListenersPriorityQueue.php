<?php
/**
 * Part of the Joomla Framework Event Package
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Event;

use SplPriorityQueue;
use SplObjectStorage;
use IteratorAggregate;
use Countable;

/**
 * A class containing an inner listeners priority queue that can be iterated multiple times.
 * One instance of ListenersPriorityQueue is used per Event in the Dispatcher.
 *
 * @since  1.0
 */
class ListenersPriorityQueue implements IteratorAggregate, Countable
{
	/**
	 * The inner priority queue.
	 *
	 * @var    SplPriorityQueue
	 *
	 * @since  1.0
	 */
	protected $queue;

	/**
	 * A copy of the listeners contained in the queue
	 * that is used when detaching them to
	 * recreate the queue or to see if the queue contains
	 * a given listener.
	 *
	 * @var    SplObjectStorage
	 *
	 * @since  1.0
	 */
	protected $storage;

	/**
	 * A decreasing counter used to compute
	 * the internal priority as an array because
	 * SplPriorityQueue dequeues elements with the same priority.
	 *
	 * @var    integer
	 *
	 * @since  1.0
	 */
	private $counter = PHP_INT_MAX;

	/**
	 * Constructor.
	 *
	 * @since  1.0
	 */
	public function __construct()
	{
		$this->queue = new SplPriorityQueue;
		$this->storage = new SplObjectStorage;
	}

	/**
	 * Add a listener with the given priority only if not already present.
	 *
	 * @param   \Closure|object  $listener  The listener.
	 * @param   integer          $priority  The listener priority.
	 *
	 * @return  ListenersPriorityQueue  This method is chainable.
	 *
	 * @since   1.0
	 */
	public function add($listener, $priority)
	{
		if (!$this->storage->contains($listener))
		{
			// Compute the internal priority as an array.
			$priority = array($priority, $this->counter--);

			$this->storage->attach($listener, $priority);
			$this->queue->insert($listener, $priority);
		}

		return $this;
	}

	/**
	 * Remove a listener from the queue.
	 *
	 * @param   \Closure|object  $listener  The listener.
	 *
	 * @return  ListenersPriorityQueue  This method is chainable.
	 *
	 * @since   1.0
	 */
	public function remove($listener)
	{
		if ($this->storage->contains($listener))
		{
			$this->storage->detach($listener);
			$this->storage->rewind();

			$this->queue = new SplPriorityQueue;

			foreach ($this->storage as $listener)
			{
				$priority = $this->storage->getInfo();
				$this->queue->insert($listener, $priority);
			}
		}

		return $this;
	}

	/**
	 * Tell if the listener exists in the queue.
	 *
	 * @param   \Closure|object  $listener  The listener.
	 *
	 * @return  boolean  True if it exists, false otherwise.
	 *
	 * @since   1.0
	 */
	public function has($listener)
	{
		return $this->storage->contains($listener);
	}

	/**
	 * Get the priority of the given listener.
	 *
	 * @param   \Closure|object  $listener  The listener.
	 * @param   mixed            $default   The default value to return if the listener doesn't exist.
	 *
	 * @return  mixed  The listener priority if it exists, null otherwise.
	 *
	 * @since   1.0
	 */
	public function getPriority($listener, $default = null)
	{
		if ($this->storage->contains($listener))
		{
			return $this->storage[$listener][0];
		}

		return $default;
	}

	/**
	 * Get all listeners contained in this queue, sorted according to their priority.
	 *
	 * @return  object[]  An array of listeners.
	 *
	 * @since   1.0
	 */
	public function getAll()
	{
		$listeners = array();

		// Get a clone of the queue.
		$queue = $this->getIterator();

		foreach ($queue as $listener)
		{
			$listeners[] = $listener;
		}

		return $listeners;
	}

	/**
	 * Get the inner queue with its cursor on top of the heap.
	 *
	 * @return  SplPriorityQueue  The inner queue.
	 *
	 * @since   1.0
	 */
	public function getIterator()
	{
		// SplPriorityQueue queue is a heap.
		$queue = clone $this->queue;

		if (!$queue->isEmpty())
		{
			$queue->top();
		}

		return $queue;
	}

	/**
	 * Count the number of listeners in the queue.
	 *
	 * @return  integer  The number of listeners in the queue.
	 *
	 * @since   1.0
	 */
	public function count()
	{
		return count($this->queue);
	}
}
