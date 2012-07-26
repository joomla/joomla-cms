<?php
/**
 * Prepares a minimalist framework for unit testing.
 *
 * Joomla is assumed to include the /unittest/ directory.
 * eg, /path/to/joomla/unittest/
 *
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @link		http://www.phpunit.de/manual/current/en/installation.html
 */

// Load the custom initialisation file if it exists.
if (file_exists('config.php')) {
	include 'config.php';
}

// Define expected Joomla constants.

define('DS',			DIRECTORY_SEPARATOR);
define('_JEXEC',		1);

if (!defined('JPATH_BASE'))
{
	// JPATH_BASE can be defined in init.php
	// This gets around problems with soft linking the unittest folder into a Joomla tree,
	// or using the unittest framework from a central location.
	define('JPATH_BASE', dirname(dirname(dirname(__FILE__))));
}

if (!defined('JPATH_TESTS'))
{
	define('JPATH_TESTS', dirname(__FILE__));
}

// Fix magic quotes.
@ini_set('magic_quotes_runtime', 0);

// Maximise error reporting.

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Include the base test cases.
require_once JPATH_TESTS.'/JoomlaTestCase.php';
require_once JPATH_TESTS.'/JoomlaDatabaseTestCase.php';

// Include relative constants, JLoader and the jimport and jexit functions.
require_once JPATH_BASE.'/includes/defines.php';
require_once JPATH_LIBRARIES.'/import.php';

// Include the Joomla session library.
require_once JPATH_BASE.'/libraries/joomla/session/session.php';

// Exclude all of the tests from code coverage reports
PHPUnit_Util_Filter::addDirectoryToFilter(JPATH_BASE . '/tests');

// Set error handling.
JError::setErrorHandling(E_NOTICE, 'ignore');
JError::setErrorHandling(E_WARNING, 'ignore');
JError::setErrorHandling(E_ERROR, 'ignore');
