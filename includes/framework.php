<?php
/**
 * @package    Joomla.Site
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

// Joomla system checks.
@ini_set('magic_quotes_runtime', 0);

/**
 * Provides methods to initialize the cms environment and dependencies.
 *
 * @since  VERSION
 */
class JBootstrap
{
	/**
	 * @var  JConfig
	 */
	protected static $config;

	/**
	 * @var  string  Relative location of installation directory, used for redirection.
	 */
	protected static $install_path = 'installation/index.php';

	/**
	 * Installation check, and check on removal of the install directory.
	 *
	 * @return  void
	 */
	public static function checkInstallation()
	{
		// If install directory exists, always redirect to it.
		// Todo: Make adjustments to allow JVersion::isInDevelopmentState() condition test
		if (file_exists(JPATH_INSTALLATION . '/index.php'))
		{
			header('Location: ' . substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], 'index.php')) . static::$install_path);

			exit;
		}

		if (!file_exists(JPATH_CONFIGURATION . '/configuration.php') || (filesize(JPATH_CONFIGURATION . '/configuration.php') < 10))
		{
			echo 'No configuration file found and no installation code available. Exiting...';

			exit;
		}
	}

	/**
	 * Load the cms and application context.
	 *
	 * @return  void
	 */
	public static function loadCms()
	{
		static::loadConfig();

		// System includes
		require_once JPATH_LIBRARIES . '/import.legacy.php';

		// Set system error handling
		JError::setErrorHandling(E_NOTICE, 'message');
		JError::setErrorHandling(E_WARNING, 'message');
		JError::setErrorHandling(E_ERROR, 'callback', array('JError', 'customErrorPage'));

		// Bootstrap the CMS libraries.
		require_once JPATH_LIBRARIES . '/cms.php';
	}

	/**
	 * Load site configuration from config file.
	 *
	 * @return  void
	 */
	protected static function loadConfig()
	{
		// Pre-Load configuration. Don't remove the Output Buffering due to BOM issues, see JCode 26026
		ob_start();
		require_once JPATH_CONFIGURATION . '/configuration.php';
		ob_end_clean();

		// System configuration.
		static::$config = new JConfig;

		// Set the error_reporting
		switch (static::$config->error_reporting)
		{
			case 'default':
			case '-1':
				break;

			case 'none':
			case '0':
				error_reporting(0);

				break;

			case 'simple':
				error_reporting(E_ERROR | E_WARNING | E_PARSE);
				ini_set('display_errors', 1);

				break;

			case 'maximum':
				error_reporting(E_ALL);
				ini_set('display_errors', 1);

				break;

			case 'development':
				error_reporting(-1);
				ini_set('display_errors', 1);

				break;

			default:
				error_reporting(static::$config->error_reporting);
				ini_set('display_errors', 1);

				break;
		}

		define('JDEBUG', static::$config->debug);
	}
}
