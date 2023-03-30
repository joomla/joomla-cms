<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Table\Table;
use Joomla\Component\Fields\Administrator\Model\FieldModel;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

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
        if ($action === 'update') {
            // Get the version we are updating from
            if (!empty($installer->extension->manifest_cache)) {
                $manifestValues = json_decode($installer->extension->manifest_cache, true);

                if (array_key_exists('version', $manifestValues)) {
                    $this->fromVersion = $manifestValues['version'];

                    // Ensure templates are moved to the correct mode
                    $this->fixTemplateMode();

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

        Log::addLogger($options, Log::INFO, ['Update', 'databasequery', 'jerror']);

        try {
            Log::add(Text::_('COM_JOOMLAUPDATE_UPDATE_LOG_DELETE_FILES'), Log::INFO, 'Update');
        } catch (RuntimeException $exception) {
            // Informational log only
        }

        // Uninstall plugins before removing their files and folders
        $this->uninstallRepeatableFieldsPlugin();
        $this->uninstallEosPlugin();

        // This needs to stay for 2.5 update compatibility
        $this->deleteUnexistingFiles();
        $this->updateManifestCaches();
        $this->updateDatabase();
        $this->updateAssets($installer);
        $this->clearStatsCache();
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

        try {
            // Get the params for the stats plugin
            $params = $db->setQuery(
                $db->getQuery(true)
                    ->select($db->quoteName('params'))
                    ->from($db->quoteName('#__extensions'))
                    ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                    ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
                    ->where($db->quoteName('element') . ' = ' . $db->quote('stats'))
            )->loadResult();
        } catch (Exception $e) {
            echo Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br>';

            return;
        }

        $params = json_decode($params, true);

        // Reset the last run parameter
        if (isset($params['lastrun'])) {
            $params['lastrun'] = '';
        }

        $params = json_encode($params);

        $query = $db->getQuery(true)
            ->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('params') . ' = ' . $db->quote($params))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('stats'));

        try {
            $db->setQuery($query)->execute();
        } catch (Exception $e) {
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
        if (Factory::getDbo()->getServerType() === 'mysql') {
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

        try {
            $results = $db->loadObjectList();
        } catch (Exception $e) {
            echo Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br>';

            return;
        }

        foreach ($results as $result) {
            if ($result->Support != 'DEFAULT') {
                continue;
            }

            $db->setQuery('ALTER TABLE #__update_sites_extensions ENGINE = ' . $result->Engine);

            try {
                $db->execute();
            } catch (Exception $e) {
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
        $db  = Factory::getDbo();

        // Check if the plg_fields_repeatable plugin is present
        $extensionId = $db->setQuery(
            $db->getQuery(true)
                ->select('extension_id')
                ->from('#__extensions')
                ->where('name = ' . $db->quote('plg_fields_repeatable'))
        )->loadResult();

        // Skip uninstalling when it doesn't exist
        if (!$extensionId) {
            return;
        }

        // Ensure the FieldsHelper class is loaded for the Repeatable fields plugin we're about to remove
        \JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');

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
        foreach ($db->loadObjectList() as $row) {
            // Skip broken rows - just a security measure, should not happen
            if (!isset($row->fieldparams) || !($oldFieldparams = json_decode($row->fieldparams)) || !is_object($oldFieldparams)) {
                continue;
            }

            // First get this field's values for later data migration, so if this fails it happens before saving new subfields
            $query = $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__fields_values'))
                ->where($db->quoteName('field_id') . ' = ' . $row->id);
            $db->setQuery($query);
            $rowFieldValues = $db->loadObjectList();

            /**
             * We basically want to transform this `repeatable` type into a `subfields` type. While $oldFieldparams
             * holds the `fieldparams` of the `repeatable` type, $newFieldparams shall hold the `fieldparams`
             * of the `subfields` type.
             */
            $newFieldparams = [
                'repeat'  => '1',
                'options' => [],
            ];

            /**
             * This array is used to store the mapping between the name of form fields from Repeatable field
             * with ID of the child-fields. It will then be used to migrate data later
             */
            $mapping = [];

            /**
             * Store name of media fields which we need to convert data from old format (string) to new
             * format (json) during the migration
             */
            $mediaFields = [];

            // If this repeatable fields actually had child-fields (normally this is always the case)
            if (isset($oldFieldparams->fields) && is_object($oldFieldparams->fields)) {
                // Small counter for the child-fields (aka sub fields)
                $newFieldCount = 0;

                // Iterate over the sub fields
                foreach (get_object_vars($oldFieldparams->fields) as $oldField) {
                    // Used for field name collision prevention
                    $fieldname_prefix = '';
                    $fieldname_suffix = 0;

                    // Try to save the new sub field in a loop because of field name collisions
                    while (true) {
                        /**
                         * We basically want to create a completely new custom fields instance for every sub field
                         * of the `repeatable` instance. This is what we use $data for, we create a new custom field
                         * for each of the sub fields of the `repeatable` instance.
                         */
                        $data = [
                            'context'  => $row->context,
                            'group_id' => $row->group_id,
                            'title'    => $oldField->fieldname,
                            'name'     => (
                                $fieldname_prefix
                                . $oldField->fieldname
                                . ($fieldname_suffix > 0 ? ('_' . $fieldname_suffix) : '')
                            ),
                            'label'               => $oldField->fieldname,
                            'default_value'       => $row->default_value,
                            'type'                => $oldField->fieldtype,
                            'description'         => $row->description,
                            'state'               => '1',
                            'params'              => $row->params,
                            'language'            => '*',
                            'assigned_cat_ids'    => [-1],
                            'only_use_in_subform' => 1,
                        ];

                        // `number` is not a valid custom field type, so use `text` instead.
                        if ($data['type'] == 'number') {
                            $data['type'] = 'text';
                        }

                        if ($data['type'] == 'media') {
                            $mediaFields[] = $oldField->fieldname;
                        }

                        // Reset the state because else \Joomla\CMS\MVC\Model\AdminModel will take an already
                        // existing value (e.g. from previous save) and do an UPDATE instead of INSERT.
                        $fieldModel->setState('field.id', 0);

                        // If an error occurred when trying to save this.
                        if (!$fieldModel->save($data)) {
                            // If the error is, that the name collided, increase the collision prevention
                            $error = $fieldModel->getError();

                            if ($error == 'COM_FIELDS_ERROR_UNIQUE_NAME') {
                                // If this is the first time this error occurs, set only the prefix
                                if ($fieldname_prefix == '') {
                                    $fieldname_prefix = ($row->name . '_');
                                } else {
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
                    if (!is_numeric($subfield_id) || $subfield_id < 1) {
                        throw new \Exception('Something went wrong.');
                    }

                    // And tell our new `subfields` field about his child
                    $newFieldparams['options'][('option' . $newFieldCount)] = [
                        'customfield'   => $subfield_id,
                        'render_values' => '1',
                    ];

                    $newFieldCount++;

                    $mapping[$oldField->fieldname] = 'field' . $subfield_id;
                }
            }

            try {
                $db->transactionStart();

                // Write back the changed stuff to the database
                $db->setQuery(
                    $db->getQuery(true)
                        ->update('#__fields')
                        ->set($db->quoteName('type') . ' = ' . $db->quote('subform'))
                        ->set($db->quoteName('fieldparams') . ' = ' . $db->quote(json_encode($newFieldparams)))
                        ->where($db->quoteName('id') . ' = ' . $db->quote($row->id))
                )->execute();

                // Migrate field values for this field
                foreach ($rowFieldValues as $rowFieldValue) {
                    // Do not do the version if no data is entered for the custom field this item
                    if (!$rowFieldValue->value) {
                        continue;
                    }

                    /**
                     * Here we will have to update the stored value of the field to new format
                     * The key for each row changes from repeatable to row, for example repeatable0 to row0, and so on
                     * The key for each sub-field change from name of field to field + ID of the new sub-field
                     * Example data format stored in J3: {"repeatable0":{"id":"1","username":"admin"}}
                     * Example data format stored in J4: {"row0":{"field1":"1","field2":"admin"}}
                     */
                    $newFieldValue = [];

                    // Convert to array to change key
                    $fieldValue = json_decode($rowFieldValue->value, true);

                    // If data could not be decoded for some reason, ignore
                    if (!$fieldValue) {
                        continue;
                    }

                    $rowIndex = 0;

                    foreach ($fieldValue as $rowKey => $rowValue) {
                        $rowKey                 = 'row' . ($rowIndex++);
                        $newFieldValue[$rowKey] = [];

                        foreach ($rowValue as $subFieldName => $subFieldValue) {
                            // This is a media field, so we need to convert data to new format required in Joomla! 4
                            if (in_array($subFieldName, $mediaFields)) {
                                $subFieldValue = ['imagefile' => $subFieldValue, 'alt_text' => ''];
                            }

                            if (isset($mapping[$subFieldName])) {
                                $newFieldValue[$rowKey][$mapping[$subFieldName]] = $subFieldValue;
                            } else {
                                // Not found, use the old key to avoid data lost
                                $newFieldValue[$subFieldName] = $subFieldValue;
                            }
                        }
                    }

                    $query->clear()
                        ->update($db->quoteName('#__fields_values'))
                        ->set($db->quoteName('value') . ' = ' . $db->quote(json_encode($newFieldValue)))
                        ->where($db->quoteName('field_id') . ' = ' . $rowFieldValue->field_id)
                        ->where($db->quoteName('item_id') . ' = ' . $db->quote($rowFieldValue->item_id));
                    $db->setQuery($query)
                        ->execute();
                }

                $db->transactionCommit();
            } catch (\Exception $e) {
                $db->transactionRollback();
                throw $e;
            }
        }

        try {
            $db->transactionStart();

            // Now, unprotect the plugin so we can uninstall it
            $db->setQuery(
                $db->getQuery(true)
                    ->update('#__extensions')
                    ->set('protected = 0')
                    ->where($db->quoteName('extension_id') . ' = ' . $extensionId)
            )->execute();

            // And now uninstall the plugin
            $installer = new Installer();
            $installer->setDatabase($db);
            $installer->uninstall('plugin', $extensionId);

            $db->transactionCommit();
        } catch (\Exception $e) {
            $db->transactionRollback();
            throw $e;
        }
    }

    /**
     * Uninstall the 3.10 EOS plugin
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function uninstallEosPlugin()
    {
        $db = Factory::getDbo();

        // Check if the plg_quickicon_eos310 plugin is present
        $extensionId = $db->setQuery(
            $db->getQuery(true)
                ->select('extension_id')
                ->from('#__extensions')
                ->where('name = ' . $db->quote('plg_quickicon_eos310'))
        )->loadResult();

        // Skip uninstalling if it doesn't exist
        if (!$extensionId) {
            return;
        }

        try {
            $db->transactionStart();

            // Unprotect the plugin so we can uninstall it
            $db->setQuery(
                $db->getQuery(true)
                    ->update('#__extensions')
                    ->set('protected = 0')
                    ->where($db->quoteName('extension_id') . ' = ' . $extensionId)
            )->execute();

            // Uninstall the plugin
            $installer = new Installer();
            $installer->setDatabase($db);
            $installer->uninstall('plugin', $extensionId);

            $db->transactionCommit();
        } catch (\Exception $e) {
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

        // If we have the search package around, it may not have a manifest cache entry after upgrades from 3.x, so add it to the list
        if (is_file(JPATH_ROOT . '/administrator/manifests/packages/pkg_search.xml')) {
            $extensions[] = ['package', 'pkg_search', '', 0];
        }

        // Attempt to refresh manifest caches
        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__extensions');

        foreach ($extensions as $extension) {
            $query->where(
                'type=' . $db->quote($extension[0])
                . ' AND element=' . $db->quote($extension[1])
                . ' AND folder=' . $db->quote($extension[2])
                . ' AND client_id=' . $extension[3],
                'OR'
            );
        }

        $db->setQuery($query);

        try {
            $extensions = $db->loadObjectList();
        } catch (Exception $e) {
            echo Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br>';

            return;
        }

        $installer = new Installer();
        $installer->setDatabase($db);

        foreach ($extensions as $extension) {
            if (!$installer->refreshManifestCache($extension->extension_id)) {
                echo Text::sprintf('FILES_JOOMLA_ERROR_MANIFEST', $extension->type, $extension->element, $extension->name, $extension->client_id) . '<br>';
            }
        }
    }

    /**
     * Delete files that should not exist
     *
     * @param bool  $dryRun          If set to true, will not actually delete files, but just report their status for use in CLI
     * @param bool  $suppressOutput   Set to true to suppress echoing any errors, and just return the $status array
     *
     * @return  array
     */
    public function deleteUnexistingFiles($dryRun = false, $suppressOutput = false)
    {
        $status = [
            'files_exist'     => [],
            'folders_exist'   => [],
            'files_deleted'   => [],
            'folders_deleted' => [],
            'files_errors'    => [],
            'folders_errors'  => [],
            'folders_checked' => [],
            'files_checked'   => [],
        ];

        $files = [
            // From 4.4 to 5.0
            '/administrator/components/com_admin/sql/others/mysql/utf8mb4-conversion.sql',
            '/administrator/components/com_admin/sql/others/mysql/utf8mb4-conversion_optional.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2018-03-05.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2018-05-15.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2018-07-19.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2018-07-29.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2018-08-29.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2019-03-09.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2019-03-30.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2019-04-15.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2019-04-22.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2019-05-20.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2019-06-29.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2019-07-13.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2019-09-13.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2019-09-22.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2019-10-06.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2019-10-17.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2020-02-02.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2020-03-10.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2020-03-25.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2020-05-29.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2020-09-27.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2020-12-20.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2021-04-22.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2021-04-27.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2021-05-30.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2021-06-04.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2021-08-13.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2021-08-17.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.3-2021-09-04.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.3-2021-09-05.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.6-2021-12-23.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.1.0-2021-11-20.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.1.0-2021-11-28.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.1.0-2021-12-29.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.1.0-2022-01-08.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.1.0-2022-01-19.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.1.0-2022-01-24.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.1.1-2022-02-20.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.1.3-2022-04-07.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.1.3-2022-04-08.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.2.0-2022-05-15.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.2.0-2022-06-15.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.2.0-2022-06-19.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.2.0-2022-06-22.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.2.0-2022-07-07.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.2.1-2022-08-23.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.2.3-2022-09-07.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.2.7-2022-12-29.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.2.9-2023-03-07.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.3.0-2022-09-23.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.3.0-2022-11-06.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.3.0-2023-01-30.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.3.0-2023-02-15.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.3.0-2023-02-25.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.3.0-2023-03-07.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.3.0-2023-03-09.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.3.0-2023-03-10.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2018-03-05.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2018-05-15.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2018-07-19.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2018-07-29.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2018-08-29.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2019-03-09.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2019-03-30.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2019-04-15.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2019-04-22.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2019-05-20.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2019-06-29.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2019-07-13.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2019-09-13.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2019-09-22.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2019-10-06.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2019-10-17.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2020-02-02.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2020-03-10.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2020-03-25.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2020-05-29.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2020-08-01.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2020-09-27.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2020-12-20.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2021-04-22.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2021-04-27.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2021-05-30.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2021-06-04.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2021-08-13.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2021-08-17.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.3-2021-09-04.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.3-2021-09-05.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.6-2021-12-23.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.1.0-2021-11-20.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.1.0-2021-11-28.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.1.0-2021-12-29.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.1.0-2022-01-08.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.1.0-2022-01-19.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.1.0-2022-01-24.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.1.1-2022-02-20.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.1.3-2022-04-07.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.1.3-2022-04-08.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.2.0-2022-05-15.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.2.0-2022-06-19.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.2.0-2022-06-22.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.2.0-2022-07-07.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.2.1-2022-08-23.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.2.3-2022-09-07.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.2.7-2022-12-29.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.2.9-2023-03-07.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.3.0-2022-09-23.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.3.0-2022-11-06.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.3.0-2023-01-30.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.3.0-2023-02-15.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.3.0-2023-02-25.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.3.0-2023-03-07.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.3.0-2023-03-09.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.3.0-2023-03-10.sql',
            '/libraries/src/Schema/ChangeItem/SqlsrvChangeItem.php',
            '/libraries/vendor/beberlei/assert/LICENSE',
            '/libraries/vendor/beberlei/assert/lib/Assert/Assert.php',
            '/libraries/vendor/beberlei/assert/lib/Assert/Assertion.php',
            '/libraries/vendor/beberlei/assert/lib/Assert/AssertionChain.php',
            '/libraries/vendor/beberlei/assert/lib/Assert/AssertionFailedException.php',
            '/libraries/vendor/beberlei/assert/lib/Assert/InvalidArgumentException.php',
            '/libraries/vendor/beberlei/assert/lib/Assert/LazyAssertion.php',
            '/libraries/vendor/beberlei/assert/lib/Assert/LazyAssertionException.php',
            '/libraries/vendor/beberlei/assert/lib/Assert/functions.php',
            '/libraries/vendor/google/recaptcha/ARCHITECTURE.md',
            '/libraries/vendor/jfcherng/php-color-output/src/helpers.php',
            '/libraries/vendor/joomla/ldap/LICENSE',
            '/libraries/vendor/joomla/ldap/src/LdapClient.php',
            '/libraries/vendor/laminas/laminas-zendframework-bridge/COPYRIGHT.md',
            '/libraries/vendor/laminas/laminas-zendframework-bridge/LICENSE.md',
            '/libraries/vendor/laminas/laminas-zendframework-bridge/config/replacements.php',
            '/libraries/vendor/laminas/laminas-zendframework-bridge/src/Autoloader.php',
            '/libraries/vendor/laminas/laminas-zendframework-bridge/src/ConfigPostProcessor.php',
            '/libraries/vendor/laminas/laminas-zendframework-bridge/src/Module.php',
            '/libraries/vendor/laminas/laminas-zendframework-bridge/src/Replacements.php',
            '/libraries/vendor/laminas/laminas-zendframework-bridge/src/RewriteRules.php',
            '/libraries/vendor/laminas/laminas-zendframework-bridge/src/autoload.php',
            '/libraries/vendor/lcobucci/jwt/compat/class-aliases.php',
            '/libraries/vendor/lcobucci/jwt/compat/json-exception-polyfill.php',
            '/libraries/vendor/lcobucci/jwt/compat/lcobucci-clock-polyfill.php',
            '/libraries/vendor/lcobucci/jwt/src/Claim.php',
            '/libraries/vendor/lcobucci/jwt/src/Claim/Basic.php',
            '/libraries/vendor/lcobucci/jwt/src/Claim/EqualsTo.php',
            '/libraries/vendor/lcobucci/jwt/src/Claim/Factory.php',
            '/libraries/vendor/lcobucci/jwt/src/Claim/GreaterOrEqualsTo.php',
            '/libraries/vendor/lcobucci/jwt/src/Claim/LesserOrEqualsTo.php',
            '/libraries/vendor/lcobucci/jwt/src/Claim/Validatable.php',
            '/libraries/vendor/lcobucci/jwt/src/Parsing/Decoder.php',
            '/libraries/vendor/lcobucci/jwt/src/Parsing/Encoder.php',
            '/libraries/vendor/lcobucci/jwt/src/Signature.php',
            '/libraries/vendor/lcobucci/jwt/src/Signer/BaseSigner.php',
            '/libraries/vendor/lcobucci/jwt/src/Signer/Keychain.php',
            '/libraries/vendor/lcobucci/jwt/src/ValidationData.php',
            '/libraries/vendor/nyholm/psr7/LICENSE',
            '/libraries/vendor/nyholm/psr7/src/Factory/HttplugFactory.php',
            '/libraries/vendor/nyholm/psr7/src/Factory/Psr17Factory.php',
            '/libraries/vendor/nyholm/psr7/src/MessageTrait.php',
            '/libraries/vendor/nyholm/psr7/src/Request.php',
            '/libraries/vendor/nyholm/psr7/src/RequestTrait.php',
            '/libraries/vendor/nyholm/psr7/src/Response.php',
            '/libraries/vendor/nyholm/psr7/src/ServerRequest.php',
            '/libraries/vendor/nyholm/psr7/src/Stream.php',
            '/libraries/vendor/nyholm/psr7/src/UploadedFile.php',
            '/libraries/vendor/nyholm/psr7/src/Uri.php',
            '/libraries/vendor/php-http/message-factory/LICENSE',
            '/libraries/vendor/php-http/message-factory/puli.json',
            '/libraries/vendor/php-http/message-factory/src/MessageFactory.php',
            '/libraries/vendor/php-http/message-factory/src/RequestFactory.php',
            '/libraries/vendor/php-http/message-factory/src/ResponseFactory.php',
            '/libraries/vendor/php-http/message-factory/src/StreamFactory.php',
            '/libraries/vendor/php-http/message-factory/src/UriFactory.php',
            '/libraries/vendor/psr/log/Psr/Log/AbstractLogger.php',
            '/libraries/vendor/psr/log/Psr/Log/InvalidArgumentException.php',
            '/libraries/vendor/psr/log/Psr/Log/LogLevel.php',
            '/libraries/vendor/psr/log/Psr/Log/LoggerAwareInterface.php',
            '/libraries/vendor/psr/log/Psr/Log/LoggerAwareTrait.php',
            '/libraries/vendor/psr/log/Psr/Log/LoggerInterface.php',
            '/libraries/vendor/psr/log/Psr/Log/LoggerTrait.php',
            '/libraries/vendor/psr/log/Psr/Log/NullLogger.php',
            '/libraries/vendor/ramsey/uuid/LICENSE',
            '/libraries/vendor/ramsey/uuid/src/BinaryUtils.php',
            '/libraries/vendor/ramsey/uuid/src/Builder/DefaultUuidBuilder.php',
            '/libraries/vendor/ramsey/uuid/src/Builder/DegradedUuidBuilder.php',
            '/libraries/vendor/ramsey/uuid/src/Builder/UuidBuilderInterface.php',
            '/libraries/vendor/ramsey/uuid/src/Codec/CodecInterface.php',
            '/libraries/vendor/ramsey/uuid/src/Codec/GuidStringCodec.php',
            '/libraries/vendor/ramsey/uuid/src/Codec/OrderedTimeCodec.php',
            '/libraries/vendor/ramsey/uuid/src/Codec/StringCodec.php',
            '/libraries/vendor/ramsey/uuid/src/Codec/TimestampFirstCombCodec.php',
            '/libraries/vendor/ramsey/uuid/src/Codec/TimestampLastCombCodec.php',
            '/libraries/vendor/ramsey/uuid/src/Converter/Number/BigNumberConverter.php',
            '/libraries/vendor/ramsey/uuid/src/Converter/Number/DegradedNumberConverter.php',
            '/libraries/vendor/ramsey/uuid/src/Converter/NumberConverterInterface.php',
            '/libraries/vendor/ramsey/uuid/src/Converter/Time/BigNumberTimeConverter.php',
            '/libraries/vendor/ramsey/uuid/src/Converter/Time/DegradedTimeConverter.php',
            '/libraries/vendor/ramsey/uuid/src/Converter/Time/PhpTimeConverter.php',
            '/libraries/vendor/ramsey/uuid/src/Converter/TimeConverterInterface.php',
            '/libraries/vendor/ramsey/uuid/src/DegradedUuid.php',
            '/libraries/vendor/ramsey/uuid/src/Exception/InvalidUuidStringException.php',
            '/libraries/vendor/ramsey/uuid/src/Exception/UnsatisfiedDependencyException.php',
            '/libraries/vendor/ramsey/uuid/src/Exception/UnsupportedOperationException.php',
            '/libraries/vendor/ramsey/uuid/src/FeatureSet.php',
            '/libraries/vendor/ramsey/uuid/src/Generator/CombGenerator.php',
            '/libraries/vendor/ramsey/uuid/src/Generator/DefaultTimeGenerator.php',
            '/libraries/vendor/ramsey/uuid/src/Generator/MtRandGenerator.php',
            '/libraries/vendor/ramsey/uuid/src/Generator/OpenSslGenerator.php',
            '/libraries/vendor/ramsey/uuid/src/Generator/PeclUuidRandomGenerator.php',
            '/libraries/vendor/ramsey/uuid/src/Generator/PeclUuidTimeGenerator.php',
            '/libraries/vendor/ramsey/uuid/src/Generator/RandomBytesGenerator.php',
            '/libraries/vendor/ramsey/uuid/src/Generator/RandomGeneratorFactory.php',
            '/libraries/vendor/ramsey/uuid/src/Generator/RandomGeneratorInterface.php',
            '/libraries/vendor/ramsey/uuid/src/Generator/RandomLibAdapter.php',
            '/libraries/vendor/ramsey/uuid/src/Generator/SodiumRandomGenerator.php',
            '/libraries/vendor/ramsey/uuid/src/Generator/TimeGeneratorFactory.php',
            '/libraries/vendor/ramsey/uuid/src/Generator/TimeGeneratorInterface.php',
            '/libraries/vendor/ramsey/uuid/src/Provider/Node/FallbackNodeProvider.php',
            '/libraries/vendor/ramsey/uuid/src/Provider/Node/RandomNodeProvider.php',
            '/libraries/vendor/ramsey/uuid/src/Provider/Node/SystemNodeProvider.php',
            '/libraries/vendor/ramsey/uuid/src/Provider/NodeProviderInterface.php',
            '/libraries/vendor/ramsey/uuid/src/Provider/Time/FixedTimeProvider.php',
            '/libraries/vendor/ramsey/uuid/src/Provider/Time/SystemTimeProvider.php',
            '/libraries/vendor/ramsey/uuid/src/Provider/TimeProviderInterface.php',
            '/libraries/vendor/ramsey/uuid/src/Uuid.php',
            '/libraries/vendor/ramsey/uuid/src/UuidFactory.php',
            '/libraries/vendor/ramsey/uuid/src/UuidFactoryInterface.php',
            '/libraries/vendor/ramsey/uuid/src/UuidInterface.php',
            '/libraries/vendor/ramsey/uuid/src/functions.php',
            '/libraries/vendor/spomky-labs/base64url/LICENSE',
            '/libraries/vendor/spomky-labs/base64url/src/Base64Url.php',
            '/libraries/vendor/spomky-labs/cbor-php/src/ByteStringWithChunkObject.php',
            '/libraries/vendor/spomky-labs/cbor-php/src/InfiniteListObject.php',
            '/libraries/vendor/spomky-labs/cbor-php/src/InfiniteMapObject.php',
            '/libraries/vendor/spomky-labs/cbor-php/src/SignedIntegerObject.php',
            '/libraries/vendor/spomky-labs/cbor-php/src/Tag/EpochTag.php',
            '/libraries/vendor/spomky-labs/cbor-php/src/Tag/PositiveBigIntegerTag.php',
            '/libraries/vendor/spomky-labs/cbor-php/src/Tag/TagObjectManager.php',
            '/libraries/vendor/spomky-labs/cbor-php/src/TagObject.php',
            '/libraries/vendor/spomky-labs/cbor-php/src/TextStringWithChunkObject.php',
            '/libraries/vendor/symfony/polyfill-php73/bootstrap.php',
            '/libraries/vendor/symfony/polyfill-php73/LICENSE',
            '/libraries/vendor/symfony/polyfill-php73/Php73.php',
            '/libraries/vendor/symfony/polyfill-php73/Resources/stubs/JsonException.php',
            '/libraries/vendor/symfony/polyfill-php80/bootstrap.php',
            '/libraries/vendor/symfony/polyfill-php80/LICENSE',
            '/libraries/vendor/symfony/polyfill-php80/Php80.php',
            '/libraries/vendor/symfony/polyfill-php80/PhpToken.php',
            '/libraries/vendor/symfony/polyfill-php80/Resources/stubs/Attribute.php',
            '/libraries/vendor/symfony/polyfill-php80/Resources/stubs/PhpToken.php',
            '/libraries/vendor/symfony/polyfill-php80/Resources/stubs/Stringable.php',
            '/libraries/vendor/symfony/polyfill-php80/Resources/stubs/UnhandledMatchError.php',
            '/libraries/vendor/symfony/polyfill-php80/Resources/stubs/ValueError.php',
            '/libraries/vendor/symfony/polyfill-php81/bootstrap.php',
            '/libraries/vendor/symfony/polyfill-php81/LICENSE',
            '/libraries/vendor/symfony/polyfill-php81/Php81.php',
            '/libraries/vendor/symfony/polyfill-php81/Resources/stubs/ReturnTypeWillChange.php',
            '/libraries/vendor/web-auth/cose-lib/src/Verifier.php',
            '/libraries/vendor/web-auth/metadata-service/src/AuthenticatorStatus.php',
            '/libraries/vendor/web-auth/metadata-service/src/BiometricAccuracyDescriptor.php',
            '/libraries/vendor/web-auth/metadata-service/src/BiometricStatusReport.php',
            '/libraries/vendor/web-auth/metadata-service/src/CodeAccuracyDescriptor.php',
            '/libraries/vendor/web-auth/metadata-service/src/DisplayPNGCharacteristicsDescriptor.php',
            '/libraries/vendor/web-auth/metadata-service/src/DistantSingleMetadata.php',
            '/libraries/vendor/web-auth/metadata-service/src/DistantSingleMetadataFactory.php',
            '/libraries/vendor/web-auth/metadata-service/src/EcdaaTrustAnchor.php',
            '/libraries/vendor/web-auth/metadata-service/src/ExtensionDescriptor.php',
            '/libraries/vendor/web-auth/metadata-service/src/MetadataService.php',
            '/libraries/vendor/web-auth/metadata-service/src/MetadataServiceFactory.php',
            '/libraries/vendor/web-auth/metadata-service/src/MetadataStatement.php',
            '/libraries/vendor/web-auth/metadata-service/src/MetadataStatementFetcher.php',
            '/libraries/vendor/web-auth/metadata-service/src/MetadataTOCPayload.php',
            '/libraries/vendor/web-auth/metadata-service/src/MetadataTOCPayloadEntry.php',
            '/libraries/vendor/web-auth/metadata-service/src/PatternAccuracyDescriptor.php',
            '/libraries/vendor/web-auth/metadata-service/src/RgbPaletteEntry.php',
            '/libraries/vendor/web-auth/metadata-service/src/RogueListEntry.php',
            '/libraries/vendor/web-auth/metadata-service/src/SimpleMetadataStatementRepository.php',
            '/libraries/vendor/web-auth/metadata-service/src/SingleMetadata.php',
            '/libraries/vendor/web-auth/metadata-service/src/StatusReport.php',
            '/libraries/vendor/web-auth/metadata-service/src/VerificationMethodANDCombinations.php',
            '/libraries/vendor/web-auth/metadata-service/src/VerificationMethodDescriptor.php',
            '/libraries/vendor/web-auth/metadata-service/src/Version.php',
            '/libraries/vendor/web-auth/webauthn-lib/src/Server.php',
            '/libraries/vendor/web-token/jwt-signature-algorithm-rsa/RSA.php',
            '/media/vendor/tinymce/plugins/bbcode/index.js',
            '/media/vendor/tinymce/plugins/bbcode/plugin.js',
            '/media/vendor/tinymce/plugins/bbcode/plugin.min.js',
            '/media/vendor/tinymce/plugins/bbcode/plugin.min.js.gz',
            '/media/vendor/tinymce/plugins/colorpicker/index.js',
            '/media/vendor/tinymce/plugins/colorpicker/plugin.js',
            '/media/vendor/tinymce/plugins/colorpicker/plugin.min.js',
            '/media/vendor/tinymce/plugins/colorpicker/plugin.min.js.gz',
            '/media/vendor/tinymce/plugins/contextmenu/index.js',
            '/media/vendor/tinymce/plugins/contextmenu/plugin.js',
            '/media/vendor/tinymce/plugins/contextmenu/plugin.min.js',
            '/media/vendor/tinymce/plugins/contextmenu/plugin.min.js.gz',
            '/media/vendor/tinymce/plugins/fullpage/index.js',
            '/media/vendor/tinymce/plugins/fullpage/plugin.js',
            '/media/vendor/tinymce/plugins/fullpage/plugin.min.js',
            '/media/vendor/tinymce/plugins/fullpage/plugin.min.js.gz',
            '/media/vendor/tinymce/plugins/hr/index.js',
            '/media/vendor/tinymce/plugins/hr/plugin.js',
            '/media/vendor/tinymce/plugins/hr/plugin.min.js',
            '/media/vendor/tinymce/plugins/hr/plugin.min.js.gz',
            '/media/vendor/tinymce/plugins/imagetools/index.js',
            '/media/vendor/tinymce/plugins/imagetools/plugin.js',
            '/media/vendor/tinymce/plugins/imagetools/plugin.min.js',
            '/media/vendor/tinymce/plugins/imagetools/plugin.min.js.gz',
            '/media/vendor/tinymce/plugins/legacyoutput/index.js',
            '/media/vendor/tinymce/plugins/legacyoutput/plugin.js',
            '/media/vendor/tinymce/plugins/legacyoutput/plugin.min.js',
            '/media/vendor/tinymce/plugins/legacyoutput/plugin.min.js.gz',
            '/media/vendor/tinymce/plugins/noneditable/index.js',
            '/media/vendor/tinymce/plugins/noneditable/plugin.js',
            '/media/vendor/tinymce/plugins/noneditable/plugin.min.js',
            '/media/vendor/tinymce/plugins/noneditable/plugin.min.js.gz',
            '/media/vendor/tinymce/plugins/paste/index.js',
            '/media/vendor/tinymce/plugins/paste/plugin.js',
            '/media/vendor/tinymce/plugins/paste/plugin.min.js',
            '/media/vendor/tinymce/plugins/paste/plugin.min.js.gz',
            '/media/vendor/tinymce/plugins/print/index.js',
            '/media/vendor/tinymce/plugins/print/plugin.js',
            '/media/vendor/tinymce/plugins/print/plugin.min.js',
            '/media/vendor/tinymce/plugins/print/plugin.min.js.gz',
            '/media/vendor/tinymce/plugins/spellchecker/index.js',
            '/media/vendor/tinymce/plugins/spellchecker/plugin.js',
            '/media/vendor/tinymce/plugins/spellchecker/plugin.min.js',
            '/media/vendor/tinymce/plugins/spellchecker/plugin.min.js.gz',
            '/media/vendor/tinymce/plugins/tabfocus/index.js',
            '/media/vendor/tinymce/plugins/tabfocus/plugin.js',
            '/media/vendor/tinymce/plugins/tabfocus/plugin.min.js',
            '/media/vendor/tinymce/plugins/tabfocus/plugin.min.js.gz',
            '/media/vendor/tinymce/plugins/textcolor/index.js',
            '/media/vendor/tinymce/plugins/textcolor/plugin.js',
            '/media/vendor/tinymce/plugins/textcolor/plugin.min.js',
            '/media/vendor/tinymce/plugins/textcolor/plugin.min.js.gz',
            '/media/vendor/tinymce/plugins/textpattern/index.js',
            '/media/vendor/tinymce/plugins/textpattern/plugin.js',
            '/media/vendor/tinymce/plugins/textpattern/plugin.min.js',
            '/media/vendor/tinymce/plugins/textpattern/plugin.min.js.gz',
            '/media/vendor/tinymce/plugins/toc/index.js',
            '/media/vendor/tinymce/plugins/toc/plugin.js',
            '/media/vendor/tinymce/plugins/toc/plugin.min.js',
            '/media/vendor/tinymce/plugins/toc/plugin.min.js.gz',
            '/media/vendor/tinymce/skins/ui/oxide-dark/content.mobile.css',
            '/media/vendor/tinymce/skins/ui/oxide-dark/content.mobile.min.css',
            '/media/vendor/tinymce/skins/ui/oxide-dark/content.mobile.min.css.gz',
            '/media/vendor/tinymce/skins/ui/oxide-dark/fonts/tinymce-mobile.woff',
            '/media/vendor/tinymce/skins/ui/oxide-dark/skin.mobile.css',
            '/media/vendor/tinymce/skins/ui/oxide-dark/skin.mobile.min.css',
            '/media/vendor/tinymce/skins/ui/oxide-dark/skin.mobile.min.css.gz',
            '/media/vendor/tinymce/skins/ui/oxide/content.mobile.css',
            '/media/vendor/tinymce/skins/ui/oxide/content.mobile.min.css',
            '/media/vendor/tinymce/skins/ui/oxide/content.mobile.min.css.gz',
            '/media/vendor/tinymce/skins/ui/oxide/fonts/tinymce-mobile.woff',
            '/media/vendor/tinymce/skins/ui/oxide/skin.mobile.css',
            '/media/vendor/tinymce/skins/ui/oxide/skin.mobile.min.css',
            '/media/vendor/tinymce/skins/ui/oxide/skin.mobile.min.css.gz',
            '/media/vendor/tinymce/themes/mobile/index.js',
            '/media/vendor/tinymce/themes/mobile/theme.js',
            '/media/vendor/tinymce/themes/mobile/theme.min.js',
            '/media/vendor/tinymce/themes/mobile/theme.min.js.gz',
            '/plugins/multifactorauth/webauthn/src/Hotfix/AndroidKeyAttestationStatementSupport.php',
            '/plugins/multifactorauth/webauthn/src/Hotfix/FidoU2FAttestationStatementSupport.php',
            '/plugins/multifactorauth/webauthn/src/Hotfix/Server.php',
            '/plugins/system/webauthn/src/Hotfix/AndroidKeyAttestationStatementSupport.php',
            '/plugins/system/webauthn/src/Hotfix/FidoU2FAttestationStatementSupport.php',
            '/plugins/system/webauthn/src/Hotfix/Server.php',
        ];

        $folders = [
            // From 4.4 to 5.0
            '/plugins/system/webauthn/src/Hotfix',
            '/plugins/multifactorauth/webauthn/src/Hotfix',
            '/media/vendor/tinymce/themes/mobile',
            '/media/vendor/tinymce/skins/ui/oxide/fonts',
            '/media/vendor/tinymce/skins/ui/oxide-dark/fonts',
            '/media/vendor/tinymce/plugins/toc',
            '/media/vendor/tinymce/plugins/textpattern',
            '/media/vendor/tinymce/plugins/textcolor',
            '/media/vendor/tinymce/plugins/tabfocus',
            '/media/vendor/tinymce/plugins/spellchecker',
            '/media/vendor/tinymce/plugins/print',
            '/media/vendor/tinymce/plugins/paste',
            '/media/vendor/tinymce/plugins/noneditable',
            '/media/vendor/tinymce/plugins/legacyoutput',
            '/media/vendor/tinymce/plugins/imagetools',
            '/media/vendor/tinymce/plugins/hr',
            '/media/vendor/tinymce/plugins/fullpage',
            '/media/vendor/tinymce/plugins/contextmenu',
            '/media/vendor/tinymce/plugins/colorpicker',
            '/media/vendor/tinymce/plugins/bbcode',
            '/libraries/vendor/symfony/polyfill-php81/Resources/stubs',
            '/libraries/vendor/symfony/polyfill-php81/Resources',
            '/libraries/vendor/symfony/polyfill-php81',
            '/libraries/vendor/symfony/polyfill-php80/Resources/stubs',
            '/libraries/vendor/symfony/polyfill-php80/Resources',
            '/libraries/vendor/symfony/polyfill-php80',
            '/libraries/vendor/symfony/polyfill-php73/Resources/stubs',
            '/libraries/vendor/symfony/polyfill-php73/Resources',
            '/libraries/vendor/symfony/polyfill-php73',
            '/libraries/vendor/spomky-labs/base64url/src',
            '/libraries/vendor/spomky-labs/base64url',
            '/libraries/vendor/ramsey/uuid/src/Provider/Time',
            '/libraries/vendor/ramsey/uuid/src/Provider/Node',
            '/libraries/vendor/ramsey/uuid/src/Provider',
            '/libraries/vendor/ramsey/uuid/src/Generator',
            '/libraries/vendor/ramsey/uuid/src/Exception',
            '/libraries/vendor/ramsey/uuid/src/Converter/Time',
            '/libraries/vendor/ramsey/uuid/src/Converter/Number',
            '/libraries/vendor/ramsey/uuid/src/Converter',
            '/libraries/vendor/ramsey/uuid/src/Codec',
            '/libraries/vendor/ramsey/uuid/src/Builder',
            '/libraries/vendor/ramsey/uuid/src',
            '/libraries/vendor/ramsey/uuid',
            '/libraries/vendor/ramsey',
            '/libraries/vendor/psr/log/Psr/Log',
            '/libraries/vendor/psr/log/Psr',
            '/libraries/vendor/php-http/message-factory/src',
            '/libraries/vendor/php-http/message-factory',
            '/libraries/vendor/php-http',
            '/libraries/vendor/nyholm/psr7/src/Factory',
            '/libraries/vendor/nyholm/psr7/src',
            '/libraries/vendor/nyholm/psr7',
            '/libraries/vendor/nyholm',
            '/libraries/vendor/lcobucci/jwt/src/Parsing',
            '/libraries/vendor/lcobucci/jwt/src/Claim',
            '/libraries/vendor/lcobucci/jwt/compat',
            '/libraries/vendor/laminas/laminas-zendframework-bridge/src',
            '/libraries/vendor/laminas/laminas-zendframework-bridge/config',
            '/libraries/vendor/laminas/laminas-zendframework-bridge',
            '/libraries/vendor/joomla/ldap/src',
            '/libraries/vendor/joomla/ldap',
            '/libraries/vendor/beberlei/assert/lib/Assert',
            '/libraries/vendor/beberlei/assert/lib',
            '/libraries/vendor/beberlei/assert',
            '/libraries/vendor/beberlei',
            '/administrator/components/com_admin/sql/others/mysql',
            '/administrator/components/com_admin/sql/others',
        ];

        $status['files_checked']   = $files;
        $status['folders_checked'] = $folders;

        foreach ($files as $file) {
            if ($fileExists = is_file(JPATH_ROOT . $file)) {
                $status['files_exist'][] = $file;

                if ($dryRun === false) {
                    if (File::delete(JPATH_ROOT . $file)) {
                        $status['files_deleted'][] = $file;
                    } else {
                        $status['files_errors'][] = Text::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $file);
                    }
                }
            }
        }

        $this->moveRemainingTemplateFiles();

        foreach ($folders as $folder) {
            if ($folderExists = Folder::exists(JPATH_ROOT . $folder)) {
                $status['folders_exist'][] = $folder;

                if ($dryRun === false) {
                    if (Folder::delete(JPATH_ROOT . $folder)) {
                        $status['folders_deleted'][] = $folder;
                    } else {
                        $status['folders_errors'][] = Text::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $folder);
                    }
                }
            }
        }

        $this->fixFilenameCasing();

        /*
         * Needed for updates from 3.10
         * If com_search doesn't exist then assume we can delete the search package manifest (included in the update packages)
         * We deliberately check for the presence of the files in case people have previously uninstalled their search extension
         * but an update has put the files back. In that case it exists even if they don't believe in it!
         */
        if (
            !is_file(JPATH_ROOT . '/administrator/components/com_search/search.php')
            && is_file(JPATH_ROOT . '/administrator/manifests/packages/pkg_search.xml')
        ) {
            File::delete(JPATH_ROOT . '/administrator/manifests/packages/pkg_search.xml');
        }

        if ($suppressOutput === false && count($status['folders_errors'])) {
            echo implode('<br>', $status['folders_errors']);
        }

        if ($suppressOutput === false && count($status['files_errors'])) {
            echo implode('<br>', $status['files_errors']);
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
        $newComponents = [
            // Components to be added here
        ];

        foreach ($newComponents as $component) {
            /** @var \Joomla\CMS\Table\Asset $asset */
            $asset = Table::getInstance('Asset');

            if ($asset->loadByName($component)) {
                continue;
            }

            $asset->name      = $component;
            $asset->parent_id = 1;
            $asset->rules     = '{}';
            $asset->title     = $component;
            $asset->setLocation(1, 'last-child');

            if (!$asset->store()) {
                // Install failed, roll back changes
                $installer->abort(Text::sprintf('JLIB_INSTALLER_ABORT_COMP_INSTALL_ROLLBACK', $asset->getError(true)));

                return false;
            }
        }

        return true;
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
        /** @var \Joomla\Component\Cache\Administrator\Model\CacheModel $model */
        $model = Factory::getApplication()->bootComponent('com_cache')->getMVCFactory()
            ->createModel('Cache', 'Administrator', ['ignore_request' => true]);

        // Clean frontend cache
        $model->clean();

        // Clean admin cache
        $model->setState('client_id', 1);
        $model->clean();
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
        if ($action !== 'update') {
            return true;
        }

        if (empty($this->fromVersion) || version_compare($this->fromVersion, '4.0.0', 'ge')) {
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

        foreach ($menuItems as $menuItem) {
            // Check an existing record
            $keys = [
                'menutype'  => $menuItem['menutype'],
                'type'      => $menuItem['type'],
                'title'     => $menuItem['title'],
                'parent_id' => $menuItem['parent_id'],
                'client_id' => $menuItem['client_id'],
            ];

            if ($tableItem->load($keys)) {
                continue;
            }

            $newTableItem = new \Joomla\Component\Menus\Administrator\Table\MenuTable($db);

            // Bind the data.
            if (!$newTableItem->bind($menuItem)) {
                return false;
            }

            $newTableItem->setLocation($menuItem['parent_id'], 'last-child');

            // Check the data.
            if (!$newTableItem->check()) {
                return false;
            }

            // Store the data.
            if (!$newTableItem->store()) {
                return false;
            }

            // Rebuild the tree path.
            if (!$newTableItem->rebuildPath($newTableItem->id)) {
                return false;
            }
        }

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

        if (!$contactMenuitem) {
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
            ],
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

        if (!$finderMenuitem) {
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
            ],
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
        $db    = Factory::getDbo();
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

        foreach ($contentTypes as $contentType) {
            list($component, $tableType) = explode('.', $contentType->type_alias);

            // Special case for core table classes.
            if ($contentType->type_alias === 'com_users.users' || $tableType === 'category') {
                $tablePrefix = 'Joomla\\CMS\Table\\';
                $tableType   = ucfirst($tableType);
            } else {
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
            if (!empty($table->common->prefix)) {
                $table->common->prefix  = 'Joomla\\CMS\\Table\\';
            }

            $table = json_encode($table);

            // Execute the query.
            $db->execute();
        }
    }

    /**
     * Renames or removes incorrectly cased files.
     *
     * @return  void
     *
     * @since   3.9.25
     */
    protected function fixFilenameCasing()
    {
        $files = [
            // From 4.4 to 5.0
            '/libraries/vendor/web-auth/cose-lib/src/Algorithm/Signature/EdDSA/ED256.php' => '/libraries/vendor/web-auth/cose-lib/src/Algorithm/Signature/EdDSA/Ed256.php',
            '/libraries/vendor/web-auth/cose-lib/src/Algorithm/Signature/EdDSA/ED512.php' => '/libraries/vendor/web-auth/cose-lib/src/Algorithm/Signature/EdDSA/Ed512.php',
        ];

        foreach ($files as $old => $expected) {
            $oldRealpath = realpath(JPATH_ROOT . $old);

            // On Unix without incorrectly cased file.
            if ($oldRealpath === false) {
                continue;
            }

            $oldBasename      = basename($oldRealpath);
            $newRealpath      = realpath(JPATH_ROOT . $expected);
            $newBasename      = basename($newRealpath);
            $expectedBasename = basename($expected);

            // On Windows or Unix with only the incorrectly cased file.
            if ($newBasename !== $expectedBasename) {
                // Rename the file.
                File::move(JPATH_ROOT . $old, JPATH_ROOT . $old . '.tmp');
                File::move(JPATH_ROOT . $old . '.tmp', JPATH_ROOT . $expected);

                continue;
            }

            // There might still be an incorrectly cased file on other OS than Windows.
            if ($oldBasename === basename($old)) {
                // Check if case-insensitive file system, eg on OSX.
                if (fileinode($oldRealpath) === fileinode($newRealpath)) {
                    // Check deeper because even realpath or glob might not return the actual case.
                    if (!in_array($expectedBasename, scandir(dirname($newRealpath)))) {
                        // Rename the file.
                        File::move(JPATH_ROOT . $old, JPATH_ROOT . $old . '.tmp');
                        File::move(JPATH_ROOT . $old . '.tmp', JPATH_ROOT . $expected);
                    }
                } else {
                    // On Unix with both files: Delete the incorrectly cased file.
                    File::delete(JPATH_ROOT . $old);
                }
            }
        }
    }

    /**
     * Move core template (s)css or js or image files which are left after deleting
     * obsolete core files to the right place in media folder.
     *
     * @return  void
     *
     * @since   4.1.0
     */
    protected function moveRemainingTemplateFiles()
    {
        $folders = [
            '/administrator/templates/atum/css'    => '/media/templates/administrator/atum/css',
            '/administrator/templates/atum/images' => '/media/templates/administrator/atum/images',
            '/administrator/templates/atum/js'     => '/media/templates/administrator/atum/js',
            '/administrator/templates/atum/scss'   => '/media/templates/administrator/atum/scss',
            '/templates/cassiopeia/css'            => '/media/templates/site/cassiopeia/css',
            '/templates/cassiopeia/images'         => '/media/templates/site/cassiopeia/images',
            '/templates/cassiopeia/js'             => '/media/templates/site/cassiopeia/js',
            '/templates/cassiopeia/scss'           => '/media/templates/site/cassiopeia/scss',
        ];

        foreach ($folders as $oldFolder => $newFolder) {
            if (Folder::exists(JPATH_ROOT . $oldFolder)) {
                $oldPath   = realpath(JPATH_ROOT . $oldFolder);
                $newPath   = realpath(JPATH_ROOT . $newFolder);
                $directory = new \RecursiveDirectoryIterator($oldPath);
                $directory->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);
                $iterator  = new \RecursiveIteratorIterator($directory);

                // Handle all files in this folder and all sub-folders
                foreach ($iterator as $oldFile) {
                    if ($oldFile->isDir()) {
                        continue;
                    }

                    $newFile = $newPath . substr($oldFile, strlen($oldPath));

                    // Create target folder and parent folders if they don't exist yet
                    if (is_dir(dirname($newFile)) || @mkdir(dirname($newFile), 0755, true)) {
                        File::move($oldFile, $newFile);
                    }
                }
            }
        }
    }

    /**
     * Ensure the core templates are correctly moved to the new mode.
     *
     * @return  void
     *
     * @since   4.1.0
     */
    protected function fixTemplateMode(): void
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        array_map(
            function ($template) use ($db) {
                $clientId = $template === 'atum' ? 1 : 0;
                $query = $db->getQuery(true)
                    ->update($db->quoteName('#__template_styles'))
                    ->set($db->quoteName('inheritable') . ' = 1')
                    ->where($db->quoteName('template') . ' = ' . $db->quote($template))
                    ->where($db->quoteName('client_id') . ' = ' . $clientId);

                try {
                    $db->setQuery($query)->execute();
                } catch (Exception $e) {
                    echo Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br>';

                    return;
                }
            },
            ['atum', 'cassiopeia']
        );
    }
}
