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
class JDatabaseInstallerSqlite extends JDatabaseInstaller
{
	/**
	 * Check the database.
	 *
	 * @return JDatabaseInstaller
	 *
	 * @throws JDatabaseInstallerException
	 */
	public function check()
	{
		jimport('joomla.filesystem.folder');

		// Ensure that a valid hostname and user name were input.
		if (empty($this->options->db_host))
		{
			throw new JDatabaseInstallerException(JText::_('INSTL_DATABASE_INVALID_DB_DETAILS'));
		}

		if (empty($this->options->db_name))
		{
			throw new JDatabaseInstallerException(JText::_('INSTL_DATABASE_EMPTY_NAME'));
		}

		$path =('localhost' == $this->options->db_host)
			? JPATH_ROOT.'/db/'
			: $this->options->db_host;

		if (!JFolder::create($path))
		{
			// @todo filesystemexception
			throw new JDatabaseInstallerException(JText::_('Can not create the database directory'));
		}

		// Ensure that a database name was input.
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

		// Get a database object - This will also create the SQLite database.
		$db = $this->getDbo();

		// Check database version.
		$db_version = $db->getVersion();

		if (!version_compare($db_version, '3.7', '>='))
		{
			throw new JDatabaseInstallerException(
				JText::sprintf('Please upgrade SQLite to version 3.7 or higher. Your version: %s', $db_version));
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
		// The database has already been created

		return $this;
	}

	/**
	 * Update the database.
	 *
	 * @return JDatabaseInstallerMysql
	 *
	 * @throws JDatabaseInstallerException
	 */
	public function updateDatabase()
	{
		// Not used (yet)

		return $this;

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

		return $this;
	}

	/**
	 * Method to set the database character set to UTF-8.
	 *
	 * @return JDatabaseInstaller
	 */
	public function setDatabaseCharset()
	{
		// Character set should be already utf-8 - is there a need to set utf-16 ¿

		return $this;
	}

}
