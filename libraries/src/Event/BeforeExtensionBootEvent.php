<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event;

use Joomla\CMS\Application\CMSApplication;
use Joomla\DI\Container;

defined('JPATH_PLATFORM') or die;

/**
 * Event class for representing the extensions's `onBeforeExtensionBoot` event
 *
 * @since  __DEPLOY_VERSION__
 */
class BeforeExtensionBootEvent extends AbstractImmutableEvent
{
	/**
	 * Get the event's extension type. Can be:
	 * - component
	 *
	 * @return  string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getExtensionType(): string
	{
		return $this->getArgument('type');
	}

	/**
	 * Get the event's extension name.
	 *
	 * @return  string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getExtensionName(): string
	{
		return $this->getArgument('extensionName');
	}

	/**
	 * Get the event's container object
	 *
	 * @return  Container
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getContainer(): Container
	{
		return $this->getArgument('container');
	}
}
