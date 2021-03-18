<?php
/**
 * Part of the Joomla Framework Console Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Console\Command;

use Joomla\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to render a command's help data.
 *
 * @since  __DEPLOY_VERSION__
 */
class HelpCommand extends AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $defaultName = 'help';

	/**
	 * The command to process help for
	 *
	 * @var    AbstractCommand|null
	 * @since  __DEPLOY_VERSION__
	 */
	private $command;

	/**
	 * Configure the command.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function configure(): void
	{
		$this->setDescription('Show the help for a command');
		$this->setHelp(<<<'EOF'
The <info>%command.name%</info> command displays a command's help information:

<info>php %command.full_name% list</info>

To display the list of available commands, please use the <info>list</info> command.
EOF
		);

		$this->addArgument('command_name', InputArgument::OPTIONAL, 'The command name', 'help');
	}

	/**
	 * Internal function to execute the command.
	 *
	 * @param   InputInterface   $input   The input to inject into the command.
	 * @param   OutputInterface  $output  The output to inject into the command.
	 *
	 * @return  integer  The command exit code
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function doExecute(InputInterface $input, OutputInterface $output): int
	{
		if (!$this->command)
		{
			$this->command = $this->getApplication()->getCommand($input->getArgument('command_name'));
		}

		$descriptor = new DescriptorHelper;

		if ($this->getHelperSet() !== null)
		{
			$this->getHelperSet()->set($descriptor);
		}

		$descriptor->describe($output, $this->command);

		return 0;
	}

	/**
	 * Set the command whose help is being presented.
	 *
	 * @param   AbstractCommand  $command  The command to process help for.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setCommand(AbstractCommand $command): void
	{
		$this->command = $command;
	}
}
