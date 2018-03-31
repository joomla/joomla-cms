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
use Joomla\String\StringHelper;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Descriptor\TextDescriptor as SymfonyTextDescriptor;

/**
 * Text object descriptor.
 *
 * @since  __DEPLOY_VERSION__
 */
final class TextDescriptor extends SymfonyTextDescriptor
{
	/**
	 * Describes an object.
	 *
	 * @param   OutputInterface  $output   The output object to use.
	 * @param   object           $object   The object to describe.
	 * @param   array            $options  Descriptor options.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function describe(OutputInterface $output, $object, array $options = [])
	{
		$this->output = $output;

		switch (true)
		{
			case $object instanceof Application:
				$this->describeJoomlaApplication($object, $options);

				break;

			case $object instanceof CommandInterface:
				$this->describeConsoleCommand($object, $options);

				break;

			default:
				parent::describe($output, $object, $options);
		}
	}

	/**
	 * Formats command aliases to show them in the command description.
	 *
	 * @param   CommandInterface  $command  The command to process
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function getCommandAliasesText(CommandInterface $command): string
	{
		$text    = '';
		$aliases = $command->getAliases();

		if ($aliases)
		{
			$text = '[' . implode('|', $aliases) . '] ';
		}

		return $text;
	}

	/**
	 * Describes a command.
	 *
	 * @param   CommandInterface  $command  The command being described.
	 * @param   array             $options  Descriptor options.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function describeConsoleCommand(CommandInterface $command, array $options)
	{
		$command->getSynopsis(true);
		$command->getSynopsis(false);
		$command->mergeApplicationDefinition($command->getApplication()->getDefinition(), false);

		$this->writeText('<comment>Usage:</comment>', $options);

		foreach (array_merge([$command->getSynopsis(true)], $command->getAliases()) as $usage)
		{
			$this->writeText("\n");
			$this->writeText('  ' . $usage, $options);
		}

		$this->writeText("\n");

		$definition = $command->getDefinition();

		if ($definition->getOptions() || $definition->getArguments())
		{
			$this->writeText("\n");
			$this->describeInputDefinition($definition, $options);
			$this->writeText("\n");
		}

		if ($help = $command->getProcessedHelp())
		{
			$this->writeText("\n");
			$this->writeText('<comment>Help:</comment>', $options);
			$this->writeText("\n");
			$this->writeText('  ' . str_replace("\n", "\n  ", $help), $options);
			$this->writeText("\n");
		}
	}

	/**
	 * Describes an application.
	 *
	 * @param   Application  $app      The application being described.
	 * @param   array        $options  Descriptor options.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function describeJoomlaApplication(Application $app, array $options)
	{
		$describedNamespace = $options['namespace'] ?? '';
		$description        = new ApplicationDescription($app, $describedNamespace);

		$version = $app->getLongVersion();

		if ($version !== '')
		{
			$this->writeText("$version\n\n", $options);
		}

		$this->writeText("<comment>Usage:</comment>\n");
		$this->writeText("  command [options] [arguments]\n\n");

		$this->describeInputDefinition(new InputDefinition($app->getDefinition()->getOptions()), $options);

		$this->writeText("\n");
		$this->writeText("\n");

		$commands   = $description->getCommands();
		$namespaces = $description->getNamespaces();

		if ($describedNamespace && $namespaces)
		{
			// Ensure all aliased commands are included when describing a specific namespace
			$describedNamespaceInfo = reset($namespaces);

			foreach ($describedNamespaceInfo['commands'] as $name)
			{
				$commands[$name] = $description->getCommand($name);
			}
		}

		$width = $this->getColumnWidth(
			call_user_func_array(
				'array_merge',
				array_map(
					function ($namespace) use ($commands)
					{
						return array_intersect($namespace['commands'], array_keys($commands));
					},
					$namespaces
				)
			)
		);

		if ($describedNamespace)
		{
			$this->writeText(sprintf('<comment>Available commands for the "%s" namespace:</comment>', $describedNamespace), $options);
		}
		else
		{
			$this->writeText('<comment>Available commands:</comment>', $options);
		}

		foreach ($namespaces as $namespace)
		{
			$namespace['commands'] = array_filter(
				$namespace['commands'],
				function ($name) use ($commands)
				{
					return isset($commands[$name]);
				}
			);

			if (!$namespace['commands'])
			{
				continue;
			}

			if (!$describedNamespace && ApplicationDescription::GLOBAL_NAMESPACE !== $namespace['id'])
			{
				$this->writeText("\n");
				$this->writeText(' <comment>' . $namespace['id'] . '</comment>', $options);
			}

			foreach ($namespace['commands'] as $name)
			{
				$this->writeText("\n");
				$spacingWidth   = $width - StringHelper::strlen($name);
				$command        = $commands[$name];
				$commandAliases = $name === $command->getName() ? $this->getCommandAliasesText($command) : '';

				$this->writeText(
					sprintf(
						'  <info>%s</info>%s%s', $name, str_repeat(' ', $spacingWidth), $commandAliases . $command->getDescription()
					),
					$options
				);
			}
		}

		$this->writeText("\n");
	}

	/**
	 * Calculate the column width for a group of commands.
	 *
	 * @param   CommandInterface[]|string[] $commands The commands to use for processing a width.
	 *
	 * @return  integer
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function getColumnWidth(array $commands): int
	{
		$widths = [];

		foreach ($commands as $command)
		{
			if ($command instanceof CommandInterface)
			{
				$widths[] = StringHelper::strlen($command->getName());

				foreach ($command->getAliases() as $alias)
				{
					$widths[] = StringHelper::strlen($alias);
				}
			}
			else
			{
				$widths[] = StringHelper::strlen($command);
			}
		}

		return $widths ? max($widths) + 2 : 0;
	}

	/**
	 * Writes text to the output.
	 *
	 * @param   string|array  $content  The message as an array of lines or a single string.
	 * @param   array         $options  The options to use for formatting the output.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function writeText($content, array $options = [])
	{
		$this->write(
			isset($options['raw_text']) && $options['raw_text'] ? strip_tags($content) : $content,
			isset($options['raw_output']) ? !$options['raw_output'] : true
		);
	}
}
