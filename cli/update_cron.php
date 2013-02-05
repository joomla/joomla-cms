<?php
/**
 * @package		Joomla.Cli
 *
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Make sure we're being called from the command line, not a web interface
if (array_key_exists('REQUEST_METHOD', $_SERVER)) die();

/**
 * This is a CRON script which should be called from the command-line, not the
 * web. For example something like:
 * /usr/bin/php /path/to/site/cli/update_cron.php
 */

// Set flag that this is a parent file.
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);

error_reporting(E_ALL | E_NOTICE);
ini_set('display_errors', 1);

// Load system defines
if (file_exists(dirname(dirname(__FILE__)) . '/defines.php'))
{
	require_once dirname(dirname(__FILE__)) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(dirname(__FILE__)));
	require_once JPATH_BASE . '/includes/defines.php';
}

require_once JPATH_LIBRARIES . '/import.php';
require_once JPATH_LIBRARIES . '/cms.php';

// Force library to be in JError legacy mode
JError::$legacy = true;

// Load the configuration
require_once JPATH_CONFIGURATION . '/configuration.php';

/**
 * This script will fetch the update information for all extensions and store
 * them in the database, speeding up your administrator.
 *
 * @package  Joomla.CLI
 * @since    2.5
 */
class Updatecron extends JApplicationCli
{
	/**
	 * Entry point for the script
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function doExecute()
	{
		// Purge all old records
		$db = JFactory::getDBO();

		// Get the update cache time
		jimport('joomla.application.component.helper');
		$component = JComponentHelper::getComponent('com_installer');

		$params = $component->params;
		$cache_timeout = $params->get('cachetimeout', 6, 'int');
		$cache_timeout = 3600 * $cache_timeout;

		// Find all updates
		$this->out('Fetching updates...');
		$updater = JUpdater::getInstance();
		$results = $updater->findUpdates(0, $cache_timeout);
		$this->out('Finished fetching updates');
	}
}

JApplicationCli::getInstance('Updatecron')->execute();
