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
require_once JPATH_LIBRARIES . '/import.legacy.php';

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

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
 * A command line cron job to import tables and data.
 *
 * @since  3.7
 */
class DbImporterCli extends JApplicationCli
{
	/**
	 * Entry point for CLI script
	 *
	 * @return  void
	 *
	 * @since   3.7
	 */
	public function doExecute()
	{
		$this->out(JText::_('DbImporterCli'));
		$this->out('============================');
		$total_time = microtime(true);

		// Import the dependencies
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$pathPart = JPATH_ROOT . '/cli/dbdump/';
		$iall    = $this->input->getString('all', false);
		$ihelp   = $this->input->getString('help', false);
		$itable  = $this->input->getString('table', false);
		$tables  = JFolder::files($pathPart, '\.xml$');

		if (!(($itable)||($iall)||($ihelp)))
		{
			if (!($ihelp))
			{
				$this->out('[WARNING] Missing or wrong parameters');
				$this->out();
			}

			$this->out('Usage: php importer.php <options>');
			$this->out('[INFO] php importer.php --all                import all files');
			$this->out('[INFO] php importer.php --table <table_name> import <table_name>');

			return;
		}

		if ($this->input->getString('table', false))
		{
			$tables = array($itable . '.xml');
		}

		$db       = JFactory::getDbo();
		$prefix   = $db->getPrefix();

		foreach ($tables as $table)
		{
			$task_i_time = microtime(true);
			$percorso = $pathPart . $table;

			// Check file
			if (!JFile::exists($percorso))
			{
				$this->out('Not Found ' . $table);

				return false;
			}

			$table_name = str_replace('.xml', '', $table);
			$this->out('Importing ' . $table_name . ' from ' . $table);

			try
			{
				$imp = JFactory::getDbo()->getImporter()->from(JFile::read($percorso))->withStructure()->asXml();
			}
			catch (JDatabaseExceptionExecuting $e)
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
			catch (JDatabaseExceptionExecuting $e)
			{
				$this->out(' Error in DROP TABLE ' . $table_name . ' ' . $e);

				return false;
			}

			try
			{
				$imp->mergeStructure();
			}
			catch (JDatabaseExceptionExecuting $e)
			{
				$this->out('Error on mergeStructure' . $table . ' ' . $e);

				return false;
			}

			$this->out('Checked structure ' . $table);

			try
			{
				$imp->importData();
			}
			catch (JDatabaseExceptionExecuting $e)
			{
				$this->out('Error on importData' . $table . ' ' . $e);

				return false;
			}
			$this->out('Data loaded ' . $table . ' in ' . round(microtime(true) - $task_i_time, 3));
			$this->out();
		}

		$this->out('Total time:' . round(microtime(true) - $total_time, 3));
	}
}

// Instantiate the application object, passing the class name to JCli::getInstance
// and use chaining to execute the application.
JApplicationCli::getInstance('DbImporterCli')->execute();
