<?php
/**
 * @package    Joomla.Cli
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// We are a valid entry point.
const _JEXEC = 1;

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php'))
{
	require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(__DIR__));
	require_once JPATH_BASE . '/includes/defines.php';
}

// Get the framework.
require_once JPATH_BASE . '/includes/framework.php';

// Configure error reporting to maximum for CLI output.
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load Library language
$lang = JFactory::getLanguage();

// Try the files_joomla file in the current language (without allowing the loading of the file in the default language)
$lang->load('files_joomla.sys', JPATH_SITE, null, false, false)
// Fallback to the files_joomla file in the default language
|| $lang->load('files_joomla.sys', JPATH_SITE, null, true);

/**
 * A command line cron job to attempt to remove files that should have been deleted at update.
 *
 * @since  3.0
 */
class DeletefilesCli extends \Joomla\CMS\Application\CliApplication
{
	/**
	 * Entry point for CLI script
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	protected function doExecute()
	{
		// Import the dependencies
		JLoader::import('joomla.filesystem.file');
		JLoader::import('joomla.filesystem.folder');

		// We need the update script
		JLoader::register('JoomlaInstallerScript', JPATH_ADMINISTRATOR . '/components/com_admin/script.php');

		// Instantiate the class
		(new JoomlaInstallerScript)->deleteUnexistingFiles();
	}
}

// Set up the container
JFactory::getContainer()->share(
	'DeletefilesCli',
	function (\Joomla\DI\Container $container)
	{
		return new DeletefilesCli(
			null,
			null,
			null,
			null,
			$container->get(\Joomla\Event\DispatcherInterface::class),
			$container
		);
	},
	true
);
$app = JFactory::getContainer()->get('DeletefilesCli');
JFactory::$application = $app;
$app->execute();
