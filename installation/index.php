<?php
/**
 * @version		$Id$
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// PHP 5 check
if (version_compare(PHP_VERSION, '5.2.0', '<')) {
	die('Your host needs to use PHP 5.2.0 or higher to run Joomla 1.6.');
}

define('_JEXEC', 1);

define('JPATH_BASE', dirname(__FILE__));

define('DS', DIRECTORY_SEPARATOR);

// Set path constants.
$parts = explode(DS, JPATH_BASE);
array_pop($parts);

define('JPATH_ROOT',			implode(DS, $parts));
define('JPATH_SITE',			JPATH_ROOT);
define('JPATH_CONFIGURATION',	JPATH_ROOT);
define('JPATH_ADMINISTRATOR',	JPATH_ROOT.DS.'administrator');
define('JPATH_LIBRARIES',		JPATH_ROOT.DS.'libraries');
define('JPATH_PLUGINS',			JPATH_ROOT.DS.'plugins');
define('JPATH_INSTALLATION',	JPATH_ROOT.DS.'installation');
define('JPATH_THEMES',			JPATH_BASE);
define('JPATH_CACHE',			JPATH_ROOT.DS.'cache');

/*
 * Joomla! system checks.
 */
error_reporting(E_ALL);
@set_magic_quotes_runtime(0);
@ini_set('zend.ze1_compatibility_mode', '0');

/*
 * Check for existing configuration file.
 */
if (file_exists(JPATH_CONFIGURATION.DS.'configuration.php') && (filesize(JPATH_CONFIGURATION.DS.'configuration.php') > 10) && !file_exists(JPATH_INSTALLATION.DS.'index.php')) {
	header('Location: ../index.php');
	exit();
}

/*
 * Joomla! system startup
 */

// Bootstrap the Joomla! Framework.
require_once(JPATH_LIBRARIES.DS.'joomla'.DS.'import.php');

// Joomla! library imports.
jimport('joomla.database.table');
jimport('joomla.user.user');
jimport('joomla.environment.uri');
jimport('joomla.html.parameter');
jimport('joomla.utilities.utility');
jimport('joomla.language.language');
jimport('joomla.utilities.string');

// Create the application object.
$app = & JFactory::getApplication('installation');

// Initialize the application.
$app->initialise();

// Render the document.
$app->render();

/**
 * RETURN THE RESPONSE
 */
echo JResponse::toString();