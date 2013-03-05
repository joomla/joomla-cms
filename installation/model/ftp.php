<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.path');

/**
 * FTP configuration model for the Joomla Core Installer.
 *
 * @package     Joomla.Installation
 * @subpackage  Model
 * @since       3.1
 */
class InstallationModelFtp extends JModelBase
{
	/**
	 * Find the ftp filesystem root for a given user/pass pair.
	 *
	 * @param   array  $options  Configuration options.
	 *
	 * @return  mixed  FTP root for given FTP user, or boolean false if not found.
	 *
	 * @since   3.1
	 */
	public function detectFtpRoot($options)
	{
		// Get the application
		/* @var InstallationApplicationWeb $app */
		$app = JFactory::getApplication();

		// Get the options as a object for easier handling.
		$options = JArrayHelper::toObject($options);

		// Connect and login to the FTP server.
		// Use binary transfer mode to be able to compare files.
		@$ftp = JClientFtp::getInstance($options->get('ftp_host'), $options->get('ftp_port'), array('type' => FTP_BINARY));

		// Check to make sure FTP is connected and authenticated.
		if (!$ftp->isConnected())
		{
			$app->enqueueMessage($options->get('ftp_host') . ':' . $options->get('ftp_port') . ' ' . JText::_('INSTL_FTP_NOCONNECT'), 'error');
			return false;
		}

		if (!$ftp->login($options->get('ftp_user'), $options->get('ftp_pass')))
		{
			$app->enqueueMessage(JText::_('INSTL_FTP_NOLOGIN'), 'error');
			return false;
		}

		// Get the current working directory from the FTP server.
		$cwd = $ftp->pwd();
		if ($cwd === false)
		{
			$app->enqueueMessage(JText::_('INSTL_FTP_NOPWD'), 'error');
			return false;
		}
		$cwd = rtrim($cwd, '/');

		// Get a list of folders in the current working directory.
		$cwdFolders = $ftp->listDetails(null, 'folders');
		if ($cwdFolders === false || count($cwdFolders) == 0)
		{
			$app->enqueueMessage(JText::_('INSTL_FTP_NODIRECTORYLISTING'), 'error');
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
		if (count(array_diff($known, $cwdFolders)) == 0)
		{
			$paths[] = $cwd . '/';
		}

		// Search through the segments of JPATH_SITE looking for root possibilities.
		$parts = explode(DIRECTORY_SEPARATOR, JPATH_SITE);
		$tmp = '';
		for ($i = count($parts) - 1; $i >= 0; $i--)
		{
			$tmp = '/' . $parts[$i] . $tmp;
			if (in_array($parts[$i], $cwdFolders))
			{
				$paths[] = $cwd . $tmp;
			}
		}

		// Check all possible paths for the real Joomla installation by comparing version files.
		$rootPath = false;
		$checkValue = file_get_contents(JPATH_LIBRARIES . '/cms/version/version.php');
		foreach ($paths as $tmp)
		{
			$filePath = rtrim($tmp, '/') . '/libraries/cms/version/version.php';
			$buffer = null;
			@ $ftp->read($filePath, $buffer);
			if ($buffer == $checkValue)
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
			$app->enqueueMessage(JText::_('INSTL_FTP_UNABLE_DETECT_ROOT_FOLDER'), 'error');
			return false;
		}

		return $rootPath;
	}

	/**
	 * Verify the FTP settings as being functional and correct.
	 *
	 * @param   array  $options  Configuration options.
	 *
	 * @return  mixed  FTP root for given FTP user, or boolean false if not found.
	 *
	 * @since   3.1
	 */
	public function verifyFtpSettings($options)
	{
		// Get the application
		/* @var InstallationApplicationWeb $app */
		$app = JFactory::getApplication();

		// Get the options as a object for easier handling.
		$options = JArrayHelper::toObject($options);

		// Connect and login to the FTP server.
		@$ftp = JClientFtp::getInstance($options->get('ftp_host'), $options->get('ftp_port'));

		// Check to make sure FTP is connected and authenticated.
		if (!$ftp->isConnected())
		{
			$app->enqueueMessage(JText::_('INSTL_FTP_NOCONNECT'), 'error');
			return false;
		}
		if (!$ftp->login($options->get('ftp_user'), $options->get('ftp_pass')))
		{
			$ftp->quit();
			$app->enqueueMessage(JText::_('INSTL_FTP_NOLOGIN'), 'error');
			return false;
		}

		// Since the root path will be trimmed when it gets saved to configuration.php,
		// we want to test with the same value as well.
		$root = rtrim($options->get('ftp_root'), '/');

		// Verify PWD function
		if ($ftp->pwd() === false)
		{
			$ftp->quit();
			$app->enqueueMessage(JText::_('INSTL_FTP_NOPWD'), 'error');
			return false;
		}

		// Verify root path exists
		if (!$ftp->chdir($root))
		{
			$ftp->quit();
			$app->enqueueMessage(JText::_('INSTL_FTP_NOROOT'), 'error');
			return false;
		}

		// Verify NLST function
		if (($rootList = $ftp->listNames()) === false)
		{
			$ftp->quit();
			$app->enqueueMessage(JText::_('INSTL_FTP_NONLST'), 'error');
			return false;
		}

		// Verify LIST function
		if ($ftp->listDetails() === false)
		{
			$ftp->quit();
			$app->enqueueMessage(JText::_('INSTL_FTP_NOLIST'), 'error');
			return false;
		}

		// Verify SYST function
		if ($ftp->syst() === false)
		{
			$ftp->quit();
			$app->enqueueMessage(JText::_('INSTL_FTP_NOSYST'), 'error');
			return false;
		}

		// Verify valid root path, part one
		$checkList = array('robots.txt', 'index.php');
		if (count(array_diff($checkList, $rootList)))
		{
			$ftp->quit();
			$app->enqueueMessage(JText::_('INSTL_FTP_INVALIDROOT'), 'error');
			return false;
		}

		// Verify RETR function
		$buffer = null;
		if ($ftp->read($root . '/libraries/cms/version/version.php', $buffer) === false)
		{
			$ftp->quit();
			$app->enqueueMessage(JText::_('INSTL_FTP_NORETR'), 'error');
			return false;
		}

		// Verify valid root path, part two
		$checkValue = file_get_contents(JPATH_ROOT . '/libraries/cms/version/version.php');
		if ($buffer !== $checkValue)
		{
			$ftp->quit();
			$app->enqueueMessage(JText::_('INSTL_FTP_INVALIDROOT'), 'error');
			return false;
		}

		// Verify STOR function
		if ($ftp->create($root . '/ftp_testfile') === false)
		{
			$ftp->quit();
			$app->enqueueMessage(JText::_('INSTL_FTP_NOSTOR'), 'error');
			return false;
		}

		// Verify DELE function
		if ($ftp->delete($root . '/ftp_testfile') === false)
		{
			$ftp->quit();
			$app->enqueueMessage(JText::_('INSTL_FTP_NODELE'), 'error');
			return false;
		}

		// Verify MKD function
		if ($ftp->mkdir($root . '/ftp_testdir') === false)
		{
			$ftp->quit();
			$app->enqueueMessage(JText::_('INSTL_FTP_NOMKD'), 'error');
			return false;
		}

		// Verify RMD function
		if ($ftp->delete($root . '/ftp_testdir') === false)
		{
			$ftp->quit();
			$app->enqueueMessage(JText::_('INSTL_FTP_NORMD'), 'error');
			return false;
		}

		$ftp->quit();
		return true;
	}
}
