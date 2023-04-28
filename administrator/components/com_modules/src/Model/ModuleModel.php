<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Modules\Administrator\Model;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\Component\Modules\Administrator\Helper\ModulesHelper;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Module model.
 *
 * @since  1.6
 */
class ModuleModel extends AdminModel
{
    /**
     * The type alias for this content type.
     *
     * @var      string
     * @since    3.4
     */
    public $typeAlias = 'com_modules.module';

    /**
     * @var    string  The prefix to use with controller messages.
     * @since  1.6
     */
    protected $text_prefix = 'COM_MODULES';

    /**
     * @var    string  The help screen key for the module.
     * @since  1.6
     */
    protected $helpKey = '';

    /**
     * @var    string  The help screen base URL for the module.
     * @since  1.6
     */
    protected $helpURL;

    /**
     * Batch copy/move command. If set to false,
     * the batch copy/move command is not supported
     *
     * @var string
     */
    protected $batch_copymove = 'position_id';

    /**
     * Allowed batch commands
     *
     * @var array
     */
    protected $batch_commands = [
        'assetgroup_id' => 'batchAccess',
        'language_id' => 'batchLanguage',
    ];

    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     */
    public function __construct($config = [])
    {
        $config = array_merge(
            [
                'event_after_delete'  => 'onExtensionAfterDelete',
                'event_after_save'    => 'onExtensionAfterSave',
                'event_before_delete' => 'onExtensionBeforeDelete',
                'event_before_save'   => 'onExtensionBeforeSave',
                'events_map'          => [
                    'save'   => 'extension',
                    'delete' => 'extension'
                ]
            ],
            $config
        );

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function populateState()
    {
        $app = Factory::getApplication();

        // Load the User state.
        $pk = $app->input->getInt('id');

        if (!$pk) {
            if ($extensionId = (int) $app->getUserState('com_modules.add.module.extension_id')) {
                $this->setState('extension.id', $extensionId);
            }
        }

        $this->setState('module.id', $pk);

        // Load the parameters.
        $params = ComponentHelper::getParams('com_modules');
        $this->setState('params', $params);
    }

    /**
     * Batch copy modules to a new position or current.
     *
     * @param   integer  $value     The new value matching a module position.
     * @param   array    $pks       An array of row IDs.
     * @param   array    $contexts  An array of item contexts.
     *
     * @return  boolean  True if successful, false otherwise and internal error is set.
     *
     * @since   2.5
     */
    protected function batchCopy($value, $pks, $contexts)
    {
        // Set the variables
        $user = Factory::getUser();
        $table = $this->getTable();
        $newIds = [];

        foreach ($pks as $pk) {
            if ($user->authorise('core.create', 'com_modules')) {
                $table->reset();
                $table->load($pk);

                // Set the new position
                if ($value == 'noposition') {
                    $position = '';
                } elseif ($value == 'nochange') {
                    $position = $table->position;
                } else {
                    $position = $value;
                }

                $table->position = $position;

                // Copy of the Asset ID
                $oldAssetId = $table->asset_id;

                // Alter the title if necessary
                $data = $this->generateNewTitle(0, $table->title, $table->position);
                $table->title = $data['0'];

                // Reset the ID because we are making a copy
                $table->id = 0;

                // Unpublish the new module
                $table->published = 0;

                if (!$table->store()) {
                    $this->setError($table->getError());

                    return false;
                }

                // Get the new item ID
                $newId = $table->get('id');

                // Add the new ID to the array
                $newIds[$pk] = $newId;

                // Now we need to handle the module assignments
                $db = $this->getDatabase();
                $query = $db->getQuery(true)
                    ->select($db->quoteName('menuid'))
                    ->from($db->quoteName('#__modules_menu'))
                    ->where($db->quoteName('moduleid') . ' = :moduleid')
                    ->bind(':moduleid', $pk, ParameterType::INTEGER);
                $db->setQuery($query);
                $menus = $db->loadColumn();

                // Insert the new records into the table
                foreach ($menus as $i => $menu) {
                    $query->clear()
                        ->insert($db->quoteName('#__modules_menu'))
                        ->columns($db->quoteName(['moduleid', 'menuid']))
                        ->values(implode(', ', [':newid' . $i, ':menu' . $i]))
                        ->bind(':newid' . $i, $newId, ParameterType::INTEGER)
                        ->bind(':menu' . $i, $menu, ParameterType::INTEGER);
                    $db->setQuery($query);
                    $db->execute();
                }

                // Copy rules
                $query->clear()
                    ->update($db->quoteName('#__assets', 't'))
                    ->join('INNER', $db->quoteName('#__assets', 's') .
                        ' ON ' . $db->quoteName('s.id') . ' = ' . $oldAssetId)
                    ->set($db->quoteName('t.rules') . ' = ' . $db->quoteName('s.rules'))
                    ->where($db->quoteName('t.id') . ' = ' . $table->asset_id);

                $db->setQuery($query)->execute();
            } else {
                $this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_CREATE'));

                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return $newIds;
    }

    /**
     * Batch move modules to a new position or current.
     *
     * @param   integer  $value     The new value matching a module position.
     * @param   array    $pks       An array of row IDs.
     * @param   array    $contexts  An array of item contexts.
     *
     * @return  boolean  True if successful, false otherwise and internal error is set.
     *
     * @since   2.5
     */
    protected function batchMove($value, $pks, $contexts)
    {
        // Set the variables
        $user = Factory::getUser();
        $table = $this->getTable();

        foreach ($pks as $pk) {
            if ($user->authorise('core.edit', 'com_modules')) {
                $table->reset();
                $table->load($pk);

                // Set the new position
                if ($value == 'noposition') {
                    $position = '';
                } elseif ($value == 'nochange') {
                    $position = $table->position;
                } else {
                    $position = $value;
                }

                $table->position = $position;

                if (!$table->store()) {
                    $this->setError($table->getError());

                    return false;
                }
            } else {
                $this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Method to test whether a record can have its state edited.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
     *
     * @since   3.2
     */
    protected function canEditState($record)
    {
        // Check for existing module.
        if (!empty($record->id)) {
            return Factory::getUser()->authorise('core.edit.state', 'com_modules.module.' . (int) $record->id);
        }

        // Default to component settings if module not known.
        return parent::canEditState($record);
    }

    /**
     * Method to delete rows.
     *
     * @param   array  &$pks  An array of item ids.
     *
     * @return  boolean  Returns true on success, false on failure.
     *
     * @since   1.6
     * @throws  \Exception
     */
    public function delete(&$pks)
    {
        $app        = Factory::getApplication();
        $pks        = (array) $pks;
        $user       = Factory::getUser();
        $table      = $this->getTable();
        $context    = $this->option . '.' . $this->name;

        // Include the plugins for the on delete events.
        PluginHelper::importPlugin($this->events_map['delete']);

        // Iterate the items to delete each one.
        foreach ($pks as $pk) {
            if ($table->load($pk)) {
                // Access checks.
                if (!$user->authorise('core.delete', 'com_modules.module.' . (int) $pk) || $table->published != -2) {
                    Factory::getApplication()->enqueueMessage(Text::_('JERROR_CORE_DELETE_NOT_PERMITTED'), 'error');

                    return;
                }

                // Trigger the before delete event.
                $result = $app->triggerEvent($this->event_before_delete, [$context, $table]);

                if (in_array(false, $result, true) || !$table->delete($pk)) {
                    throw new \Exception($table->getError());
                } else {
                    // Delete the menu assignments
                    $pk    = (int) $pk;
                    $db    = $this->getDatabase();
                    $query = $db->getQuery(true)
                        ->delete($db->quoteName('#__modules_menu'))
                        ->where($db->quoteName('moduleid') . ' = :moduleid')
                        ->bind(':moduleid', $pk, ParameterType::INTEGER);
                    $db->setQuery($query);
                    $db->execute();

                    // Trigger the after delete event.
                    $app->triggerEvent($this->event_after_delete, [$context, $table]);
                }

                // Clear module cache
                parent::cleanCache($table->module);
            } else {
                throw new \Exception($table->getError());
            }
        }

        // Clear modules cache
        $this->cleanCache();

        return true;
    }

    /**
     * Method to duplicate modules.
     *
     * @param   array  &$pks  An array of primary key IDs.
     *
     * @return  boolean  Boolean true on success
     *
     * @since   1.6
     * @throws  \Exception
     */
    public function duplicate(&$pks)
    {
        $user = Factory::getUser();
        $db   = $this->getDatabase();

        // Access checks.
        if (!$user->authorise('core.create', 'com_modules')) {
            throw new \Exception(Text::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
        }

        $table = $this->getTable();

        foreach ($pks as $pk) {
            if ($table->load($pk, true)) {
                // Reset the id to create a new record.
                $table->id = 0;

                // Alter the title.
                $m = null;

                if (preg_match('#\((\d+)\)$#', $table->title, $m)) {
                    $table->title = preg_replace('#\(\d+\)$#', '(' . ($m[1] + 1) . ')', $table->title);
                }

                $data = $this->generateNewTitle(0, $table->title, $table->position);
                $table->title = $data[0];

                // Unpublish duplicate module
                $table->published = 0;

                if (!$table->check() || !$table->store()) {
                    throw new \Exception($table->getError());
                }

                $pk    = (int) $pk;
                $query = $db->getQuery(true)
                    ->select($db->quoteName('menuid'))
                    ->from($db->quoteName('#__modules_menu'))
                    ->where($db->quoteName('moduleid') . ' = :moduleid')
                    ->bind(':moduleid', $pk, ParameterType::INTEGER);

                $db->setQuery($query);
                $rows = $db->loadColumn();

                foreach ($rows as $menuid) {
                    $tuples[] = (int) $table->id . ',' . (int) $menuid;
                }
            } else {
                throw new \Exception($table->getError());
            }
        }

        if (!empty($tuples)) {
            // Module-Menu Mapping: Do it in one query
            $query = $db->getQuery(true)
                ->insert($db->quoteName('#__modules_menu'))
                ->columns($db->quoteName(['moduleid', 'menuid']))
                ->values($tuples);

            $db->setQuery($query);

            try {
                $db->execute();
            } catch (\RuntimeException $e) {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

                return false;
            }
        }

        // Clear modules cache
        $this->cleanCache();

        return true;
    }

    /**
     * Method to change the title.
     *
     * @param   integer  $categoryId  The id of the category. Not used here.
     * @param   string   $title       The title.
     * @param   string   $position    The position.
     *
     * @return  array  Contains the modified title.
     *
     * @since   2.5
     */
    protected function generateNewTitle($categoryId, $title, $position)
    {
        // Alter the title & alias
        $table = $this->getTable();

        while ($table->load(['position' => $position, 'title' => $title])) {
            $title = StringHelper::increment($title);
        }

        return [$title];
    }

    /**
     * Method to get the client object
     *
     * @return  void
     *
     * @since   1.6
     */
    public function &getClient()
    {
        return $this->_client;
    }

    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  Form|bool  A Form object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = [], $loadData = true)
    {
        // The folder and element vars are passed when saving the form.
        if (empty($data)) {
            $item     = $this->getItem();
            $clientId = $item->client_id;
            $module   = $item->module;
            $id       = $item->id;
        } else {
            $clientId = ArrayHelper::getValue($data, 'client_id');
            $module   = ArrayHelper::getValue($data, 'module');
            $id       = ArrayHelper::getValue($data, 'id');
        }

        // Add the default fields directory
        $baseFolder = $clientId ? JPATH_ADMINISTRATOR : JPATH_SITE;
        Form::addFieldPath($baseFolder . '/modules/' . $module . '/field');

        // These variables are used to add data from the plugin XML files.
        $this->setState('item.client_id', $clientId);
        $this->setState('item.module', $module);

        // Get the form.
        if ($clientId == 1) {
            $form = $this->loadForm('com_modules.module.admin', 'moduleadmin', ['control' => 'jform', 'load_data' => $loadData], true);

            // Display language field to filter admin custom menus per language
            if (!ModuleHelper::isAdminMultilang()) {
                $form->setFieldAttribute('language', 'type', 'hidden');
            }
        } else {
            $form = $this->loadForm('com_modules.module', 'module', ['control' => 'jform', 'load_data' => $loadData], true);
        }

        if (empty($form)) {
            return false;
        }

        $user = Factory::getUser();

        /**
         * Check for existing module
         * Modify the form based on Edit State access controls.
         */
        if (
            $id != 0 && (!$user->authorise('core.edit.state', 'com_modules.module.' . (int) $id))
            || ($id == 0 && !$user->authorise('core.edit.state', 'com_modules'))
        ) {
            // Disable fields for display.
            $form->setFieldAttribute('ordering', 'disabled', 'true');
            $form->setFieldAttribute('published', 'disabled', 'true');
            $form->setFieldAttribute('publish_up', 'disabled', 'true');
            $form->setFieldAttribute('publish_down', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is a record you can edit.
            $form->setFieldAttribute('ordering', 'filter', 'unset');
            $form->setFieldAttribute('published', 'filter', 'unset');
            $form->setFieldAttribute('publish_up', 'filter', 'unset');
            $form->setFieldAttribute('publish_down', 'filter', 'unset');
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     *
     * @since   1.6
     */
    protected function loadFormData()
    {
        $app = Factory::getApplication();

        // Check the session for previously entered form data.
        $data = $app->getUserState('com_modules.edit.module.data', []);

        if (empty($data)) {
            $data = $this->getItem();

            // Pre-select some filters (Status, Module Position, Language, Access Level) in edit form if those have been selected in Module Manager
            if (!$data->id) {
                $clientId = $app->input->getInt('client_id', 0);
                $filters  = (array) $app->getUserState('com_modules.modules.' . $clientId . '.filter');
                $data->set('published', $app->input->getInt('published', ((isset($filters['state']) && $filters['state'] !== '') ? $filters['state'] : null)));
                $data->set('position', $app->input->getInt('position', (!empty($filters['position']) ? $filters['position'] : null)));
                $data->set('language', $app->input->getString('language', (!empty($filters['language']) ? $filters['language'] : null)));
                $data->set('access', $app->input->getInt('access', (!empty($filters['access']) ? $filters['access'] : $app->get('access'))));
            }

            // Avoid to delete params of a second module opened in a new browser tab while new one is not saved yet.
            if (empty($data->params)) {
                // This allows us to inject parameter settings into a new module.
                $params = $app->getUserState('com_modules.add.module.params');

                if (is_array($params)) {
                    $data->set('params', $params);
                }
            }
        }

        $this->preprocessData('com_modules.module', $data);

        return $data;
    }

    /**
     * Method to get a single record.
     *
     * @param   integer  $pk  The id of the primary key.
     *
     * @return  mixed  Object on success, false on failure.
     *
     * @since   1.6
     */
    public function getItem($pk = null)
    {
        $pk = (!empty($pk)) ? (int) $pk : (int) $this->getState('module.id');
        $db = $this->getDatabase();

        if (!isset($this->_cache[$pk])) {
            // Get a row instance.
            $table = $this->getTable();

            // Attempt to load the row.
            $return = $table->load($pk);

            // Check for a table object error.
            if ($return === false && $error = $table->getError()) {
                $this->setError($error);

                return false;
            }

            // Check if we are creating a new extension.
            if (empty($pk)) {
                if ($extensionId = (int) $this->getState('extension.id')) {
                    $query = $db->getQuery(true)
                        ->select($db->quoteName(['element', 'client_id']))
                        ->from($db->quoteName('#__extensions'))
                        ->where($db->quoteName('extension_id') . ' = :extensionid')
                        ->where($db->quoteName('type') . ' = ' . $db->quote('module'))
                        ->bind(':extensionid', $extensionId, ParameterType::INTEGER);
                    $db->setQuery($query);

                    try {
                        $extension = $db->loadObject();
                    } catch (\RuntimeException $e) {
                        $this->setError($e->getMessage());

                        return false;
                    }

                    if (empty($extension)) {
                        $this->setError('COM_MODULES_ERROR_CANNOT_FIND_MODULE');

                        return false;
                    }

                    // Extension found, prime some module values.
                    $table->module    = $extension->element;
                    $table->client_id = $extension->client_id;
                } else {
                    Factory::getApplication()->redirect(Route::_('index.php?option=com_modules&view=modules', false));

                    return false;
                }
            }

            // Convert to the \Joomla\CMS\Object\CMSObject before adding other data.
            $properties        = $table->getProperties(1);
            $this->_cache[$pk] = ArrayHelper::toObject($properties, CMSObject::class);

            // Convert the params field to an array.
            $registry = new Registry($table->params);
            $this->_cache[$pk]->params = $registry->toArray();

            // Determine the page assignment mode.
            $query = $db->getQuery(true)
                ->select($db->quoteName('menuid'))
                ->from($db->quoteName('#__modules_menu'))
                ->where($db->quoteName('moduleid') . ' = :moduleid')
                ->bind(':moduleid', $pk, ParameterType::INTEGER);
            $db->setQuery($query);
            $assigned = $db->loadColumn();

            if (empty($pk)) {
                // If this is a new module, assign to all pages.
                $assignment = 0;
            } elseif (empty($assigned)) {
                // For an existing module it is assigned to none.
                $assignment = '-';
            } else {
                if ($assigned[0] > 0) {
                    $assignment = 1;
                } elseif ($assigned[0] < 0) {
                    $assignment = -1;
                } else {
                    $assignment = 0;
                }
            }

            $this->_cache[$pk]->assigned   = $assigned;
            $this->_cache[$pk]->assignment = $assignment;

            // Get the module XML.
            $client = ApplicationHelper::getClientInfo($table->client_id);
            $path   = Path::clean($client->path . '/modules/' . $table->module . '/' . $table->module . '.xml');

            if (file_exists($path)) {
                $this->_cache[$pk]->xml = simplexml_load_file($path);
            } else {
                $this->_cache[$pk]->xml = null;
            }
        }

        return $this->_cache[$pk];
    }

    /**
     * Get the necessary data to load an item help screen.
     *
     * @return  object  An object with key, url, and local properties for loading the item help screen.
     *
     * @since   1.6
     */
    public function getHelp()
    {
        return (object) ['key' => $this->helpKey, 'url' => $this->helpURL];
    }

    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   string  $type    The table type to instantiate
     * @param   string  $prefix  A prefix for the table class name. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  Table  A database object
     *
     * @since   1.6
     */
    public function getTable($type = 'Module', $prefix = 'JTable', $config = [])
    {
        return Table::getInstance($type, $prefix, $config);
    }

    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @param   Table  $table  The database object
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function prepareTable($table)
    {
        $table->title    = htmlspecialchars_decode($table->title, ENT_QUOTES);
        $table->position = trim($table->position);
    }

    /**
     * Method to preprocess the form
     *
     * @param   Form    $form   A form object.
     * @param   mixed   $data   The data expected for the form.
     * @param   string  $group  The name of the plugin group to import (defaults to "content").
     *
     * @return  void
     *
     * @since   1.6
     * @throws  \Exception if there is an error loading the form.
     */
    protected function preprocessForm(Form $form, $data, $group = 'content')
    {
        $lang     = Factory::getLanguage();
        $clientId = $this->getState('item.client_id');
        $module   = $this->getState('item.module');

        $client   = ApplicationHelper::getClientInfo($clientId);
        $formFile = Path::clean($client->path . '/modules/' . $module . '/' . $module . '.xml');

        // Load the core and/or local language file(s).
        $lang->load($module, $client->path)
        ||  $lang->load($module, $client->path . '/modules/' . $module);

        if (file_exists($formFile)) {
            // Get the module form.
            if (!$form->loadFile($formFile, false, '//config')) {
                throw new \Exception(Text::_('JERROR_LOADFILE_FAILED'));
            }

            // Attempt to load the xml file.
            if (!$xml = simplexml_load_file($formFile)) {
                throw new \Exception(Text::_('JERROR_LOADFILE_FAILED'));
            }

            // Get the help data from the XML file if present.
            $help = $xml->xpath('/extension/help');

            if (!empty($help)) {
                $helpKey = trim((string) $help[0]['key']);
                $helpURL = trim((string) $help[0]['url']);

                $this->helpKey = $helpKey ?: $this->helpKey;
                $this->helpURL = $helpURL ?: $this->helpURL;
            }
        }

        // Load the default advanced params
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_modules/models/forms');
        $form->loadFile('advanced', false);

        // Load chrome specific params for global files
        $chromePath      = JPATH_SITE . '/layouts/chromes';
        $chromeFormFiles = Folder::files($chromePath, '.*\.xml');

        if ($chromeFormFiles) {
            Form::addFormPath($chromePath);

            foreach ($chromeFormFiles as $formFile) {
                $form->loadFile(basename($formFile, '.xml'), false);
            }
        }

        // Load chrome specific params for template files
        $templates = ModulesHelper::getTemplates($clientId);

        foreach ($templates as $template) {
            $chromePath = $client->path . '/templates/' . $template->element . '/html/layouts/chromes';

            // Skip if there is no chrome folder in that template.
            if (!is_dir($chromePath)) {
                continue;
            }

            $chromeFormFiles = Folder::files($chromePath, '.*\.xml');

            if ($chromeFormFiles) {
                Form::addFormPath($chromePath);

                foreach ($chromeFormFiles as $formFile) {
                    $form->loadFile(basename($formFile, '.xml'), false);
                }
            }
        }

        // Trigger the default form events.
        parent::preprocessForm($form, $data, $group);
    }

    /**
     * Loads ContentHelper for filters before validating data.
     *
     * @param   object  $form   The form to validate against.
     * @param   array   $data   The data to validate.
     * @param   string  $group  The name of the group(defaults to null).
     *
     * @return  mixed  Array of filtered data if valid, false otherwise.
     *
     * @since   1.1
     */
    public function validate($form, $data, $group = null)
    {
        if (!Factory::getUser()->authorise('core.admin', 'com_modules')) {
            if (isset($data['rules'])) {
                unset($data['rules']);
            }
        }

        return parent::validate($form, $data, $group);
    }

    /**
     * Method to save the form data.
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean  True on success.
     *
     * @since   1.6
     */
    public function save($data)
    {
        $input      = Factory::getApplication()->input;
        $table      = $this->getTable();
        $pk         = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('module.id');
        $isNew      = true;
        $context    = $this->option . '.' . $this->name;

        // Include the plugins for the save event.
        PluginHelper::importPlugin($this->events_map['save']);

        // Load the row if saving an existing record.
        if ($pk > 0) {
            $table->load($pk);
            $isNew = false;
        }

        // Alter the title and published state for Save as Copy
        if ($input->get('task') == 'save2copy') {
            $orig_table = clone $this->getTable();
            $orig_table->load((int) $input->getInt('id'));
            $data['published'] = 0;

            if ($data['title'] == $orig_table->title) {
                $data['title'] = StringHelper::increment($data['title']);
            }
        }

        // Bind the data.
        if (!$table->bind($data)) {
            $this->setError($table->getError());

            return false;
        }

        // Prepare the row for saving
        $this->prepareTable($table);

        // Check the data.
        if (!$table->check()) {
            $this->setError($table->getError());

            return false;
        }

        // Trigger the before save event.
        $result = Factory::getApplication()->triggerEvent($this->event_before_save, [$context, &$table, $isNew]);

        if (in_array(false, $result, true)) {
            $this->setError($table->getError());

            return false;
        }

        // Store the data.
        if (!$table->store()) {
            $this->setError($table->getError());

            return false;
        }

        // Process the menu link mappings.
        $assignment = $data['assignment'] ?? 0;

        $table->id = (int) $table->id;

        // Delete old module to menu item associations
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__modules_menu'))
            ->where($db->quoteName('moduleid') . ' = :moduleid')
            ->bind(':moduleid', $table->id, ParameterType::INTEGER);
        $db->setQuery($query);

        try {
            $db->execute();
        } catch (\RuntimeException $e) {
            $this->setError($e->getMessage());

            return false;
        }

        // If the assignment is numeric, then something is selected (otherwise it's none).
        if (is_numeric($assignment)) {
            // Variable is numeric, but could be a string.
            $assignment = (int) $assignment;

            // Logic check: if no module excluded then convert to display on all.
            if ($assignment == -1 && empty($data['assigned'])) {
                $assignment = 0;
            }

            // Check needed to stop a module being assigned to `All`
            // and other menu items resulting in a module being displayed twice.
            if ($assignment === 0) {
                // Assign new module to `all` menu item associations.
                $query->clear()
                    ->insert($db->quoteName('#__modules_menu'))
                    ->columns($db->quoteName(['moduleid', 'menuid']))
                    ->values(implode(', ', [':moduleid', 0]))
                    ->bind(':moduleid', $table->id, ParameterType::INTEGER);
                $db->setQuery($query);

                try {
                    $db->execute();
                } catch (\RuntimeException $e) {
                    $this->setError($e->getMessage());

                    return false;
                }
            } elseif (!empty($data['assigned'])) {
                // Get the sign of the number.
                $sign = $assignment < 0 ? -1 : 1;

                $query->clear()
                    ->insert($db->quoteName('#__modules_menu'))
                    ->columns($db->quoteName(['moduleid', 'menuid']));

                foreach ($data['assigned'] as &$pk) {
                    $query->values((int) $table->id . ',' . (int) $pk * $sign);
                }

                $db->setQuery($query);

                try {
                    $db->execute();
                } catch (\RuntimeException $e) {
                    $this->setError($e->getMessage());

                    return false;
                }
            }
        }

        // Trigger the after save event.
        Factory::getApplication()->triggerEvent($this->event_after_save, [$context, &$table, $isNew]);

        // Compute the extension id of this module in case the controller wants it.
        $query->clear()
            ->select($db->quoteName('extension_id'))
            ->from($db->quoteName('#__extensions', 'e'))
            ->join(
                'LEFT',
                $db->quoteName('#__modules', 'm') . ' ON ' . $db->quoteName('e.client_id') . ' = ' . (int) $table->client_id .
                ' AND ' . $db->quoteName('e.element') . ' = ' . $db->quoteName('m.module')
            )
            ->where($db->quoteName('m.id') . ' = :id')
            ->bind(':id', $table->id, ParameterType::INTEGER);
        $db->setQuery($query);

        try {
            $extensionId = $db->loadResult();
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }

        $this->setState('module.extension_id', $extensionId);
        $this->setState('module.id', $table->id);

        // Clear modules cache
        $this->cleanCache();

        // Clean module cache
        parent::cleanCache($table->module);

        return true;
    }

    /**
     * A protected method to get a set of ordering conditions.
     *
     * @param   object  $table  A record object.
     *
     * @return  array  An array of conditions to add to ordering queries.
     *
     * @since   1.6
     */
    protected function getReorderConditions($table)
    {
        $db = $this->getDatabase();

        return [
            $db->quoteName('client_id') . ' = ' . (int) $table->client_id,
            $db->quoteName('position') . ' = ' . $db->quote($table->position),
        ];
    }

    /**
     * Custom clean cache method for different clients
     *
     * @param   string   $group     The name of the plugin group to import (defaults to null).
     * @param   integer  $clientId  @deprecated   5.0   No longer used.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function cleanCache($group = null, $clientId = 0)
    {
        parent::cleanCache('com_modules');
    }
}
