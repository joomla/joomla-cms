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

/**
 * Event class for JTable's onBeforeStore event
 *
 * @since  4.0.0
 */
class BeforeStoreEvent extends AbstractEvent
{
	/**
	 * Constructor.
	 *
	 * Mandatory arguments:
	 * subject		JTableInterface	The table we are operating on
	 * updateNulls	boolean			True to update fields even if they are null.
	 * k			mixed			Name of the primary key fields in the table (string or array of strings).
	 *
	 * @param   string  $name       The event name.
	 * @param   array   $arguments  The event arguments.
	 *
	 * @throws  BadMethodCallException
	 */
	public function __construct($name, array $arguments = array())
	{
		if (!array_key_exists('updateNulls', $arguments))
		{
			throw new BadMethodCallException("Argument 'updateNulls' is required for event $name");
		}

		if (!array_key_exists('k', $arguments))
		{
			throw new BadMethodCallException("Argument 'k' is required for event $name");
		}

		parent::__construct($name, $arguments);
	}

	/**
	 * Setter for the updateNulls attribute
	 *
	 * @param   mixed  $value  The value to set
	 *
	 * @return  boolean  Normalised value
	 */
	protected function setUpdateNulls($value)
	{
		return $value ? true : false;
	}
}
