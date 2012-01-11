<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * JDatabaseInstallerMysql class
 *
 * @package  Joomla.Libraries
 * @since    ¿
 */
class JDatabaseInstallerMysql extends JDatabaseInstaller
{
	/**
	 * Constructor.
	 *
	 * @param   JObject  $options  Installation options.
	 */
	public function __construct(JObject $options)
	{
		$this->options = $options;
	}

	/**
	 * Pre check the database.
	 *
	 * @return JDatabaseInstaller
	 *
	 * @throws JDatabaseInstallerException
	 */
	public function preCheck()
	{
		// Ensure that a valid hostname and user name were input.
		if (empty($this->options->db_host) || empty($this->options->db_user))
		{
			throw new JDatabaseInstallerException(JText::_('INSTL_DATABASE_INVALID_DB_DETAILS'));
		}

		// Ensure that a database name was input.
		if (empty($this->options->db_name))
		{
			throw new JDatabaseInstallerException(JText::_('INSTL_DATABASE_EMPTY_NAME'));
		}

		// Validate database table prefix.
		if (!preg_match('#^[a-zA-Z]+[a-zA-Z0-9_]*$#', $this->options->db_prefix))
		{
			throw new JDatabaseInstallerException(JText::_('INSTL_DATABASE_PREFIX_INVALID_CHARS'));
		}

		// Validate length of database table prefix.
		if (strlen($this->options->db_prefix) > 15)
		{
			throw new JDatabaseInstallerException(JText::_('INSTL_DATABASE_FIX_TOO_LONG'));
		}

		// Validate length of database name.
		if (strlen($this->options->db_name) > 64)
		{
			throw new JDatabaseInstallerException(JText::_('INSTL_DATABASE_NAME_TOO_LONG'));
		}

		return $this;
	}

	/**
	 * Check the database.
	 *
	 * @return JDatabaseInstaller
	 *
	 * @throws JDatabaseInstallerException
	 */
	public function check()
	{
		// Get a database object.
		$db = $this->getDbo();

		// Check for errors.
		if ($db instanceof Exception)
		{
			throw new JDatabaseInstallerException(JText::sprintf('INSTL_DATABASE_COULD_NOT_CONNECT', (string) $db));
		}

		// Check for database errors.
		if ($err = $db->getErrorNum())
		{
			throw new JDatabaseInstallerException(JText::sprintf('INSTL_DATABASE_COULD_NOT_CONNECT', $db->getErrorNum()));
		}

		// Check database version.
		$db_version = $db->getVersion();

		if (($position = strpos($db_version, '-')) !== false)
		{
			$db_version = substr($db_version, 0, $position);
		}

		if (!version_compare($db_version, '5.0.4', '>='))
		{
			throw new JDatabaseInstallerException(JText::sprintf('INSTL_DATABASE_INVALID_MYSQL_VERSION', $db_version));
		}

		// @internal MySQL versions pre 5.1.6 forbid . / or \ or NULL
		if ((preg_match('#[\\\/\.\0]#', $this->options->db_name))
			&& (!version_compare($db_version, '5.1.6', '>=')))
		{
			throw new JDatabaseInstallerException(JText::sprintf('INSTL_DATABASE_INVALID_NAME', $db_version));
		}

		// @internal Check for spaces in beginning or end of name
		if (strlen(trim($this->options->db_name)) <> strlen($this->options->db_name))
		{
			throw new JDatabaseInstallerException(JText::_('INSTL_DATABASE_NAME_INVALID_SPACES'));
		}

		// @internal Check for asc(00) Null in name
		if (strpos($this->options->db_name, chr(00)) !== false)
		{
			throw new JDatabaseInstallerException(JText::_('INSTL_DATABASE_NAME_INVALID_CHAR'));
		}

		return $this;
	}

	/**
	 * Method to create a new database.
	 *
	 * @return JDatabaseInstallerMysql
	 */
	public function createDatabase()
	{
		$db = $this->getDbo();
		$utfSupport = $db->hasUTF();

		// Build the create database query.
		if ($utfSupport)
		{
			$query = 'CREATE DATABASE ' . $db->quoteName($this->options->db_name) . ' CHARACTER SET utf8';
		}
		else
		{
			$query = 'CREATE DATABASE ' . $db->quoteName($this->options->db_name);
		}

		// Run the create database query.
		$db->setQuery($query)->query();

		return $this;
	}

	/**
	 * Update the database.
	 *
	 * @return JDatabaseInstallerMysql
	 *
	 * @throws JDatabaseInstallerException
	 */
	public function update()
	{
		$db = $this->getDbo();

		// Attempt to update the table #__schema.
		$files = JFolder::files(JPATH_ADMINISTRATOR . '/components/com_admin/sql/updates/mysql/', '\.sql$');

		if (empty($files))
		{
			throw new JDatabaseInstallerException(JText::_('INSTL_ERROR_INITIALISE_SCHEMA'));
		}

		$version = '';

		foreach ($files as $file)
		{
			if (version_compare($version, JFile::stripExt($file)) < 0)
			{
				$version = JFile::stripExt($file);
			}
		}

		$query = $db->getQuery(true);
		$query->insert('#__schemas');

		$query->columns(
			array(
				$db->quoteName('extension_id'),
				$db->quoteName('version_id'))
		);

		$query->values('700, ' . $db->quote($version));

		$db->setQuery($query)->query();

		// Attempt to refresh manifest caches
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__extensions');

		$extensions = $db->setQuery($query)->loadObjectList();

		JFactory::$database = $db;

		$installer = JInstaller::getInstance();

		foreach ($extensions as $extension)
		{
			if (!$installer->refreshManifestCache($extension->extension_id))
			{
				throw new JDatabaseInstallerException(
					JText::sprintf('INSTL_DATABASE_COULD_NOT_REFRESH_MANIFEST_CACHE', $extension->name)
				);
			}
		}

		return $this;
	}

	/**
	 * Apply localization options.
	 *
	 * @return JDatabaseInstaller
	 */
	public function localize()
	{
		$db = $this->getDbo();

		// Load the localise.sql for translating the data in joomla.sql/joomla_backwards.sql
		$type = ($this->options->db_type == 'mysqli') ? 'mysql' : $this->options->db_type;

		$dblocalise = 'sql/' . $type . '/localise.sql';

		if (JFile::exists($dblocalise))
		{
			$this->populateDatabase($dblocalise);
		}

		// Handle default backend language setting. This feature is available for localized versions of Joomla 1.5.
		$languages = JFactory::getApplication()->getLocaliseAdmin($db);

		if (in_array($this->options->language, $languages['admin'])
			|| in_array($this->options->language, $languages['site']))
		{
			// Build the language parameters for the language manager.
			$params = array();

			// Set default administrator/site language to sample data values:
			$params['administrator'] = 'en-GB';
			$params['site'] = 'en-GB';

			if (in_array($this->options->language, $languages['admin']))
			{
				$params['administrator'] = $this->options->language;
			}

			if (in_array($this->options->language, $languages['site']))
			{
				$params['site'] = $this->options->language;
			}

			$params = json_encode($params);

			// Update the language settings in the language manager.
			$db->setQuery(
				'UPDATE ' . $db->quoteName('#__extensions') .
					' SET ' . $db->quoteName('params') . ' = ' . $db->Quote($params) .
					' WHERE ' . $db->quoteName('element') . '=\'com_languages\''
			)->query();
		}

		return $this;
	}

	/**
	 * Method to set the database character set to UTF-8.
	 *
	 * @return JDatabaseInstaller
	 */
	public function setDatabaseCharset()
	{
		$db = $this->getDbo();

		// Only alter the database if it supports the character set.
		if ($db->hasUTF())
		{
			// Run the create database query.
			$db->setQuery(
				'ALTER DATABASE ' . $db->quoteName($this->options->db_name) . ' CHARACTER' .
					' SET utf8'
			)->query();
		}

		return $this;
	}

}
