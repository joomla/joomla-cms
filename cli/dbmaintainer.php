#!/usr/bin/php
<?php
/**
 * Database maintainer.
 *
 * Available options:
 * --check
 * --optimize
 * --backup
 *
 * -v --verbose
 *
 * @package     Joomla.CMS
 * @subpackage  CLI
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

'cli' == PHP_SAPI || die('This script must be executed from the command line'."\n");
version_compare(PHP_VERSION, '5.3.2', '>=') || 	die('This script requires PHP >= 5.3.2'."\n");

define('_JEXEC', 1);

ini_set('display_errors', '1');
ini_set('error_reporting', E_ALL | E_STRICT);

define('JPATH_BASE', __DIR__);
define('JPATH_SITE', __DIR__);

define('JPATH_ROOT', dirname(__DIR__));

require JPATH_ROOT . '/libraries/import.php';

require JPATH_ROOT . '/libraries/cms/cmsloader.php';

JCmsLoader::setup();

require JPATH_ROOT . '/configuration.php';

jimport('joomla.filesystem.file');

JError::$legacy = false;

/**
 * JDbMaintainer
 */
class JDbMaintainer extends JApplicationCli
{
	private $verbose = true;

	/**
	 * Execute the application.
	 */
	public function execute()
	{
		$this->verbose = ($this->input->get('verbose') || $this->input->get('v')) ? true : false;

		$this->out('Joomla! Database maintainer');

		$maintainer = JDatabaseMaintainer::getInstance(JFactory::getDbo(), $this->verbose);

		if ($this->input->get('check'))
		{
			$this->out('Check: ' . $maintainer->check());
		}

		if ($this->input->get('optimize'))
		{
			$maintainer->optimize();
		}

		if ($this->input->get('backup'))
		{
			$backupPath = $this->input->get('backuppath', JPATH_ROOT . '/administrator/backups');

			$maintainer->backup($backupPath);
		}

		$this->out('Finished =;)');
	}

	public function out($text = '', $nl = true)
	{
		if (!$this->verbose)
		{
			return;
		}

		parent::out($text, $nl);
	}

}

try
{
	// Execute the application.
	JApplicationCli::getInstance('JDbMaintainer')->execute();

	exit(0);
}
catch (Exception $e)
{
	// An exception has been caught, just echo the message.
	fwrite(STDOUT, $e->getMessage() . "\n");

	exit($e->getCode());
}//try
