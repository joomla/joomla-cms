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
use Joomla\Cms\Event\AbstractImmutableEvent;
use JTableInterface;

/**
 * Event class for JTable's events
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class AbstractEvent extends AbstractImmutableEvent
{
	/**
	 * Constructor.
	 *
	 * @param   string  $name       The event name.
	 * @param   array   $arguments  The event arguments.
	 *
	 * @throws  BadMethodCallException
	 *
	 * @since   1.0
	 */
	public function __construct($name, array $arguments = array())
	{
		if (!array_key_exists('subject', $arguments))
		{
			throw new BadMethodCallException("Argument 'subject' of event {$this->name} is required but has not been provided");
		}

		parent::__construct($name, $arguments);
	}

	/**
	 * Setter for the subject argument
	 *
	 * @param   JTableInterface  $value  The value to set
	 *
	 * @return  JTableInterface
	 *
	 * @throws  BadMethodCallException  if the argument is not of the expected type
	 */
	protected function setSubject($value)
	{
		if (!is_object($value) || !($value instanceof JTableInterface))
		{
			throw new BadMethodCallException("Argument 'subject' of event {$this->name} is not of the expected type");
		}

		return $value;
	}
}
