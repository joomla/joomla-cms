<?php
/**
 * Prepares a minimalist framework for unit testing.
 *
 * Joomla is assumed to include the /unittest/ directory.
 * eg, /path/to/joomla/unittest/
 *
 * @version		$Id: bootstrap.php 20196 2011-01-09 02:40:25Z ian $
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @link		http://www.phpunit.de/manual/current/en/installation.html
 */
// Load the custom initialisation file if it exists.
if (file_exists('config.php')) {
	include 'config.php';
}

// Include helper class
require_once(dirname(__FILE__).'/includes/JUnitHelper.php');

// Define expected Joomla constants.
define('DS', '/');

if (!defined('JPATH_BASE'))
{
	// JPATH_BASE can be defined in init.php
	// This gets around problems with soft linking the unittest folder into a Joomla tree,
	// or using the unittest framework from a central location.
	define('JPATH_BASE', JUnitHelper::normalize(dirname(__FILE__)).'/test_application');
}

if (!defined('JPATH_TESTS'))
{
	define('JPATH_TESTS', dirname(__FILE__));
}

// Fix magic quotes.
@ini_set('magic_quotes_runtime', 0);

// Maximise error reporting.

@ini_set('zend.ze1_compatibility_mode', '0');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the base test cases.
require_once JPATH_TESTS.'/includes/JoomlaTestCase.php';
require_once JPATH_TESTS.'/includes/JoomlaDatabaseTestCase.php';

// Include relative constants, JLoader and the jimport and jexit functions.
require_once JPATH_BASE.'/defines.php';
require_once JPATH_LIBRARIES.'/joomla/import.php';

// Exclude all of the tests from code coverage reports
PHP_CodeCoverage_Filter::getInstance()->addDirectoryToBlacklist(JPATH_TESTS);

// Set error handling.
JError::setErrorHandling(E_NOTICE, 'ignore');
JError::setErrorHandling(E_WARNING, 'ignore');
JError::setErrorHandling(E_ERROR, 'ignore');
