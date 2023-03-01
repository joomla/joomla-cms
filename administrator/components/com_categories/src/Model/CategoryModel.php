<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Categories\Administrator\Model;

use Joomla\CMS\Access\Rules;
use Joomla\CMS\Association\AssociationServiceInterface;
use Joomla\CMS\Categories\CategoryServiceInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Category;
use Joomla\CMS\UCM\UCMType;
use Joomla\CMS\Versioning\VersionableModelTrait;
use Joomla\Component\Categories\Administrator\Helper\CategoriesHelper;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Categories Component Category Model
 *
 * @since  1.6
 */
class CategoryModel extends AdminModel
{
    use VersionableModelTrait;

    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     * @since  1.6
     */
    protected $text_prefix = 'COM_CATEGORIES';

    /**
     * The type alias for this content type. Used for content version history.
     *
     * @var      string
     * @since    3.2
     */
    public $typeAlias = null;

    /**
     * The context used for the associations table
     *
     * @var      string
     * @since    3.4.4
     */
    protected $associationsContext = 'com_categories.item';

    /**
     * Does an association exist? Caches the result of getAssoc().
     *
     * @var   boolean|null
     * @since 3.10.4
     */
    private $hasAssociation;

    /**
     * Override parent constructor.
     *
     * @param   array                     $config   An optional associative array of configuration settings.
     * @param   MVCFactoryInterface|null  $factory  The factory.
     *
     * @see     \Joomla\CMS\MVC\Model\BaseDatabaseModel
     * @since   3.2
     */
    public function __construct($config = [], MVCFactoryInterface $factory = null)
    {
        $extension = Factory::getApplication()->input->get('extension', 'com_content');
        $this->typeAlias = $extension . '.category';

        // Add a new batch command
        $this->batch_commands['flip_ordering'] = 'batchFlipordering';

        parent::__construct($config, $factory);
    }

    /**
     * Method to test whether a record can be deleted.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
     *
     * @since   1.6
     */
    protected function canDelete($record)
    {
        if (empty($record->id) || $record->published != -2) {
            return false;
        }

        return Factory::getUser()->authorise('core.delete', $record->extension . '.category.' . (int) $record->id);
    }

    /**
     * Method to test whether a record can have its state changed.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
     *
     * @since   1.6
     */
    protected function canEditState($record)
    {
        $user = Factory::getUser();

        // Check for existing category.
        if (!empty($record->id)) {
            return $user->authorise('core.edit.state', $record->extension . '.category.' . (int) $record->id);
        }

        // New category, so check against the parent.
        if (!empty($record->parent_id)) {
            return $user->authorise('core.edit.state', $record->extension . '.category.' . (int) $record->parent_id);
        }

        // Default to component settings if neither category nor parent known.
        return $user->authorise('core.edit.state', $record->extension);
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $type    The table name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  \Joomla\CMS\Table\Table  A Table object
     *
     * @since   1.6
     */
    public function getTable($type = 'Category', $prefix = 'Administrator', $config = [])
    {
        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Auto-populate the model state.
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

        $parentId = $app->input->getInt('parent_id');
        $this->setState('category.parent_id', $parentId);

        // Load the User state.
        $pk = $app->input->getInt('id');
        $this->setState($this->getName() . '.id', $pk);

        $extension = $app->input->get('extension', 'com_content');
        $this->setState('category.extension', $extension);
        $parts = explode('.', $extension);

        // Extract the component name
        $this->setState('category.component', $parts[0]);

        // Extract the optional section name
        $this->setState('category.section', (\count($parts) > 1) ? $parts[1] : null);

        // Load the parameters.
        $params = ComponentHelper::getParams('com_categories');
        $this->setState('params', $params);
    }

    /**
     * Method to get a category.
     *
     * @param   integer  $pk  An optional id of the object to get, otherwise the id from the model state is used.
     *
     * @return  mixed    Category data object on success, false on failure.
     *
     * @since   1.6
     */
    public function getItem($pk = null)
    {
        if ($result = parent::getItem($pk)) {
            // Prime required properties.
            if (empty($result->id)) {
                $result->parent_id = $this->getState('category.parent_id');
                $result->extension = $this->getState('category.extension');
            }

            // Convert the metadata field to an array.
            $registry = new Registry($result->metadata);
            $result->metadata = $registry->toArray();

            if (!empty($result->id)) {
                $result->tags = new TagsHelper();
                $result->tags->getTagIds($result->id, $result->extension . '.category');
            }
        }

        $assoc = $this->getAssoc();

        if ($assoc) {
            if ($result->id != null) {
                $result->associations = ArrayHelper::toInteger(CategoriesHelper::getAssociations($result->id, $result->extension));
            } else {
                $result->associations = [];
            }
        }

        return $result;
    }

    /**
     * Method to get the row form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  Form|boolean  A JForm object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = [], $loadData = true)
    {
        $extension = $this->getState('category.extension');
        $jinput = Factory::getApplication()->input;

        // A workaround to get the extension into the model for save requests.
        if (empty($extension) && isset($data['extension'])) {
            $extension = $data['extension'];
            $parts = explode('.', $extension);

            $this->setState('category.extension', $extension);
            $this->setState('category.component', $parts[0]);
            $this->setState('category.section', @$parts[1]);
        }

        // Get the form.
        $form = $this->loadForm('com_categories.category' . $extension, 'category', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        // Modify the form based on Edit State access controls.
        if (empty($data['extension'])) {
            $data['extension'] = $extension;
        }

        $categoryId = $jinput->get('id');
        $parts      = explode('.', $extension);
        $assetKey   = $categoryId ? $extension . '.category.' . $categoryId : $parts[0];

        if (!Factory::getUser()->authorise('core.edit.state', $assetKey)) {
            // Disable fields for display.
            $form->setFieldAttribute('ordering', 'disabled', 'true');
            $form->setFieldAttribute('published', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is a record you can edit.
            $form->setFieldAttribute('ordering', 'filter', 'unset');
            $form->setFieldAttribute('published', 'filter', 'unset');
        }

        // Don't allow to change the created_user_id user if not allowed to access com_users.
        if (!Factory::getUser()->authorise('core.manage', 'com_users')) {
            $form->setFieldAttribute('created_user_id', 'filter', 'unset');
        }

        return $form;
    }

    /**
     * A protected method to get the where clause for the reorder
     * This ensures that the row will be moved relative to a row with the same extension
     *
     * @param   Category  $table  Current table instance
     *
     * @return  array  An array of conditions to add to ordering queries.
     *
     * @since   1.6
     */
    protected function getReorderConditions($table)
    {
        $db = $this->getDatabase();

        return [
            $db->quoteName('extension') . ' = ' . $db->quote($table->extension),
        ];
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
        // Check the session for previously entered form data.
        $app = Factory::getApplication();
        $data = $app->getUserState('com_categories.edit.' . $this->getName() . '.data', []);

        if (empty($data)) {
            $data = $this->getItem();

            // Pre-select some filters (Status, Language, Access) in edit form if those have been selected in Category Manager
            if (!$data->id) {
                // Check for which extension the Category Manager is used and get selected fields
                $extension = substr($app->getUserState('com_categories.categories.filter.extension', ''), 4);
                $filters = (array) $app->getUserState('com_categories.categories.' . $extension . '.filter');

                $data->set(
                    'published',
                    $app->input->getInt(
                        'published',
                        ((isset($filters['published']) && $filters['published'] !== '') ? $filters['published'] : null)
                    )
                );
                $data->set('language', $app->input->getString('language', (!empty($filters['language']) ? $filters['language'] : null)));
                $data->set(
                    'access',
                    $app->input->getInt('access', (!empty($filters['access']) ? $filters['access'] : $app->get('access')))
                );
            }
        }

        $this->preprocessData('com_categories.category', $data);

        return $data;
    }

    /**
     * Method to validate the form data.
     *
     * @param   Form    $form   The form to validate against.
     * @param   array   $data   The data to validate.
     * @param   string  $group  The name of the field group to validate.
     *
     * @return  array|boolean  Array of filtered data if valid, false otherwise.
     *
     * @see     JFormRule
     * @see     JFilterInput
     * @since   3.9.23
     */
    public function validate($form, $data, $group = null)
    {
        if (!Factory::getUser()->authorise('core.admin', $data['extension'])) {
            if (isset($data['rules'])) {
                unset($data['rules']);
            }
        }

        return parent::validate($form, $data, $group);
    }

    /**
     * Method to preprocess the form.
     *
     * @param   Form    $form  A Form object.
     * @param   mixed   $data  The data expected for the form.
     * @param   string  $group The name of the plugin group to import.
     *
     * @return  mixed
     *
     * @since   1.6
     *
     * @throws  \Exception if there is an error in the form event.
     *
     * @see     \Joomla\CMS\Form\FormField
     */
    protected function preprocessForm(Form $form, $data, $group = 'content')
    {
        $lang = Factory::getLanguage();
        $component = $this->getState('category.component');
        $section = $this->getState('category.section');
        $extension = Factory::getApplication()->input->get('extension', null);

        // Get the component form if it exists
        $name = 'category' . ($section ? ('.' . $section) : '');

        // Looking first in the component forms folder
        $path = Path::clean(JPATH_ADMINISTRATOR . "/components/$component/forms/$name.xml");

        // Looking in the component models/forms folder (J! 3)
        if (!file_exists($path)) {
            $path = Path::clean(JPATH_ADMINISTRATOR . "/components/$component/models/forms/$name.xml");
        }

        // Old way: looking in the component folder
        if (!file_exists($path)) {
            $path = Path::clean(JPATH_ADMINISTRATOR . "/components/$component/$name.xml");
        }

        if (file_exists($path)) {
            $lang->load($component, JPATH_BASE);
            $lang->load($component, JPATH_BASE . '/components/' . $component);

            if (!$form->loadFile($path, false)) {
                throw new \Exception(Text::_('JERROR_LOADFILE_FAILED'));
            }
        }

        $componentInterface = Factory::getApplication()->bootComponent($component);

        if ($componentInterface instanceof CategoryServiceInterface) {
            $componentInterface->prepareForm($form, $data);
        } else {
            // Try to find the component helper.
            $eName = str_replace('com_', '', $component);
            $path = Path::clean(JPATH_ADMINISTRATOR . "/components/$component/helpers/category.php");

            if (file_exists($path)) {
                $cName = ucfirst($eName) . ucfirst($section) . 'HelperCategory';

                \JLoader::register($cName, $path);

                if (class_exists($cName) && \is_callable([$cName, 'onPrepareForm'])) {
                    $lang->load($component, JPATH_BASE, null, false, false)
                        || $lang->load($component, JPATH_BASE . '/components/' . $component, null, false, false)
                        || $lang->load($component, JPATH_BASE, $lang->getDefault(), false, false)
                        || $lang->load($component, JPATH_BASE . '/components/' . $component, $lang->getDefault(), false, false);
                    \call_user_func_array([$cName, 'onPrepareForm'], [&$form]);

                    // Check for an error.
                    if ($form instanceof \Exception) {
                        $this->setError($form->getMessage());

                        return false;
                    }
                }
            }
        }

        // Set the access control rules field component value.
        $form->setFieldAttribute('rules', 'component', $component);
        $form->setFieldAttribute('rules', 'section', $name);

        // Association category items
        if ($this->getAssoc()) {
            $languages = LanguageHelper::getContentLanguages(false, false, null, 'ordering', 'asc');

            if (\count($languages) > 1) {
                $addform = new \SimpleXMLElement('<form />');
                $fields = $addform->addChild('fields');
                $fields->addAttribute('name', 'associations');
                $fieldset = $fields->addChild('fieldset');
                $fieldset->addAttribute('name', 'item_associations');

                foreach ($languages as $language) {
                    $field = $fieldset->addChild('field');
                    $field->addAttribute('name', $language->lang_code);
                    $field->addAttribute('type', 'modal_category');
                    $field->addAttribute('language', $language->lang_code);
                    $field->addAttribute('label', $language->title);
                    $field->addAttribute('translate_label', 'false');
                    $field->addAttribute('extension', $extension);
                    $field->addAttribute('select', 'true');
                    $field->addAttribute('new', 'true');
                    $field->addAttribute('edit', 'true');
                    $field->addAttribute('clear', 'true');
                    $field->addAttribute('propagate', 'true');
                }

                $form->load($addform, false);
            }
        }

        // Trigger the default form events.
        parent::preprocessForm($form, $data, $group);
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
        $table      = $this->getTable();
        $input      = Factory::getApplication()->input;
        $pk         = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
        $isNew      = true;
        $context    = $this->option . '.' . $this->name;

        if (!empty($data['tags']) && $data['tags'][0] != '') {
            $table->newTags = $data['tags'];
        }

        // Include the plugins for the save events.
        PluginHelper::importPlugin($this->events_map['save']);

        // Load the row if saving an existing category.
        if ($pk > 0) {
            $table->load($pk);
            $isNew = false;
        }

        // Set the new parent id if parent id not matched OR while New/Save as Copy .
        if ($table->parent_id != $data['parent_id'] || $data['id'] == 0) {
            $table->setLocation($data['parent_id'], 'last-child');
        }

        // Alter the title for save as copy
        if ($input->get('task') == 'save2copy') {
            $origTable = clone $this->getTable();
            $origTable->load($input->getInt('id'));

            if ($data['title'] == $origTable->title) {
                [$title, $alias] = $this->generateNewTitle($data['parent_id'], $data['alias'], $data['title']);
                $data['title'] = $title;
                $data['alias'] = $alias;
            } else {
                if ($data['alias'] == $origTable->alias) {
                    $data['alias'] = '';
                }
            }

            $data['published'] = 0;
        }

        // Bind the data.
        if (!$table->bind($data)) {
            $this->setError($table->getError());

            return false;
        }

        // Bind the rules.
        if (isset($data['rules'])) {
            $rules = new Rules($data['rules']);
            $table->setRules($rules);
        }

        // Check the data.
        if (!$table->check()) {
            $this->setError($table->getError());

            return false;
        }

        // Trigger the before save event.
        $result = Factory::getApplication()->triggerEvent($this->event_before_save, [$context, &$table, $isNew, $data]);

        if (\in_array(false, $result, true)) {
            $this->setError($table->getError());

            return false;
        }

        // Store the data.
        if (!$table->store()) {
            $this->setError($table->getError());

            return false;
        }

        $assoc = $this->getAssoc();

        if ($assoc) {
            // Adding self to the association
            $associations = $data['associations'] ?? [];

            // Unset any invalid associations
            $associations = ArrayHelper::toInteger($associations);

            foreach ($associations as $tag => $id) {
                if (!$id) {
                    unset($associations[$tag]);
                }
            }

            // Detecting all item menus
            $allLanguage = $table->language == '*';

            if ($allLanguage && !empty($associations)) {
                Factory::getApplication()->enqueueMessage(Text::_('COM_CATEGORIES_ERROR_ALL_LANGUAGE_ASSOCIATED'), 'notice');
            }

            // Get associationskey for edited item
            $db    = $this->getDatabase();
            $id    = (int) $table->id;
            $query = $db->getQuery(true)
                ->select($db->quoteName('key'))
                ->from($db->quoteName('#__associations'))
                ->where($db->quoteName('context') . ' = :associationscontext')
                ->where($db->quoteName('id') . ' = :id')
                ->bind(':associationscontext', $this->associationsContext)
                ->bind(':id', $id, ParameterType::INTEGER);
            $db->setQuery($query);
            $oldKey = $db->loadResult();

            if ($associations || $oldKey !== null) {
                $where = [];

                // Deleting old associations for the associated items
                $query = $db->getQuery(true)
                    ->delete($db->quoteName('#__associations'))
                    ->where($db->quoteName('context') . ' = :associationscontext')
                    ->bind(':associationscontext', $this->associationsContext);

                if ($associations) {
                    $where[] = $db->quoteName('id') . ' IN (' . implode(',', $query->bindArray(array_values($associations))) . ')';
                }

                if ($oldKey !== null) {
                    $where[] = $db->quoteName('key') . ' = :oldKey';
                    $query->bind(':oldKey', $oldKey);
                }

                $query->extendWhere('AND', $where, 'OR');
            }

            $db->setQuery($query);

            try {
                $db->execute();
            } catch (\RuntimeException $e) {
                $this->setError($e->getMessage());

                return false;
            }

            // Adding self to the association
            if (!$allLanguage) {
                $associations[$table->language] = (int) $table->id;
            }

            if (\count($associations) > 1) {
                // Adding new association for these items
                $key = md5(json_encode($associations));
                $query->clear()
                    ->insert($db->quoteName('#__associations'))
                    ->columns(
                        [
                            $db->quoteName('id'),
                            $db->quoteName('context'),
                            $db->quoteName('key'),
                        ]
                    );

                foreach ($associations as $id) {
                    $id = (int) $id;

                    $query->values(
                        implode(
                            ',',
                            $query->bindArray(
                                [$id, $this->associationsContext, $key],
                                [ParameterType::INTEGER, ParameterType::STRING, ParameterType::STRING]
                            )
                        )
                    );
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
        Factory::getApplication()->triggerEvent($this->event_after_save, [$context, &$table, $isNew, $data]);

        // Rebuild the path for the category:
        if (!$table->rebuildPath($table->id)) {
            $this->setError($table->getError());

            return false;
        }

        // Rebuild the paths of the category's children:
        if (!$table->rebuild($table->id, $table->lft, $table->level, $table->path)) {
            $this->setError($table->getError());

            return false;
        }

        $this->setState($this->getName() . '.id', $table->id);

        if (Factory::getApplication()->input->get('task') == 'editAssociations') {
            return $this->redirectToAssociations($data);
        }

        // Clear the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Method to change the published state of one or more records.
     *
     * @param   array    $pks    A list of the primary keys to change.
     * @param   integer  $value  The value of the published state.
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     */
    public function publish(&$pks, $value = 1)
    {
        if (parent::publish($pks, $value)) {
            $extension = Factory::getApplication()->input->get('extension');

            // Include the content plugins for the change of category state event.
            PluginHelper::importPlugin('content');

            // Trigger the onCategoryChangeState event.
            Factory::getApplication()->triggerEvent('onCategoryChangeState', [$extension, $pks, $value]);

            return true;
        }
    }

    /**
     * Method rebuild the entire nested set tree.
     *
     * @return  boolean  False on failure or error, true otherwise.
     *
     * @since   1.6
     */
    public function rebuild()
    {
        // Get an instance of the table object.
        $table = $this->getTable();

        if (!$table->rebuild()) {
            $this->setError($table->getError());

            return false;
        }

        // Clear the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Method to save the reordered nested set tree.
     * First we save the new order values in the lft values of the changed ids.
     * Then we invoke the table rebuild to implement the new ordering.
     *
     * @param   array    $idArray   An array of primary key ids.
     * @param   integer  $lftArray  The lft value
     *
     * @return  boolean  False on failure or error, True otherwise
     *
     * @since   1.6
     */
    public function saveorder($idArray = null, $lftArray = null)
    {
        // Get an instance of the table object.
        $table = $this->getTable();

        if (!$table->saveorder($idArray, $lftArray)) {
            $this->setError($table->getError());

            return false;
        }

        // Clear the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Batch flip category ordering.
     *
     * @param   integer  $value     The new category.
     * @param   array    $pks       An array of row IDs.
     * @param   array    $contexts  An array of item contexts.
     *
     * @return  mixed    An array of new IDs on success, boolean false on failure.
     *
     * @since   3.6.3
     */
    protected function batchFlipordering($value, $pks, $contexts)
    {
        $successful = [];

        $db = $this->getDatabase();
        $query = $db->getQuery(true);

        /**
         * For each category get the max ordering value
         * Re-order with max - ordering
         */
        foreach ($pks as $id) {
            $query->clear()
                ->select('MAX(' . $db->quoteName('ordering') . ')')
                ->from($db->quoteName('#__content'))
                ->where($db->quoteName('catid') . ' = :catid')
                ->bind(':catid', $id, ParameterType::INTEGER);

            $db->setQuery($query);

            $max = (int) $db->loadResult();
            $max++;

            $query->clear()
                ->update($db->quoteName('#__content'))
                ->set($db->quoteName('ordering') . ' = :max - ' . $db->quoteName('ordering'))
                ->where($db->quoteName('catid') . ' = :catid')
                ->bind(':max', $max, ParameterType::INTEGER)
                ->bind(':catid', $id, ParameterType::INTEGER);

            $db->setQuery($query);

            if ($db->execute()) {
                $successful[] = $id;
            }
        }

        return empty($successful) ? false : $successful;
    }

    /**
     * Batch copy categories to a new category.
     *
     * @param   integer  $value     The new category.
     * @param   array    $pks       An array of row IDs.
     * @param   array    $contexts  An array of item contexts.
     *
     * @return  mixed    An array of new IDs on success, boolean false on failure.
     *
     * @since   1.6
     */
    protected function batchCopy($value, $pks, $contexts)
    {
        $type = new UCMType();
        $this->type = $type->getTypeByAlias($this->typeAlias);

        // $value comes as {parent_id}.{extension}
        $parts = explode('.', $value);
        $parentId = (int) ArrayHelper::getValue($parts, 0, 1);

        $db = $this->getDatabase();
        $extension = Factory::getApplication()->input->get('extension', '', 'word');
        $newIds = [];

        // Check that the parent exists
        if ($parentId) {
            if (!$this->table->load($parentId)) {
                if ($error = $this->table->getError()) {
                    // Fatal error
                    $this->setError($error);

                    return false;
                } else {
                    // Non-fatal error
                    $this->setError(Text::_('JGLOBAL_BATCH_MOVE_PARENT_NOT_FOUND'));
                    $parentId = 0;
                }
            }

            // Check that user has create permission for parent category
            if ($parentId == $this->table->getRootId()) {
                $canCreate = $this->user->authorise('core.create', $extension);
            } else {
                $canCreate = $this->user->authorise('core.create', $extension . '.category.' . $parentId);
            }

            if (!$canCreate) {
                // Error since user cannot create in parent category
                $this->setError(Text::_('COM_CATEGORIES_BATCH_CANNOT_CREATE'));

                return false;
            }
        }

        // If the parent is 0, set it to the ID of the root item in the tree
        if (empty($parentId)) {
            if (!$parentId = $this->table->getRootId()) {
                $this->setError($this->table->getError());

                return false;
            } elseif (!$this->user->authorise('core.create', $extension)) {
                // Make sure we can create in root
                $this->setError(Text::_('COM_CATEGORIES_BATCH_CANNOT_CREATE'));

                return false;
            }
        }

        // We need to log the parent ID
        $parents = [];

        // Calculate the emergency stop count as a precaution against a runaway loop bug
        $query = $db->getQuery(true)
            ->select('COUNT(' . $db->quoteName('id') . ')')
            ->from($db->quoteName('#__categories'));
        $db->setQuery($query);

        try {
            $count = $db->loadResult();
        } catch (\RuntimeException $e) {
            $this->setError($e->getMessage());

            return false;
        }

        // Parent exists so let's proceed
        while (!empty($pks) && $count > 0) {
            // Pop the first id off the stack
            $pk = array_shift($pks);

            $this->table->reset();

            // Check that the row actually exists
            if (!$this->table->load($pk)) {
                if ($error = $this->table->getError()) {
                    // Fatal error
                    $this->setError($error);

                    return false;
                } else {
                    // Not fatal error
                    $this->setError(Text::sprintf('JGLOBAL_BATCH_MOVE_ROW_NOT_FOUND', $pk));
                    continue;
                }
            }

            // Copy is a bit tricky, because we also need to copy the children
            $lft = (int) $this->table->lft;
            $rgt = (int) $this->table->rgt;
            $query->clear()
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__categories'))
                ->where($db->quoteName('lft') . ' > :lft')
                ->where($db->quoteName('rgt') . ' < :rgt')
                ->bind(':lft', $lft, ParameterType::INTEGER)
                ->bind(':rgt', $rgt, ParameterType::INTEGER);
            $db->setQuery($query);
            $childIds = $db->loadColumn();

            // Add child ID's to the array only if they aren't already there.
            foreach ($childIds as $childId) {
                if (!\in_array($childId, $pks)) {
                    $pks[] = $childId;
                }
            }

            // Make a copy of the old ID, Parent ID and Asset ID
            $oldId       = $this->table->id;
            $oldParentId = $this->table->parent_id;
            $oldAssetId  = $this->table->asset_id;

            // Reset the id because we are making a copy.
            $this->table->id = 0;

            // If we a copying children, the Old ID will turn up in the parents list
            // otherwise it's a new top level item
            $this->table->parent_id = $parents[$oldParentId] ?? $parentId;

            // Set the new location in the tree for the node.
            $this->table->setLocation($this->table->parent_id, 'last-child');

            // @TODO: Deal with ordering?
            // $this->table->ordering = 1;
            $this->table->level = null;
            $this->table->asset_id = null;
            $this->table->lft = null;
            $this->table->rgt = null;

            // Alter the title & alias
            [$title, $alias] = $this->generateNewTitle($this->table->parent_id, $this->table->alias, $this->table->title);
            $this->table->title  = $title;
            $this->table->alias  = $alias;

            // Unpublish because we are making a copy
            $this->table->published = 0;

            // Store the row.
            if (!$this->table->store()) {
                $this->setError($this->table->getError());

                return false;
            }

            // Get the new item ID
            $newId = $this->table->get('id');

            // Add the new ID to the array
            $newIds[$pk] = $newId;

            // Copy rules
            $query->clear()
                ->update($db->quoteName('#__assets', 't'))
                ->join(
                    'INNER',
                    $db->quoteName('#__assets', 's'),
                    $db->quoteName('s.id') . ' = :oldid'
                )
                ->bind(':oldid', $oldAssetId, ParameterType::INTEGER)
                ->set($db->quoteName('t.rules') . ' = ' . $db->quoteName('s.rules'))
                ->where($db->quoteName('t.id') . ' = :assetid')
                ->bind(':assetid', $this->table->asset_id, ParameterType::INTEGER);
            $db->setQuery($query)->execute();

            // Now we log the old 'parent' to the new 'parent'
            $parents[$oldId] = $this->table->id;
            $count--;
        }

        // Rebuild the hierarchy.
        if (!$this->table->rebuild()) {
            $this->setError($this->table->getError());

            return false;
        }

        // Rebuild the tree path.
        if (!$this->table->rebuildPath($this->table->id)) {
            $this->setError($this->table->getError());

            return false;
        }

        return $newIds;
    }

    /**
     * Batch move categories to a new category.
     *
     * @param   integer  $value     The new category ID.
     * @param   array    $pks       An array of row IDs.
     * @param   array    $contexts  An array of item contexts.
     *
     * @return  boolean  True on success.
     *
     * @since   1.6
     */
    protected function batchMove($value, $pks, $contexts)
    {
        $parentId = (int) $value;
        $type = new UCMType();
        $this->type = $type->getTypeByAlias($this->typeAlias);

        $db = $this->getDatabase();
        $query = $db->getQuery(true);
        $extension = Factory::getApplication()->input->get('extension', '', 'word');

        // Check that the parent exists.
        if ($parentId) {
            if (!$this->table->load($parentId)) {
                if ($error = $this->table->getError()) {
                    // Fatal error.
                    $this->setError($error);

                    return false;
                } else {
                    // Non-fatal error.
                    $this->setError(Text::_('JGLOBAL_BATCH_MOVE_PARENT_NOT_FOUND'));
                    $parentId = 0;
                }
            }

            // Check that user has create permission for parent category.
            if ($parentId == $this->table->getRootId()) {
                $canCreate = $this->user->authorise('core.create', $extension);
            } else {
                $canCreate = $this->user->authorise('core.create', $extension . '.category.' . $parentId);
            }

            if (!$canCreate) {
                // Error since user cannot create in parent category
                $this->setError(Text::_('COM_CATEGORIES_BATCH_CANNOT_CREATE'));

                return false;
            }

            // Check that user has edit permission for every category being moved
            // Note that the entire batch operation fails if any category lacks edit permission
            foreach ($pks as $pk) {
                if (!$this->user->authorise('core.edit', $extension . '.category.' . $pk)) {
                    // Error since user cannot edit this category
                    $this->setError(Text::_('COM_CATEGORIES_BATCH_CANNOT_EDIT'));

                    return false;
                }
            }
        }

        // We are going to store all the children and just move the category
        $children = [];

        $table = $this->getTable();

        // Parent exists so let's proceed
        foreach ($pks as $pk) {
            // Check that the row actually exists
            if (!$this->table->load($pk)) {
                if ($error = $this->table->getError()) {
                    // Fatal error
                    $this->setError($error);

                    return false;
                } else {
                    // Not fatal error
                    $this->setError(Text::sprintf('JGLOBAL_BATCH_MOVE_ROW_NOT_FOUND', $pk));
                    continue;
                }
            }

            // Set the new location in the tree for the node.
            $this->table->setLocation($parentId, 'last-child');

            // Check if we are moving to a different parent
            if ($parentId != $this->table->parent_id) {
                $lft = (int) $this->table->lft;
                $rgt = (int) $this->table->rgt;

                // Add the child node ids to the children array.
                $query->clear()
                    ->select($db->quoteName('id'))
                    ->from($db->quoteName('#__categories'))
                    ->where($db->quoteName('lft') . ' BETWEEN :lft AND :rgt')
                    ->bind(':lft', $lft, ParameterType::INTEGER)
                    ->bind(':rgt', $rgt, ParameterType::INTEGER);
                $db->setQuery($query);

                try {
                    $children = array_merge($children, (array) $db->loadColumn());
                } catch (\RuntimeException $e) {
                    $this->setError($e->getMessage());

                    return false;
                }

                // Verify that the alias is unique before move
                $conditions = [
                    'alias'     => $this->table->alias,
                    'parent_id' => $parentId,
                    'extension' => $extension,
                ];

                if ($table->load($conditions)) {
                    $this->setError(Text::_('JLIB_DATABASE_ERROR_CATEGORY_UNIQUE_ALIAS'));

                    return false;
                }
            }

            // Store the row.
            if (!$this->table->store()) {
                $this->setError($this->table->getError());

                return false;
            }

            // Rebuild the tree path.
            if (!$this->table->rebuildPath()) {
                $this->setError($this->table->getError());

                return false;
            }
        }

        // Process the child rows
        if (!empty($children)) {
            // Remove any duplicates and sanitize ids.
            $children = array_unique($children);
            $children = ArrayHelper::toInteger($children);
        }

        return true;
    }

    /**
     * Custom clean the cache of com_content and content modules
     *
     * @param   string   $group     Cache group name.
     * @param   integer  $clientId  @deprecated   5.0   No longer used.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function cleanCache($group = null, $clientId = 0)
    {
        $extension = Factory::getApplication()->input->get('extension');

        switch ($extension) {
            case 'com_content':
                parent::cleanCache('com_content');
                parent::cleanCache('mod_articles_archive');
                parent::cleanCache('mod_articles_categories');
                parent::cleanCache('mod_articles_category');
                parent::cleanCache('mod_articles_latest');
                parent::cleanCache('mod_articles_news');
                parent::cleanCache('mod_articles_popular');
                break;
            default:
                parent::cleanCache($extension);
                break;
        }
    }

    /**
     * Method to change the title & alias.
     *
     * @param   integer  $parentId  The id of the parent.
     * @param   string   $alias     The alias.
     * @param   string   $title     The title.
     *
     * @return  array    Contains the modified title and alias.
     *
     * @since   1.7
     */
    protected function generateNewTitle($parentId, $alias, $title)
    {
        // Alter the title & alias
        $table = $this->getTable();

        while ($table->load(['alias' => $alias, 'parent_id' => $parentId])) {
            $title = StringHelper::increment($title);
            $alias = StringHelper::increment($alias, 'dash');
        }

        return [$title, $alias];
    }

    /**
     * Method to determine if a category association is available.
     *
     * @return  boolean True if a category association is available; false otherwise.
     */
    public function getAssoc()
    {
        if (!\is_null($this->hasAssociation)) {
            return $this->hasAssociation;
        }

        $extension = $this->getState('category.extension', '');

        $this->hasAssociation = Associations::isEnabled();
        $extension = explode('.', $extension);
        $component = array_shift($extension);
        $cname = str_replace('com_', '', $component);

        if (!$this->hasAssociation || !$component || !$cname) {
            $this->hasAssociation = false;

            return $this->hasAssociation;
        }

        $componentObject = $this->bootComponent($component);

        if ($componentObject instanceof AssociationServiceInterface && $componentObject instanceof CategoryServiceInterface) {
            $this->hasAssociation = true;

            return $this->hasAssociation;
        }

        $hname = $cname . 'HelperAssociation';
        \JLoader::register($hname, JPATH_SITE . '/components/' . $component . '/helpers/association.php');

        $this->hasAssociation = class_exists($hname) && !empty($hname::$category_association);

        return $this->hasAssociation;
    }
}
