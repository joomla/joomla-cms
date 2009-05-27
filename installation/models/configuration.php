<?php
/**
 * @version		$Id: configuration.php 251 2009-05-26 19:17:22Z louis.landry $
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die('Invalid Request.');

jimport('joomla.application.component.model');
jimport('joomla.filesystem.file');
jimport('joomla.user.helper');
require_once(JPATH_INSTALLATION.'/helpers/database.php');

/**
 * Install Configuration model for the Joomla Core Installer.
 *
 * @package		Joomla.Installation
 * @since		1.6
 */
class JInstallationModelConfiguration extends JModel
{
	function setup($options)
	{
		// Get the options as a JObject for easier handling.
		$options = JArrayHelper::toObject($options, 'JObject');

		// Attempt to create the root user.
		if (!$this->_createConfiguration($options)) {
			return false;
		}

		// Attempt to create the root user.
		if (!$this->_createRootUser($options)) {
			return false;
		}

		return true;
	}

	function _createConfiguration($options)
	{
		jimport('joomla.registry.registry');

		// Create a new registry to build the configuration options.
		$registry = new JRegistry();

		/* Site Settings */
		$registry->setValue('offline', 0);
		$registry->setValue('offline_message', JText::_('STDOFFLINEMSG'));
		$registry->setValue('sitename', $options->siteName);
		$registry->setValue('editor', 'tinymce');
		$registry->setValue('list_limit', 20);
		$registry->setValue('root_user', 42);

		/* Debug Settings */
		$registry->setValue('debug', 0);
		$registry->setValue('debug_lang', 0);

		/* Database Settings */
		$registry->setValue('dbtype', $options->DBtype);
		$registry->setValue('host', $options->DBhostname);
		$registry->setValue('user', $options->DBuserName);
		$registry->setValue('password', $options->DBpassword);
		$registry->setValue('db', $options->DBname);
		$registry->setValue('dbprefix', $options->DBPrefix);

		/* Server Settings */
		$registry->setValue('live_site', '');
		$registry->setValue('secret', JUserHelper::genRandomPassword(16));
		$registry->setValue('gzip', 0);
		$registry->setValue('error_reporting', -1);
		$registry->setValue('helpurl', 'http://help.joomla.org');
		$registry->setValue('xmlrpc_server', 0);
		$registry->setValue('ftp_host', $options->ftpHost);
		$registry->setValue('ftp_port', $options->ftpPort);
		$registry->setValue('ftp_user', $options->ftpUser);
		$registry->setValue('ftp_pass', $options->ftpPassword);
		$registry->setValue('ftp_root', $options->ftpRoot);
		$registry->setValue('ftp_enable', $options->ftpEnable);

		/* Locale Settings */
		$registry->setValue('offset', 0);
		$registry->setValue('offset_user', 0);

		/* Mail Settings */
		$registry->setValue('mailer', 'mail');
		$registry->setValue('mailfrom', $options->adminEmail);
		$registry->setValue('fromname', $options->siteName);
		$registry->setValue('sendmail', '/usr/sbin/sendmail');
		$registry->setValue('smtpauth', 0);
		$registry->setValue('smtpuser', '');
		$registry->setValue('smtppass', '');
		$registry->setValue('smtphost', 'localhost');

		/* Cache Settings */
		$registry->setValue('caching', 0);
		$registry->setValue('cachetime', 15);
		$registry->setValue('cache_handler', 'file');

		/* Meta Settings */
		$registry->setValue('MetaDesc', JText::_('STDMETADESC'));
		$registry->setValue('MetaKeys', JText::_('STDMETAKEYS'));
		$registry->setValue('MetaTitle', 1);
		$registry->setValue('MetaAuthor', 1);

		/* SEO Settings */
		$registry->setValue('sef', 1);
		$registry->setValue('sef_rewrite', 0);
		$registry->setValue('sef_suffix', 1);

		/* Feed Settings */
		$registry->setValue('feed_limit', 10);
		$registry->setValue('log_path', JPATH_ROOT.DS.'logs');
		$registry->setValue('tmp_path', JPATH_ROOT.DS.'tmp');

		/* Session Setting */
		$registry->setValue('lifetime', 15);
		$registry->setValue('session_handler', 'database');

		// Generate the configuration class string buffer.
		$buffer = $registry->toString('PHP', null, array('class'=>'JConfig'));


		// Build the configuration file path.
		$path = JPATH_CONFIGURATION.DS.'configuration.php';

		// Determine if the configuration file path is writable.
		if (file_exists($path)) {
			$canWrite = is_writable($path);
		} else {
			$canWrite = is_writable(JPATH_CONFIGURATION.DS);
		}

		/*
		 * If the file exists but isn't writable OR if the file doesn't exist and the parent directory
		 * is not writable we need to use FTP
		 */
		$useFTP = false;
		if ((file_exists($path) && !is_writable($path)) || (!file_exists($path) && !is_writable(dirname($path).'/'))) {
			$useFTP = true;
		}

		// Check for safe mode
		if (ini_get('safe_mode')) {
			$useFTP = true;
		}

		// Enable/Disable override
		if (!isset($options->ftpEnable) || ($options->ftpEnable != 1)) {
			$useFTP = false;
		}

		if ($useFTP == true)
		{
			// Connect the FTP client
			jimport('joomla.client.ftp');
			jimport('joomla.filesystem.path');

			$ftp = & JFTP::getInstance($options->ftpHost, $options->ftpPort);
			$ftp->login($options->ftpUser, $options->ftpPassword);

			// Translate path for the FTP account
			$file = JPath::clean(str_replace(JPATH_CONFIGURATION, $options->ftpRoot, $path), '/');

			// Use FTP write buffer to file
			if (!$ftp->write($file, $buffer)) {
				$this->setData('buffer', $buffer);
				return false;
			}

			$ftp->quit();
		}
		else
		{
			if ($canWrite) {
				file_put_contents($path, $buffer);
			} else {
				$this->setData('buffer', $buffer);
				return true;
			}
		}

		return true;
	}

	function _createRootUser($options)
	{
		// Get a database object.
		$db = & JInstallationHelperDatabase::getDBO($options->DBtype, $options->DBhostname, $options->DBuserName, $options->DBpassword, $options->DBname, $options->DBPrefix);

		// Check for errors.
		if (JError::isError($db)) {
			$this->setError(JText::sprintf('WARNNOTCONNECTDB', $db->toString()));
			return false;
		}

		// Check for database errors.
		if ($err = $db->getErrorNum()) {
			$this->setError(JText::sprintf('WARNNOTCONNECTDB', $db->getErrorNum()));
			return false;
		}

		// Create random salt/password for the admin user
		$salt = JUserHelper::genRandomPassword(32);
		$crypt = JUserHelper::getCryptedPassword($options->adminPassword, $salt);
		$cryptpass = $crypt.':'.$salt;

		// create the admin user
		$installdate 	= date('Y-m-d H:i:s');
		$nullDate 		= $db->getNullDate();
		$query	= 'INSERT INTO #__users SET'
				. ' id = 42'
				. ', name = '.$db->quote('Administrator')
				. ', username = '.$db->quote('admin')
				. ', email = '.$db->quote($options->adminEmail)
				. ', password = '.$db->quote($cryptpass)
				. ', usertype = '.$db->quote('deprecated')		// Need to weed out where this is used
				. ', block = 0'
				. ', sendEmail = 1'
				. ', gid = 0 '									// Need to weed out where this is used
				. ', registerDate = '.$db->quote($installdate)
				. ', lastvisitDate = '.$db->quote($nullDate)
				. ', activation = '.$db->quote('')
				. ', params = '.$db->quote('');
		$db->setQuery($query);
		if (!$db->query()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		// Map the super admin to the Super Admin Group
		$query = 'INSERT INTO #__user_usergroup_map' .
				' SET user_id = 42, group_id = 8';
		$db->setQuery($query);
		if (!$db->query()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		return true;
	}
}