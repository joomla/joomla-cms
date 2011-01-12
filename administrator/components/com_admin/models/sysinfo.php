<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.model');
/**
 * @package		Joomla.Administrator
 * @subpackage	com_admin
 * @since		1.6
 */
class AdminModelSysInfo extends JModel
{
	/**
	 * @var array some php settings
	 */
	protected $php_settings = null;

	/**
	 * @var array config values
	 */
	protected $config = null;

	/**
	 * @var array somme system values
	 */
	protected $info = null;

	/**
	 * @var string php info
	 */
	protected $php_info = null;

	/**
	 * @var array informations about writable state of directories
	 */
	protected $directory = null;

	/**
	 * @var string The current editor.
	 */
	protected $editor = null;

	/**
	 * Method to get the ChangeLog
	 *
	 * @return array some php settings
	 */
	function &getPhpSettings()
	{
		if (is_null($this->php_settings))
		{
			$this->php_settings = array();
			$this->php_settings['safe_mode']			= ini_get('safe_mode') == '1';
			$this->php_settings['display_errors']		= ini_get('display_errors') == '1';
			$this->php_settings['short_open_tag']		= ini_get('short_open_tag') == '1';
			$this->php_settings['file_uploads']			= ini_get('file_uploads') == '1';
			$this->php_settings['magic_quotes_gpc']		= ini_get('magic_quotes_gpc') == '1';
			$this->php_settings['register_globals']		= ini_get('register_globals') == '1';
			$this->php_settings['output_buffering']		= (bool) ini_get('output_buffering');
			$this->php_settings['open_basedir']			= ini_get('open_basedir');
			$this->php_settings['session.save_path']	= ini_get('session.save_path');
			$this->php_settings['session.auto_start']	= ini_get('session.auto_start');
			$this->php_settings['disable_functions']	= ini_get('disable_functions');
			$this->php_settings['xml']					= extension_loaded('xml');
			$this->php_settings['zlib']					= extension_loaded('zlib');
			$this->php_settings['mbstring']				= extension_loaded('mbstring');
			$this->php_settings['iconv']				= function_exists('iconv');
		}
		return $this->php_settings;
	}
	/**
	 * method to get the config
	 *
	 * @return array config values
	 */
	function &getConfig()
	{
		if (is_null($this->config))
		{
			$registry = JFactory::getConfig();
			$this->config = $registry->toArray();
			$hidden = array('host', 'user', 'password', 'ftp_user', 'ftp_pass', 'smtpuser', 'smtppass');
			foreach($hidden as $key) {
				$this->config[$key] = 'xxxxxx';
			}
		}
		return $this->config;
	}
	/**
	 * method to get the system information
	 *
	 * @return array system information values
	 */
	function &getInfo()
	{
		if (is_null($this->info))
		{
			$this->info = array();
			$version = new JVersion();
			$db = JFactory::getDBO();
			if (isset($_SERVER['SERVER_SOFTWARE'])) {
				$sf = $_SERVER['SERVER_SOFTWARE'];
			}
			else {
				$sf = getenv('SERVER_SOFTWARE');
			}
			$this->info['php']			= php_uname();
			$this->info['dbversion']	= $db->getVersion();
			$this->info['dbcollation']	= $db->getCollation();
			$this->info['phpversion']	= phpversion();
			$this->info['server']		= $sf;
			$this->info['sapi_name']	= php_sapi_name();
			$this->info['version']		= $version->getLongVersion();
			$this->info['useragent']	= phpversion() <= '4.2.1' ? getenv("HTTP_USER_AGENT") : $_SERVER['HTTP_USER_AGENT'];
		}
		return $this->info;
	}
	/**
	 * method to get the PHP info
	 *
	 * @return string PHP info
	 */
	function &getPHPInfo()
	{
		if (is_null($this->php_info))
		{
			ob_start();
			date_default_timezone_set('UTC');
			phpinfo(INFO_GENERAL | INFO_CONFIGURATION | INFO_MODULES);
			$phpinfo = ob_get_contents();
			ob_end_clean();
			preg_match_all('#<body[^>]*>(.*)</body>#siU', $phpinfo, $output);
			$output = preg_replace('#<table[^>]*>#', '<table class="adminlist">', $output[1][0]);
			$output = preg_replace('#(\w),(\w)#', '\1, \2', $output);
			$output = preg_replace('#<hr />#', '', $output);
			$output = str_replace('<div class="center">', '', $output);
			$output = preg_replace('#<tr class="h">(.*)<\/tr>#', '<thead><tr class="h">$1</tr></thead><tbody>', $output); 
			$output = str_replace('</table>', '</tbody></table>', $output);
			$output = str_replace('</div>', '', $output);
			$this->php_info = $output;
		}
		return $this->php_info;
	}
	/**
	 * method to get the directory states
	 *
	 * @return array states of directories
	 */
	function &getDirectory()
	{
		if (is_null($this->directory))
		{
			$registry = JFactory::getConfig();
			jimport('joomla.filesystem.folder');
			$cparams = JComponentHelper::getParams('com_media');
			$this->directory = array();
			$this->directory['administrator'.DS.'components']	= array('writable' => is_writable(JPATH_SITE.DS.'administrator'.DS.'components'), 'message' => '');
			$this->directory['administrator'.DS.'language']		= array('writable' => is_writable(JPATH_SITE.DS.'administrator'.DS.'language'), 'message' => '');

			// List all admin languages
			$admin_langs = JFolder::folders(JPATH_ADMINISTRATOR.DS.'language');
			foreach($admin_langs as $alang) {
				$this->directory['administrator'.DS.'language'.DS.$alang] = array('writable' => is_writable(JPATH_SITE.DS.'administrator'.DS.'language'.DS.$alang), 'message' => '');
			}

			$this->directory['administrator'.DS.'modules']		= array('writable' => is_writable(JPATH_SITE.DS.'administrator'.DS.'modules'), 'message' => '');
			$this->directory['administrator'.DS.'templates']	= array('writable' => is_writable(JPATH_SITE.DS.'administrator'.DS.'templates'), 'message' => '');

			$this->directory['components']					= array('writable' => is_writable(JPATH_SITE.DS.'components'), 'message' => '');
			$this->directory['images']						= array('writable' => is_writable(JPATH_SITE.DS.'images'), 'message' => '');
			$this->directory['images'.DS.'banners']			= array('writable' => is_writable(JPATH_SITE.DS.'images'.DS.'banners'), 'message' => '');
			$this->directory[$cparams->get('image_path') ]	= array('writable' => is_writable(JPATH_SITE.DS.$cparams->get('image_path')), 'message' => '');
			$this->directory['language']					= array('writable' => is_writable(JPATH_SITE.DS.'language'), 'message' => '');

			// List all site languages
			$site_langs = JFolder::folders(JPATH_SITE.DS.'language');
			foreach ($site_langs as $slang) {
				$this->directory['language'.DS.$slang] = array('writable' => is_writable(JPATH_SITE.DS.'language'.DS.$slang), 'message' => '');
			}
			$this->directory['media']		= array('writable' => is_writable(JPATH_SITE.DS.'media'), 'message' => '');
			$this->directory['modules']		= array('writable' => is_writable(JPATH_SITE.DS.'modules'), 'message' => '');
			$this->directory['plugins']		= array('writable' => is_writable(JPATH_SITE.DS.'plugins'), 'message' => '');
			$this->directory['plugins'.DS.'content']		= array('writable' => is_writable(JPATH_SITE.DS.'plugins'.DS.'content'), 'message' => '');
			$this->directory['plugins'.DS.'editors']		= array('writable' => is_writable(JPATH_SITE.DS.'plugins'.DS.'editors'), 'message' => '');
			$this->directory['plugins'.DS.'editors-xtd']	= array('writable' => is_writable(JPATH_SITE.DS.'plugins'.DS.'editors-xtd'), 'message' => '');
			$this->directory['plugins'.DS.'search']			= array('writable' => is_writable(JPATH_SITE.DS.'plugins'.DS.'search'), 'message' => '');
			$this->directory['plugins'.DS.'system']			= array('writable' => is_writable(JPATH_SITE.DS.'plugins'.DS.'system'), 'message' => '');
			$this->directory['plugins'.DS.'user']			= array('writable' => is_writable(JPATH_SITE.DS.'plugins'.DS.'user'), 'message' => '');
			$this->directory['templates']					= array('writable' => is_writable(JPATH_SITE.DS.'templates'), 'message' => '');
			$this->directory['cache']						= array('writable' => is_writable(JPATH_SITE.DS.'cache'), 'message' => 'COM_ADMIN_CACHE_DIRECTORY');
			$this->directory['administrator'.DS.'cache']	= array('writable' => is_writable(JPATH_SITE.DS.'administrator'.DS.'cache'), 'message' => 'COM_ADMIN_CACHE_DIRECTORY');

			$this->directory[$registry->get('log_path', JPATH_ROOT.DS.'log') ] = array('writable' => is_writable($registry->get('log_path', JPATH_ROOT.DS.'log')), 'message' => 'COM_ADMIN_LOG_DIRECTORY');
			$this->directory[$registry->get('tmp_path', JPATH_ROOT.DS.'tmp') ] = array('writable' => is_writable($registry->get('tmp_path', JPATH_ROOT.DS.'tmp')), 'message' => 'COM_ADMIN_TEMP_DIRECTORY');
		}
		return $this->directory;
	}
	/**
	 * method to get the editor
	 *
	 * @return string the default editor
	 *
	 * has to be removed (it is present in the config...)
	 */
	function &getEditor()
	{
		if (is_null($this->editor))
		{
			$config = JFactory::getConfig();
			$this->editor = $config->get('editor');
		}
		return $this->editor;
	}
}
