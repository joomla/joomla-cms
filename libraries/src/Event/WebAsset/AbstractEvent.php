<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\WebAsset;

defined('JPATH_PLATFORM') or die;

use BadMethodCallException;
use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\WebAsset\WebAssetRegistry;

/**
 * Event class for WebAsset events
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
	 * @since   __DEPLOY_VERSION__
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
	 * @param   WebAssetRegistry  $value  The value to set
	 *
	 * @return  WebAssetRegistry
	 *
	 * @throws  BadMethodCallException  if the argument is not of the expected type
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function setSubject($value)
	{
		if (!$value || !($value instanceof WebAssetRegistry))
		{
			throw new BadMethodCallException("Argument 'subject' of event {$this->name} is not of the expected type");
		}

		return $value;
	}
}
