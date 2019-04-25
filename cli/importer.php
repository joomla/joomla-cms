<?php
/**
 * @package    Joomla.Cli
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// We are a valid entry point.
const _JEXEC = 1;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Exception\ExceptionHandler;

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

// Load Library language
$lang = Factory::getLanguage();

// Try the files_joomla file in the current language (without allowing the loading of the file in the default language)
$lang->load('files_joomla.sys', JPATH_SITE, null, false, false)
// Fallback to the files_joomla file in the default language
|| $lang->load('files_joomla.sys', JPATH_SITE, null, true);

/**
 * A command line cron job to import tables and data.
 *
 * @since  __DEPLOY_VERSION__
 */
class DbImporterCli extends \Joomla\CMS\Application\CliApplication
{
	/**
	 * Entry point for CLI script
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function doExecute()
	{
		$this->out(JText::_('DbImporterCli'));
		$this->out('============================');
		$total_time = microtime(true);

		$ipath  = $this->input->get('folder', null, 'folder');
		$iall   = $this->input->getString('all', null);
		$ihelp  = $this->input->getString('help', null);
		$itable = $this->input->getString('table', null);
		$tables = Folder::files($ipath, '\.xml$');

		if (!($itable || $iall || $ihelp))
		{
			if (!$ihelp)
			{
				$this->out('[WARNING] Missing or wrong parameters');
				$this->out();
			}

			$this->out('Usage: php importer.php <options>');
			$this->out('php importer.php --all                  import all files');
			$this->out('php importer.php --table <table_name>   import <table_name>');
			$this->out('php importer.php --folder <folder_path> import from <folder_path>');

			return;
		}

		if ($this->input->getString('table', false))
		{
			$tables = array($itable . '.xml');
		}

		$db       = Factory::getDbo();
		$prefix   = $db->getPrefix();

		foreach ($tables as $table)
		{
			$task_i_time = microtime(true);
			$percorso    = $ipath . '/' . $table;

			// Check file
			if (!File::exists($percorso))
			{
				$this->out('Not Found ' . $table);

				return false;
			}

			$table_name = str_replace('.xml', '', $table);
			$this->out('Importing ' . $table_name . ' from ' . $table);

			try
			{
				$imp = Factory::getDbo()->getImporter()->from(file_get_contents($percorso))->withStructure()->asXml();
			}
			catch (Exception $e)
			{
				$this->out('Error on getImporter' . $table . ' ' . $e);

				return false;
			}

			$this->out('Reading data from ' . $table);

			try
			{
				$this->out('Drop ' . $table_name);
				$db->dropTable($table_name, true);
			}
			catch (Exception $e)
			{
				$this->out(' Error in DROP TABLE ' . $table_name . ' ' . $e);

				return false;
			}

			try
			{
				$imp->mergeStructure();
			}
			catch (Exception $e)
			{
				$this->out('Error on mergeStructure' . $table . ' ' . $e);

				return false;
			}

			$this->out('Checked structure ' . $table);

			try
			{
				$imp->importData();
			}
			catch (Exception $e)
			{
				$this->out('Error on importData' . $table . ' ' . $e);

				return false;
			}
			$this->out('Data loaded ' . $table . ' in ' . round(microtime(true) - $task_i_time, 3));
			$this->out();
		}

		$this->out('Total time: ' . round(microtime(true) - $total_time, 3));
	}
}

// Set up the container
Factory::getContainer()->share(
	'DbImporterCli',
	function (\Joomla\DI\Container $container)
	{
		return new FinderCli(
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
$app = Factory::getContainer()->get('DbImporterCli');
Factory::$application = $app;
$app->execute();
