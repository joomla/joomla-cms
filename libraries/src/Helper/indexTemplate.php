<?php

/**
 * File automatically generated
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') || die;

// Defines.
define('JPATH_BASE', {{BASEFOLDER}});
define('JPATH_ROOT', {{ROOTFOLDER}});
define('JPATH_PUBLIC', {{PUBLICFOLDER}});

// Define the application's minimum supported PHP version as a constant so it can be referenced within the application.
define('JOOMLA_MINIMUM_PHP', '8.1.0');

if (version_compare(PHP_VERSION, JOOMLA_MINIMUM_PHP, '<')) {
    {{DIECONDITIONAL}}
}

/**
 * Constant that is checked in included files to prevent direct access.
 * define() is used rather than "const" to not error for PHP 5.2 and lower
 */
define('_JEXEC', 1);

// Run the application - All executable code should be triggered through this file
require_once JPATH_ROOT . '/includes/app.php';
