<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Service
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\Service\Service;
use Joomla\Service\CommandBusBuilder;

defined('JPATH_PLATFORM') or die;

/**
 * Default service layer class.
 *
 * @since  __DEPLOY_VERSION__
 */
class JService extends Service
{
	/**
	 * Constructor.
	 * 
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct()
	{
		// Get the regular dispatcher.
		$dispatcher = \JEventDispatcher::getInstance();

		// Get the command bus buidler.
		$commandBusBuilder = new CommandBusBuilder($dispatcher);

		// Build the command bus.
		$this->commandBus = $commandBusBuilder->getCommandBus();
	}
}
