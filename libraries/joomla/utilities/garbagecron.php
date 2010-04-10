<?php
/**
 * @license		GNU/GPL
 */

// BEFORE USING RENAME THIS FILE TO SOMETHING UNIQUE !!!

/* Initialize Joomla framework */
define( '_JEXEC', 1 );
define( 'DS', DIRECTORY_SEPARATOR );
define('JPATH_BASE', dirname(__FILE__).DS.'..'.DS.'..'.DS.'..');
/* Required Files */
require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );

// Instantiate the application.
$app = JFactory::getApplication('site');

$cache = JFactory::getCache();
$cache->gc();
