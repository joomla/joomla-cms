<?php
/**
 * @package    Joomla.Cli
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// We are a valid entry point.
const _JEXEC = 1;

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php'))
{
	require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(__DIR__));
	require_once JPATH_BASE . '/includes/defines.php';
}

// Get the framework.
require_once JPATH_LIBRARIES . '/import.legacy.php';

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

// Configure error reporting to maximum for CLI output.
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load Library language
$lang = JFactory::getLanguage();

// Try the files_joomla file in the current language (without allowing the loading of the file in the default language)
$lang->load('files_joomla.sys', JPATH_SITE, null, false, false)
// Fallback to the files_joomla file in the default language
|| $lang->load('files_joomla.sys', JPATH_SITE, null, true);

/**
 * A command line end point to install extension with it.
 * From path or URL
 *
 * @since  __DEPLOY_VERSION__
 */
class JApplicationInstallExtensionCli extends JApplicationCli
{
	/**
	 * Entry point for CLI script
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function doExecute()
	{
		$path = $this->input->get('path', $this->input->get('p', null, 'STRING'), 'STRING');
		$url  = $this->input->get('url', $this->input->get('u', null, 'STRING'), 'STRING');

		jimport('joomla.filesystem.file');

		if (strpos($path, DIRECTORY_SEPARATOR) !== 0)
		{
			$path = getcwd() . DIRECTORY_SEPARATOR . $path;
		}

		JFactory::getApplication('InstallExtensionCli');
		JFactory::getApplication()->input->cookie = new JInputCookie;
		$input = JFactory::getApplication()->input;
		$session = JFactory::getSession();
		$session->initialise($this->input);

		JFactory::getLanguage()->load('com_installer', JPATH_ADMINISTRATOR);
		JLoader::register('InstallerModelInstall', JPATH_ADMINISTRATOR . '/components/com_installer/models/install.php');

		$installer = new InstallerModelInstall;

		if (JFile::exists($path))
		{
			$this->out('Trying to install from: ' . $path);
			$input->set('installtype', 'folder');

			try
			{
				// Build the appropriate paths.
				$config   = JFactory::getConfig();
				$tmp_dest = $config->get('tmp_path') . '/' . basename($path);

				// Move uploaded file.
				JFile::copy($path, $tmp_dest);

				// Unpack the downloaded package file.
				$package = JInstallerHelper::unpack($tmp_dest, true);
				$input->set('install_directory', $package['extractdir']);
				$installer->install();
			}
			catch (Exception $exception)
			{
				echo '<pre>' . print_r($exception) . '</pre>';
			}
		}
		elseif ($url)
		{
			$this->out('Trying to install from: ' . $path);
			$input->set('installtype', 'url');
			$input->set('install_url', $url);

			try
			{
				$installer->install();
			}
			catch (Exception $exception)
			{
				echo '<pre>' . print_r($exception) . '</pre>';
			}
		}
		else
		{
			$this->out('File ' . $path . ' do not exists');
		}
	}

	/**
	 * Alias for JInput->get as we have/need no session with cli
	 *
	 * @param   string  $key      The key of the user state variable.
	 * @param   string  $request  The name of the variable passed in a request.
	 * @param   string  $default  The default value for the variable if not found. Optional.
	 * @param   string  $type     Filter for the variable, for valid values see {@link JFilterInput::clean()}. Optional.
	 *
	 * @return  The request user state.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getUserStateFromRequest($key, $request, $default = null, $type = 'none')
	{
		return $this->input->get($request, $default, $type);
	}

	/**
	 * Alias for JInput->set as we have/need no session with cli
	 *
	 * @param   string  $key    The key of the user state variable.
	 * @param   string  $value  The value for the variable.
	 *
	 * @return  The request user state.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setUserState($key, $value = null)
	{
		return $this->input->set($key, $value);
	}

	/**
	 * Alias for CLI->out as have no other application messages
	 *
	 * @param   string  $msg   The message sting.
	 * @param   string  $type  The message type/prefix.
	 *
	 * @return  The request user state.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function enqueueMessage($msg, $type = 'message')
	{
		$this->out(strtoupper($type) . ': ' . $msg);
	}

	/**
	 * Dummy as this method is called on application but we
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function flushAssets()
	{
		return true;
	}

	/**
	 * Dummy as this method is called on application but we in are in CLI,
	 * so lets pretend we're in admin
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isSite()
	{
		return false;
	}

	/**
	 * Dummy as this method is called on application but we in are in CLI,
	 * so lets pretend we're in admin by default
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isAdmin()
	{
		return true;
	}

	/**
	 * Dummy as this method is called on application but we in are in CLI,
	 * so lets pretend we're in admin
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isClient($option)
	{
		if ($option == 'site')
		{
			return false;
		}
		return true;
	}


	/**
	 * Dummy for the application JRouter object.
	 *
	 * @param   string  $name     The name of the application.
	 * @param   array   $options  An optional associative array of configuration settings.
	 *
	 * @return  null  Instead of JRouter object
	 *
	 * @since   __DEPLOY_VERSION__
	 * @deprecated  4.0
	 */
	public static function getRouter($name = null, array $options = array()){
		return;
	}
}

// Instantiate the application object, passing the class name to JCli::getInstance
// and use chaining to execute the application.
JApplicationCli::getInstance('JApplicationInstallExtensionCli')->execute();
