<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
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
	 * Exit Code for extenions remove abort
	 * @since
	 */
	public const REMOVE_ABORT = 3;

	/**
	 * Exit Code for extenions remove failure
	 * @since
	 */
	public const REMOVE_FAILED = 1;

	/**
	 * Exit Code for invalid response
	 * @since
	 */
	public const REMOVE_INVALID_RESPONSE = 5;

	/**
	 * Exit Code for invalid type
	 * @since
	 */
	public const REMOVE_INVALID_TYPE = 6;

	/**
	 * Exit Code for extenions locked remove failure
	 * @since
	 */
	public const REMOVE_LOCKED = 4;

	/**
	 * Exit Code for extenions not found
	 * @since
	 */
	public const REMOVE_NOT_FOUND = 2;

	/**
	 * Exit Code for extenions remove success
	 * @since
	 */
	public const REMOVE_SUCCESSFUL = 0;

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
	private function configureIO(InputInterface $input, OutputInterface $output): void
	{
		$this->cliInput = $input;
		$this->ioStyle = new SymfonyStyle($input, $output);
		$language = Factory::getLanguage();
		$language->load('', JPATH_ADMINISTRATOR, null, false, false) ||
		$language->load('', JPATH_ADMINISTRATOR, null, true);
		$language->load('com_installer', JPATH_ADMINISTRATOR, null, false, false)||
		$language->load('com_installer', JPATH_ADMINISTRATOR, null, true);
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
			'extensionId',
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
	 * Internal function to execute the command.
	 *
	 * @param   InputInterface   $input   The input to inject into the command.
	 * @param   OutputInterface  $output  The output to inject into the command.
	 *
	 * @return  integer  The command exit code
	 *
	 * @since   4.0.0
	 */
	protected function doExecute(InputInterface $input, OutputInterface $output): int
	{
		$this->configureIO($input, $output);
		$extensionId = $this->cliInput->getArgument('extensionId');

		$response = $this->ioStyle->ask('Are you sure you want to remove this extension?', 'yes/no');

		if (strtolower($response) === 'yes')
		{
			// Get an installer object for the extension type
			$installer = Installer::getInstance();
			$row       = new \Joomla\CMS\Table\Extension(Factory::getDbo());

			if ((int) $extensionId === 0 || !$row->load($extensionId))
			{
				$this->ioStyle->error("Extension with ID of $extensionId not found.");

				return self::REMOVE_NOT_FOUND;
			}

			// Do not allow to uninstall locked extensions.
			if ((int) $row->locked === 1)
			{
				$this->ioStyle->error(Text::sprintf('COM_INSTALLER_UNINSTALL_ERROR_LOCKED_EXTENSION', $row->name, $extensionId));

				return self::REMOVE_LOCKED;
			}

			if ($row->type)
			{
				if (!$installer->uninstall($row->type, $extensionId))
				{
					$this->ioStyle->error('Extension not removed.');

					return self::REMOVE_FAILED;
				}

				$this->ioStyle->success('Extension removed!');

				return self::REMOVE_SUCCESSFUL;
			}

			return self::REMOVE_INVALID_TYPE;
		}
		elseif (strtolower($response) === 'no')
		{
			$this->ioStyle->note('Extension not removed.');

			return self::REMOVE_ABORT;
		}

		$this->ioStyle->warning('Invalid response');

		return self::REMOVE_INVALID_RESPONSE;
	}
}
