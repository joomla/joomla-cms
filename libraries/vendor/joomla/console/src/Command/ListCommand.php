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
 * Command listing all available commands.
 *
 * @since  __DEPLOY_VERSION__
 */
class ListCommand extends AbstractCommand
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
		$descriptor = new DescriptorHelper;

		$this->getHelperSet()->set($descriptor);

		$descriptor->describe(
			$this->getApplication()->getConsoleOutput(),
			$this->getApplication(),
			[
				'namespace' => $this->getApplication()->input->getString('namespace', ''),
			]
		);

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
		$this->setName('list');
		$this->setDescription("List the application's available commands");
		$this->addArgument('namespace', InputArgument::OPTIONAL, 'The namespace name');
		$this->setHelp(<<<'EOF'
The <info>%command.name%</info> command lists all of the application's commands:

  <info>php %command.full_name%</info>
EOF
		);
	}
}
