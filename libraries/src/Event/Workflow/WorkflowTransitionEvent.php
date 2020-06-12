<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\Workflow;

\defined('JPATH_PLATFORM') or die;

use BadMethodCallException;
use Joomla\CMS\Event\AbstractImmutableEvent;
use function explode;

/**
 * Event class for Workflow Functionality Used events
 *
 * @since  4.0.0
 */
class WorkflowTransitionEvent extends AbstractEvent
{
	/**
	 * Constructor.
	 *
	 * @param   string  $name       The event name.
	 * @param   array   $arguments  The event arguments.
	 *
	 * @throws  BadMethodCallException
	 *
	 * @since   4.0.0
	 */
	public function __construct($name, array $arguments = array())
	{
		$arguments['stopTransition'] = false;

		parent::__construct($name, $arguments);
	}

	/**
	 * Set used parameter to true
	 *
	 * @param   bool  $value  The value to set
	 *
	 * @return void
	 *
	 * @since   4.0.0
	 */
	public function setStopTransition($value = true)
	{
		$this->arguments['stopTransition'] = $value;

		if ($value === true)
		{
			$this->stopPropagation();
		}
	}


}
