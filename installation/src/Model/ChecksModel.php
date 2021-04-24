<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Model
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Client\FtpClient;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;

/**
 * Checks model for the Joomla Core Installer.
 *
 * @since  4.0.0
 */
class ChecksModel extends BaseInstallationModel
{
	/**
	 * Method to check the form data.
	 *
	 * @param   string  $page  The view being checked.
	 *
	 * @return  array|boolean  Array with the validated form data or boolean false on a validation failure.
	 *
	 * @since   3.1
	 */
	public function checkForm($page = 'setup')
	{
		// Get the posted values from the request and validate them.
		$data   = Factory::getApplication()->input->post->get('jform', array(), 'array');
		$return = $this->validate($data, $page);

		// Attempt to save the data before validation.
		$form = $this->getForm();
		$data = $form->filter($data);

		$this->storeOptions($data);

		// Check for validation errors.
		if ($return === false)
		{
			return false;
		}

		// Store the options in the session.
		return $this->storeOptions($return);
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   array        $data  The form data.
	 * @param   string|null  $view  The view.
	 *
	 * @return  array|boolean  Array of filtered data if valid, false otherwise.
	 *
	 * @since   3.1
	 */
	public function validate($data, $view = null)
	{
		// Get the form.
		$form = $this->getForm($view);

		// Check for an error.
		if ($form === false)
		{
			return false;
		}

		// Filter and validate the form data.
		$data   = $form->filter($data);
		$return = $form->validate($data);

		// Check for an error.
		if ($return instanceof \Exception)
		{
			Factory::getApplication()->enqueueMessage($return->getMessage(), 'warning');

			return false;
		}

		// Check the validation results.
		if ($return === false)
		{
			// Get the validation messages from the form.
			$messages = array_reverse($form->getErrors());

			foreach ($messages as $message)
			{
				if ($message instanceof \Exception)
				{
					Factory::getApplication()->enqueueMessage($message->getMessage(), 'warning');
				}
				else
				{
					Factory::getApplication()->enqueueMessage($message, 'warning');
				}
			}

			return false;
		}

		return $data;
	}

	/**
	 * Checks the availability of the parse_ini_file and parse_ini_string functions.
	 *
	 * @return  boolean  True if the method exists.
	 *
	 * @since   3.1
	 */
	public function getIniParserAvailability()
	{
		$disabled_functions = ini_get('disable_functions');

		if (!empty($disabled_functions))
		{
			// Attempt to detect them in the PHP INI disable_functions variable.
			$disabled_functions = explode(',', trim($disabled_functions));
			$number_of_disabled_functions = count($disabled_functions);

			for ($i = 0, $l = $number_of_disabled_functions; $i < $l; $i++)
			{
				$disabled_functions[$i] = trim($disabled_functions[$i]);
			}

			$result = !in_array('parse_ini_string', $disabled_functions);
		}
		else
		{
			// Attempt to detect their existence; even pure PHP implementation of them will trigger a positive response, though.
			$result = function_exists('parse_ini_string');
		}

		return $result;
	}

	/**
	 * Gets PHP options.
	 *
	 * @return  array  Array of PHP config options
	 *
	 * @since   3.1
	 */
	public function getPhpOptions()
	{
		$options = [];

		// Check for zlib support.
		$option = new \stdClass;
		$option->label  = Text::_('INSTL_ZLIB_COMPRESSION_SUPPORT');
		$option->state  = extension_loaded('zlib');
		$option->notice = $option->state ? null : Text::_('INSTL_NOTICE_ZLIB_COMPRESSION_SUPPORT');
		$options[] = $option;

		// Check for XML support.
		$option = new \stdClass;
		$option->label  = Text::_('INSTL_XML_SUPPORT');
		$option->state  = extension_loaded('xml');
		$option->notice = $option->state ? null : Text::_('INSTL_NOTICE_XML_SUPPORT');
		$options[] = $option;

		// Check for database support.
		// We are satisfied if there is at least one database driver available.
		$available = DatabaseDriver::getConnectors();
		$option = new \stdClass;
		$option->label  = Text::_('INSTL_DATABASE_SUPPORT');
		$option->label .= '<br>(' . implode(', ', $available) . ')';
		$option->state  = count($available);
		$option->notice = $option->state ? null : Text::_('INSTL_NOTICE_DATABASE_SUPPORT');
		$options[] = $option;

		// Check for mbstring options.
		if (extension_loaded('mbstring'))
		{
			// Check for default MB language.
			$option = new \stdClass;
			$option->label  = Text::_('INSTL_MB_LANGUAGE_IS_DEFAULT');
			$option->state  = (strtolower(ini_get('mbstring.language')) == 'neutral');
			$option->notice = $option->state ? null : Text::_('INSTL_NOTICE_MBLANG_NOTDEFAULT');
			$options[] = $option;

			// Check for MB function overload.
			$option = new \stdClass;
			$option->label  = Text::_('INSTL_MB_STRING_OVERLOAD_OFF');
			$option->state  = (ini_get('mbstring.func_overload') == 0);
			$option->notice = $option->state ? null : Text::_('INSTL_NOTICE_MBSTRING_OVERLOAD_OFF');
			$options[] = $option;
		}

		// Check for a missing native parse_ini_file implementation.
		$option = new \stdClass;
		$option->label  = Text::_('INSTL_PARSE_INI_FILE_AVAILABLE');
		$option->state  = $this->getIniParserAvailability();
		$option->notice = $option->state ? null : Text::_('INSTL_NOTICE_PARSE_INI_FILE_AVAILABLE');
		$options[] = $option;

		// Check for missing native json_encode / json_decode support.
		$option = new \stdClass;
		$option->label  = Text::_('INSTL_JSON_SUPPORT_AVAILABLE');
		$option->state  = function_exists('json_encode') && function_exists('json_decode');
		$option->notice = $option->state ? null : Text::_('INSTL_NOTICE_JSON_SUPPORT_AVAILABLE');
		$options[] = $option;

		// Check for configuration file writable.
		$session  = Factory::getSession();
		$iOptions = $this->getOptions();
		$writable = (is_writable(JPATH_CONFIGURATION . '/configuration.php')
			|| (!file_exists(JPATH_CONFIGURATION . '/configuration.php') && is_writable(JPATH_ROOT)));

		if ((!$writable
			&& isset($iOptions['ftp_enable'])
			&& $iOptions['ftp_enable']
			&& $iOptions['ftp_user'])
			|| $session->get('setup.skipftp', false)
		)
		{
			$writable = true;
		}

		$option = new \stdClass;
		$option->label   = Text::sprintf('INSTL_WRITABLE', 'configuration.php');
		$option->state   = $writable;
		$option->showFTP = !$writable;
		$option->notice  = $option->state ? null : Text::_('INSTL_NOTICEYOUCANSTILLINSTALL');
		$options[] = $option;

		return $options;
	}

	/**
	 * Checks if all of the mandatory PHP options are met.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.1
	 */
	public function getPhpOptionsSufficient()
	{
		$options = $this->getPhpOptions();

		foreach ($options as $option)
		{
			if ($option->state === false)
			{
				$result = $option->state;
			}
		}

		return isset($result) ? false : true;
	}

	/**
	 * Gets PHP Settings.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function getPhpSettings()
	{
		$settings = array();

		// Check for display errors.
		$setting = new \stdClass;
		$setting->label = Text::_('INSTL_DISPLAY_ERRORS');
		$setting->state = (bool) ini_get('display_errors');
		$setting->recommended = false;
		$settings[] = $setting;

		// Check for file uploads.
		$setting = new \stdClass;
		$setting->label = Text::_('INSTL_FILE_UPLOADS');
		$setting->state = (bool) ini_get('file_uploads');
		$setting->recommended = true;
		$settings[] = $setting;

		// Check for output buffering.
		$setting = new \stdClass;
		$setting->label = Text::_('INSTL_OUTPUT_BUFFERING');
		$setting->state = (int) ini_get('output_buffering') !== 0;
		$setting->recommended = false;
		$settings[] = $setting;

		// Check for session auto-start.
		$setting = new \stdClass;
		$setting->label = Text::_('INSTL_SESSION_AUTO_START');
		$setting->state = (bool) ini_get('session.auto_start');
		$setting->recommended = false;
		$settings[] = $setting;

		// Check for native ZIP support.
		$setting = new \stdClass;
		$setting->label = Text::_('INSTL_ZIP_SUPPORT_AVAILABLE');
		$setting->state = function_exists('zip_open') && function_exists('zip_read');
		$setting->recommended = true;
		$settings[] = $setting;

		return $settings;
	}

	/**
	 * Method to get the form.
	 *
	 * @param   string|null  $view  The view being processed.
	 *
	 * @return  Form|boolean  Form object on success, false on failure.
	 *
	 * @since   3.1
	 */
	public function getForm($view = null)
	{
		if (!$view)
		{
			$view = Factory::getApplication()->input->getWord('view', 'preinstall');
		}

		// Get the form.
		Form::addFormPath(JPATH_COMPONENT . '/forms');

		try
		{
			$form = Form::getInstance('jform', $view, array('control' => 'jform'));
		}
		catch (\Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		// Check the session for previously entered form data.
		$data = (array) $this->getOptions();

		// Bind the form data if present.
		if (!empty($data))
		{
			$form->bind($data);
		}

		return $form;
	}

	/**
	 * Find the ftp filesystem root for a given user/pass pair.
	 *
	 * @return  mixed  FTP root for given FTP user, or boolean false if not found.
	 *
	 * @since   4.0
	 */
	public function detectFtpRoot()
	{
		// Get the options as an object for easier handling.
		$options = $this->getOptions();

		// Connect and login to the FTP server.
		// Use binary transfer mode to be able to compare files.
		@$ftp = FtpClient::getInstance($options['ftp_host'], $options['ftp_port'], array('type' => FTP_BINARY));

		// Check to make sure FTP is connected and authenticated.
		if (!$ftp->isConnected())
		{
			Factory::getApplication()->enqueueMessage(
				$options['ftp_host'] . ':' . $options['ftp_port'] . ' ' . Text::_('INSTL_FTP_NOCONNECT'), 'error'
			);

			return false;
		}

		if (!$ftp->login($options['ftp_user'], $options['ftp_pass_plain']))
		{
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_FTP_NOLOGIN'), 'error');

			return false;
		}

		// Get the current working directory from the FTP server.
		$cwd = $ftp->pwd();

		if ($cwd === false)
		{
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_FTP_NOPWD'), 'error');

			return false;
		}

		$cwd = rtrim($cwd, '/');

		// Get a list of folders in the current working directory.
		$cwdFolders = $ftp->listDetails(null, 'folders');

		if ($cwdFolders === false || count($cwdFolders) === 0)
		{
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_FTP_NODIRECTORYLISTING'), 'error');

			return false;
		}

		// Get just the folder names from the list of folder data.
		for ($i = 0, $n = count($cwdFolders); $i < $n; $i++)
		{
			$cwdFolders[$i] = $cwdFolders[$i]['name'];
		}

		// Check to see if Joomla is installed at the FTP current working directory.
		$paths = array();
		$known = array('administrator', 'components', 'installation', 'language', 'libraries', 'plugins');

		if (count(array_diff($known, $cwdFolders)) === 0)
		{
			$paths[] = $cwd . '/';
		}

		// Search through the segments of JPATH_SITE looking for root possibilities.
		$parts = explode(DIRECTORY_SEPARATOR, JPATH_SITE);
		$tmp = '';

		for ($i = count($parts) - 1; $i >= 0; $i--)
		{
			$tmp = '/' . $parts[$i] . $tmp;

			if (in_array($parts[$i], $cwdFolders, true))
			{
				$paths[] = $cwd . $tmp;
			}
		}

		// Check all possible paths for the real Joomla installation by comparing version files.
		$rootPath   = false;
		$checkValue = file_get_contents(JPATH_LIBRARIES . '/src/Version.php');

		foreach ($paths as $tmp)
		{
			$filePath = rtrim($tmp, '/') . '/libraries/src/Version.php';
			$buffer   = null;

			@$ftp->read($filePath, $buffer);

			if ($buffer === $checkValue)
			{
				$rootPath = $tmp;

				break;
			}
		}

		// Close the FTP connection.
		$ftp->quit();

		// Return an error if no root path was found.
		if ($rootPath === false)
		{
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_FTP_UNABLE_DETECT_ROOT_FOLDER'), 'error');

			return false;
		}

		return $rootPath;
	}

	/**
	 * Verify the FTP settings as being functional and correct.
	 *
	 * @return  boolean  FTP connection is valid.
	 *
	 * @since   3.1
	 */
	public function verifyFtpSettings()
	{
		// Get the options as an object for easier handling.
		$options = $this->getOptions();

		// Connect and login to the FTP server.
		// Use binary transfer mode to be able to compare files.
		@$ftp = FtpClient::getInstance($options['ftp_host'], $options['ftp_port'], array('type' => FTP_BINARY));

		// Check to make sure FTP is connected and authenticated.
		if (!$ftp->isConnected())
		{
			Factory::getApplication()->enqueueMessage(
				$options['ftp_host'] . ':' . $options['ftp_port'] . ' ' . Text::_('INSTL_FTP_NOCONNECT'), 'error'
			);

			return false;
		}

		if (!$ftp->login($options['ftp_user'], $options['ftp_pass_plain']))
		{
			$ftp->quit();
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_FTP_NOLOGIN'), 'error');

			return false;
		}

		// Since the root path will be trimmed when it gets saved to configuration.php,
		// we want to test with the same value as well.
		$root = rtrim($options['ftp_root'], '/');

		// Verify PWD function.
		if ($ftp->pwd() === false)
		{
			$ftp->quit();
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_FTP_NOPWD'), 'error');

			return false;
		}

		// Verify root path exists.
		if (!$ftp->chdir($root))
		{
			$ftp->quit();
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_FTP_NOROOT'), 'error');

			return false;
		}

		// Verify NLST function.
		if (($rootList = $ftp->listNames()) === false)
		{
			$ftp->quit();
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_FTP_NONLST'), 'error');

			return false;
		}

		// Verify LIST function.
		if ($ftp->listDetails() === false)
		{
			$ftp->quit();
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_FTP_NOLIST'), 'error');

			return false;
		}

		// Verify SYST function.
		if ($ftp->syst() === false)
		{
			$ftp->quit();
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_FTP_NOSYST'), 'error');

			return false;
		}

		// Verify valid root path, part one.
		if (!(in_array('index.php', $rootList) && (in_array('robots.txt', $rootList) || in_array('robots.txt.dist', $rootList))))
		{
			$ftp->quit();
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_FTP_INVALIDROOT'), 'error');

			return false;
		}

		// Verify RETR function
		$buffer = null;

		if ($ftp->read($root . '/libraries/src/Version.php', $buffer) === false)
		{
			$ftp->quit();
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_FTP_NORETR'), 'error');

			return false;
		}

		// Verify valid root path, part two.
		$checkValue = file_get_contents(JPATH_ROOT . '/libraries/src/Version.php');

		if ($buffer !== $checkValue)
		{
			$ftp->quit();
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_FTP_INVALIDROOT'), 'error');

			return false;
		}

		// Verify STOR function.
		if ($ftp->create($root . '/ftp_testfile') === false)
		{
			$ftp->quit();
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_FTP_NOSTOR'), 'error');

			return false;
		}

		// Verify DELE function.
		if ($ftp->delete($root . '/ftp_testfile') === false)
		{
			$ftp->quit();
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_FTP_NODELE'), 'error');

			return false;
		}

		// Verify MKD function.
		if ($ftp->mkdir($root . '/ftp_testdir') === false)
		{
			$ftp->quit();
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_FTP_NOMKD'), 'error');

			return false;
		}

		// Verify RMD function.
		if ($ftp->delete($root . '/ftp_testdir') === false)
		{
			$ftp->quit();
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_FTP_NORMD'), 'error');

			return false;
		}

		$ftp->quit();

		return true;
	}
}
