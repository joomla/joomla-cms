<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * Helper class to aid with modules installing.
 *
 * @since  3.4
 */
class JExtensionscriptHelper
{
	/**
	 * @var		string	The version number of the module.
	 * @since   3.4
	 */
	protected $release;

	/**
	 * @var		string	The table the parameters are stored in.
	 * @since   3.4
	 */
	protected $paramTable;

	/**
	 * @var		string	The extension name. This should be set in the installer script.
	 * @since   3.4
	 */
	protected $extension;

	/**
	 * @var		array  A list of files to be deleted.
	 * @since   3.4
	 */
	protected $deleteFiles = array();

	/**
	 * @var		array  A list of folders to be deleted.
	 * @since   3.4
	 */
	protected $deleteFolders = array();

	/**
	 * @var   array  A list of cli script files to be copied to the cli directory
	 * @since   3.4
	 */
	protected $cliScriptFiles = array();

	/**
	 * @var   string  Minimum PHP version required to install the extension
	 */
	protected $minimumPhp;

	/**
	 * @var   string  Minimum Joomla version required to install the extension
	 */
	protected $minimumJoomla;

	/**
	 * @var   boolean  Allow downgrades of your extension. Use at your own risk
	 *                 as if there is a change in functionality people may wish
	 *                 to downgrade
	 */
	protected $allowDowngrades = false;

	/**
	 * Function called before module installation/update/removal procedure commences
	 *
	 * @param   string                   $type    The type of change (install, update or discover_install,
	 *                                            not uninstall)
	 * @param   JInstallerAdapterModule  $parent  The class calling this method
	 *
	 * @return  boolean  true on success and false on failure
	 *
	 * @since  3.4
	 */
	public function preflight($type, $parent)
	{
		// Check for the minimum PHP version before continuing
		if (!empty($this->minimumPhp) && !version_compare(PHP_VERSION, $this->minimumPhp, '>'))
		{
			JLog::add(JText::sprintf('JGLOBAL_MINIMUM_PHP', $this->minimumPhp), JLog::WARNING, 'jerror');
		}

		// Check for the minimum Joomla version before continuing
		if (!empty($this->minimumJoomla) && !version_compare(JVERSION, $this->minimumJoomla, '>'))
		{
			JLog::add(JText::sprintf('JGLOBAL_MINIMUM_JOOMLA', $this->minimumJoomla), JLog::WARNING, 'jerror');
		}

		// Module manifest file version
		$this->release = $parent->get("manifest")->version;
		$extensionType = substr($this->extension, 0, 3);

		if ($extensionType === 'mod')
		{
			// Modules belong in the module table - else in the extension table
			$this->paramTable = '#__modules';
		}
		else
		{
			$this->paramTable = '#__extensions';
		}

		// Abort if the module being installed is not newer than the currently installed version
		if ($type == 'Update' && $this->allowDowngrades)
		{
			$manifest = $this->getItemArray('manifest_cache', '#__extensions', 'element', JFactory::getDbo()->quote($this->extension));
			$oldRelease = $manifest['version'];

			if (version_compare($this->release, $oldRelease, '<'))
			{
				JFactory::getApplication()->enqueueMessage(JText::sprintf('JGLOBAL_INCORRECT_SEQUENCE', $oldRelease, $this->release), 'error');

				return false;
			}
		}

		return true;
	}

	/**
	 * Gets each instance of a module in the #__modules table
	 *
	 * @param   boolean  $isModule  True if the extension is a module as this can have multiple instances
	 *
	 * @return  array  An array of ID's of the extension
	 *
	 * @since  3.4
	 */
	public function getInstances($isModule)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Select the item(s) and retrieve the id
		$query->select($db->quoteName('id'));

		if ($isModule)
		{
			$query->from($db->quoteName('#__modules'))
				->where($db->quoteName('module') . ' = ' . $db->Quote($this->extension));
		}
		else
		{
			$query->from($db->quoteName('#__extensions'))
				->where($db->quoteName('element') . ' = ' . $db->Quote($this->extension));
		}

		// Set the query and obtain an array of id's
		$db->setQuery($query);
		$items = $db->loadColumn();

		return $items;
	}

	/**
	 * Gets parameter value in the extensions row of the extension table
	 *
	 * @param   string   $name  The name of the parameter to be retrieved
	 * @param   integer  $id    The id of the item in the Param Table
	 *
	 * @return  string  The parameter desired
	 *
	 * @since   3.4
	 */
	public function getParam($name, $id = 0)
	{
		if (!is_int($id) || $id == 0)
		{
			// Return false if there is no item given
			return false;
		}

		$params = $this->getItemArray('params', $this->paramTable, 'id', $id);

		return $params[$name];
	}

	/**
	 * Sets parameter values in the extensions row of the extension table. Note that the
	 * this must be called separately for deleting and editing. Note if edit is called as a
	 * type then if the param doesn't exist it will be created
	 *
	 * @param   array    $param_array  The array of parameters to be added/edited/removed
	 * @param   string   $type         The type of change to be made to the param (edit/remove)
	 * @param   integer  $id           The id of the item in the relevant table
	 *
	 * @return  mixed  false on failure, void otherwise
	 *
	 * @since   3.4
	 */
	public function setParams($param_array = null, $type = 'edit', $id = 0)
	{
		if (!is_int($id) || $id == 0)
		{
			// Return false if there is no valid item given
			return false;
		}

		$params = $this->getItemArray('params', $this->paramTable, 'id', $id);

		if ($param_array)
		{
			foreach ($param_array as $name => $value)
			{
				if ($type == 'edit')
				{
					// Add or edit the new variable(s) to the existing params
					if (is_array($value))
					{
						// Convert an array into a json encoded string
						$params[(string) $name] = array_values($value);
					}
					else
					{
						$params[(string) $name] = (string) $value;
					}
				}
				elseif ($type == 'remove')
				{
					// Unset the parameter from the array
					unset($params[(string) $name]);
				}
			}
		}

		// Store the combined new and existing values back as a JSON string
		$paramsString = json_encode($params);

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update($db->quoteName($this->paramTable))
			->set('params = ' . $db->quote($paramsString))
			->where('id = ' . $id);

		// Update table
		$db->setQuery($query);
		$db->execute();

		return true;
	}

	/**
	 * Builds a standard select query to produce better DRY code in this script.
	 * This should produce a single unique cell which is json encoded - it will then
	 * return an associated array with this data in.
	 *
	 * @param   string  $element     The element to get from the query
	 * @param   string  $table       The table to search for the data in
	 * @param   string  $column      The column of the database to search from
	 * @param   mixed   $identifier  The integer id or the already quoted string
	 *
	 * @return  array  associated array containing data from the cell
	 *
	 * @since   3.4
	 */
	public function getItemArray($element, $table, $column, $identifier)
	{
		// Get the DB and query objects
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Build the query
		$query->select($db->quoteName($element))
			->from($db->quoteName($table))
			->where($db->quoteName($column) . ' = ' . $identifier);
		$db->setQuery($query);

		// Load the single cell and json_decode data
		$array = json_decode($db->loadResult(), true);

		return $array;
	}

	/**
	 * Remove the files and folders in the given array from
	 *
	 * @return  null
	 *
	 * @since   3.4
	 */
	public function removeFiles()
	{
		if (!empty($this->deleteFiles))
		{
			foreach ($this->deleteFiles as $file)
			{
				if (JFile::exists(JPATH_ROOT . $file) && !JFile::delete(JPATH_ROOT . $file))
				{
					echo JText::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $file) . '<br />';
				}
			}
		}

		if (!empty($this->deleteFolders))
		{
			foreach ($this->deleteFolders as $folder)
			{
				if (JFolder::exists(JPATH_ROOT . $folder) && !JFolder::delete(JPATH_ROOT . $folder))
				{
					echo JText::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $folder) . '<br />';
				}
			}
		}
	}

	/**
	 * Moves the CLI scripts into the CLI folder in the CMS
	 *
	 * @return  null
	 *
	 * @since   3.4
	 */
	public function moveCliFiles()
	{
		if (!empty($this->cliScriptFiles))
		{
			foreach ($this->cliScriptFiles as $file)
			{
				$name = basename($file);

				if (JFile::exists(JPATH_ROOT . $file) && !JFile::move(JPATH_ROOT . $file, JPATH_ROOT . '/cli/' . $name))
				{
					echo JText::sprintf('FILES_JOOMLA_ERROR_MOVE', $name);
				}
			}
		}
	}
}
