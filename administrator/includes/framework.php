<?php
/**
 * @package    Joomla.Administrator
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Version;

// System includes
require_once JPATH_LIBRARIES . '/bootstrap.php';

// Installation check, and check on removal of the install directory.
if (!file_exists(JPATH_CONFIGURATION . '/configuration.php')
	|| (filesize(JPATH_CONFIGURATION . '/configuration.php') < 10)
	|| (file_exists(JPATH_INSTALLATION . '/index.php') && (false === (new Version)->isInDevelopmentState())))
{
	if (file_exists(JPATH_INSTALLATION . '/index.php'))
	{
		header('Location: ../installation/index.php');

		exit();
	}
	else
	{
		echo 'No configuration file found and no installation code available. Exiting...';

		exit;
	}
}

// Pre-Load configuration. Don't remove the Output Buffering due to BOM issues, see JCode 26026
ob_start();
require_once JPATH_CONFIGURATION . '/configuration.php';
ob_end_clean();

// System configuration.
$config = new JConfig;

// Set the error_reporting, and adjust a global Error Handler
switch ($config->error_reporting)
{
	case 'default':
	case '-1':
		$errorHandler->scopeAt(0, true);

		break;

	case 'none':
	case '0':
		error_reporting(0);
		$errorHandler->scopeAt(0, true);

		break;

	case 'simple':
		error_reporting(E_ERROR | E_WARNING | E_PARSE);
		ini_set('display_errors', 1);
		$errorHandler->scopeAt(E_ERROR | E_WARNING | E_PARSE, true);

		break;

	case 'maximum':
		error_reporting(E_ALL);
		ini_set('display_errors', 1);
		$errorHandler->scopeAt(E_ALL, true);

		break;

	case 'development':
		error_reporting(-1);
		ini_set('display_errors', 1);
		$errorHandler->scopeAt(E_ALL, true);

		break;

	default:
		error_reporting($config->error_reporting);
		ini_set('display_errors', 1);

		if (is_int($config->error_reporting))
		{
			$errorHandler->scopeAt($config->error_reporting, true);
		}

		break;
}

define('JDEBUG', $config->debug);

if (JDEBUG)
{
	// Restore ErrorHandler default level
	$errorHandler->scopeAt(0x1FFF, true);
}

unset($config);
