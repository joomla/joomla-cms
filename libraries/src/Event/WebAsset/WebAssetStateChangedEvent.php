<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\WebAsset;

defined('JPATH_PLATFORM') or die;

use BadMethodCallException;
use Joomla\CMS\WebAsset\WebAssetItem;

/**
 * Event class for WebAsset events
 *
 * @since  __DEPLOY_VERSION__
 */
class WebAssetStateChangedEvent extends AbstractEvent
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
		// Check for required arguments
		foreach (array('asset', 'oldState', 'newState') as $argument)
		{
			if (!array_key_exists($argument, $arguments))
			{
				throw new BadMethodCallException("Argument '$argument' is required for event $name");
			}
		}

		if (!($arguments['asset'] instanceof WebAssetItem))
		{
			throw new BadMethodCallException("Argument 'asset' of event $name is not of the expected type");
		}

		parent::__construct($name, $arguments);
	}

	/**
	 * Return affected Asset object
	 *
	 * @return  WebAssetItem
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getAsset(): WebAssetItem
	{
		return $this->arguments['asset'];
	}

	/**
	 * Get previous state of the asset
	 *
	 * @return  int
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getOldState(): int
	{
		return (int) $this->arguments['oldState'];
	}

	/**
	 * Get new state of the asset
	 *
	 * @return  int
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getNewState(): int
	{
		return (int) $this->arguments['newState'];
	}
}
