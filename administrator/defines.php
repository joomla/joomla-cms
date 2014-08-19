<?php
// Example for Joomla! in PHAR 
defined('_JEXEC') or die;

if (substr(__DIR__, 0, 7) == 'phar://') {
	$tmpRootPath = dirname($_SERVER['SCRIPT_FILENAME']);
	define('JPATH_BASE', __DIR__);
	// Defines
	define('JPATH_ROOT',          dirname(JPATH_BASE));
	define('JPATH_SITE',          JPATH_ROOT);
	// use real path for configuration.php
	define('JPATH_CONFIGURATION', dirname($tmpRootPath));
	define('JPATH_ADMINISTRATOR', JPATH_ROOT . '/administrator');
	define('JPATH_LIBRARIES',     JPATH_ROOT . '/libraries');
	define('JPATH_PLUGINS',       JPATH_ROOT . '/plugins');
	define('JPATH_INSTALLATION',  JPATH_ROOT . '/installation');
	// use real path for templates and cache
	define('JPATH_THEMES',        $tmpRootPath . '/templates');
	define('JPATH_CACHE',         $tmpRootPath . '/cache');
	define('JPATH_MANIFESTS',     JPATH_ADMINISTRATOR . '/manifests');

	define('_JDEFINES', 1);
}
else {
	error_reporting(E_ALL ^ E_STRICT);
	ini_set('display_errors', 1);
	ini_set('log_errors', 1);
	ini_set('error_log', 'c:/tmp/error_log');
	include 'phar://../joomla.phar/administrator/index.php'; 
	exit;
}
