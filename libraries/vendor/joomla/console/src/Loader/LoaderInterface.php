<?php
/**
 * Part of the Joomla Framework Console Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Console\Loader;

use Joomla\Console\CommandInterface;
use Joomla\Console\Exception\CommandNotFoundException;

/**
 * Interface defining a command loader.
 *
 * @since  __DEPLOY_VERSION__
 */
interface LoaderInterface
{
	/**
	 * Loads a command.
	 *
	 * @param   string  $name  The command to load.
	 *
	 * @return  CommandInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  CommandNotFoundException
	 */
	public function get(string $name): CommandInterface;

	/**
	 * Get the names of the registered commands.
	 *
	 * @return  string[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getNames(): array;

	/**
	 * Checks if a command exists.
	 *
	 * @param   string  $name  The command to check.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function has($name): bool;
}
