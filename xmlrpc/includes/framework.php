<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Installation
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/*
 * Joomla! system checks
 */

@set_magic_quotes_runtime(0);
@ini_set('zend.ze1_compatibility_mode', '0');

if (!file_exists(JPATH_CONFIGURATION . DS . 'configuration.php') || (filesize(JPATH_CONFIGURATION . DS . 'configuration.php') < 10)) {
	// TODO: Throw 500 error
	header('Location: ../installation/index.php');
	exit();
}

/*
 * Joomla! system startup
 */

// System includes
require_once JPATH_LIBRARIES		.DS.'joomla'.DS.'import.php';

// Pre-Load configuration
require_once JPATH_CONFIGURATION	.DS.'configuration.php';

// System configuration
$CONFIG = new JConfig();

if (@$CONFIG->error_reporting === 0) {
	error_reporting(0);
} else if (@$CONFIG->error_reporting > 0) {
	error_reporting($CONFIG->error_reporting);
	ini_set('display_errors', 1);
}

unset($CONFIG);

/*
 * Joomla! framework loading
 */

// Include object abstract class
jimport('joomla.utilities.compat.compat');

// Joomla! library imports
jimport('joomla.acl.acl');
jimport('joomla.environment.uri');
jimport('joomla.user.user');
jimport('joomla.event.event');
jimport('joomla.event.dispatcher');
jimport('joomla.plugin.helper');
jimport('joomla.utilities.string');
