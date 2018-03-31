<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\Table;

defined('JPATH_PLATFORM') or die;

use BadMethodCallException;
use stdClass;

/**
 * Event class for JTable's onAfterMove event
 *
 * @since  4.0.0
 */
class AfterMoveEvent extends AbstractEvent
{
	/**
	 * Constructor.
	 *
	 * Mandatory arguments:
	 * subject		JTableInterface	The table we are operating on
	 * row			stdClass|null	The primary keys and ordering value for the selection.
	 * delta		int				The direction and magnitude to move the row in the ordering sequence.
	 * where		string			WHERE clause which was used for limiting the selection of rows to compact the ordering values.
	 *
	 * @param   string  $name       The event name.
	 * @param   array   $arguments  The event arguments.
	 *
	 * @throws  BadMethodCallException
	 */
	public function __construct($name, array $arguments = array())
	{
		if (!array_key_exists('row', $arguments))
		{
			throw new BadMethodCallException("Argument 'row' is required for event $name");
		}

		if (!array_key_exists('delta', $arguments))
		{
			throw new BadMethodCallException("Argument 'delta' is required for event $name");
		}

		if (!array_key_exists('where', $arguments))
		{
			throw new BadMethodCallException("Argument 'ignore' is required for event $name");
		}

		parent::__construct($name, $arguments);
	}

	/**
	 * Setter for the rows argument
	 *
	 * @param   stdClass|null  $value  The value to set
	 *
	 * @return  mixed
	 *
	 * @throws  BadMethodCallException  if the argument is not of the expected type
	 */
	protected function setRow($value)
	{
		if (!($value instanceof stdClass) && !empty($value))
		{
			throw new BadMethodCallException("Argument 'row' of event {$this->name} must be an stdClass object or null");
		}

		return $value;
	}

	/**
	 * Setter for the delta argument
	 *
	 * @param   int  $value  The value to set
	 *
	 * @return  int
	 *
	 * @throws  BadMethodCallException  if the argument is not of the expected type
	 */
	protected function setDelta($value)
	{
		if (!is_numeric($value))
		{
			throw new BadMethodCallException("Argument 'delta' of event {$this->name} must be an integer");
		}

		return (int) $value;
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
