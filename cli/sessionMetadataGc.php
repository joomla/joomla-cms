<?php
/**
 * @package    Joomla.Cli
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * This is a CRON script to delete expired optional session metadata which should be called from the command-line, not the
 * web. For example something like:
 * /usr/bin/php /path/to/site/cli/sessionMetadataGc.php
 */

// Initialize Joomla framework
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

/**
 * Cron job to trash expired session metadata.
 *
 * @since  3.8.6
 */
class SessionMetadataGc extends JApplicationCli
{
	/**
	 * Entry point for the script
	 *
	 * @return  void
	 *
	 * @since   3.8.6
	 */
	public function doExecute()
	{
		$metadataManager = new \Joomla\CMS\Session\MetadataManager($this, \Joomla\CMS\Factory::getDbo());
		$sessionExpire   = \Joomla\CMS\Factory::getSession()->getExpire();

		$metadataManager->deletePriorTo(time() - $sessionExpire);
	}
}

JApplicationCli::getInstance('SessionMetadataGc')->execute();
