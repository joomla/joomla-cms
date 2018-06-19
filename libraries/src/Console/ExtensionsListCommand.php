<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

defined('JPATH_PLATFORM') or die;

use Joomla\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Joomla\CMS\Factory;

/**
 * Console command for checking if there are pending extension updates
 *
 * @since  4.0.0
 */
class ExtensionsListCommand extends AbstractCommand
{
	/*
	 * Stores the installed Extensions
	 */
	private $extensions;

	/**
	 * Stores the Input Object
	 * @var
	 * @since 4.0
	 */
	private $cliInput;

	/**
	 * SymfonyStyle Object
	 * @var
	 * @since 4.0
	 */
	private $ioStyle;

	/**
	 * Configures the IO
	 *
	 * @return void
	 *
	 * @since 4.0
	 */
	private function configureIO()
	{
		$this->cliInput = $this->getApplication()->getConsoleInput();
		$this->ioStyle = new SymfonyStyle($this->getApplication()->getConsoleInput(), $this->getApplication()->getConsoleOutput());
	}

	/**
	 * Execute the command.
	 *
	 * @return  integer  The exit code for the command.
	 *
	 * @since   4.0.0
	 */
	public function execute(): int
	{
		$this->configureIO();
		$extensions = $this->getExtensions();
		$type = $this->cliInput->getOption('type');
		if ($type)
		{
			$extensions = $this->filterExtensionsBasedOn($type);
		}

		if (empty($extensions))
		{
			$this->ioStyle->error("Cannot find extensions of the type '$type' specified.");
			return 0;
		}

		$extensions = $this->getExtensionsNameAndId($extensions);

		$this->ioStyle->title('Installed extensions.');
		$this->ioStyle->table(['Name', 'Extension ID', 'Version', 'Type', 'Active'], $extensions);
		return 0;
	}

	/**
	 * Initialise the command.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function initialise()
	{
		$this->setName('extension:list');
		$this->setDescription('List installed extensions');

		$this->addOption('type', null, InputOption::VALUE_REQUIRED, 'Type of the extension');

		$help = "The <info>%command.name%</info> List all currently installed extensions
				\nUsage: <info>php %command.full_name%</info> --type=<extensiontype>";

		$this->setHelp($help);
	}

	/**
	 * Retrieves all extensions
	 *
	 * @return mixed
	 *
	 * @since 4.0
	 */
	public function getExtensions()
	{
		if (!$this->extensions)
		{
			$this->setExtensions();
		}
		return $this->extensions;
	}

	/**
	 * Retrieves the extension from the model and sets the class variable
	 *
	 * @param   null  $extensions  Array of extensions
	 *
	 * @return void
	 *
	 * @since 4.0
	 */
	public function setExtensions($extensions = null)
	{
		if (!$extensions)
		{
			$this->extensions = $this->getAllExtensionsFromDB();
		}
		else
		{
			$this->extensions = $extensions;
		}
	}

	/**
	 * Retrieves extension list from DB
	 *
	 * @return array
	 *
	 * @since 4.0
	 */
	private function getAllExtensionsFromDB()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__extensions');
		$db->setQuery($query);
		$extensions = $db->loadAssocList('extension_id');

		return $extensions;
	}

	/**
	 * Transforms extension arrays into required form
	 *
	 * @param   array  $extensions  Array of extensions
	 *
	 * @return array
	 *
	 * @since 4.0
	 */
	private function getExtensionsNameAndId($extensions)
	{
		$extInfo = [];
		foreach ($extensions as $key => $extension)
		{
			$manifest = json_decode($extension['manifest_cache']);
			$extInfo[] = [
				$extension['name'],
				$extension['extension_id'],
				$manifest->version,
				$extension['type'],
				$extension['enabled'] == 1 ? 'Yes' : 'No',
			];
		}

		return $extInfo;
	}

	/**
	 * Filters the extension type
	 *
	 * @param   string  $type  Extension type
	 *
	 * @return array
	 *
	 * @since 4.0
	 */
	private function filterExtensionsBasedOn($type)
	{
		$extensions = [];

		foreach ($this->extensions as $key => $extension)
		{
			if ($extension['type'] == $type)
			{
				$extensions[] = $extension;
			}
		}

		return $extensions;
	}
}
