<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Queue class for the Finder indexer package.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 * @since       2.5
 */
class FinderIndexerQueue
{
	/**
	 * Method to add a content item to the queue.
	 *
	 * @param   string   $context  The content context.
	 * @param   integer  $id       The content id.
	 * @param   string   $time     The content creation date.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public static function add($context, $id, $time)
	{
		// Load the queue from the session.
		$queue = JFactory::getSession()->get('_finder.queue', array());

		// Initialize the context if necessary.
		if (!array_key_exists($context, $queue))
		{
			$queue[$context] = array();
		}

		// Add the item to the queue if it is unique or new.
		if (!array_key_exists($id, $queue[$context]) || empty($id))
		{
			if (empty($id))
			{
				// Add an item to be indexed.
				$queue[$context][] = array('context' => $context, 'id' => $id, 'timestamp' => $time);
			}
			else
			{
				// Add an item to be updated.
				$queue[$context][$id] = array('context' => $context, 'id' => $id, 'timestamp' => $time);
			}
		}

		// Store the queue in the session.
		JFactory::getSession()->set('_finder.queue', $queue);
	}

	/**
	 * Method to get the content items in the queue.
	 *
	 * @param   string  $context  The content context. [optional]
	 *
	 * @return  array  An array of content items.
	 *
	 * @since   2.5
	 */
	public static function get($context = null)
	{
		// Load the queue from the session.
		$queue = JFactory::getSession()->get('_finder.queue', array());
		$return = array();

		if ($context === null)
		{
			return $queue;
		}

		// Get the context from the queue.
		if (array_key_exists($context, $queue))
		{
			$return = $queue[$context];
		}

		return $return;
	}

	/**
	 * Method to remove items from the queue.
	 *
	 * @param   string  $context  The content context.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public static function remove($context)
	{
		// Load the queue from the session.
		$queue = JFactory::getSession()->get('_finder.queue', array());

		// Check if the context exists.
		if (array_key_exists($context, $queue))
		{
			// Remove the context from the queue.
			unset($queue[$context]);

			// Store the queue in the session.
			JFactory::getSession()->set('_finder.queue', $queue);
		}
	}

	/**
	 * Method to purge the queue.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public static function purge()
	{
		// Purge the queue from the session.
		JFactory::getSession()->set('_finder.queue', array());
	}
}
