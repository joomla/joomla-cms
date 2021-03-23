<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Config\Administrator\Model\ComponentModel;
use Joomla\Component\Fields\Administrator\Model\FieldModel;
use Joomla\Database\ParameterType;

/**
 * Script file of Joomla CMS
 *
 * @since  1.6.4
 */
class JoomlaInstallerScript
{
	/**
	 * The Joomla Version we are updating from
	 *
	 * @var    string
	 * @since  3.7
	 */
	protected $fromVersion = null;

	/**
	 * Function to act prior to installation process begins
	 *
	 * @param   string     $action     Which action is happening (install|uninstall|discover_install|update)
	 * @param   Installer  $installer  The class calling this method
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.7.0
	 */
	public function preflight($action, $installer)
	{
		if ($action === 'update')
		{
			// Get the version we are updating from
			if (!empty($installer->extension->manifest_cache))
			{
				$manifestValues = json_decode($installer->extension->manifest_cache, true);

				if (array_key_exists('version', $manifestValues))
				{
					$this->fromVersion = $manifestValues['version'];

					return true;
				}
			}

			return false;
		}

		return true;
	}

	/**
	 * Method to update Joomla!
	 *
	 * @param   Installer  $installer  The class calling this method
	 *
	 * @return  void
	 */
	public function update($installer)
	{
		$options['format']    = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
		$options['text_file'] = 'joomla_update.php';

		Log::addLogger($options, Log::INFO, array('Update', 'databasequery', 'jerror'));

		try
		{
			Log::add(Text::_('COM_JOOMLAUPDATE_UPDATE_LOG_DELETE_FILES'), Log::INFO, 'Update');
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

		// Ensure we delete the repeatable fields plugin before we remove its files
		$this->uninstallRepeatableFieldsPlugin();

		// This needs to stay for 2.5 update compatibility
		$this->deleteUnexistingFiles();
		$this->updateManifestCaches();
		$this->updateDatabase();
		$this->updateAssets($installer);
		$this->clearStatsCache();
		$this->convertTablesToUtf8mb4(true);
		$this->cleanJoomlaCache();
	}

	/**
	 * Method to clear our stats plugin cache to ensure we get fresh data on Joomla Update
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	protected function clearStatsCache()
	{
		$db = Factory::getDbo();

		try
		{
			// Get the params for the stats plugin
			$params = $db->setQuery(
				$db->getQuery(true)
					->select($db->quoteName('params'))
					->from($db->quoteName('#__extensions'))
					->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
					->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
					->where($db->quoteName('element') . ' = ' . $db->quote('stats'))
			)->loadResult();
		}
		catch (Exception $e)
		{
			echo Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br>';

			return;
		}

		$params = json_decode($params, true);

		// Reset the last run parameter
		if (isset($params['lastrun']))
		{
			$params['lastrun'] = '';
		}

		$params = json_encode($params);

		$query = $db->getQuery(true)
			->update($db->quoteName('#__extensions'))
			->set($db->quoteName('params') . ' = ' . $db->quote($params))
			->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
			->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
			->where($db->quoteName('element') . ' = ' . $db->quote('stats'));

		try
		{
			$db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			echo Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br>';

			return;
		}
	}

	/**
	 * Method to update Database
	 *
	 * @return  void
	 */
	protected function updateDatabase()
	{
		if (Factory::getDbo()->getServerType() === 'mysql')
		{
			$this->updateDatabaseMysql();
		}
	}

	/**
	 * Method to update MySQL Database
	 *
	 * @return  void
	 */
	protected function updateDatabaseMysql()
	{
		$db = Factory::getDbo();

		$db->setQuery('SHOW ENGINES');

		try
		{
			$results = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			echo Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br>';

			return;
		}

		foreach ($results as $result)
		{
			if ($result->Support != 'DEFAULT')
			{
				continue;
			}

			$db->setQuery('ALTER TABLE #__update_sites_extensions ENGINE = ' . $result->Engine);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				echo Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br>';

				return;
			}

			break;
		}
	}

	/**
	 * Uninstalls the plg_fields_repeatable plugin and transforms its custom field instances
	 * to instances of the plg_fields_subfields plugin.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function uninstallRepeatableFieldsPlugin()
	{
		$app = Factory::getApplication();
		$db = Factory::getDbo();

		// Check if the plg_fields_repeatable plugin is present
		$extensionId = $db->setQuery(
			$db->getQuery(true)
				->select('extension_id')
				->from('#__extensions')
				->where('name = ' . $db->quote('plg_fields_repeatable'))
		)->loadResult();

		// Skip uninstalling when it doesn't exist
		if (!$extensionId)
		{
			return;
		}

		// Ensure the FieldsHelper class is loaded for the Repeatable fields plugin we're about to remove
		\JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');

		try
		{
			$db->transactionStart();

			// Get the FieldsModelField, we need it in a sec
			$fieldModel = $app->bootComponent('com_fields')->getMVCFactory()->createModel('Field', 'Administrator', ['ignore_request' => true]);
			/** @var FieldModel $fieldModel */

			// Now get a list of all `repeatable` custom field instances
			$db->setQuery(
				$db->getQuery(true)
					->select('*')
					->from('#__fields')
					->where($db->quoteName('type') . ' = ' . $db->quote('repeatable'))
			);

			// Execute the query and iterate over the `repeatable` instances
			foreach ($db->loadObjectList() as $row)
			{
				// Skip broken rows - just a security measure, should not happen
				if (!isset($row->fieldparams) || !($oldFieldparams = json_decode($row->fieldparams)) || !is_object($oldFieldparams))
				{
					continue;
				}

				/**
				 * We basically want to transform this `repeatable` type into a `subfields` type. While $oldFieldparams
				 * holds the `fieldparams` of the `repeatable` type, $newFieldparams shall hold the `fieldparams`
				 * of the `subfields` type.
				 */
				$newFieldparams = array(
					'repeat'  => '1',
					'options' => array(),
				);

				// If this repeatable fields actually had child-fields (normally this is always the case)
				if (isset($oldFieldparams->fields) && is_object($oldFieldparams->fields))
				{
					// Small counter for the child-fields (aka sub fields)
					$newFieldCount = 0;

					// Iterate over the sub fields
					foreach (get_object_vars($oldFieldparams->fields) as $oldField)
					{
						// Used for field name collision prevention
						$fieldname_prefix = '';
						$fieldname_suffix = 0;

						// Try to save the new sub field in a loop because of field name collisions
						while (true)
						{
							/**
							 * We basically want to create a completely new custom fields instance for every sub field
							 * of the `repeatable` instance. This is what we use $data for, we create a new custom field
							 * for each of the sub fields of the `repeatable` instance.
							 */
							$data = array(
								'context'       => $row->context,
								'group_id'      => $row->group_id,
								'title'         => $oldField->fieldname,
								'name'          => (
									$fieldname_prefix
									. $oldField->fieldname
									. ($fieldname_suffix > 0 ? ('_' . $fieldname_suffix) : '')
								),
								'label'         => $oldField->fieldname,
								'default_value' => $row->default_value,
								'type'          => $oldField->fieldtype,
								'description'   => $row->description,
								'state'         => '1',
								'params'        => $row->params,
								'language'      => '*',
								'assigned_cat_ids' => [-1],
							);

							// `number` is not a valid custom field type, so use `text` instead.
							if ($data['type'] == 'number')
							{
								$data['type'] = 'text';
							}

							// Reset the state because else \Joomla\CMS\MVC\Model\AdminModel will take an already
							// existing value (e.g. from previous save) and do an UPDATE instead of INSERT.
							$fieldModel->setState('field.id', 0);

							// If an error occurred when trying to save this.
							if (!$fieldModel->save($data))
							{
								// If the error is, that the name collided, increase the collision prevention
								$error = $fieldModel->getError();

								if ($error == 'COM_FIELDS_ERROR_UNIQUE_NAME')
								{
									// If this is the first time this error occurs, set only the prefix
									if ($fieldname_prefix == '')
									{
										$fieldname_prefix = ($row->name . '_');
									}
									else
									{
										// Else increase the suffix
										$fieldname_suffix++;
									}

									// And start again with the while loop.
									continue 1;
								}

								// Else bail out with the error. Something is totally wrong.
								throw new \Exception($error);
							}

							// Break out of the while loop, saving was successful.
							break 1;
						}

						// Get the newly created id
						$subfield_id = $fieldModel->getState('field.id');

						// Really check that it is valid
						if (!is_numeric($subfield_id) || $subfield_id < 1)
						{
							throw new \Exception('Something went wrong.');
						}

						// And tell our new `subfields` field about his child
						$newFieldparams['options'][('option' . $newFieldCount)] = array(
							'customfield'   => $subfield_id,
							'render_values' => '1',
						);

						$newFieldCount++;
					}
				}

				// Write back the changed stuff to the database
				$db->setQuery(
					$db->getQuery(true)
						->update('#__fields')
						->set($db->quoteName('type') . ' = ' . $db->quote('subfields'))
						->set($db->quoteName('fieldparams') . ' = ' . $db->quote(json_encode($newFieldparams)))
						->where($db->quoteName('id') . ' = ' . $db->quote($row->id))
				)->execute();
			}

			// Now, unprotect the plugin so we can uninstall it
			$db->setQuery(
				$db->getQuery(true)
					->update('#__extensions')
					->set('protected = 0')
					->where($db->quoteName('extension_id') . ' = ' . $extensionId)
			)->execute();

			// And now uninstall the plugin
			$installer = new Installer;
			$installer->uninstall('plugin', $extensionId);

			$db->transactionCommit();
		}
		catch (\Exception $e)
		{
			$db->transactionRollback();
			throw $e;
		}
	}

	/**
	 * Update the manifest caches
	 *
	 * @return  void
	 */
	protected function updateManifestCaches()
	{
		$extensions = ExtensionHelper::getCoreExtensions();

		// Attempt to refresh manifest caches
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from('#__extensions');

		foreach ($extensions as $extension)
		{
			$query->where(
				'type=' . $db->quote($extension[0])
				. ' AND element=' . $db->quote($extension[1])
				. ' AND folder=' . $db->quote($extension[2])
				. ' AND client_id=' . $extension[3], 'OR'
			);
		}

		$db->setQuery($query);

		try
		{
			$extensions = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			echo Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br>';

			return;
		}

		$installer = new Installer;

		foreach ($extensions as $extension)
		{
			if (!$installer->refreshManifestCache($extension->extension_id))
			{
				echo Text::sprintf('FILES_JOOMLA_ERROR_MANIFEST', $extension->type, $extension->element, $extension->name, $extension->client_id) . '<br>';
			}
		}
	}

	/**
	 * Delete files that should not exist
	 *
	 * @param  bool  $dryRun          If set to true, will not actually delete files, but just report their status for use in CLI
	 * @param  bool  $suppressOutput  Set to true to suppress echoing any errors, and just return the $status array
	 *
	 * @return  array
	 * @throws  Exception
	 */
	public function deleteUnexistingFiles($dryRun = false, $suppressOutput = false)
	{
		$forDeletion = $this->getListOfDeletions();

		$status = [
			'files_exist'     => [],
			'folders_exist'   => [],
			'files_deleted'   => [],
			'folders_deleted' => [],
			'files_errors'    => [],
			'folders_errors'  => [],
			'folders_checked' => $forDeletion['folders'],
			'files_checked'   => $forDeletion['files'],
		];

		foreach ($status['files_checked'] as $file)
		{
			if ($fileExists = File::exists(JPATH_ROOT . $file))
			{
				$status['files_exist'][] = $file;

				if ($dryRun === false)
				{
					if (File::delete(JPATH_ROOT . $file))
					{
						$status['files_deleted'][] = $file;
					}
					else
					{
						$status['files_errors'][] = Text::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $file);
					}
				}
			}
		}

		foreach ($status['folders_checked'] as $folder)
		{
			if ($folderExists = Folder::exists(JPATH_ROOT . $folder))
			{
				$status['folders_exist'][] = $folder;

				if ($dryRun === false)
				{
					// TODO There is an issue while deleting folders using the ftp mode
					if (Folder::delete(JPATH_ROOT . $folder))
					{
						$status['folders_deleted'][] = $folder;
					}
					else
					{
						$status['folders_errors'][] = Text::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $folder);
					}
				}
			}
		}

		$this->fixFilenameCasing();

		if ($suppressOutput === false && \count($status['folders_errors']))
		{
			echo implode('<br/>', $status['folders_errors']);
		}

		if ($suppressOutput === false && \count($status['files_errors']))
		{
			echo implode('<br/>', $status['files_errors']);
		}

		return $status;
	}

	/**
	 * Method to create assets for newly installed components
	 *
	 * @param   Installer  $installer  The class calling this method
	 *
	 * @return  boolean
	 *
	 * @since   3.2
	 */
	public function updateAssets($installer)
	{
		// List all components added since 4.0
		$newComponents = array(
			'com_csp',
		);

		foreach ($newComponents as $component)
		{
			/** @var JTableAsset $asset */
			$asset = Table::getInstance('Asset');

			if ($asset->loadByName($component))
			{
				continue;
			}

			$asset->name      = $component;
			$asset->parent_id = 1;
			$asset->rules     = '{}';
			$asset->title     = $component;
			$asset->setLocation(1, 'last-child');

			if (!$asset->store())
			{
				// Install failed, roll back changes
				$installer->abort(Text::sprintf('JLIB_INSTALLER_ABORT_COMP_INSTALL_ROLLBACK', $asset->getError(true)));

				return false;
			}
		}

		return true;
	}

	/**
	 * Converts the site's database tables to support UTF-8 Multibyte.
	 *
	 * @param   boolean  $doDbFixMsg  Flag if message to be shown to check db fix
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	public function convertTablesToUtf8mb4($doDbFixMsg = false)
	{
		$db = Factory::getDbo();

		if ($db->getServerType() !== 'mysql')
		{
			return;
		}

		// Check if the #__utf8_conversion table exists
		$db->setQuery('SHOW TABLES LIKE ' . $db->quote($db->getPrefix() . 'utf8_conversion'));

		try
		{
			$rows = $db->loadRowList(0);
		}
		catch (Exception $e)
		{
			// Render the error message from the Exception object
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			if ($doDbFixMsg)
			{
				// Show an error message telling to check database problems
				Factory::getApplication()->enqueueMessage(Text::_('JLIB_DATABASE_ERROR_DATABASE_UPGRADE_FAILED'), 'error');
			}

			return;
		}

		// Nothing to do if the table doesn't exist because the CMS has never been updated from a pre-4.0 version
		if (\count($rows) === 0)
		{
			return;
		}

		// Set required conversion status
		$converted = 5;

		// Check conversion status in database
		$db->setQuery('SELECT ' . $db->quoteName('converted')
			. ' FROM ' . $db->quoteName('#__utf8_conversion')
		);

		try
		{
			$convertedDB = $db->loadResult();
		}
		catch (Exception $e)
		{
			// Render the error message from the Exception object
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			if ($doDbFixMsg)
			{
				// Show an error message telling to check database problems
				Factory::getApplication()->enqueueMessage(Text::_('JLIB_DATABASE_ERROR_DATABASE_UPGRADE_FAILED'), 'error');
			}

			return;
		}

		// If conversion status from DB is equal to required final status, try to drop the #__utf8_conversion table
		if ($convertedDB === $converted)
		{
			$this->dropUtf8ConversionTable();

			return;
		}

		// Perform the required conversions of core tables if not done already in a previous step
		if ($convertedDB !== 99)
		{
			$fileName1 = JPATH_ROOT . '/administrator/components/com_admin/sql/others/mysql/utf8mb4-conversion.sql';

			if (is_file($fileName1))
			{
				$fileContents1 = @file_get_contents($fileName1);
				$queries1      = $db->splitSql($fileContents1);

				if (!empty($queries1))
				{
					foreach ($queries1 as $query1)
					{
						try
						{
							$db->setQuery($query1)->execute();
						}
						catch (Exception $e)
						{
							$converted = $convertedDB;

							// Still render the error message from the Exception object
							Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
						}
					}
				}
			}
		}

		// If no error before, perform the optional conversions of tables which might or might not exist
		if ($converted === 5)
		{
			$fileName2 = JPATH_ROOT . '/administrator/components/com_admin/sql/others/mysql/utf8mb4-conversion_optional.sql';

			if (is_file($fileName2))
			{
				$fileContents2 = @file_get_contents($fileName2);
				$queries2      = $db->splitSql($fileContents2);

				if (!empty($queries2))
				{
					foreach ($queries2 as $query2)
					{
						// Get table name from query
						if (preg_match('/^ALTER\s+TABLE\s+([^\s]+)\s+/i', $query2, $matches) === 1)
						{
							$tableName = str_replace('`', '', $matches[1]);
							$tableName = str_replace('#__', $db->getPrefix(), $tableName);

							// Check if the table exists and if yes, run the query
							try
							{
								$db->setQuery('SHOW TABLES LIKE ' . $db->quote($tableName));

								$rows = $db->loadRowList(0);

								if (\count($rows) > 0)
								{
									$db->setQuery($query2)->execute();
								}
							}
							catch (Exception $e)
							{
								$converted = 99;

								// Still render the error message from the Exception object
								Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
							}
						}
					}
				}
			}
		}

		if ($doDbFixMsg && $converted !== 5)
		{
			// Show an error message telling to check database problems
			Factory::getApplication()->enqueueMessage(Text::_('JLIB_DATABASE_ERROR_DATABASE_UPGRADE_FAILED'), 'error');
		}

		// If the conversion was successful try to drop the #__utf8_conversion table
		if ($converted === 5 && $this->dropUtf8ConversionTable())
		{
			// Table successfully dropped
			return;
		}

		// Set flag in database if the conversion status has changed.
		if ($converted !== $convertedDB)
		{
			$db->setQuery('UPDATE ' . $db->quoteName('#__utf8_conversion')
				. ' SET ' . $db->quoteName('converted') . ' = ' . $converted . ';'
			)->execute();
		}
	}

	/**
	 * This method clean the Joomla Cache using the method `clean` from the com_cache model
	 *
	 * @return  void
	 *
	 * @since   3.5.1
	 */
	private function cleanJoomlaCache()
	{
		$model = new \Joomla\Component\Cache\Administrator\Model\CacheModel;

		// Clean frontend cache
		$model->clean();

		// Clean admin cache
		$model->setState('client_id', 1);
		$model->clean();
	}

	/**
	 * This method drops the #__utf8_conversion table
	 *
	 * @return  boolean  True on success
	 *
	 * @since   4.0.0
	 */
	private function dropUtf8ConversionTable()
	{
		$db = Factory::getDbo();

		try
		{
			$db->setQuery('DROP TABLE ' . $db->quoteName('#__utf8_conversion') . ';'
			)->execute();
		}
		catch (Exception $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Called after any type of action
	 *
	 * @param   string     $action     Which action is happening (install|uninstall|discover_install|update)
	 * @param   Installer  $installer  The class calling this method
	 *
	 * @return  boolean  True on success
	 *
	 * @since   4.0.0
	 */
	public function postflight($action, $installer)
	{
		if ($action !== 'update')
		{
			return true;
		}

		if (empty($this->fromVersion) || version_compare($this->fromVersion, '4.0.0', 'ge'))
		{
			return true;
		}

		// Update UCM content types.
		$this->updateContentTypes();

		$db = Factory::getDbo();
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_menus/Table/');

		$tableItem   = new \Joomla\Component\Menus\Administrator\Table\MenuTable($db);

		$contactItems = $this->contactItems($tableItem);
		$finderItems  = $this->finderItems($tableItem);

		$menuItems = array_merge($contactItems, $finderItems);

		foreach ($menuItems as $menuItem)
		{
			// Check an existing record
			$keys = [
				'menutype'  => $menuItem['menutype'],
				'type'      => $menuItem['type'],
				'title'     => $menuItem['title'],
				'parent_id' => $menuItem['parent_id'],
				'client_id' => $menuItem['client_id'],
			];

			if ($tableItem->load($keys))
			{
				continue;
			}

			$newTableItem = new \Joomla\Component\Menus\Administrator\Table\MenuTable($db);

			// Bind the data.
			if (!$newTableItem->bind($menuItem))
			{
				return false;
			}

			$newTableItem->setLocation($menuItem['parent_id'], 'last-child');

			// Check the data.
			if (!$newTableItem->check())
			{
				return false;
			}

			// Store the data.
			if (!$newTableItem->store())
			{
				return false;
			}

			// Rebuild the tree path.
			if (!$newTableItem->rebuildPath($newTableItem->id))
			{
				return false;
			}
		}

		$this->convertBlogLayouts();

		return true;
	}

	/**
	 * Prepare the contact menu items
	 *
	 * @return  array  Menu items
	 *
	 * @since   4.0.0
	 */
	private function contactItems(Table $tableItem): array
	{
		// Check for the Contact parent Id Menu Item
		$keys = [
			'menutype'  => 'main',
			'type'      => 'component',
			'title'     => 'com_contact',
			'parent_id' => 1,
			'client_id' => 1,
		];

		$contactMenuitem = $tableItem->load($keys);

		if (!$contactMenuitem)
		{
			return [];
		}

		$parentId    = $tableItem->id;
		$componentId = ExtensionHelper::getExtensionRecord('com_fields', 'component')->extension_id;

		// Add Contact Fields Menu Items.
		$menuItems = [
			[
				'menutype'          => 'main',
				'title'             => '-',
				'alias'             => microtime(true),
				'note'              => '',
				'path'              => '',
				'link'              => '#',
				'type'              => 'separator',
				'published'         => 1,
				'parent_id'         => $parentId,
				'level'             => 2,
				'component_id'      => $componentId,
				'checked_out'       => null,
				'checked_out_time'  => null,
				'browserNav'        => 0,
				'access'            => 0,
				'img'               => '',
				'template_style_id' => 0,
				'params'            => '{}',
				'home'              => 0,
				'language'          => '*',
				'client_id'         => 1,
				'publish_up'        => null,
				'publish_down'      => null,
			],
			[
				'menutype'          => 'main',
				'title'             => 'mod_menu_fields',
				'alias'             => 'Contact Custom Fields',
				'note'              => '',
				'path'              => 'contact/Custom Fields',
				'link'              => 'index.php?option=com_fields&context=com_contact.contact',
				'type'              => 'component',
				'published'         => 1,
				'parent_id'         => $parentId,
				'level'             => 2,
				'component_id'      => $componentId,
				'checked_out'       => null,
				'checked_out_time'  => null,
				'browserNav'        => 0,
				'access'            => 0,
				'img'               => '',
				'template_style_id' => 0,
				'params'            => '{}',
				'home'              => 0,
				'language'          => '*',
				'client_id'         => 1,
				'publish_up'        => null,
				'publish_down'      => null,
			],
			[
				'menutype'          => 'main',
				'title'             => 'mod_menu_fields_group',
				'alias'             => 'Contact Custom Fields Group',
				'note'              => '',
				'path'              => 'contact/Custom Fields Group',
				'link'              => 'index.php?option=com_fields&view=groups&context=com_contact.contact',
				'type'              => 'component',
				'published'         => 1,
				'parent_id'         => $parentId,
				'level'             => 2,
				'component_id'      => $componentId,
				'checked_out'       => null,
				'checked_out_time'  => null,
				'browserNav'        => 0,
				'access'            => 0,
				'img'               => '',
				'template_style_id' => 0,
				'params'            => '{}',
				'home'              => 0,
				'language'          => '*',
				'client_id'         => 1,
				'publish_up'        => null,
				'publish_down'      => null,
			]
		];

		return $menuItems;
	}

	/**
	 * Prepare the finder menu items
	 *
	 * @return  array  Menu items
	 *
	 * @since   4.0.0
	 */
	private function finderItems(Table $tableItem): array
	{
		// Check for the Finder parent Id Menu Item
		$keys = [
			'menutype'  => 'main',
			'type'      => 'component',
			'title'     => 'com_finder',
			'parent_id' => 1,
			'client_id' => 1,
		];

		$finderMenuitem = $tableItem->load($keys);

		if (!$finderMenuitem)
		{
			return [];
		}

		$parentId    = $tableItem->id;
		$componentId = ExtensionHelper::getExtensionRecord('com_finder', 'component')->extension_id;

		// Add Finder Fields Menu Items.
		$menuItems = [
			[
				'menutype'          => 'main',
				'title'             => '-',
				'alias'             => microtime(true),
				'note'              => '',
				'path'              => '',
				'link'              => '#',
				'type'              => 'separator',
				'published'         => 1,
				'parent_id'         => $parentId,
				'level'             => 2,
				'component_id'      => $componentId,
				'checked_out'       => null,
				'checked_out_time'  => null,
				'browserNav'        => 0,
				'access'            => 0,
				'img'               => '',
				'template_style_id' => 0,
				'params'            => '{}',
				'home'              => 0,
				'language'          => '*',
				'client_id'         => 1,
				'publish_up'        => null,
				'publish_down'      => null,
			],
			[
				'menutype'          => 'main',
				'title'             => 'com_finder_index',
				'alias'             => 'Smart-Search-Index',
				'note'              => '',
				'path'              => 'Smart Search/Index',
				'link'              => 'index.php?option=com_finder&view=index',
				'type'              => 'component',
				'published'         => 1,
				'parent_id'         => $parentId,
				'level'             => 2,
				'component_id'      => $componentId,
				'checked_out'       => null,
				'checked_out_time'  => null,
				'browserNav'        => 0,
				'access'            => 0,
				'img'               => '',
				'template_style_id' => 0,
				'params'            => '{}',
				'home'              => 0,
				'language'          => '*',
				'client_id'         => 1,
				'publish_up'        => null,
				'publish_down'      => null,
			],
			[
				'menutype'          => 'main',
				'title'             => 'com_finder_maps',
				'alias'             => 'Smart-Search-Maps',
				'note'              => '',
				'path'              => 'Smart Search/Maps',
				'link'              => 'index.php?option=com_finder&view=maps',
				'type'              => 'component',
				'published'         => 1,
				'parent_id'         => $parentId,
				'level'             => 2,
				'component_id'      => $componentId,
				'checked_out'       => null,
				'checked_out_time'  => null,
				'browserNav'        => 0,
				'access'            => 0,
				'img'               => '',
				'template_style_id' => 0,
				'params'            => '{}',
				'home'              => 0,
				'language'          => '*',
				'client_id'         => 1,
				'publish_up'        => null,
				'publish_down'      => null,
			],
			[
				'menutype'          => 'main',
				'title'             => 'com_finder_filters',
				'alias'             => 'Smart-Search-Filters',
				'note'              => '',
				'path'              => 'Smart Search/Filters',
				'link'              => 'index.php?option=com_finder&view=filters',
				'type'              => 'component',
				'published'         => 1,
				'parent_id'         => $parentId,
				'level'             => 2,
				'component_id'      => $componentId,
				'checked_out'       => null,
				'checked_out_time'  => null,
				'browserNav'        => 0,
				'access'            => 0,
				'img'               => '',
				'template_style_id' => 0,
				'params'            => '{}',
				'home'              => 0,
				'language'          => '*',
				'client_id'         => 1,
				'publish_up'        => null,
				'publish_down'      => null,
			],
			[
				'menutype'          => 'main',
				'title'             => 'com_finder_searches',
				'alias'             => 'Smart-Search-Searches',
				'note'              => '',
				'path'              => 'Smart Search/Searches',
				'link'              => 'index.php?option=com_finder&view=searches',
				'type'              => 'component',
				'published'         => 1,
				'parent_id'         => $parentId,
				'level'             => 2,
				'component_id'      => $componentId,
				'checked_out'       => null,
				'checked_out_time'  => null,
				'browserNav'        => 0,
				'access'            => 0,
				'img'               => '',
				'template_style_id' => 0,
				'params'            => '{}',
				'home'              => 0,
				'language'          => '*',
				'client_id'         => 1,
				'publish_up'        => null,
				'publish_down'      => null,
			]
		];

		return $menuItems;
	}

	/**
	 * Updates content type table classes.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	private function updateContentTypes(): void
	{
		// Content types to update.
		$contentTypes = [
			'com_content.article',
			'com_contact.contact',
			'com_newsfeeds.newsfeed',
			'com_tags.tag',
			'com_banners.banner',
			'com_banners.client',
			'com_users.note',
			'com_content.category',
			'com_contact.category',
			'com_newsfeeds.category',
			'com_banners.category',
			'com_users.category',
			'com_users.user',
		];

		// Get table definitions.
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select(
				[
					$db->quoteName('type_alias'),
					$db->quoteName('table'),
				]
			)
			->from($db->quoteName('#__content_types'))
			->whereIn($db->quoteName('type_alias'), $contentTypes, ParameterType::STRING);

		$db->setQuery($query);
		$contentTypes = $db->loadObjectList();

		// Prepare the update query.
		$query = $db->getQuery(true)
			->update($db->quoteName('#__content_types'))
			->set($db->quoteName('table') . ' = :table')
			->where($db->quoteName('type_alias') . ' = :typeAlias')
			->bind(':table', $table)
			->bind(':typeAlias', $typeAlias);

		$db->setQuery($query);

		foreach ($contentTypes as $contentType)
		{
			list($component, $tableType) = explode('.', $contentType->type_alias);

			// Special case for core table classes.
			if ($contentType->type_alias === 'com_users.users' || $tableType === 'category')
			{
				$tablePrefix = 'Joomla\\CMS\Table\\';
				$tableType   = ucfirst($tableType);
			}
			else
			{
				$tablePrefix = 'Joomla\\Component\\' . ucfirst(substr($component, 4)) . '\\Administrator\\Table\\';
				$tableType   = ucfirst($tableType) . 'Table';
			}

			// Bind type alias.
			$typeAlias = $contentType->type_alias;

			$table = json_decode($contentType->table);

			// Update table definitions.
			$table->special->type   = $tableType;
			$table->special->prefix = $tablePrefix;

			// Some content types don't have this property.
			if (!empty($table->common->prefix))
			{
				$table->common->prefix  = 'Joomla\\CMS\\Table\\';
			}

			$table = json_encode($table);

			// Execute the query.
			$db->execute();
		}
	}

	/**
	 * Converts layout parameters for blog / featured views into the according CSS classes.
	 *
	 * @return void
	 *
	 * @since 4.0.0
	 */
	private function convertBlogLayouts()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select(
				[
					$db->quoteName('m.id'),
					$db->quoteName('m.link'),
					$db->quoteName('m.params'),
				]
			)
			->from($db->quoteName('#__menu', 'm'))
			->leftJoin($db->quoteName('#__extensions', 'e'), $db->quoteName('e.extension_id') . ' = ' . $db->quoteName('m.component_id'))
			->where($db->quoteName('e.element') . ' = ' . $db->quote('com_content'));

		$menuItems = $db->setQuery($query)->loadAssocList('id');
		$contentParams = ComponentHelper::getParams('com_content');

		foreach ($menuItems as $id => $menuItem)
		{
			$view = Uri::getInstance($menuItem['link'])->getVar('view');

			if (!in_array($view, ['category', 'categories', 'featured']))
			{
				continue;
			}

			$params = json_decode($menuItem['params'], true);

			// Don't update parameters if num_columns is unset.
			if (!isset($params['num_columns']))
			{
				continue;
			}

			$useLocalCols = $params['num_columns'] !== '';

			if ($useLocalCols)
			{
				$nColumns = (int) $params['num_columns'];
			}
			else
			{
				$nColumns = (int) $contentParams->get('num_columns', '1');
			}

			unset($params['num_columns']);

			$order = 0;
			$useLocalOrder = false;

			if (isset($params['multi_column_order']))
			{
				if ($params['multi_column_order'] !== '')
				{
					$useLocalOrder = true;
					$order = (int) $params['multi_column_order'];
				}
				else
				{
					$order = (int) $contentParams->get('multi_column_order', '0');
				}

				unset($params['multi_column_order']);
			}

			// Only add CSS class if columns > 1 and a local value was set for columns or order.
			if ($nColumns > 1 && ($useLocalOrder || $useLocalCols))
			{
				// Convert to the according CSS class depending on order = "down" or "across".
				$layout = ($order === 0) ? 'masonry-' : 'columns-';

				if (!isset($params['blog_class']))
				{
					$params['blog_class'] = '';
				}

				if (strpos($params['blog_class'], $layout) === false)
				{
					$params['blog_class'] .= ' ' . $layout . $nColumns;
				}
			}

			$newParams = json_encode($params);

			$query = $db->getQuery(true)
				->update($db->quoteName('#__menu'))
				->set($db->quoteName('params') . ' = :params')
				->where($db->quoteName('id') . ' = :id')
				->bind(':params', $newParams, ParameterType::STRING)
				->bind(':id', $id, ParameterType::INTEGER);

			$db->setQuery($query)->execute();
		}

		// Update global parameters for com_content.
		$nColumns = $contentParams->get('num_columns');

		if ($nColumns !== null)
		{
			$nColumns = (int) $nColumns;
			$order  = (int) $contentParams->get('multi_column_order', '0');
			$params = $contentParams->toArray();

			if (!isset($params['blog_class']))
			{
				$params['blog_class'] = '';
			}

			// Convert to the according CSS class depending on order = "down" or "across".
			$layout = ($order === 0) ? 'masonry-' : 'columns-';

			if (strpos($params['blog_class'], $layout) === false && $nColumns > 1)
			{
				$params['blog_class'] .= ' ' . $layout . $nColumns;
			}

			unset($params['num_columns']);

			$app = Factory::getApplication();
			/** @var ComponentModel $configModel */
			$configModel = $app->bootComponent('com_config')
				->getMVCFactory()
				->createModel('Component', 'Administrator', ['ignore_request' => true]);

			$query = $db->getQuery(true)
				->select($db->quoteName('extension_id'))
				->from($db->quoteName('#__extensions'))
				->where($db->quoteName('element') . ' = ' . $db->quote('com_content'));

			$componentId = $db->setQuery($query)->loadResult();

			$data = array(
				'id'     => $componentId,
				'option' => 'com_content',
				'params' => $params,
			);
			$configModel->save($data);
		}
	}

	/**
	 * Renames or removes incorrectly cased files.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function fixFilenameCasing()
	{
		$files = array(
			// 3.10 changes
			'libraries/src/Filesystem/Support/Stringcontroller.php' => 'libraries/src/Filesystem/Support/StringController.php',
			'libraries/src/Form/Rule/SubFormRule.php' => 'libraries/src/Form/Rule/SubformRule.php',
			// 4.0.0
			'media/vendor/skipto/js/skipTo.js' => 'media/vendor/skipto/js/skipto.js',
		);

		foreach ($files as $old => $expected)
		{
			$oldRealpath = realpath(JPATH_ROOT . '/' . $old);

			// On Unix without incorrectly cased file.
			if ($oldRealpath === false)
			{
				continue;
			}

			$oldBasename      = basename($oldRealpath);
			$newRealpath      = realpath(JPATH_ROOT . '/' . $expected);
			$newBasename      = basename($newRealpath);
			$expectedBasename = basename($expected);

			// On Windows or Unix with only the incorrectly cased file.
			if ($newBasename !== $expectedBasename)
			{
				// Rename the file.
				rename(JPATH_ROOT . '/' . $old, JPATH_ROOT . '/' . $old . '.tmp');
				rename(JPATH_ROOT . '/' . $old . '.tmp', JPATH_ROOT . '/' . $expected);

				continue;
			}

			// There might still be an incorrectly cased file on other OS than Windows.
			if ($oldBasename === basename($old))
			{
				// Check if case-insensitive file system, eg on OSX.
				if (fileinode($oldRealpath) === fileinode($newRealpath))
				{
					// Check deeper because even realpath or glob might not return the actual case.
					if (!in_array($expectedBasename, scandir(dirname($newRealpath))))
					{
						// Rename the file.
						rename(JPATH_ROOT . '/' . $old, JPATH_ROOT . '/' . $old . '.tmp');
						rename(JPATH_ROOT . '/' . $old . '.tmp', JPATH_ROOT . '/' . $expected);
					}
				}
				else
				{
					// On Unix with both files: Delete the incorrectly cased file.
					unlink(JPATH_ROOT . '/' . $old);
				}
			}
		}
	}

	/**
	 * Get the array of files or folders that should be deleted
	 *
	 * @return  array
	 * @throws  Exception
	 */
	private function getListOfDeletions(): array
	{
		if (empty($type))
		{
			throw new \Exception('Type is required');
		}

		$dir = __DIR__ . '/_deletions/' . $type;

		if (!opendir($dir))
		{
			throw new \Exception('Directory "' . str_replace(JPATH_ROOT, $dir) . '" doesn\'t exist');
		}

		$filesForDeletion   = [];
		$foldersForDeletion = [];
		$files              = readdir($dir);

		if (!$files)
		{
			throw new \Exception('Empty directory: ' . str_replace(JPATH_ROOT, $dir));
		}

		foreach ($files as $file)
		{
			if ($file !== '.' && $file !== '..')
			{
				$this->parseXMLFile($file, $filesForDeletion, $foldersForDeletion);
			}
		}

		return [
			'files'   => $filesForDeletion,
			'folders' => $foldersForDeletion,
		];
	}

	/**
	 * Returns the JSON decoded data of a file
	 * Assumes that the JSON files have only array data!
	 *
	 * @param string  $file     The file path
	 * @param array   $files    The array of files to be deleted
	 * @param array   $folders  The array of folders to be deleted
	 *
	 * @return void
	 */
	private function parseXMLFile(string $file, $files = [], $folders = []): void
	{
		try
		{
			$fileContent = file_get_contents($file);
		}
		catch (\Exception $error)
		{
			// @todo Do some logging or echo the error???
		}

		$xml = new \SimpleXMLElement($fileContent);

		foreach ($xml->files as $element)
		{
			foreach ($element as $key => $val)
			{
				if ($key === 'folder')
				{
					$folders[] = $val;
				}

				if ($key === 'filename')
				{
					$files[] = $val;
				}
			}
		}
	}
}
