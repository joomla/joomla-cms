<?php
/**
 * Part of the Joomla Framework Console Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Console\Descriptor;

use Joomla\Console\Application;
use Joomla\Console\CommandInterface;
use Joomla\Console\Exception\CommandNotFoundException;

/**
 * Describes an application.
 *
 * @since  __DEPLOY_VERSION__
 */
final class ApplicationDescription
{
	const GLOBAL_NAMESPACE = '_global';

	/**
	 * The application's aliased commands.
	 *
	 * @var    CommandInterface[]
	 * @since  __DEPLOY_VERSION__
	 */
	private $aliases;

	/**
	 * The application being described.
	 *
	 * @var    Application
	 * @since  __DEPLOY_VERSION__
	 */
	private $application;

	/**
	 * The application's commands.
	 *
	 * @var    CommandInterface[]
	 * @since  __DEPLOY_VERSION__
	 */
	private $commands;

	/**
	 * The command namespace to process.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	private $namespace = '';

	/**
	 * The application's command namespaces.
	 *
	 * @var    string[]
	 * @since  __DEPLOY_VERSION__
	 */
	private $namespaces;

	/**
	 * Flag indicating hidden commands should be displayed.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	private $showHidden;

	/**
	 * Constructor.
	 *
	 * @param   Application  $application  The application being described.
	 * @param   string       $namespace    The command namespace to process.
	 * @param   boolean      $showHidden   Flag indicating hidden commands should be displayed.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(Application $application, string $namespace = '', bool $showHidden = false)
	{
		$this->application = $application;
		$this->namespace   = $namespace;
		$this->showHidden  = $showHidden;
	}

	/**
	 * Get the application's command namespaces.
	 *
	 * @return  string[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getNamespaces(): array
	{
		if (null === $this->namespaces)
		{
			$this->inspectApplication();
		}

		return $this->namespaces;
	}

	/**
	 * Get the application's commands.
	 *
	 * @return  CommandInterface[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getCommands(): array
	{
		if (null === $this->commands)
		{
			$this->inspectApplication();
		}

		return $this->commands;
	}

	/**
	 * Get a command by name.
	 *
	 * @param   string  $name  The name of the command to retrieve.
	 *
	 * @return  CommandInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  CommandNotFoundException
	 */
	public function getCommand(string $name): CommandInterface
	{
		if (!isset($this->commands[$name]) && !isset($this->aliases[$name]))
		{
			throw new CommandNotFoundException(sprintf('Command %s does not exist.', $name));
		}

		return $this->commands[$name] ?? $this->aliases[$name];
	}

	/**
	 * Returns the namespace part of the command name.
	 *
	 * @param   string   $name   The command name to process
	 * @param   integer  $limit  The maximum number of parts of the namespace
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function extractNamespace(string $name, $limit = null)
	{
		$parts = explode(':', $name);
		array_pop($parts);

		return implode(':', $limit === null ? $parts : array_slice($parts, 0, $limit));
	}

	/**
	 * Inspects the application.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function inspectApplication()
	{
		$this->commands   = [];
		$this->namespaces = [];

		$all = $this->application->getAllCommands($this->namespace ? $this->application->findNamespace($this->namespace) : '');

		foreach ($this->sortCommands($all) as $namespace => $commands)
		{
			$names = [];

			/** @var CommandInterface $command */
			foreach ($commands as $name => $command)
			{
				if (!$command->getName() || (!$this->showHidden && $command->isHidden()))
				{
					continue;
				}

				if ($command->getName() === $name)
				{
					$this->commands[$name] = $command;
				}
				else
				{
					$this->aliases[$name] = $command;
				}

				$names[] = $name;
			}

			$this->namespaces[$namespace] = ['id' => $namespace, 'commands' => $names];
		}
	}

	/**
	 * Sort a set of commands.
	 *
	 * @param   CommandInterface[]  $commands  The commands to sort.
	 *
	 * @return  CommandInterface[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function sortCommands(array $commands): array
	{
		$namespacedCommands = [];
		$globalCommands     = [];

		foreach ($commands as $name => $command)
		{
			$key = $this->extractNamespace($name, 1);

			if (!$key)
			{
				$globalCommands[self::GLOBAL_NAMESPACE][$name] = $command;
			}
			else
			{
				$namespacedCommands[$key][$name] = $command;
			}
		}

		ksort($namespacedCommands);
		$namespacedCommands = array_merge($globalCommands, $namespacedCommands);

		foreach ($namespacedCommands as &$commandsSet)
		{
			ksort($commandsSet);
		}

		// Unset reference to keep scope clear
		unset($commandsSet);

		return $namespacedCommands;
	}
}
