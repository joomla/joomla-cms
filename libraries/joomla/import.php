<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Load the loader class
 */
if (! class_exists('JLoader')) {
	require_once JPATH_LIBRARIES.DS.'loader.php';
}

/**
 * Joomla! library imports
 */

//Base classes
JLoader::import( 'joomla.base.stdclass' );
JLoader::import( 'joomla.base.object' );


//Environment classes
JLoader::import( 'joomla.environment.request'   );
JRequest::clean();

JLoader::import( 'joomla.environment.response'  );

//Factory class and methods
JLoader::import( 'joomla.factory' 				);
JLoader::import( 'joomla.version' 				);
if (!defined('JVERSION')) {
	$version = new JVersion();
	define('JVERSION', $version->getShortVersion());
}

//Error
JLoader::import( 'joomla.error.error' 			);
JLoader::import( 'joomla.error.exception' 		);

//Utilities
JLoader::import( 'joomla.utilities.arrayhelper' );

//Filters
JLoader::import( 'joomla.filter.filterinput'	);
JLoader::import( 'joomla.filter.filteroutput'	);

//Register class that don't follow one file per class naming conventions
JLoader::register('JText' , dirname(__FILE__).DS.'methods.php');
JLoader::register('JRoute', dirname(__FILE__).DS.'methods.php');

// Detect if we have full UTF-8 and unicode property support.
if (!defined('JCOMPAT_UNICODE_PROPERTIES')) {
	define('JCOMPAT_UNICODE_PROPERTIES', (bool)@preg_match('/\pL/u', 'a'));
}
