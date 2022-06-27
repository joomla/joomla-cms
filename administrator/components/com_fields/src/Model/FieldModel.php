<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Fields\Administrator\Model;

use Joomla\CMS\Categories\CategoryServiceInterface;
use Joomla\CMS\Categories\SectionNotFoundException;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\Exception\DatabaseNotFoundException;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

/**
 * Field Model
 *
 * @since  3.7.0
 */
class FieldModel extends AdminModel
{
    /**
     * @var null|string
     *
     * @since   3.7.0
     */
    public $typeAlias = null;

    /**
     * @var string
     *
     * @since   3.7.0
     */
    protected $text_prefix = 'COM_FIELDS';

    /**
     * Batch copy/move command. If set to false,
     * the batch copy/move command is not supported
     *
     * @var    string
     * @since  3.4
     */
    protected $batch_copymove = 'group_id';

    /**
     * Allowed batch commands
     *
     * @var array
     */
    protected $batch_commands = array(
        'assetgroup_id' => 'batchAccess',
        'language_id'   => 'batchLanguage'
    );

    /**
     * @var array
     *
     * @since   3.7.0
     */
    private $valueCache = array();

    /**
     * Constructor
     *
     * @param   array                $config   An array of configuration options (name, state, dbo, table_path, ignore_request).
     * @param   MVCFactoryInterface  $factory  The factory.
     *
     * @since   3.7.0
     * @throws  \Exception
     */
    public function __construct($config = array(), MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $this->typeAlias = Factory::getApplication()->input->getCmd('context', 'com_content.article') . '.field';
    }

    /**
     * Method to save the form data.
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean  True on success, False on error.
     *
     * @since   3.7.0
     */
    public function save($data)
    {
        $field = null;

        if (isset($data['id']) && $data['id']) {
            $field = $this->getItem($data['id']);
        }

        if (!isset($data['label']) && isset($data['params']['label'])) {
            $data['label'] = $data['params']['label'];

            unset($data['params']['label']);
        }

        // Alter the title for save as copy
        $input = Factory::getApplication()->input;

        if ($input->get('task') == 'save2copy') {
            $origTable = clone $this->getTable();
            $origTable->load($input->getInt('id'));

            if ($data['title'] == $origTable->title) {
                list($title, $name) = $this->generateNewTitle($data['group_id'], $data['name'], $data['title']);
                $data['title'] = $title;
                $data['label'] = $title;
                $data['name'] = $name;
            } else {
                if ($data['name'] == $origTable->name) {
                    $data['name'] = '';
                }
            }

            $data['state'] = 0;
        }

        // Load the fields plugins, perhaps they want to do something
        PluginHelper::importPlugin('fields');

        $message = $this->checkDefaultValue($data);

        if ($message !== true) {
            $this->setError($message);

            return false;
        }

        if (!parent::save($data)) {
            return false;
        }

        // Save the assigned categories into #__fields_categories
        $db = $this->getDatabase();
        $id = (int) $this->getState('field.id');

        /**
         * If the field is only used in subform, set Category to None automatically so that it will only be displayed
         * as part of SubForm on add/edit item screen
         */
        if (!empty($data['only_use_in_subform'])) {
            $cats = [-1];
        } else {
            $cats = isset($data['assigned_cat_ids']) ? (array) $data['assigned_cat_ids'] : array();
            $cats = ArrayHelper::toInteger($cats);
        }

        $assignedCatIds = array();

        foreach ($cats as $cat) {
            // If we have found the 'JNONE' category, remove all other from the result and break.
            if ($cat == '-1') {
                $assignedCatIds = array('-1');
                break;
            }

            if ($cat) {
                $assignedCatIds[] = $cat;
            }
        }

        // First delete all assigned categories
        $query = $db->getQuery(true);
        $query->delete('#__fields_categories')
            ->where($db->quoteName('field_id') . ' = :fieldid')
            ->bind(':fieldid', $id, ParameterType::INTEGER);

        $db->setQuery($query);
        $db->execute();

        // Inset new assigned categories
        $tupel = new \stdClass();
        $tupel->field_id = $id;

        foreach ($assignedCatIds as $catId) {
            $tupel->category_id = $catId;
            $db->insertObject('#__fields_categories', $tupel);
        }

        /**
         * If the options have changed, delete the values. This should only apply for list, checkboxes and radio
         * custom field types, because when their options are being changed, their values might get invalid, because
         * e.g. there is a value selected from a list, which is not part of the list anymore. Hence we need to delete
         * all values that are not part of the options anymore. Note: The only field types with fieldparams+options
         * are those above listed plus the subfields type. And we do explicitly not want the values to be deleted
         * when the options of a subfields field are getting changed.
         */
        if (
            $field && in_array($field->type, array('list', 'checkboxes', 'radio'), true)
            && isset($data['fieldparams']['options']) && isset($field->fieldparams['options'])
        ) {
            $oldParams = $this->getParams($field->fieldparams['options']);
            $newParams = $this->getParams($data['fieldparams']['options']);

            if (is_object($oldParams) && is_object($newParams) && $oldParams != $newParams) {
                // Get new values.
                $names = array_column((array) $newParams, 'value');

                $fieldId = (int) $field->id;
                $query = $db->getQuery(true);
                $query->delete($db->quoteName('#__fields_values'))
                    ->where($db->quoteName('field_id') . ' = :fieldid')
                    ->bind(':fieldid', $fieldId, ParameterType::INTEGER);

                // If new values are set, delete only old values. Otherwise delete all values.
                if ($names) {
                    $query->whereNotIn($db->quoteName('value'), $names, ParameterType::STRING);
                }

                $db->setQuery($query);
                $db->execute();
            }
        }

        FieldsHelper::clearFieldsCache();

        return true;
    }


    /**
     * Checks if the default value is valid for the given data. If a string is returned then
     * it can be assumed that the default value is invalid.
     *
     * @param   array  $data  The data.
     *
     * @return  true|string  true if valid, a string containing the exception message when not.
     *
     * @since   3.7.0
     */
    private function checkDefaultValue($data)
    {
        // Empty default values are correct
        if (empty($data['default_value']) && $data['default_value'] !== '0') {
            return true;
        }

        $types = FieldsHelper::getFieldTypes();

        // Check if type exists
        if (!array_key_exists($data['type'], $types)) {
            return true;
        }

        $path = $types[$data['type']]['rules'];

        // Add the path for the rules of the plugin when available
        if ($path) {
            // Add the lookup path for the rule
            FormHelper::addRulePath($path);
        }

        // Create the fields object
        $obj              = (object) $data;
        $obj->params      = new Registry($obj->params);
        $obj->fieldparams = new Registry(!empty($obj->fieldparams) ? $obj->fieldparams : array());

        // Prepare the dom
        $dom  = new \DOMDocument();
        $node = $dom->appendChild(new \DOMElement('form'));

        // Trigger the event to create the field dom node
        $form = new Form($data['context']);
        $form->setDatabase($this->getDatabase());
        Factory::getApplication()->triggerEvent('onCustomFieldsPrepareDom', array($obj, $node, $form));

        // Check if a node is created
        if (!$node->firstChild) {
            return true;
        }

        // Define the type either from the field or from the data
        $type = $node->firstChild->getAttribute('validate') ? : $data['type'];

        // Load the rule
        $rule = FormHelper::loadRuleType($type);

        // When no rule exists, we allow the default value
        if (!$rule) {
            return true;
        }

        if ($rule instanceof DatabaseAwareInterface) {
            try {
                $rule->setDatabase($this->getDatabase());
            } catch (DatabaseNotFoundException $e) {
                @trigger_error(sprintf('Database must be set, this will not be caught anymore in 5.0.'), E_USER_DEPRECATED);
                $rule->setDatabase(Factory::getContainer()->get(DatabaseInterface::class));
            }
        }

        try {
            // Perform the check
            $result = $rule->test(simplexml_import_dom($node->firstChild), $data['default_value']);

            // Check if the test succeeded
            return $result === true ? : Text::_('COM_FIELDS_FIELD_INVALID_DEFAULT_VALUE');
        } catch (\UnexpectedValueException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Converts the unknown params into an object.
     *
     * @param   mixed  $params  The params.
     *
     * @return  \stdClass  Object on success, false on failure.
     *
     * @since   3.7.0
     */
    private function getParams($params)
    {
        if (is_string($params)) {
            $params = json_decode($params);
        }

        if (is_array($params)) {
            $params = (object) $params;
        }

        return $params;
    }

    /**
     * Method to get a single record.
     *
     * @param   integer  $pk  The id of the primary key.
     *
     * @return  mixed    Object on success, false on failure.
     *
     * @since   3.7.0
     */
    public function getItem($pk = null)
    {
        $result = parent::getItem($pk);

        if ($result) {
            // Prime required properties.
            if (empty($result->id)) {
                $result->context = Factory::getApplication()->input->getCmd('context', $this->getState('field.context'));
            }

            if (property_exists($result, 'fieldparams') && $result->fieldparams !== null) {
                $registry = new Registry();

                if ($result->fieldparams) {
                    $registry->loadString($result->fieldparams);
                }

                $result->fieldparams = $registry->toArray();
            }

            $db = $this->getDatabase();
            $query = $db->getQuery(true);
            $fieldId = (int) $result->id;
            $query->select($db->quoteName('category_id'))
                ->from($db->quoteName('#__fields_categories'))
                ->where($db->quoteName('field_id') . ' = :fieldid')
                ->bind(':fieldid', $fieldId, ParameterType::INTEGER);

            $db->setQuery($query);
            $result->assigned_cat_ids = $db->loadColumn() ?: array(0);
        }

        return $result;
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $name     The table name. Optional.
     * @param   string  $prefix   The class prefix. Optional.
     * @param   array   $options  Configuration array for model. Optional.
     *
     * @return  Table  A Table object
     *
     * @since   3.7.0
     * @throws  \Exception
     */
    public function getTable($name = 'Field', $prefix = 'Administrator', $options = array())
    {
        // Default to text type
        $table       = parent::getTable($name, $prefix, $options);
        $table->type = 'text';

        return $table;
    }

    /**
     * Method to change the title & name.
     *
     * @param   integer  $categoryId  The id of the category.
     * @param   string   $name        The name.
     * @param   string   $title       The title.
     *
     * @return  array  Contains the modified title and name.
     *
     * @since    3.7.0
     */
    protected function generateNewTitle($categoryId, $name, $title)
    {
        // Alter the title & name
        $table = $this->getTable();

        while ($table->load(array('name' => $name))) {
            $title = StringHelper::increment($title);
            $name = StringHelper::increment($name, 'dash');
        }

        return array(
            $title,
            $name,
        );
    }

    /**
     * Method to delete one or more records.
     *
     * @param   array  $pks  An array of record primary keys.
     *
     * @return  boolean  True if successful, false if an error occurs.
     *
     * @since   3.7.0
     */
    public function delete(&$pks)
    {
        $success = parent::delete($pks);

        if ($success) {
            $pks = (array) $pks;
            $pks = ArrayHelper::toInteger($pks);
            $pks = array_filter($pks);

            if (!empty($pks)) {
                // Delete Values
                $query = $this->getDatabase()->getQuery(true);

                $query->delete($query->quoteName('#__fields_values'))
                    ->whereIn($query->quoteName('field_id'), $pks);

                $this->getDatabase()->setQuery($query)->execute();

                // Delete Assigned Categories
                $query = $this->getDatabase()->getQuery(true);

                $query->delete($query->quoteName('#__fields_categories'))
                    ->whereIn($query->quoteName('field_id'), $pks);

                $this->getDatabase()->setQuery($query)->execute();
            }
        }

        return $success;
    }

    /**
     * Abstract method for getting the form from the model.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  Form|bool  A Form object on success, false on failure
     *
     * @since   3.7.0
     */
    public function getForm($data = array(), $loadData = true)
    {
        $context = $this->getState('field.context');
        $jinput  = Factory::getApplication()->input;

        // A workaround to get the context into the model for save requests.
        if (empty($context) && isset($data['context'])) {
            $context = $data['context'];
            $parts   = FieldsHelper::extract($context);

            $this->setState('field.context', $context);

            if ($parts) {
                $this->setState('field.component', $parts[0]);
                $this->setState('field.section', $parts[1]);
            }
        }

        if (isset($data['type'])) {
            // This is needed that the plugins can determine the type
            $this->setState('field.type', $data['type']);
        }

        // Load the fields plugin that they can add additional parameters to the form
        PluginHelper::importPlugin('fields');

        // Get the form.
        $form = $this->loadForm(
            'com_fields.field.' . $context,
            'field',
            array(
                'control'   => 'jform',
                'load_data' => true,
            )
        );

        if (empty($form)) {
            return false;
        }

        // Modify the form based on Edit State access controls.
        if (empty($data['context'])) {
            $data['context'] = $context;
        }

        $fieldId  = $jinput->get('id');
        $assetKey = $this->state->get('field.component') . '.field.' . $fieldId;

        if (!Factory::getUser()->authorise('core.edit.state', $assetKey)) {
            // Disable fields for display.
            $form->setFieldAttribute('ordering', 'disabled', 'true');
            $form->setFieldAttribute('state', 'disabled', 'true');

            // Disable fields while saving. The controller has already verified this is a record you can edit.
            $form->setFieldAttribute('ordering', 'filter', 'unset');
            $form->setFieldAttribute('state', 'filter', 'unset');
        }

        // Don't allow to change the created_user_id user if not allowed to access com_users.
        if (!Factory::getUser()->authorise('core.manage', 'com_users')) {
            $form->setFieldAttribute('created_user_id', 'filter', 'unset');
        }

        // In case we are editing a field, field type cannot be changed, so some extra handling below is needed
        if ($fieldId) {
            $fieldType = $form->getField('type');

            if ($fieldType->value == 'subform') {
                // Only Use In subform should not be available for subform field type, so we remove it
                $form->removeField('only_use_in_subform');
            } else {
                // Field type could not be changed, so remove showon attribute to avoid js errors
                $form->setFieldAttribute('only_use_in_subform', 'showon', '');
            }
        }

        return $form;
    }

    /**
     * Setting the value for the given field id, context and item id.
     *
     * @param   string  $fieldId  The field ID.
     * @param   string  $itemId   The ID of the item.
     * @param   string  $value    The value.
     *
     * @return  boolean
     *
     * @since   3.7.0
     */
    public function setFieldValue($fieldId, $itemId, $value)
    {
        $field  = $this->getItem($fieldId);
        $params = $field->params;

        if (is_array($params)) {
            $params = new Registry($params);
        }

        // Don't save the value when the user is not authorized to change it
        if (!$field || !FieldsHelper::canEditFieldValue($field)) {
            return false;
        }

        $needsDelete = false;
        $needsInsert = false;
        $needsUpdate = false;

        $oldValue = $this->getFieldValue($fieldId, $itemId);
        $value    = (array) $value;

        if ($oldValue === null) {
            // No records available, doing normal insert
            $needsInsert = true;
        } elseif (count($value) == 1 && count((array) $oldValue) == 1) {
            // Only a single row value update can be done when not empty
            $needsUpdate = is_array($value[0]) ? count($value[0]) : strlen($value[0]);
            $needsDelete = !$needsUpdate;
        } else {
            // Multiple values, we need to purge the data and do a new
            // insert
            $needsDelete = true;
            $needsInsert = true;
        }

        if ($needsDelete) {
            $fieldId = (int) $fieldId;

            // Deleting the existing record as it is a reset
            $query = $this->getDatabase()->getQuery(true);

            $query->delete($query->quoteName('#__fields_values'))
                ->where($query->quoteName('field_id') . ' = :fieldid')
                ->where($query->quoteName('item_id') . ' = :itemid')
                ->bind(':fieldid', $fieldId, ParameterType::INTEGER)
                ->bind(':itemid', $itemId);

            $this->getDatabase()->setQuery($query)->execute();
        }

        if ($needsInsert) {
            $newObj = new \stdClass();

            $newObj->field_id = (int) $fieldId;
            $newObj->item_id  = $itemId;

            foreach ($value as $v) {
                $newObj->value = $v;

                $this->getDatabase()->insertObject('#__fields_values', $newObj);
            }
        }

        if ($needsUpdate) {
            $updateObj = new \stdClass();

            $updateObj->field_id = (int) $fieldId;
            $updateObj->item_id  = $itemId;
            $updateObj->value    = reset($value);

            $this->getDatabase()->updateObject('#__fields_values', $updateObj, array('field_id', 'item_id'));
        }

        $this->valueCache = array();
        FieldsHelper::clearFieldsCache();

        return true;
    }

    /**
     * Returning the value for the given field id, context and item id.
     *
     * @param   string  $fieldId  The field ID.
     * @param   string  $itemId   The ID of the item.
     *
     * @return  NULL|string
     *
     * @since  3.7.0
     */
    public function getFieldValue($fieldId, $itemId)
    {
        $values = $this->getFieldValues(array($fieldId), $itemId);

        if (array_key_exists($fieldId, $values)) {
            return $values[$fieldId];
        }

        return null;
    }

    /**
     * Returning the values for the given field ids, context and item id.
     *
     * @param   array   $fieldIds  The field Ids.
     * @param   string  $itemId    The ID of the item.
     *
     * @return  NULL|array
     *
     * @since  3.7.0
     */
    public function getFieldValues(array $fieldIds, $itemId)
    {
        if (!$fieldIds) {
            return array();
        }

        // Create a unique key for the cache
        $key = md5(serialize($fieldIds) . $itemId);

        // Fill the cache when it doesn't exist
        if (!array_key_exists($key, $this->valueCache)) {
            // Create the query
            $query = $this->getDatabase()->getQuery(true);

            $query->select($query->quoteName(['field_id', 'value']))
                ->from($query->quoteName('#__fields_values'))
                ->whereIn($query->quoteName('field_id'), ArrayHelper::toInteger($fieldIds))
                ->where($query->quoteName('item_id') . ' = :itemid')
                ->bind(':itemid', $itemId);

            // Fetch the row from the database
            $rows = $this->getDatabase()->setQuery($query)->loadObjectList();

            $data = array();

            // Fill the data container from the database rows
            foreach ($rows as $row) {
                // If there are multiple values for a field, create an array
                if (array_key_exists($row->field_id, $data)) {
                    // Transform it to an array
                    if (!is_array($data[$row->field_id])) {
                        $data[$row->field_id] = array($data[$row->field_id]);
                    }

                    // Set the value in the array
                    $data[$row->field_id][] = $row->value;

                    // Go to the next row, otherwise the value gets overwritten in the data container
                    continue;
                }

                // Set the value
                $data[$row->field_id] = $row->value;
            }

            // Assign it to the internal cache
            $this->valueCache[$key] = $data;
        }

        // Return the value from the cache
        return $this->valueCache[$key];
    }

    /**
     * Cleaning up the values for the given item on the context.
     *
     * @param   string  $context  The context.
     * @param   string  $itemId   The Item ID.
     *
     * @return  void
     *
     * @since   3.7.0
     */
    public function cleanupValues($context, $itemId)
    {
        // Delete with inner join is not possible so we need to do a subquery
        $fieldsQuery = $this->getDatabase()->getQuery(true);
        $fieldsQuery->select($fieldsQuery->quoteName('id'))
            ->from($fieldsQuery->quoteName('#__fields'))
            ->where($fieldsQuery->quoteName('context') . ' = :context');

        $query = $this->getDatabase()->getQuery(true);

        $query->delete($query->quoteName('#__fields_values'))
            ->where($query->quoteName('field_id') . ' IN (' . $fieldsQuery . ')')
            ->where($query->quoteName('item_id') . ' = :itemid')
            ->bind(':itemid', $itemId)
            ->bind(':context', $context);

        $this->getDatabase()->setQuery($query)->execute();
    }

    /**
     * Method to test whether a record can be deleted.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
     *
     * @since   3.7.0
     */
    protected function canDelete($record)
    {
        if (empty($record->id) || $record->state != -2) {
            return false;
        }

        $parts = FieldsHelper::extract($record->context);

        return Factory::getUser()->authorise('core.delete', $parts[0] . '.field.' . (int) $record->id);
    }

    /**
     * Method to test whether a record can have its state changed.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the
     *                   component.
     *
     * @since   3.7.0
     */
    protected function canEditState($record)
    {
        $user  = Factory::getUser();
        $parts = FieldsHelper::extract($record->context);

        // Check for existing field.
        if (!empty($record->id)) {
            return $user->authorise('core.edit.state', $parts[0] . '.field.' . (int) $record->id);
        }

        return $user->authorise('core.edit.state', $parts[0]);
    }

    /**
     * Stock method to auto-populate the model state.
     *
     * @return  void
     *
     * @since   3.7.0
     */
    protected function populateState()
    {
        $app = Factory::getApplication();

        // Load the User state.
        $pk = $app->input->getInt('id');
        $this->setState($this->getName() . '.id', $pk);

        $context = $app->input->get('context', 'com_content.article');
        $this->setState('field.context', $context);
        $parts = FieldsHelper::extract($context);

        // Extract the component name
        $this->setState('field.component', $parts[0]);

        // Extract the optional section name
        $this->setState('field.section', (count($parts) > 1) ? $parts[1] : null);

        // Load the parameters.
        $params = ComponentHelper::getParams('com_fields');
        $this->setState('params', $params);
    }

    /**
     * A protected method to get a set of ordering conditions.
     *
     * @param   Table  $table  A Table object.
     *
     * @return  array  An array of conditions to add to ordering queries.
     *
     * @since   3.7.0
     */
    protected function getReorderConditions($table)
    {
        $db = $this->getDatabase();

        return [
            $db->quoteName('context') . ' = ' . $db->quote($table->context),
        ];
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  array  The default data is an empty array.
     *
     * @since   3.7.0
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $app  = Factory::getApplication();
        $data = $app->getUserState('com_fields.edit.field.data', array());

        if (empty($data)) {
            $data = $this->getItem();

            // Pre-select some filters (Status, Language, Access) in edit form
            // if those have been selected in Category Manager
            if (!$data->id) {
                // Check for which context the Category Manager is used and
                // get selected fields
                $filters = (array) $app->getUserState('com_fields.fields.filter');

                $data->set('state', $app->input->getInt('state', ((isset($filters['state']) && $filters['state'] !== '') ? $filters['state'] : null)));
                $data->set('language', $app->input->getString('language', (!empty($filters['language']) ? $filters['language'] : null)));
                $data->set('group_id', $app->input->getString('group_id', (!empty($filters['group_id']) ? $filters['group_id'] : null)));
                $data->set(
                    'access',
                    $app->input->getInt('access', (!empty($filters['access']) ? $filters['access'] : $app->get('access')))
                );

                // Set the type if available from the request
                $data->set('type', $app->input->getWord('type', $this->state->get('field.type', $data->get('type'))));
            }

            if ($data->label && !isset($data->params['label'])) {
                $data->params['label'] = $data->label;
            }
        }

        $this->preprocessData('com_fields.field', $data);

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
        if (!Factory::getUser()->authorise('core.admin', 'com_fields')) {
            if (isset($data['rules'])) {
                unset($data['rules']);
            }
        }

        return parent::validate($form, $data, $group);
    }

    /**
     * Method to allow derived classes to preprocess the form.
     *
     * @param   Form    $form   A Form object.
     * @param   mixed   $data   The data expected for the form.
     * @param   string  $group  The name of the plugin group to import (defaults to "content").
     *
     * @return  void
     *
     * @since   3.7.0
     *
     * @throws  \Exception if there is an error in the form event.
     *
     * @see     \Joomla\CMS\Form\FormField
     */
    protected function preprocessForm(Form $form, $data, $group = 'content')
    {
        $component  = $this->state->get('field.component');
        $section    = $this->state->get('field.section');
        $dataObject = $data;

        if (is_array($dataObject)) {
            $dataObject = (object) $dataObject;
        }

        if (isset($dataObject->type)) {
            $form->setFieldAttribute('type', 'component', $component);

            // Not allowed to change the type of an existing record
            if ($dataObject->id) {
                $form->setFieldAttribute('type', 'readonly', 'true');
            }

            // Allow to override the default value label and description through the plugin
            $key = 'PLG_FIELDS_' . strtoupper($dataObject->type) . '_DEFAULT_VALUE_LABEL';

            if (Factory::getLanguage()->hasKey($key)) {
                $form->setFieldAttribute('default_value', 'label', $key);
            }

            $key = 'PLG_FIELDS_' . strtoupper($dataObject->type) . '_DEFAULT_VALUE_DESC';

            if (Factory::getLanguage()->hasKey($key)) {
                $form->setFieldAttribute('default_value', 'description', $key);
            }

            // Remove placeholder field on list fields
            if ($dataObject->type == 'list') {
                $form->removeField('hint', 'params');
            }
        }

        // Get the categories for this component (and optionally this section, if available)
        $cat = (
            function () use ($component, $section) {
                // Get the CategoryService for this component
                $componentObject = $this->bootComponent($component);

                if (!$componentObject instanceof CategoryServiceInterface) {
                    // No CategoryService -> no categories
                    return null;
                }

                $cat = null;

                // Try to get the categories for this component and section
                try {
                    $cat = $componentObject->getCategory([], $section ?: '');
                } catch (SectionNotFoundException $e) {
                    // Not found for component and section -> Now try once more without the section, so only component
                    try {
                        $cat = $componentObject->getCategory();
                    } catch (SectionNotFoundException $e) {
                        // If we haven't found it now, return (no categories available for this component)
                        return null;
                    }
                }

                // So we found categories for at least the component, return them
                return $cat;
            }
        )();

        // If we found categories, and if the root category has children, set them in the form
        if ($cat && $cat->get('root')->hasChildren()) {
            $form->setFieldAttribute('assigned_cat_ids', 'extension', $cat->getExtension());
        } else {
            // Else remove the field from the form
            $form->removeField('assigned_cat_ids');
        }

        $form->setFieldAttribute('type', 'component', $component);
        $form->setFieldAttribute('group_id', 'context', $this->state->get('field.context'));
        $form->setFieldAttribute('rules', 'component', $component);

        // Looking in the component forms folder for a specific section forms file
        $path = Path::clean(JPATH_ADMINISTRATOR . '/components/' . $component . '/forms/fields/' . $section . '.xml');

        if (!file_exists($path)) {
            // Looking in the component models/forms folder for a specific section forms file
            $path = Path::clean(JPATH_ADMINISTRATOR . '/components/' . $component . '/models/forms/fields/' . $section . '.xml');
        }

        if (file_exists($path)) {
            $lang = Factory::getLanguage();
            $lang->load($component, JPATH_BASE);
            $lang->load($component, JPATH_BASE . '/components/' . $component);

            if (!$form->loadFile($path, false)) {
                throw new \Exception(Text::_('JERROR_LOADFILE_FAILED'));
            }
        }

        // Trigger the default form events.
        parent::preprocessForm($form, $data, $group);
    }

    /**
     * Clean the cache
     *
     * @param   string   $group     The cache group
     * @param   integer  $clientId  @deprecated   5.0   No longer used.
     *
     * @return  void
     *
     * @since   3.7.0
     */
    protected function cleanCache($group = null, $clientId = 0)
    {
        $context = Factory::getApplication()->input->get('context');

        switch ($context) {
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
                parent::cleanCache($context);
                break;
        }
    }

    /**
     * Batch copy fields to a new group.
     *
     * @param   integer  $value     The new value matching a fields group.
     * @param   array    $pks       An array of row IDs.
     * @param   array    $contexts  An array of item contexts.
     *
     * @return  array|boolean  new IDs if successful, false otherwise and internal error is set.
     *
     * @since   3.7.0
     */
    protected function batchCopy($value, $pks, $contexts)
    {
        // Set the variables
        $user      = Factory::getUser();
        $table     = $this->getTable();
        $newIds    = array();
        $component = $this->state->get('filter.component');
        $value     = (int) $value;

        foreach ($pks as $pk) {
            if ($user->authorise('core.create', $component . '.fieldgroup.' . $value)) {
                $table->reset();
                $table->load($pk);

                $table->group_id = $value;

                // Reset the ID because we are making a copy
                $table->id = 0;

                // Unpublish the new field
                $table->state = 0;

                if (!$table->store()) {
                    $this->setError($table->getError());

                    return false;
                }

                // Get the new item ID
                $newId = $table->get('id');

                // Add the new ID to the array
                $newIds[$pk] = $newId;
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
     * Batch move fields to a new group.
     *
     * @param   integer  $value     The new value matching a fields group.
     * @param   array    $pks       An array of row IDs.
     * @param   array    $contexts  An array of item contexts.
     *
     * @return  boolean  True if successful, false otherwise and internal error is set.
     *
     * @since   3.7.0
     */
    protected function batchMove($value, $pks, $contexts)
    {
        // Set the variables
        $user      = Factory::getUser();
        $table     = $this->getTable();
        $context   = explode('.', Factory::getApplication()->getUserState('com_fields.fields.context'));
        $value     = (int) $value;

        foreach ($pks as $pk) {
            if ($user->authorise('core.edit', $context[0] . '.fieldgroup.' . $value)) {
                $table->reset();
                $table->load($pk);

                $table->group_id = $value;

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
}
