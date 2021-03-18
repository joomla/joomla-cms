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
 * Command listing all available commands.
 *
 * @since  __DEPLOY_VERSION__
 */
class ListCommand extends AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $defaultName = 'list';

	/**
	 * Configure the command.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function configure(): void
	{
		$this->setDescription("List the application's available commands");
		$this->addArgument('namespace', InputArgument::OPTIONAL, 'The namespace name');
		$this->setHelp(<<<'EOF'
The <info>%command.name%</info> command lists all of the application's commands:

  <info>php %command.full_name%</info>
EOF
		);
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
		$descriptor = new DescriptorHelper;

		if ($this->getHelperSet() !== null)
		{
			$this->getHelperSet()->set($descriptor);
		}

		$descriptor->describe(
			$output,
			$this->getApplication(),
			[
				'namespace' => $input->getArgument('namespace'),
			]
		);

		return 0;
	}
}
