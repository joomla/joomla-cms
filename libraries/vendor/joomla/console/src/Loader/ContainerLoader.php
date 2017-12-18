<?php
/**
 * Part of the Joomla Framework Console Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Console\Loader;

use Joomla\Console\CommandInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Exception\CommandNotFoundException;

/**
 * PSR-11 compatible command loader.
 *
 * @since  __DEPLOY_VERSION__
 */
final class ContainerLoader implements LoaderInterface
{
	/**
	 * The service container.
	 *
	 * @var    ContainerInterface
	 * @since  __DEPLOY_VERSION__
	 */
	private $container;

	/**
	 * The command name to service ID map.
	 *
	 * @var    string[]
	 * @since  __DEPLOY_VERSION__
	 */
	private $commandMap;

	/**
	 * Constructor.
	 *
	 * @param   ContainerInterface  $container   A container from which to load command services.
	 * @param   array               $commandMap  An array with command names as keys and service IDs as values.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(ContainerInterface $container, array $commandMap)
	{
		$this->container  = $container;
		$this->commandMap = $commandMap;
	}

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
	public function get(string $name): CommandInterface
	{
		if (!$this->has($name))
		{
			throw new CommandNotFoundException(sprintf('Command "%s" does not exist.', $name));
		}

		return $this->container->get($this->commandMap[$name]);
	}

	/**
	 * Get the names of the registered commands.
	 *
	 * @return  string[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getNames(): array
	{
		return array_keys($this->commandMap);
	}

	/**
	 * Checks if a command exists.
	 *
	 * @param   string  $name  The command to check.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function has($name): bool
	{
		return isset($this->commandMap[$name]) && $this->container->has($this->commandMap[$name]);
	}
}
