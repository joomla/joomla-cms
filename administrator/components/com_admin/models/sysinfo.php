<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Model for the display of system information.
 *
 * @since  1.6
 */
class AdminModelSysInfo extends JModelLegacy
{
	/**
	 * Some PHP settings
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected $php_settings = array();

	/**
	 * Config values
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected $config = array();

	/**
	 * Some system values
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected $info = array();

	/**
	 * PHP info
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $php_info = null;

	/**
	 * Information about writable state of directories
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected $directories = array();

	/**
	 * The current editor.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $editor = null;

	/**
	 * Method to get the PHP settings
	 *
	 * @return  array  Some PHP settings
	 *
	 * @since   1.6
	 */
	public function &getPhpSettings()
	{
		if (!empty($this->php_settings))
		{
			return $this->php_settings;
		}

		$this->php_settings = array(
			'safe_mode'          => ini_get('safe_mode') == '1',
			'display_errors'     => ini_get('display_errors') == '1',
			'short_open_tag'     => ini_get('short_open_tag') == '1',
			'file_uploads'       => ini_get('file_uploads') == '1',
			'magic_quotes_gpc'   => ini_get('magic_quotes_gpc') == '1',
			'register_globals'   => ini_get('register_globals') == '1',
			'output_buffering'   => (bool) ini_get('output_buffering'),
			'open_basedir'       => ini_get('open_basedir'),
			'session.save_path'  => ini_get('session.save_path'),
			'session.auto_start' => ini_get('session.auto_start'),
			'disable_functions'  => ini_get('disable_functions'),
			'xml'                => extension_loaded('xml'),
			'zlib'               => extension_loaded('zlib'),
			'zip'                => function_exists('zip_open') && function_exists('zip_read'),
			'mbstring'           => extension_loaded('mbstring'),
			'iconv'              => function_exists('iconv')
		);

		return $this->php_settings;
	}

	/**
	 * Method to get the config
	 *
	 * @return  array  config values
	 *
	 * @since   1.6
	 */
	public function &getConfig()
	{
		if (!empty($this->config))
		{
			return $this->config;
		}

		$registry = new Registry(new JConfig);
		$this->config = $registry->toArray();
		$hidden = array('host', 'user', 'password', 'ftp_user', 'ftp_pass', 'smtpuser', 'smtppass');

		foreach ($hidden as $key)
		{
			$this->config[$key] = 'xxxxxx';
		}

		return $this->config;
	}

	/**
	 * Method to get the system information
	 *
	 * @return  array  System information values
	 *
	 * @since   1.6
	 */
	public function &getInfo()
	{
		if (!empty($this->info))
		{
			return $this->info;
		}

		$version    = new JVersion;
		$platform   = new JPlatform;
		$db         = $this->getDbo();

		$this->info = array(
			'php'         => php_uname(),
			'dbversion'   => $db->getVersion(),
			'dbcollation' => $db->getCollation(),
			'phpversion'  => phpversion(),
			'server'      => isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : getenv('SERVER_SOFTWARE'),
			'sapi_name'   => php_sapi_name(),
			'version'     => $version->getLongVersion(),
			'platform'    => $platform->getLongVersion(),
			'useragent'   => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ""
		);

		return $this->info;
	}

	/**
	 * Check if the phpinfo function is enabled
	 *
	 * @return  boolean True if enabled
	 *
	 * @since   3.4.1
	 */
	public function phpinfoEnabled()
	{
		return !in_array('phpinfo', explode(',', ini_get('disable_functions')));
	}

	/**
	 * Method to get the PHP info
	 *
	 * @return  string  PHP info
	 *
	 * @since   1.6
	 */
	public function &getPHPInfo()
	{
		if (!$this->phpinfoEnabled())
		{
			$this->php_info = JText::_('COM_ADMIN_PHPINFO_DISABLED');

			return $this->php_info;
		}

		if (!is_null($this->php_info))
		{
			return $this->php_info;
		}

		ob_start();
		date_default_timezone_set('UTC');
		phpinfo(INFO_GENERAL | INFO_CONFIGURATION | INFO_MODULES);
		$phpInfo = ob_get_contents();
		ob_end_clean();
		preg_match_all('#<body[^>]*>(.*)</body>#siU', $phpInfo, $output);
		$output = preg_replace('#<table[^>]*>#', '<table class="table table-striped adminlist">', $output[1][0]);
		$output = preg_replace('#(\w),(\w)#', '\1, \2', $output);
		$output = preg_replace('#<hr />#', '', $output);
		$output = str_replace('<div class="center">', '', $output);
		$output = preg_replace('#<tr class="h">(.*)<\/tr>#', '<thead><tr class="h">$1</tr></thead><tbody>', $output);
		$output = str_replace('</table>', '</tbody></table>', $output);
		$output = str_replace('</div>', '', $output);
		$this->php_info = $output;

		return $this->php_info;
	}

	/**
	 * Method to get the directory states
	 *
	 * @return  array States of directories
	 *
	 * @since   1.6
	 */
	public function getDirectory()
	{
		if (!empty($this->directories))
		{
			return $this->directories;
		}

		$this->directories = array();

		$registry = JFactory::getConfig();
		$cparams  = JComponentHelper::getParams('com_media');

		$this->addDirectory('administrator/components', JPATH_ADMINISTRATOR . '/components');
		$this->addDirectory('administrator/language', JPATH_ADMINISTRATOR . '/language');

		// List all admin languages
		$admin_langs = new DirectoryIterator(JPATH_ADMINISTRATOR . '/language');

		foreach ($admin_langs as $folder)
		{
			if (!$folder->isDir() || $folder->isDot())
			{
				continue;
			}

			$this->addDirectory('administrator/language/' . $folder->getFilename(), JPATH_ADMINISTRATOR . '/language/' . $folder->getFilename());
		}

		// List all manifests folders
		$manifests = new DirectoryIterator(JPATH_ADMINISTRATOR . '/manifests');

		foreach ($manifests as $folder)
		{
			if (!$folder->isDir() || $folder->isDot())
			{
				continue;
			}

			$this->addDirectory('administrator/manifests/' . $folder->getFilename(), JPATH_ADMINISTRATOR . '/manifests/' . $folder->getFilename());
		}

		$this->addDirectory('administrator/modules', JPATH_ADMINISTRATOR . '/modules');
		$this->addDirectory('administrator/templates', JPATH_THEMES);

		$this->addDirectory('components', JPATH_SITE . '/components');

		$this->addDirectory($cparams->get('image_path'), JPATH_SITE . '/' . $cparams->get('image_path'));

		// List all images folders
		$image_folders = new DirectoryIterator(JPATH_SITE . '/' . $cparams->get('image_path'));

		foreach ($image_folders as $folder)
		{
			if (!$folder->isDir() || $folder->isDot())
			{
				continue;
			}

			$this->addDirectory('images/' . $folder->getFilename(), JPATH_SITE . '/' . $cparams->get('image_path') . '/' . $folder->getFilename());
		}

		$this->addDirectory('language', JPATH_SITE . '/language');

		// List all site languages
		$site_langs = new DirectoryIterator(JPATH_SITE . '/language');

		foreach ($site_langs as $folder)
		{
			if (!$folder->isDir() || $folder->isDot())
			{
				continue;
			}

			$this->addDirectory('language/' . $folder->getFilename(), JPATH_SITE . '/language/' . $folder->getFilename());
		}

		$this->addDirectory('libraries', JPATH_LIBRARIES);

		$this->addDirectory('media', JPATH_SITE . '/media');
		$this->addDirectory('modules', JPATH_SITE . '/modules');
		$this->addDirectory('plugins', JPATH_PLUGINS);

		$plugin_groups = new DirectoryIterator(JPATH_SITE . '/plugins');

		foreach ($plugin_groups as $folder)
		{
			if (!$folder->isDir() || $folder->isDot())
			{
				continue;
			}

			$this->addDirectory('plugins/' . $folder->getFilename(), JPATH_PLUGINS . '/' . $folder->getFilename());
		}

		$this->addDirectory('templates', JPATH_SITE . '/templates');
		$this->addDirectory('configuration.php', JPATH_CONFIGURATION . '/configuration.php');

		// Is there a cache path in configuration.php?
		if ($cache_path = trim($registry->get('cache_path', '')))
		{
			// Frontend and backend use same directory for caching.
			$this->addDirectory($cache_path, $cache_path, 'COM_ADMIN_CACHE_DIRECTORY');
		}
		else
		{
			$this->addDirectory('cache', JPATH_SITE . '/cache', 'COM_ADMIN_CACHE_DIRECTORY');
			$this->addDirectory('administrator/cache', JPATH_CACHE, 'COM_ADMIN_CACHE_DIRECTORY');
		}

		$this->addDirectory($registry->get('log_path', JPATH_ROOT . '/log'), $registry->get('log_path', JPATH_ROOT . '/log'), 'COM_ADMIN_LOG_DIRECTORY');
		$this->addDirectory($registry->get('tmp_path', JPATH_ROOT . '/tmp'), $registry->get('tmp_path', JPATH_ROOT . '/tmp'), 'COM_ADMIN_TEMP_DIRECTORY');

		return $this->directories;
	}

	/**
	 * Method to add a directory
	 *
	 * @param   string  $name     Directory Name
	 * @param   string  $path     Directory path
	 * @param   string  $message  Message
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	private function addDirectory($name, $path, $message = '')
	{
		$this->directories[$name] = array('writable' => is_writable($path), 'message' => $message);
	}

	/**
	 * Method to get the editor
	 *
	 * @return  string  The default editor
	 *
	 * @note    Has to be removed (it is present in the config...)
	 * @since   1.6
	 */
	public function &getEditor()
	{
		if (!is_null($this->editor))
		{
			return $this->editor;
		}

		$this->editor = JFactory::getConfig()->get('editor');

		return $this->editor;
	}
}
