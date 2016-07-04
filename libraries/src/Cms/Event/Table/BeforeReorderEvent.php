<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cms\Event\Table;

defined('JPATH_PLATFORM') or die;

use BadMethodCallException;
use JDatabaseQuery;

/**
 * Event class for JTable's onBeforeReorder event
 *
 * @since  __DEPLOY_VERSION__
 */
class BeforeReorderEvent extends AbstractEvent
{
	/**
	 * Constructor.
	 *
	 * Mandatory arguments:
	 * subject		JTableInterface	The table we are operating on
	 * query		JDatabaseQuery	The query to get the primary keys and ordering values for the selection.
	 * where		string			WHERE clause to use for limiting the selection of rows to compact the ordering values.
	 *
	 * @param   string  $name       The event name.
	 * @param   array   $arguments  The event arguments.
	 *
	 * @throws  BadMethodCallException
	 */
	public function __construct($name, array $arguments = array())
	{
		if (!array_key_exists('query', $arguments))
		{
			throw new BadMethodCallException("Argument 'query' is required for event $name");
		}

		if (!array_key_exists('where', $arguments))
		{
			throw new BadMethodCallException("Argument 'where' is required for event $name");
		}

		parent::__construct($name, $arguments);
	}

	/**
	 * Setter for the query argument
	 *
	 * @param   JDatabaseQuery  $value  The value to set
	 *
	 * @return  mixed
	 *
	 * @throws  BadMethodCallException  if the argument is not of the expected type
	 */
	protected function setQuery($value)
	{
		if (!($value instanceof JDatabaseQuery))
		{
			throw new BadMethodCallException("Argument 'query' of event {$this->name} must be of JDatabaseQuery type");
		}

		return $value;
	}

	/**
	 * Setter for the where argument
	 *
	 * @param   string|null  $value  The value to set
	 *
	 * @return  mixed
	 *
	 * @throws  BadMethodCallException  if the argument is not of the expected type
	 */
	protected function setWhere($value)
	{
		if (!empty($value) && !is_string($value))
		{
			throw new BadMethodCallException("Argument 'where' of event {$this->name} must be empty or string");
		}

		return $value;
	}
}
