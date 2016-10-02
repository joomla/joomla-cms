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
use stdClass;

/**
 * Event class for JTable's onAfterReorder event
 *
 * @since  __DEPLOY_VERSION__
 */
class AfterReorderEvent extends AbstractEvent
{
	/**
	 * Constructor.
	 *
	 * Mandatory arguments:
	 * subject		JTableInterface	The table we are operating on
	 * rows			stdClass[]|null	The primary keys and ordering values for the selection.
	 * where		string			WHERE clause which was used for limiting the selection of rows to compact the ordering values.
	 *
	 * @param   string  $name       The event name.
	 * @param   array   $arguments  The event arguments.
	 *
	 * @throws  BadMethodCallException
	 */
	public function __construct($name, array $arguments = array())
	{
		if (!array_key_exists('rows', $arguments))
		{
			throw new BadMethodCallException("Argument 'rows' is required for event $name");
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
	 * @param   stdClass[]|null  $value  The value to set
	 *
	 * @return  mixed
	 *
	 * @throws  BadMethodCallException  if the argument is not of the expected type
	 */
	protected function setRows($value)
	{
		if (!is_array($value) && !empty($value))
		{
			throw new BadMethodCallException("Argument 'rows' of event {$this->name} must be an array or null");
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
