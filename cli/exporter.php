<?php
/**
 * @package    Joomla.Cli
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// We are a valid entry point.
const _JEXEC = 1;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\Archive\Archive;

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
 * A command line cron job to export tables and data.
 *
 * @since  __DEPLOY_VERSION__
 */
class DbExporterCli extends \Joomla\CMS\Application\CliApplication
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
		$this->out(Text::_('DbExporterCli'));
		$this->out('============================');
		$total_time = microtime(true);

		$tables   = Factory::getDbo()->getTableList();
		$prefix   = Factory::getDbo()->getPrefix();
		$exp      = Factory::getDbo()->getExporter()->withStructure();

		$iall    = $this->input->getString('all', null);
		$ihelp   = $this->input->getString('help', null);
		$itable  = $this->input->getString('table', null);
		$imode   = $this->input->getString('mode', null);
		$ipath   = $this->input->get('folder', null, 'folder');
		$zipfile = $ipath . 'jdata_exported_' . Factory::getDate()->format('Y-m-d') . '.zip';

		if (!($itable || $iall || $ihelp))
		{
			if (!$ihelp)
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

		if ($itable)
		{
			if (!\in_array($itable, $tables))
			{
				$this->out('Not Found ' . $itable . '....');
				$this->out();

				return;
			}

			$tables = array($itable);
		}

		$archive = new Archive;
		$zip = $archive->getAdapter('zip');

		foreach ($tables as $table)
		{
			if (strpos(substr($table, 0, strlen($prefix)), $prefix) !== false)
			{
				$task_i_time = microtime(true);
				$filename    = $ipath . '/' . $table . '.xml';
				$this->out();
				$this->out('Exporting ' . $table . '....');
				$data = (string) $exp->from($table)->withData(true);

				if (File::exists($filename))
				{
					File::delete($filename);
				}

				File::write($filename, $data);

				if ($imode && $imode === 'zip')
				{
					$zipFilesArray[] = array('name' => $table . '.xml', 'data' => $data);
					$zip->create($zipfile, $zipFilesArray);
					File::delete($filename);
				}

				$this->out('Exported in ' . round(microtime(true) - $task_i_time, 3));
			}
		}

		$this->out('Total time: ' . round(microtime(true) - $total_time, 3));
	}
}

// Set up the container
Factory::getContainer()->share(
	'DbExporterCli',
	function (\Joomla\DI\Container $container)
	{
		return new DbExporterCli(
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
$app = Factory::getContainer()->get('DbExporterCli');
Factory::$application = $app;
$app->execute();
