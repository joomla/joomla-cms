<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Table\Table;
use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;


/**
 * Console command for removing extensions
 *
 * @since  4.0.0
 */
class ExtensionRemoveCommand extends AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  4.0
	 */
	protected static $defaultName = 'extension:remove';

	/**
	 * @var InputInterface
	 * @since version
	 */
	private $cliInput;

	/**
	 * @var SymfonyStyle
	 * @since version
	 */
	private $ioStyle;

	/**
	 * Configures the IO
	 *
	 * @param   InputInterface   $input   Console Input
	 * @param   OutputInterface  $output  Console Output
	 *
	 * @return void
	 *
	 * @since 4.0
	 *
	 */
	private function configureIO(InputInterface $input, OutputInterface $output)
	{
		$this->cliInput = $input;
		$this->ioStyle = new SymfonyStyle($input, $output);
	}

	/**
	 * Initialise the command.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function configure(): void
	{
		$this->addArgument(
			'extension_id',
			InputArgument::REQUIRED,
			'ID of extension to be removed (run extension:list command to check)'
		);
		$this->setDescription('Removes an extension');

		$help = <<<'EOF'
The <info>%command.name%</info> is used to uninstall extensions.  
The command requires one argument, the ID of the extension to uninstall.  
You may find this ID by running the <info>extension:list</info> command.

<info>php %command.full_name% <extension_id></info>
EOF;
		$this->setHelp($help);
	}

	/**
	 * Gets the extension from DB
	 *
	 * @return boolean
	 *
	 * @since 4.0
	 */
	protected function getExtension()
	{
		return Table::getInstance('extension');
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
		$this->configureIO($input, $output);
		$extensionId = $this->cliInput->getArgument('extensionId');

		$extension = $this->getExtension();

		if ((int) $extensionId === 0 || !$extension->load($extensionId))
		{
			$this->ioStyle->error("Extension with ID of $extensionId not found.");

			return 0;
		}

		$response = $this->ioStyle->ask('Are you sure you want to remove this extension?', 'yes/no');

		if (strtolower($response) === 'yes')
		{
			if ($extension->type && $extension->type != 'language')
			{
				$installer = Installer::getInstance();
				$result    = $installer->uninstall($extension->type, $extensionId);

				if ($result)
				{
					$this->ioStyle->success('Extension removed!');
				}
			}
		}
		elseif (strtolower($response) === 'no')
		{
			$this->ioStyle->note('Extension not removed.');

			return 0;
		}
		else
		{
			$this->ioStyle->warning('Invalid response');

			return 2;
		}
	}
}
