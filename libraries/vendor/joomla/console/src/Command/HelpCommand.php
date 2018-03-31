<?php
/**
 * Part of the Joomla Framework Console Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Console\Command;

use Joomla\Console\AbstractCommand;
use Joomla\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Command to render a command's help data.
 *
 * @since  __DEPLOY_VERSION__
 */
class HelpCommand extends AbstractCommand
{
	/**
	 * Execute the command.
	 *
	 * @return  integer  The exit code for the command.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function execute(): int
	{
		$commandName = $this->getApplication()->input->get('command_name');
		$command     = $commandName === $this->getName() ? $this : $this->getApplication()->getCommand($commandName);

		$descriptor = new DescriptorHelper;

		$this->getHelperSet()->set($descriptor);

		$descriptor->describe($this->getApplication()->getConsoleOutput(), $command);

		return 0;
	}

	/**
	 * Initialise the command.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function initialise()
	{
		$this->setName('help');
		$this->setDescription('Show the help for a command');
		$this->setHelp(<<<'EOF'
The <info>%command.name%</info> command displays a command's help information:

<info>php %command.full_name% list</info>

To display the list of available commands, please use the <info>list</info> command.
EOF
		);

		$this->addArgument('command_name', InputArgument::OPTIONAL, 'The command name', 'help');
	}
}
