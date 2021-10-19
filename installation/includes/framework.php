<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Application
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/*
 * Joomla system checks.
 */

const JDEBUG = false;

/*
 * Check if a configuration file already exists.
 */

if (file_exists(JPATH_CONFIGURATION . '/configuration.php')
	&& (filesize(JPATH_CONFIGURATION . '/configuration.php') > 10)
	&& !file_exists(JPATH_INSTALLATION . '/index.php'))
{
	header('Location: ../index.php');
	exit();
}

/*
 * Joomla system startup.
 */

// Import the Joomla Platform.
require_once JPATH_LIBRARIES . '/bootstrap.php';

if (JDEBUG)
{
	// Set new Exception handler with debug enabled
	$errorHandler->setExceptionHandler(
		[
			new \Symfony\Component\ErrorHandler\ErrorHandler(null, true),
			'renderException'
		]
	);
}
