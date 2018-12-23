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
 * A command line cron job to export tables and data.
 *
 * @since  __DEPLOY_VERSION__
 */
class DbExporterCli extends JApplicationCli
{
	/**
	 * Entry point for CLI script
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function doExecute()
	{
		$this->out(JText::_('DbExporterCli'));
		$this->out('============================');
		$total_time = microtime(true);

		// Import the dependencies
		jimport('joomla.filesystem.file');

		$tables   = JFactory::getDbo()->getTableList();
		$prefix   = JFactory::getDbo()->getPrefix();
		$exp      = JFactory::getDbo()->getExporter()->withStructure();

		$iall    = $this->input->getString('all', null);
		$ihelp   = $this->input->getString('help', null);
		$itable  = $this->input->getString('table', null);
		$imode   = $this->input->getString('mode', null);
		$ipath   = $this->input->get('folder', null, 'folder');
		$zipfile = $ipath . 'jdata_exported_' . JFactory::getDate()->format('Y-m-d') . '.zip';

		if (!(($itable)||($iall)||($ihelp)))
		{
			if (!($ihelp))
			{
				$this->out('[WARNING] Missing or wrong parameters');
				$this->out();
			}

			$this->out('Usage: php exporter.php <options>');
			$this->out('php exporter.php --all                  dump all tables');
			$this->out('php exporter.php --mode zip             dump in zip format');
			$this->out('php exporter.php --table <table_name>   dump <table_name>');
			$this->out('php exporter.php --folder <folder_path> dump in <folder_path>');

			return;
		}

		if (($itable))
		{
			if (!in_array($itable, $tables))
			{
				$this->out('Not Found ' . $itable . '....');
				$this->out();

				return;
			}

			$tables = array($itable);
		}

		$zip = JArchive::getAdapter('zip');

		foreach ($tables as $table)
		{
			if (strpos(substr($table, 0, strlen($prefix)), $prefix) !== false)
			{
				$task_i_time = microtime(true);
				$filename    = $ipath . $table . '.xml';
				$this->out();
				$this->out('Exporting ' . $table . '....');
				$data = (string) $exp->from($table)->withData(true);

				if (JFile::exists($filename))
				{
					JFile::delete($filename);
				}

				JFile::write($filename, $data);

				if (($imode) && ($imode === 'zip'))
				{
					$zipFilesArray[] = array('name' => $table . '.xml', 'data' => $data);
					$zip->create($zipfile, $zipFilesArray);
					JFile::delete($filename);
				}

				$this->out('Exported in ' . round(microtime(true) - $task_i_time, 3));
			}
		}

		$this->out('Total time:' . round(microtime(true) - $total_time, 3));
	}
}

// Instantiate the application object, passing the class name to JCli::getInstance
// and use chaining to execute the application.
JApplicationCli::getInstance('DbExporterCli')->execute();
