<?php
/**
 * @version		$Id$
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Invalid Request.');

jimport('joomla.application.component.model');
jimport('joomla.filesystem.file');
jimport('joomla.user.helper');
require_once JPATH_INSTALLATION.'/helpers/database.php';

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
		$registry->setValue('sitename', $options->site_name);
		$registry->setValue('editor', 'tinymce');
		$registry->setValue('list_limit', 20);
		$registry->setValue('access', 1);

		/* Debug Settings */
		$registry->setValue('debug', 0);
		$registry->setValue('debug_lang', 0);
		$registry->setValue('debug_modules', 1);

		/* Database Settings */
		$registry->setValue('dbtype', $options->db_type);
		$registry->setValue('host', $options->db_host);
		$registry->setValue('user', $options->db_user);
		$registry->setValue('password', $options->db_pass);
		$registry->setValue('db', $options->db_name);
		$registry->setValue('dbprefix', $options->db_prefix);

		/* Server Settings */
		$registry->setValue('live_site', '');
		$registry->setValue('secret', JUserHelper::genRandomPassword(16));
		$registry->setValue('gzip', 0);
		$registry->setValue('error_reporting', -1);
		$registry->setValue('helpurl', 'http://help.joomla.org');
		$registry->setValue('xmlrpc_server', 0);
		$registry->setValue('ftp_host', $options->ftp_host);
		$registry->setValue('ftp_port', $options->ftp_port);
		$registry->setValue('ftp_user', $options->ftp_save ? $options->ftp_user : '');
		$registry->setValue('ftp_pass', $options->ftp_save ? $options->ftp_pass : '');
		$registry->setValue('ftp_root', $options->ftp_save ? $options->ftp_root : '');
		$registry->setValue('ftp_enable', $options->ftp_enable);

		/* Locale Settings */
		$registry->setValue('offset', 0);
		$registry->setValue('offset_user', 0);

		/* Mail Settings */
		$registry->setValue('mailer', 'mail');
		$registry->setValue('mailfrom', $options->admin_email);
		$registry->setValue('fromname', $options->site_name);
		$registry->setValue('sendmail', '/usr/sbin/sendmail');
		$registry->setValue('smtpauth', 0);
		$registry->setValue('smtpuser', '');
		$registry->setValue('smtppass', '');
		$registry->setValue('smtphost', 'localhost');
		$registry->setValue('smtpsecure', 'none');
		$registry->setValue('smtpport', '25');

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
		$registry->setValue('unicodeslugs', 0);

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

			$ftp = & JFTP::getInstance($options->ftp_host, $options->ftp_port);
			$ftp->login($options->ftp_user, $options->ftp_pass);

			// Translate path for the FTP account
			$file = JPath::clean(str_replace(JPATH_CONFIGURATION, $options->ftp_root, $path), '/');

			// Use FTP write buffer to file
			if (!$ftp->write($file, $buffer)) {
				// Set the config string to the session.
				$session = & JFactory::getSession();
				$session->set('setup.config', $buffer);
			}

			$ftp->quit();
		}
		else
		{
			if ($canWrite) {
				file_put_contents($path, $buffer);
				$session = & JFactory::getSession();
				$session->set('setup.config', null);
			} else {
				// Set the config string to the session.
				$session = & JFactory::getSession();
				$session->set('setup.config', $buffer);
			}
		}

		return true;
	}

	function _createRootUser($options)
	{
		// Get a database object.
		$db = & JInstallationHelperDatabase::getDBO($options->db_type, $options->db_host, $options->db_user, $options->db_pass, $options->db_name, $options->db_prefix);

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
		$crypt = JUserHelper::getCryptedPassword($options->admin_password, $salt);
		$cryptpass = $crypt.':'.$salt;

		// create the admin user
		$installdate 	= date('Y-m-d H:i:s');
		$nullDate 		= $db->getNullDate();
		$query	= 'INSERT INTO #__users SET'
				. ' id = 42'
				. ', name = '.$db->quote('Super User')
				. ', username = '.$db->quote($options->admin_user)
				. ', email = '.$db->quote($options->admin_email)
				. ', password = '.$db->quote($cryptpass)
				. ', usertype = '.$db->quote('deprecated')		// Need to weed out where this is used
				. ', block = 0'
				. ', sendEmail = 1'
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