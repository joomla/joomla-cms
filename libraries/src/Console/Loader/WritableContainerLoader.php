<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console\Loader;

use Joomla\Console\Command\AbstractCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Exception\CommandNotFoundException;

\defined('JPATH_PLATFORM') or die;

/**
 * PSR-11 compatible writable command loader.
 *
 * @since  4.0.0
 */
final class WritableContainerLoader implements WritableLoaderInterface
{
	/**
	 * The service container.
	 *
	 * @var    ContainerInterface
	 * @since  4.0.0
	 */
	private $container;

	/**
	 * The command name to service ID map.
	 *
	 * @var    string[]
	 * @since  4.0.0
	 */
	private $commandMap;

	/**
	 * Constructor.
	 *
	 * @param   ContainerInterface  $container   A container from which to load command services.
	 * @param   array               $commandMap  An array with command names as keys and service IDs as values.
	 *
	 * @since   4.0.0
	 */
	public function __construct(ContainerInterface $container, array $commandMap)
	{
		$this->container  = $container;
		$this->commandMap = $commandMap;
	}

	/**
	 * Adds a command to the loader.
	 *
	 * @param   string  $commandName  The name of the command to load.
	 * @param   string  $className    The fully qualified class name of the command.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function add(string $commandName, string $className)
	{
		$this->commandMap[$commandName] = $className;
	}

	/**
	 * Loads a command.
	 *
	 * @param   string  $name  The command to load.
	 *
	 * @return  AbstractCommand
	 *
	 * @since   4.0.0
	 * @throws  CommandNotFoundException
	 */
	public function get(string $name): AbstractCommand
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
	 * @since   4.0.0
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
	 * @since   4.0.0
	 */
	public function has($name): bool
	{
		return isset($this->commandMap[$name]) && $this->container->has($this->commandMap[$name]);
	}
}
