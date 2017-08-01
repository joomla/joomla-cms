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
/** @var \Joomla\CMS\Language\Language $lang */
$lang = JFactory::getLanguage();

/**
 * Note: Do NOT combine these lines with a Boolean Or (||) operator. That causes the default
 *       language (en-GB) files to only be loaded from the first directory that has a (partial)
 *       translation, leading to untranslated strings. See gh-17372 for context of this issue.
 */
$lang->load('files_joomla.sys', JPATH_SITE, null, false, true);

/**
 * A command line cron job to attempt to remove files that should have been deleted at update.
 *
 * @since  3.0
 */
class DeletefilesCli extends JApplicationCli
{
	/**
	 * Entry point for CLI script
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function doExecute()
	{
		// Import the dependencies
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		// We need the update script
		JLoader::register('JoomlaInstallerScript', JPATH_ADMINISTRATOR . '/components/com_admin/script.php');

		// Instantiate the class
		$class = new JoomlaInstallerScript;

		// Run the delete method
		$class->deleteUnexistingFiles();
	}
}

// Instantiate the application object, passing the class name to JCli::getInstance
// and use chaining to execute the application.
JApplicationCli::getInstance('DeletefilesCli')->execute();
