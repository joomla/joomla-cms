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
 * Event class for JTable's onBeforeLoad event
 *
 * @since  4.0.0
 */
class BeforeLoadEvent extends AbstractEvent
{
	/**
	 * Constructor.
	 *
	 * Mandatory arguments:
	 * subject	JTableInterface	The table we are operating on
	 * keys		mixed			The optional primary key value to load the row by, or an array of fields to match.
	 * reset	boolean			True to reset the default values before loading the new row.
	 *
	 * @param   string  $name       The event name.
	 * @param   array   $arguments  The event arguments.
	 *
	 * @throws  BadMethodCallException
	 */
	public function __construct($name, array $arguments = array())
	{
		if (!array_key_exists('keys', $arguments))
		{
			throw new BadMethodCallException("Argument 'keys' is required for event $name");
		}

		if (!array_key_exists('reset', $arguments))
		{
			throw new BadMethodCallException("Argument 'reset' is required for event $name");
		}

		parent::__construct($name, $arguments);
	}

	/**
	 * Setter for the reset attribute
	 *
	 * @param   mixed  $value  The value to set
	 *
	 * @return  boolean  Normalised value
	 */
	protected function setReset($value)
	{
		return $value ? true : false;
	}
}
