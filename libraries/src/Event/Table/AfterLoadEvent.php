<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\Table;

\defined('JPATH_PLATFORM') or die;

use BadMethodCallException;

/**
 * Event class for JTable's onAfterLoad event
 *
 * @since  4.0.0
 */
class AfterLoadEvent extends AbstractEvent
{
	/**
	 * Constructor.
	 *
	 * Mandatory arguments:
	 * subject	JTableInterface	The table we are operating on
	 * result	boolean			Did the table record load succeed?
	 * row		null|array		The values loaded from the database, null if it failed
	 *
	 * @param   string  $name       The event name.
	 * @param   array   $arguments  The event arguments.
	 *
	 * @throws  BadMethodCallException
	 */
	public function __construct($name, array $arguments = array())
	{
		if (!\array_key_exists('result', $arguments))
		{
			throw new BadMethodCallException("Argument 'result' is required for event $name");
		}

		if (!\array_key_exists('row', $arguments))
		{
			throw new BadMethodCallException("Argument 'row' is required for event $name");
		}

		parent::__construct($name, $arguments);
	}

	/**
	 * Setter for the result argument
	 *
	 * @param   boolean  $value  The value to set
	 *
	 * @return  boolean
	 *
	 * @throws  BadMethodCallException  if the argument is not of the expected type
	 */
	protected function setResult($value)
	{
		return $value ? true : false;
	}

	/**
	 * Setter for the row argument
	 *
	 * @param   array|null  $value  The value to set
	 *
	 * @return  array|null
	 *
	 * @throws  BadMethodCallException  if the argument is not of the expected type
	 */
	protected function setRow($value)
	{
		if (!\is_null($value) && !\is_array($value))
		{
			throw new BadMethodCallException("Argument 'row' of event {$this->name} is not of the expected type");
		}

		return $value;
	}

}
